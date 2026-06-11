<?php
// Datalaag voor accounts, lidmaatschappen, reserveringen en remember tokens.
class Account
{
	private $pdo;

	public function __construct()
	{
		$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME_ACCOUNTS . ';charset=utf8mb4';

		try {
			$this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
				PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES   => false
			]);

			$this->ensureSchema();
		} catch (PDOException $e) {
			$this->pdo = null;
		}
	}

	private function ensureSchema()
	{
		// Kleine migraties hier houden lokale setups bruikbaar zonder losse migratiescripts.
		if ($this->pdo === null) {
			return;
		}

		try {
			$statement = $this->pdo->query("SHOW COLUMNS FROM Accounts LIKE 'Tussenvoegsel'");
			$kolom = $statement ? $statement->fetch() : false;

			if ($kolom === false) {
				$this->pdo->exec("ALTER TABLE Accounts ADD COLUMN Tussenvoegsel VARCHAR(20) NULL DEFAULT NULL AFTER Voornaam");
			}
		} catch (PDOException $e) {
			// databasegebruiker heeft mogelijk geen ALTER-rechten; laat de app verder proberen
		}

		try {
			$statement = $this->pdo->query("SHOW COLUMNS FROM Accounts LIKE 'ProfielFotoMime'");
			$kolom = $statement ? $statement->fetch() : false;

			if ($kolom === false) {
				// Bestaande foto's (bestandsnamen) wissen en kolom omzetten naar BLOB
				$this->pdo->exec("UPDATE Accounts SET ProfielFoto = NULL");
				$this->pdo->exec("ALTER TABLE Accounts MODIFY COLUMN ProfielFoto MEDIUMBLOB NULL DEFAULT NULL");
				$this->pdo->exec("ALTER TABLE Accounts ADD COLUMN ProfielFotoMime VARCHAR(50) NULL DEFAULT NULL AFTER ProfielFoto");
			}
		} catch (PDOException $e) {
			// databasegebruiker heeft mogelijk geen ALTER-rechten; laat de app verder proberen
		}
	}

	public function getAccountById($accountId)
	{
		if ($this->pdo === null) {
			return null;
		}

		try {
			$sql = 'SELECT  A.Id             AS id,
							A.Voornaam       AS voornaam,
							A.Tussenvoegsel  AS tussenvoegsel,
							A.Achternaam     AS achternaam,
							A.Email          AS email,
							A.Telefoon       AS telefoon,
							A.Geboortedatum  AS geboortedatum,
							A.LidmaatschapId AS lidmaatschap_id,
							A.StartDatum     AS start_datum,
							A.EindDatum      AS eind_datum,
							A.Status         AS status,
							A.Rol            AS rol,
							A.ProfielFoto    AS profiel_foto,
							A.AangemaaktOp   AS aangemaakt_op,
							L.Naam           AS lidmaatschap,
							L.Beschrijving   AS lid_beschrijving,
							L.PrijsPerMaand  AS prijs_per_maand,
							L.Toegang        AS toegang
					FROM    Accounts AS A
					LEFT JOIN Lidmaatschappen AS L
							ON A.LidmaatschapId = L.Id
					WHERE   A.Id = :accountId';

			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':accountId', (int)$accountId, PDO::PARAM_INT);
			$statement->execute();

			$result = $statement->fetch();
			return $result === false ? null : $result;
		} catch (PDOException $e) {
			return null;
		}
	}

	public function getAccountHeaderById($accountId)
	{
		if ($this->pdo === null) {
			return null;
		}

		try {
			$sql = 'SELECT  Id         AS id,
							Voornaam   AS voornaam,
							Tussenvoegsel AS tussenvoegsel,
							Achternaam AS achternaam,
							Rol        AS rol
					FROM    Accounts
					WHERE   Id = :accountId';

			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':accountId', (int)$accountId, PDO::PARAM_INT);
			$statement->execute();

			$result = $statement->fetch();
			return $result === false ? null : $result;
		} catch (PDOException $e) {
			return null;
		}
	}

	public function getAantalReserveringen($lesId, $datum)
	{
		if ($this->pdo === null) {
			return 0;
		}

		try {
			$sql = "SELECT COUNT(*) FROM Reserveringen WHERE LesId = :lesId AND Datum = :datum AND Status != 'Geannuleerd'";
			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':lesId', (int)$lesId, PDO::PARAM_INT);
			$statement->bindValue(':datum', $datum, PDO::PARAM_STR);
			$statement->execute();
			return (int)$statement->fetchColumn();
		} catch (PDOException $e) {
			return 0;
		}
	}

	public function heeftAlGereserveerd($accountId, $lesId, $datum)
	{
		if ($this->pdo === null) {
			return false;
		}

		try {
			$sql = "SELECT COUNT(*) FROM Reserveringen WHERE AccountId = :accountId AND LesId = :lesId AND Datum = :datum AND Status != 'Geannuleerd'";
			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':accountId', (int)$accountId, PDO::PARAM_INT);
			$statement->bindValue(':lesId', (int)$lesId, PDO::PARAM_INT);
			$statement->bindValue(':datum', $datum, PDO::PARAM_STR);
			$statement->execute();
			return (int)$statement->fetchColumn() > 0;
		} catch (PDOException $e) {
			return false;
		}
	}

	public function getGereserveerdeLesIds($accountId)
	{
		if ($this->pdo === null) {
			return [];
		}

		try {
			$sql = "SELECT LesId FROM Reserveringen WHERE AccountId = :accountId AND Status != 'Geannuleerd'";
			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':accountId', (int)$accountId, PDO::PARAM_INT);
			$statement->execute();
			return array_column($statement->fetchAll(PDO::FETCH_ASSOC), 'LesId');
		} catch (PDOException $e) {
			return [];
		}
	}

	public function updateVerlopenReserveringen($accountId)
	{
		// Automatisch onderhoud: oude reserveringen verschuiven naar 'Verlopen'.
		if ($this->pdo === null) {
			return;
		}

		try {
			$sql = "UPDATE Reserveringen
					SET    Status = 'Verlopen'
					WHERE  AccountId = :accountId
					AND    Datum < CURDATE()
					AND    Status NOT IN ('Geannuleerd', 'Verlopen')";
			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':accountId', (int)$accountId, PDO::PARAM_INT);
			$statement->execute();
		} catch (PDOException $e) {
			// negeer
		}
	}

	// Haal het aantal reserveringen per les op voor een gegeven jaar en maand.
	public function getReserveringenPerPeriode($jaar, $maand)
	{
		if ($this->pdo === null) {
			return [];
		}

		try {
			// Groepeer per les en tel alleen niet-geannuleerde reserveringen mee.
			$sql = "SELECT  L.Naam        AS LesNaam,
							L.Emoji,
							L.Locatie,
							COUNT(R.Id)   AS AantalReserveringen
					FROM    Reserveringen AS R
					JOIN    " . DB_NAME . ".Lessen AS L ON R.LesId = L.Id
					WHERE   YEAR(R.Datum)  = :jaar
					AND     MONTH(R.Datum) = :maand
					AND     R.Status != 'Geannuleerd'
					GROUP BY R.LesId, L.Naam, L.Emoji, L.Locatie
					ORDER BY AantalReserveringen DESC, L.Naam ASC";

			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':jaar',  (int)$jaar,  PDO::PARAM_INT);
			$statement->bindValue(':maand', (int)$maand, PDO::PARAM_INT);
			$statement->execute();

			return $statement->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			return [];
		}
	}

	// Haal alle reserveringen op van alle leden inclusief les- en accountgegevens.
	public function getAlleReserveringen()
	{
		if ($this->pdo === null) {
			return [];
		}

		try {
			$sql = "SELECT  R.Id,
							R.AccountId,
							R.Datum,
							R.Status,
							A.Voornaam,
							A.Tussenvoegsel,
							A.Achternaam,
							L.Naam        AS LesNaam,
							L.Emoji,
							TIME_FORMAT(L.Tijdstip, '%H:%i') AS Tijdstip,
							L.Locatie
					FROM    Reserveringen AS R
					JOIN    Accounts AS A ON R.AccountId = A.Id
					JOIN    " . DB_NAME . ".Lessen AS L ON R.LesId = L.Id
					ORDER BY R.Datum DESC, L.Tijdstip DESC";

			$statement = $this->pdo->prepare($sql);
			$statement->execute();

			return $statement->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			return [];
		}
	}

	// Haal alle reserveringen op van één specifiek lid inclusief lesgegevens.
	public function getReserveringenVanLid($accountId)
	{
		// Lessen staan in de andere database, daarom expliciet met DB_NAME joinen.
		if ($this->pdo === null) {
			return [];
		}

		try {
			$sql = "SELECT  R.Id,
							R.LesId,
							R.Datum,
							R.Status,
							L.Naam,
							L.Emoji,
							TIME_FORMAT(L.Tijdstip, '%H:%i') AS Tijdstip,
							L.Locatie
					FROM    Reserveringen AS R
					JOIN    " . DB_NAME . ".Lessen AS L
							ON R.LesId = L.Id
					WHERE   R.AccountId = :accountId
					ORDER BY R.Datum DESC,
							 L.Tijdstip DESC";

			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':accountId', (int)$accountId, PDO::PARAM_INT);
			$statement->execute();

			return $statement->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			return [];
		}
	}

	// Haal één reservering op inclusief lesgegevens op basis van het reserverings-id.
	public function getReserveringById($id)
	{
		if ($this->pdo === null) {
			return null;
		}

		try {
			$sql = "SELECT  R.Id,
							R.AccountId,
							R.LesId,
							R.Datum,
							R.Status,
							L.Naam,
							L.Emoji,
							TIME_FORMAT(L.Tijdstip, '%H:%i') AS Tijdstip,
							L.Locatie
					FROM    Reserveringen AS R
					JOIN    " . DB_NAME . ".Lessen AS L
							ON R.LesId = L.Id
					WHERE   R.Id = :id";

			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':id', (int)$id, PDO::PARAM_INT);
			$statement->execute();

			$result = $statement->fetch(PDO::FETCH_OBJ);
			return $result === false ? null : $result;
		} catch (PDOException $e) {
			return null;
		}
	}

	// Verwijder een reservering permanent op basis van het id.
	public function deleteReservering($id)
	{
		if ($this->pdo === null) {
			return false;
		}

		try {
			$sql = "DELETE FROM Reserveringen WHERE Id = :id";
			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':id', (int)$id, PDO::PARAM_INT);
			$statement->execute();
			return $statement->rowCount() > 0;
		} catch (PDOException $e) {
			return false;
		}
	}

	// Werk de status en datum van een bestaande reservering bij.
	public function updateReservering($id, $status, $datum)
	{
		if ($this->pdo === null) {
			return false;
		}

		// Controleer of de opgegeven status een toegestane waarde heeft.
		$toegestaan = ['Bevestigd', 'Geannuleerd', 'Wachtlijst', 'Verlopen'];
		if (!in_array($status, $toegestaan, true)) {
			return false;
		}

		try {
			$sql = "UPDATE Reserveringen SET Status = :status, Datum = :datum WHERE Id = :id";
			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':status', $status, PDO::PARAM_STR);
			$statement->bindValue(':datum', $datum, PDO::PARAM_STR);
			$statement->bindValue(':id', (int)$id, PDO::PARAM_INT);
			$statement->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	public function annuleerReservering($accountId, $lesId, $datum)
	{
		if ($this->pdo === null) {
			return false;
		}

		try {
			$sql = "UPDATE Reserveringen SET Status = 'Geannuleerd' WHERE AccountId = :accountId AND LesId = :lesId AND Datum = :datum AND Status != 'Geannuleerd'";
			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':accountId', (int)$accountId, PDO::PARAM_INT);
			$statement->bindValue(':lesId', (int)$lesId, PDO::PARAM_INT);
			$statement->bindValue(':datum', $datum, PDO::PARAM_STR);
			$statement->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	public function reserveerLes($accountId, $lesId, $datum)
	{
		if ($this->pdo === null) {
			return false;
		}

		try {
			$sql = "INSERT INTO Reserveringen (AccountId, LesId, Datum, Status)
					VALUES (:accountId, :lesId, :datum, 'Bevestigd')";
			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':accountId', (int)$accountId, PDO::PARAM_INT);
			$statement->bindValue(':lesId', (int)$lesId, PDO::PARAM_INT);
			$statement->bindValue(':datum', $datum, PDO::PARAM_STR);
			$statement->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	public function getReserveringenByAccountId($accountId)
	{
		// Lessen staan in de andere database, daarom expliciet met DB_NAME joinen.
		if ($this->pdo === null) {
			return [];
		}

		try {
			$sql = "SELECT  R.LesId,
							R.Datum,
							R.Status,
							L.Naam,
							L.Emoji,
							TIME_FORMAT(L.Tijdstip, '%H:%i') AS Tijdstip,
							L.Locatie,
							L.Dag
					FROM    Reserveringen AS R
					JOIN    " . DB_NAME . ".Lessen AS L
							ON R.LesId = L.Id
					WHERE   R.AccountId = :accountId
					ORDER BY R.Datum ASC,
							 L.Tijdstip ASC";

			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':accountId', (int)$accountId, PDO::PARAM_INT);
			$statement->execute();

			return $statement->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			return [];
		}
	}

	// Haal account op voor login check
	public function getAccountByEmail($email)
	{
		if ($this->pdo === null) {
			return null;
		}

		try {
			$sql = 'SELECT Id AS id, Voornaam AS voornaam, Tussenvoegsel AS tussenvoegsel, Achternaam AS achternaam,
						   Email AS email, Wachtwoord AS wachtwoord, Rol AS rol
					FROM   Accounts
					WHERE  Email = :email';

			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':email', $email, PDO::PARAM_STR);
			$statement->execute();

			$result = $statement->fetch();
			return $result === false ? null : $result;
		} catch (PDOException $e) {
			return null;
		}
	}

	// Sla remember token op voor blijf ingelogd functie
	public function saveRememberToken($accountId, $hashedToken)
	{
		if ($this->pdo === null) {
			return;
		}

		try {
			$sql = 'INSERT INTO RememberTokens (AccountId, Token, VerlooptOp)
					VALUES (:accountId, :token, DATE_ADD(NOW(), INTERVAL 30 DAY))';

			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':accountId', $accountId, PDO::PARAM_INT);
			$statement->bindValue(':token', $hashedToken, PDO::PARAM_STR);
			$statement->execute();
		} catch (PDOException $e) {
			// tabel bestaat mogelijk nog niet
		}
	}

	// Check remember token en haal account op
	public function getAccountByRememberToken($hashedToken)
	{
		// Alleen nog geldige tokens accepteren; nieuwste token heeft voorrang.
		if ($this->pdo === null) {
			return null;
		}

		try {
			$sql = 'SELECT A.Id AS id, A.Voornaam AS voornaam, A.Achternaam AS achternaam, A.Rol AS rol
					FROM   RememberTokens AS RT
					JOIN   Accounts AS A ON RT.AccountId = A.Id
					WHERE  RT.Token = :token
					  AND  RT.VerlooptOp > NOW()
					ORDER BY RT.AangemaaktOp DESC
					LIMIT 1';

			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':token', $hashedToken, PDO::PARAM_STR);
			$statement->execute();

			$result = $statement->fetch();
			return $result === false ? null : $result;
		} catch (PDOException $e) {
			return null;
		}
	}

	public function deleteRememberToken($hashedToken)
	{
		if ($this->pdo === null) {
			return;
		}

		try {
			$sql = 'DELETE FROM RememberTokens WHERE Token = :token';
			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':token', $hashedToken, PDO::PARAM_STR);
			$statement->execute();
		} catch (PDOException $e) {
			// negeer
		}
	}

	// Upload profielfoto als binary data
	public function updateProfielFoto($accountId, $binaryData, $mime)
	{
		if ($this->pdo === null) {
			return false;
		}

		try {
			$sql = 'UPDATE Accounts SET ProfielFoto = :foto, ProfielFotoMime = :mime WHERE Id = :id';
			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':foto', $binaryData, PDO::PARAM_LOB);
			$statement->bindValue(':mime', $mime, PDO::PARAM_STR);
			$statement->bindValue(':id', (int)$accountId, PDO::PARAM_INT);
			$statement->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	public function getProfielFoto($accountId)
	{
		if ($this->pdo === null) {
			return null;
		}

		try {
			$sql = 'SELECT ProfielFoto, ProfielFotoMime FROM Accounts WHERE Id = :id AND ProfielFoto IS NOT NULL';
			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':id', (int)$accountId, PDO::PARAM_INT);
			$statement->execute();

			$result = $statement->fetch(PDO::FETCH_ASSOC);
			if ($result === false || $result['ProfielFoto'] === null) {
				return null;
			}

			return [
				'data' => $result['ProfielFoto'],
				'mime' => isset($result['ProfielFotoMime']) && $result['ProfielFotoMime'] !== '' ? $result['ProfielFotoMime'] : 'image/jpeg'
			];
		} catch (PDOException $e) {
			return null;
		}
	}

	public function checkEnUpdateStatus($accountId)
	{
		// Zet alleen naar verlopen als einddatum echt verstreken is en account niet al opgezegd is.
		if ($this->pdo === null) {
			return;
		}

		try {
			$sql = "UPDATE Accounts
					SET    Status = 'Verlopen'
					WHERE  Id = :id
					  AND  EindDatum IS NOT NULL
					  AND  EindDatum < CURDATE()
					  AND  Status NOT IN ('Verlopen', 'Opgezegd')";
			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':id', (int)$accountId, PDO::PARAM_INT);
			$statement->execute();
		} catch (PDOException $e) {
			// negeer
		}
	}

	public function updateEindDatum($accountId, $eindDatum)
	{
		// Status hangt samen met einddatum: leeg/toekomst = actief, verleden = verlopen.
		if ($this->pdo === null) {
			return false;
		}

		try {
			$sql = "UPDATE Accounts
					SET    EindDatum = :eindDatum,
						   Status    = CASE
							   WHEN :eindDatum2 IS NULL THEN 'Actief'
							   WHEN :eindDatum3 >= CURDATE() THEN 'Actief'
							   ELSE 'Verlopen'
						   END
					WHERE  Id = :id
					  AND  Status NOT IN ('Opgezegd')";
			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':eindDatum',  $eindDatum ?: null, PDO::PARAM_STR);
			$statement->bindValue(':eindDatum2', $eindDatum ?: null, PDO::PARAM_STR);
			$statement->bindValue(':eindDatum3', $eindDatum ?: null, PDO::PARAM_STR);
			$statement->bindValue(':id', (int)$accountId, PDO::PARAM_INT);
			$statement->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	public function updateStatus($accountId, $status)
	{
		if ($this->pdo === null) {
			return false;
		}

		$toegestaan = ['Actief', 'Gepauzeerd', 'Opgezegd'];
		if (!in_array($status, $toegestaan, true)) {
			return false;
		}

		try {
			$sql = 'UPDATE Accounts SET Status = :status WHERE Id = :id';
			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':status', $status, PDO::PARAM_STR);
			$statement->bindValue(':id', (int)$accountId, PDO::PARAM_INT);
			$statement->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	public function emailExists($email)
	{
		if ($this->pdo === null) {
			return false;
		}

		try {
			$sql = 'SELECT COUNT(*) FROM Accounts WHERE Email = :email';
			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':email', $email, PDO::PARAM_STR);
			$statement->execute();
			return (int)$statement->fetchColumn() > 0;
		} catch (PDOException $e) {
			return false;
		}
	}

	public function emailExistsForOther($email, $excludeAccountId)
	{
		if ($this->pdo === null) {
			return false;
		}

		try {
			$sql = 'SELECT COUNT(*) FROM Accounts WHERE Email = :email AND Id <> :id';
			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':email', $email, PDO::PARAM_STR);
			$statement->bindValue(':id', (int)$excludeAccountId, PDO::PARAM_INT);
			$statement->execute();
			return (int)$statement->fetchColumn() > 0;
		} catch (PDOException $e) {
			return false;
		}
	}

	public function registerAccount($data)
	{
		// Nieuwe registraties starten altijd als actief lid vanaf vandaag.
		if ($this->pdo === null) {
			return false;
		}

		try {
			$sql = 'INSERT INTO Accounts (Voornaam, Achternaam, Email, Telefoon, Geboortedatum, LidmaatschapId, StartDatum, Status, Rol, Wachtwoord)
					VALUES (:voornaam, :achternaam, :email, :telefoon, :geboortedatum, :lidmaatschapId, CURDATE(), \'Actief\', \'lid\', :wachtwoord)';

			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':voornaam', $data['voornaam'], PDO::PARAM_STR);
			$statement->bindValue(':achternaam', $data['achternaam'], PDO::PARAM_STR);
			$statement->bindValue(':email', $data['email'], PDO::PARAM_STR);
			$statement->bindValue(':telefoon', $data['telefoon'] ?: null, PDO::PARAM_STR);
			$statement->bindValue(':geboortedatum', $data['geboortedatum'], PDO::PARAM_STR);
			$statement->bindValue(':lidmaatschapId', (int)$data['lidmaatschap_id'], PDO::PARAM_INT);
			$statement->bindValue(':wachtwoord', password_hash($data['wachtwoord'], PASSWORD_DEFAULT), PDO::PARAM_STR);
			$statement->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	public function getAllAccounts()
	{
		// Gebruikt door accountbeheer-overzicht voor medewerkers.
		if ($this->pdo === null) {
			return null;
		}

		try {
			$sql = "SELECT  A.Id             AS id,
							A.Voornaam       AS voornaam,
							A.Tussenvoegsel  AS tussenvoegsel,
							A.Achternaam     AS achternaam,
							A.Email          AS email,
							A.Telefoon       AS telefoon,
							A.Geboortedatum  AS geboortedatum,
							A.StartDatum     AS start_datum,
							A.EindDatum      AS eind_datum,
							A.Status         AS status,
							A.Rol            AS rol,
							A.ProfielFoto    AS profiel_foto,
							L.Naam           AS lidmaatschap
					FROM    Accounts AS A
					LEFT JOIN Lidmaatschappen AS L
							ON A.LidmaatschapId = L.Id
					ORDER BY A.Id ASC";

			$statement = $this->pdo->prepare($sql);
			$statement->execute();
			return $statement->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			return null;
		}
	}

	// Maakt een nieuw account aan namens een medewerker.
	// Geeft true terug bij succes, een string bij een UNIQUE-conflict, of false bij een andere databasefout.
	public function createAccountByMedewerker($data)
	{
		if ($this->pdo === null) {
			return false;
		}

		// Rol mag alleen 'lid' of 'medewerker' zijn; andere waarden weigeren we vóór de query.
		$toegestaneRollen = ['lid', 'medewerker'];
		if (!in_array($data['rol'], $toegestaneRollen, true)) {
			return false;
		}

		try {
			// Nieuw account wordt direct op Actief gezet met de huidige datum als startdatum.
			$sql = 'INSERT INTO Accounts (Voornaam, Tussenvoegsel, Achternaam, Email, Telefoon, Geboortedatum, LidmaatschapId, StartDatum, Status, Rol, Wachtwoord)
					VALUES (:voornaam, :tussenvoegsel, :achternaam, :email, :telefoon, :geboortedatum, :lidmaatschapId, CURDATE(), \'Actief\', :rol, :wachtwoord)';

			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':voornaam',      $data['voornaam'],                   PDO::PARAM_STR);
			// Leeg tussenvoegsel opslaan als NULL zodat de kolom consistent blijft.
			$statement->bindValue(':tussenvoegsel', $data['tussenvoegsel'] ?: null,      PDO::PARAM_STR);
			$statement->bindValue(':achternaam',    $data['achternaam'],                 PDO::PARAM_STR);
			$statement->bindValue(':email',         $data['email'],                      PDO::PARAM_STR);
			// Leeg telefoonnummer opslaan als NULL (UNIQUE-kolom mag meerdere NULL-waarden bevatten).
			$statement->bindValue(':telefoon',      $data['telefoon'] ?: null,           PDO::PARAM_STR);
			$statement->bindValue(':geboortedatum', $data['geboortedatum'],              PDO::PARAM_STR);
			$statement->bindValue(':lidmaatschapId',(int)$data['lidmaatschap_id'],       PDO::PARAM_INT);
			$statement->bindValue(':rol',           $data['rol'],                        PDO::PARAM_STR);
			// Wachtwoord wordt gehasht met bcrypt; nooit plaintext opslaan.
			$statement->bindValue(':wachtwoord',    password_hash($data['wachtwoord'], PASSWORD_DEFAULT), PDO::PARAM_STR);
			$statement->execute();
			return true;
		} catch (PDOException $e) {
			// Errorcode 1062 = UNIQUE constraint violation.
			// We lezen de foutmelding uit om te bepalen welk veld al in gebruik is,
			// zodat de controller een specifieke melding kan tonen aan de medewerker.
			if ((int)$e->errorInfo[1] === 1062) {
				$bericht = $e->errorInfo[2];
				if (str_contains($bericht, 'Email') || str_contains($bericht, 'UQ_Accounts_Email')) {
					return 'duplicate_email';
				}
				if (str_contains($bericht, 'Telefoon') || str_contains($bericht, 'UQ_Accounts_Telefoon')) {
					return 'duplicate_telefoon';
				}
				return 'duplicate';
			}
			return false;
		}
	}

	public function getMedewerkers()
	{
		if ($this->pdo === null) {
			return [];
		}

		try {
			$sql = "SELECT  A.Id         AS id,
							A.Voornaam   AS voornaam,
							A.Tussenvoegsel AS tussenvoegsel,
							A.Achternaam AS achternaam,
							A.Email      AS email,
							A.Telefoon   AS telefoon,
							A.StartDatum AS start_datum,
							A.Status     AS status,
							A.Rol        AS rol
					FROM    Accounts AS A
					WHERE   A.Rol = 'medewerker'
					ORDER BY A.Voornaam ASC,
							 A.Achternaam ASC";

			$statement = $this->pdo->prepare($sql);
			$statement->execute();

			return $statement->fetchAll();
		} catch (PDOException $e) {
			return [];
		}
	}

	// Haal alle actieve lidmaatschappen op met volledige info
	public function getLidmaatschappenVolledig()
	{
		if ($this->pdo === null) {
			return [];
		}

		try {
			$sql = 'SELECT  Id             AS id,
							Naam           AS naam,
							Beschrijving   AS beschrijving,
							PrijsPerMaand  AS prijs_per_maand,
							Toegang        AS toegang
					FROM    Lidmaatschappen
					WHERE   Actief = 1
					ORDER BY PrijsPerMaand ASC';
			$statement = $this->pdo->prepare($sql);
			$statement->execute();
			return $statement->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			return [];
		}
	}

	public function getAllLidmaatschappen()
	{
		// Korte lijst (id + naam) voor dropdowns in formulieren.
		if ($this->pdo === null) {
			return [];
		}

		try {
			$sql = 'SELECT Id AS id, Naam AS naam FROM Lidmaatschappen WHERE Actief = 1 ORDER BY Id ASC';
			$statement = $this->pdo->prepare($sql);
			$statement->execute();
			return $statement->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			return [];
		}
	}

	public function createAccount($data)
	{
		if ($this->pdo === null) {
			return false;
		}

		try {
			$sql = 'INSERT INTO Accounts (Voornaam, Tussenvoegsel, Achternaam, Email, Telefoon, Geboortedatum, LidmaatschapId, StartDatum, EindDatum, Status, Rol, Wachtwoord)
					VALUES (:voornaam, :tussenvoegsel, :achternaam, :email, :telefoon, :geboortedatum, :lidmaatschapId, :startDatum, :eindDatum, :status, :rol, :wachtwoord)';

			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':voornaam', $data['voornaam'], PDO::PARAM_STR);
			$statement->bindValue(':tussenvoegsel', $data['tussenvoegsel'] ?: null, PDO::PARAM_STR);
			$statement->bindValue(':achternaam', $data['achternaam'], PDO::PARAM_STR);
			$statement->bindValue(':email', $data['email'], PDO::PARAM_STR);
			$statement->bindValue(':telefoon', $data['telefoon'] ?: null, PDO::PARAM_STR);
			$statement->bindValue(':geboortedatum', $data['geboortedatum'], PDO::PARAM_STR);
			$statement->bindValue(':lidmaatschapId', (int)$data['lidmaatschap_id'], PDO::PARAM_INT);
			$statement->bindValue(':startDatum', $data['start_datum'], PDO::PARAM_STR);
			$statement->bindValue(':eindDatum', $data['eind_datum'] ?: null, PDO::PARAM_STR);
			$statement->bindValue(':status', $data['status'], PDO::PARAM_STR);
			$statement->bindValue(':rol', $data['rol'], PDO::PARAM_STR);
			// Wachtwoorden worden alleen gehasht opgeslagen.
			$statement->bindValue(':wachtwoord', password_hash($data['wachtwoord'], PASSWORD_DEFAULT), PDO::PARAM_STR);
			$statement->execute();
			return (int)$this->pdo->lastInsertId();
		} catch (PDOException $e) {
			return false;
		}
	}

	public function updateAccount($accountId, $data)
	{
		if ($this->pdo === null) {
			return false;
		}

		try {
			$withPassword = !empty($data['wachtwoord']);
			// Geen nieuw wachtwoord ingevuld? Dan laten we het huidige hashveld met rust.

			$sql = 'UPDATE Accounts
					SET Voornaam = :voornaam,
						Tussenvoegsel = :tussenvoegsel,
						Achternaam = :achternaam,
						Email = :email,
						Telefoon = :telefoon,
						Geboortedatum = :geboortedatum,
						LidmaatschapId = :lidmaatschapId,
						StartDatum = :startDatum,
						EindDatum = :eindDatum,
						Status = :status,
						Rol = :rol';

			if ($withPassword) {
				$sql .= ', Wachtwoord = :wachtwoord';
			}

			$sql .= ' WHERE Id = :id';

			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':voornaam', $data['voornaam'], PDO::PARAM_STR);
			$statement->bindValue(':tussenvoegsel', $data['tussenvoegsel'] ?: null, PDO::PARAM_STR);
			$statement->bindValue(':achternaam', $data['achternaam'], PDO::PARAM_STR);
			$statement->bindValue(':email', $data['email'], PDO::PARAM_STR);
			$statement->bindValue(':telefoon', $data['telefoon'] ?: null, PDO::PARAM_STR);
			$statement->bindValue(':geboortedatum', $data['geboortedatum'], PDO::PARAM_STR);
			$statement->bindValue(':lidmaatschapId', (int)$data['lidmaatschap_id'], PDO::PARAM_INT);
			$statement->bindValue(':startDatum', $data['start_datum'], PDO::PARAM_STR);
			$statement->bindValue(':eindDatum', $data['eind_datum'] ?: null, PDO::PARAM_STR);
			$statement->bindValue(':status', $data['status'], PDO::PARAM_STR);
			$statement->bindValue(':rol', $data['rol'], PDO::PARAM_STR);
			$statement->bindValue(':id', (int)$accountId, PDO::PARAM_INT);

			if ($withPassword) {
				$statement->bindValue(':wachtwoord', password_hash($data['wachtwoord'], PASSWORD_DEFAULT), PDO::PARAM_STR);
			}

			$statement->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	public function deleteAccount($accountId)
	{
		if ($this->pdo === null) {
			return false;
		}

		try {
			// Eerst afhankelijke data opruimen om FK-fouten te voorkomen.
			$sql = 'DELETE FROM Reserveringen WHERE AccountId = :id';
			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':id', (int)$accountId, PDO::PARAM_INT);
			$statement->execute();

			$sql = 'DELETE FROM RememberTokens WHERE AccountId = :id';
			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':id', (int)$accountId, PDO::PARAM_INT);
			$statement->execute();

			$sql = 'DELETE FROM Accounts WHERE Id = :id';
			$statement = $this->pdo->prepare($sql);
			$statement->bindValue(':id', (int)$accountId, PDO::PARAM_INT);
			$statement->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}
}



