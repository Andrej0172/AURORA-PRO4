<?php
// Controller voor alles rond inloggen, registreren en accountinstellingen.
class AccountsController extends BaseController
{
    private $accountModel;

    public function __construct()
    {
        $this->accountModel = $this->model('Account');
    }

    public function index()
    {
        // Alleen ingelogde gebruikers mogen hun accountoverzicht zien.
        if (!isset($_SESSION['account_id'])) {
            header('Location: ' . URLROOT . 'AccountsController/login');
            exit;
        }

        $accountId = (int)$_SESSION['account_id'];
        $this->accountModel->checkEnUpdateStatus($accountId);
        $account = $this->accountModel->getAccountById($accountId);

        if ($account === null) {
            header('Location: ' . URLROOT . 'AccountsController/login');
            exit;
        }

        $geboortedatum = !empty($account['geboortedatum']) ? new DateTime($account['geboortedatum']) : null;
        $leeftijd      = $geboortedatum ? $geboortedatum->diff(new DateTime())->y : 0;

        $startDatum = !empty($account['start_datum']) ? new DateTime($account['start_datum']) : null;
        $lidDuur    = $startDatum ? $startDatum->diff(new DateTime()) : null;
        $lidMaanden = $lidDuur ? ($lidDuur->y * 12) + $lidDuur->m : 0;

        $this->view('accounts/index', [
            'title'         => 'Mijn Account - FitForFun',
            'documentTitle' => 'FitForFun - Account',
            'activePage'    => '',
            'styles'        => ['accounts.css'],
            'accountId'     => $accountId,
            'headerAccount' => [
                'voornaam'   => $account['voornaam'],
                'achternaam' => $account['achternaam'],
                'rol'        => $account['rol']
            ],
            'account'       => $account,
            'leeftijd'      => $leeftijd,
            'lidMaanden'    => $lidMaanden
        ]);
    }

    // Annuleer reservering
    public function annuleren()
    {
        // Check of gebruiker ingelogd is
        if (!isset($_SESSION['account_id'])) {
            header('Location: ' . URLROOT . 'AccountsController/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . 'AccountsController/reserveringen');
            exit;
        }

        $accountId = (int)$_SESSION['account_id'];
        $lesId     = isset($_POST['les_id']) ? (int)$_POST['les_id'] : 0;
        $datum     = isset($_POST['datum']) ? trim($_POST['datum']) : '';

        if ($lesId <= 0 || $datum === '') {
            header('Location: ' . URLROOT . 'AccountsController/reserveringen');
            exit;
        }

        if ($this->accountModel->annuleerReservering($accountId, $lesId, $datum)) {
            $_SESSION['reservering_melding']     = 'Reservering succesvol geannuleerd.';
            $_SESSION['reservering_melding_fout'] = false;
        } else {
            $_SESSION['reservering_melding']     = 'Annuleren mislukt. Probeer het opnieuw.';
            $_SESSION['reservering_melding_fout'] = true;
        }

        header('Location: ' . URLROOT . 'AccountsController/reserveringen');
        exit;
    }

    public function reserveringen()
    {
        // Laad altijd eerst accountheader; die wordt ook in de layout gebruikt.
        if (!isset($_SESSION['account_id'])) {
            header('Location: ' . URLROOT . 'AccountsController/login');
            exit;
        }

        $accountId = (int)$_SESSION['account_id'];
        $account = $this->accountModel->getAccountHeaderById($accountId);

        if ($account === null) {
            header('Location: ' . URLROOT . 'AccountsController/login');
            exit;
        }

        $this->accountModel->updateVerlopenReserveringen($accountId);
        $reserveringen = $this->accountModel->getReserveringenByAccountId($accountId);

        $this->view('accounts/reserveringen', [
            'title'         => 'Mijn reserveringen - FitForFun',
            'documentTitle' => 'FitForFun - Reserveringen',
            'activePage'    => '',
            'styles'        => ['accounts.css', 'reserveringen.css'],
            'accountId'     => $accountId,
            'headerAccount' => $account,
            'account'       => $account,
            'reserveringen' => $reserveringen
        ]);
    }

    // Login pagina en verwerking
    public function login()
    {
        // POST = inlogpoging verwerken, GET = leeg formulier tonen.
        // Login validatie
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email      = trim(isset($_POST['email']) ? $_POST['email'] : '');
            $wachtwoord = isset($_POST['wachtwoord']) ? $_POST['wachtwoord'] : '';

            $account = $this->accountModel->getAccountByEmail($email);

            // Check wachtwoord en zet sessie
            if ($account !== null && isset($account['wachtwoord']) && password_verify($wachtwoord, $account['wachtwoord'])) {
                $_SESSION['account_id'] = (int)$account['id'];
                $_SESSION['voornaam']   = $account['voornaam'];
                $_SESSION['tussenvoegsel'] = isset($account['tussenvoegsel']) ? $account['tussenvoegsel'] : '';
                $_SESSION['achternaam'] = $account['achternaam'];
                $_SESSION['rol']        = $account['rol'];

                // Remember token opslaan
                if (!empty($_POST['onthoud_mij'])) {
                    $token = bin2hex(random_bytes(32));
                    $this->accountModel->saveRememberToken((int)$account['id'], hash('sha256', $token));
                    setcookie('remember_token', $token, [
                        'expires'  => time() + (30 * 24 * 60 * 60),
                        'path'     => '/',
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]);
                }

                header('Location: ' . URLROOT . 'AccountsController/index');
                exit;
            }

            $this->view('accounts/login', [
                'title'         => 'Inloggen - FitForFun',
                'documentTitle' => 'FitForFun - Inloggen',
                'activePage'    => '',
                'styles'        => ['accounts.css'],
                'foutmelding'   => 'Onjuist e-mailadres of wachtwoord.',
                'email'         => $email
            ]);
            return;
        }

        $this->view('accounts/login', [
            'title'         => 'Inloggen - FitForFun',
            'documentTitle' => 'FitForFun - Inloggen',
            'activePage'    => '',
            'styles'        => ['accounts.css'],
            'foutmelding'   => '',
            'email'         => ''
        ]);
    }

    public function abonnement()
    {
        // Alle abonnement-acties lopen via dezelfde endpoint met een actie-naam.
        if (!isset($_SESSION['account_id'])) {
            header('Location: ' . URLROOT . 'AccountsController/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . 'AccountsController/instellingen');
            exit;
        }

        $accountId = (int)$_SESSION['account_id'];
        $actie     = isset($_POST['actie']) ? $_POST['actie'] : '';

        $account = $this->accountModel->getAccountById($accountId);
        if ($account === null || $account['status'] === 'Opgezegd') {
            header('Location: ' . URLROOT . 'AccountsController/instellingen');
            exit;
        }

        if ($actie === 'instellen') {
            $eindDatum = isset($_POST['eind_datum']) ? trim($_POST['eind_datum']) : '';
            if ($eindDatum === '' || !strtotime($eindDatum)) {
                $_SESSION['instellingen_melding'] = 'Ongeldige datum opgegeven.';
                $_SESSION['instellingen_fout']    = true;
            } else {
                $this->accountModel->updateEindDatum($accountId, $eindDatum);
                $_SESSION['instellingen_melding'] = 'Einddatum bijgewerkt.';
            }
        } elseif ($actie === 'verlengen') {
            $maanden  = isset($_POST['verlengen_maanden']) ? (int)$_POST['verlengen_maanden'] : 0;
            $toegestaan = [1, 3, 6, 12];
            if (!in_array($maanden, $toegestaan, true)) {
                $_SESSION['instellingen_melding'] = 'Ongeldige verlengperiode.';
                $_SESSION['instellingen_fout']    = true;
            } else {
                $huidig = $account['eind_datum'];
                if ($huidig && strtotime($huidig) > time()) {
                    $basis = new DateTime($huidig);
                } else {
                    $basis = new DateTime();
                }
                $basis->modify("+{$maanden} months");
                $this->accountModel->updateEindDatum($accountId, $basis->format('Y-m-d'));
                $_SESSION['instellingen_melding'] = 'Abonnement verlengd tot ' . $basis->format('d-m-Y') . '.';
            }
        } elseif ($actie === 'doorlopend') {
            $this->accountModel->updateEindDatum($accountId, null);
            $_SESSION['instellingen_melding'] = 'Abonnement ingesteld op doorlopend.';
        } elseif ($actie === 'pauzeren' && $account['status'] === 'Actief') {
            $this->accountModel->updateStatus($accountId, 'Gepauzeerd');
            $_SESSION['instellingen_melding'] = 'Abonnement gepauzeerd.';
        } elseif ($actie === 'heractiveren' && $account['status'] === 'Gepauzeerd') {
            $this->accountModel->updateStatus($accountId, 'Actief');
            $_SESSION['instellingen_melding'] = 'Abonnement hervat.';
        } elseif ($actie === 'opzeggen' && $account['status'] !== 'Opgezegd') {
            $this->accountModel->updateStatus($accountId, 'Opgezegd');
            $_SESSION['instellingen_melding'] = 'Abonnement opgezegd.';
        }

        header('Location: ' . URLROOT . 'AccountsController/instellingen');
        exit;
    }

    // Registratie nieuwe leden
    public function aanmelden()
    {
        // Lidmaatschappen zijn nodig om de select-box op het formulier te vullen.
        $lidmaatschappen = $this->accountModel->getAllLidmaatschappen();

        // Registratie validatie
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $voornaam       = trim(isset($_POST['voornaam']) ? $_POST['voornaam'] : '');
            $achternaam     = trim(isset($_POST['achternaam']) ? $_POST['achternaam'] : '');
            $email          = trim(isset($_POST['email']) ? $_POST['email'] : '');
            $telefoon       = trim(isset($_POST['telefoon']) ? $_POST['telefoon'] : '');
            $geboortedatum  = trim(isset($_POST['geboortedatum']) ? $_POST['geboortedatum'] : '');
            $lidmaatschapId = isset($_POST['lidmaatschap_id']) ? (int)$_POST['lidmaatschap_id'] : 0;
            $wachtwoord     = isset($_POST['wachtwoord']) ? $_POST['wachtwoord'] : '';
            $bevestig       = isset($_POST['bevestig_wachtwoord']) ? $_POST['bevestig_wachtwoord'] : '';

            $fout = '';

            // Validatie in vaste volgorde, zodat de gebruiker 1 duidelijke fout tegelijk ziet.
            if ($voornaam === '' || $achternaam === '' || $email === '' || $geboortedatum === '' || $lidmaatschapId === 0 || $wachtwoord === '') {
                $fout = 'Vul alle verplichte velden in.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $fout = 'Voer een geldig e-mailadres in.';
            } elseif ($wachtwoord !== $bevestig) {
                $fout = 'De wachtwoorden komen niet overeen.';
            } elseif (strlen($wachtwoord) < 6) {
                $fout = 'Het wachtwoord moet minimaal 6 tekens bevatten.';
            } elseif ($this->accountModel->emailExists($email)) {
                $fout = 'Dit e-mailadres is al in gebruik.';
            }

            if ($fout === '') {
                // Registratie pas afronden nadat alle checks groen zijn.
                $succes = $this->accountModel->registerAccount([
                    'voornaam'        => $voornaam,
                    'achternaam'      => $achternaam,
                    'email'           => $email,
                    'telefoon'        => $telefoon,
                    'geboortedatum'   => $geboortedatum,
                    'lidmaatschap_id' => $lidmaatschapId,
                    'wachtwoord'      => $wachtwoord
                ]);

                if ($succes) {
                    $_SESSION['aanmeld_melding'] = 'Account aangemaakt! Je kunt nu inloggen.';
                    header('Location: ' . URLROOT . 'AccountsController/login');
                    exit;
                }

                $fout = 'Er is iets misgegaan. Probeer het opnieuw.';
            }

            $this->view('accounts/aanmelden', [
                'title'          => 'Aanmelden - FitForFun',
                'documentTitle'  => 'FitForFun - Aanmelden',
                'activePage'     => '',
                'styles'         => ['accounts.css'],
                'foutmelding'    => $fout,
                'lidmaatschappen' => $lidmaatschappen,
                'invoer'         => compact('voornaam', 'achternaam', 'email', 'telefoon', 'geboortedatum', 'lidmaatschapId')
            ]);
            return;
        }

        $this->view('accounts/aanmelden', [
            'title'          => 'Aanmelden - FitForFun',
            'documentTitle'  => 'FitForFun - Aanmelden',
            'activePage'     => '',
            'styles'         => ['accounts.css'],
            'foutmelding'    => '',
            'lidmaatschappen' => $lidmaatschappen,
            'invoer'         => []
        ]);
    }

    public function instellingen()
    {
        // Instellingen zijn persoonlijk; daarom altijd op sessieaccount laden.
        if (!isset($_SESSION['account_id'])) {
            header('Location: ' . URLROOT . 'AccountsController/login');
            exit;
        }

        $account = $this->accountModel->getAccountById((int)$_SESSION['account_id']);

        if ($account === null) {
            header('Location: ' . URLROOT . 'AccountsController/login');
            exit;
        }

        $isFout = !empty($_SESSION['instellingen_fout']);
        $this->view('accounts/instellingen', [
            'title'         => 'Instellingen - FitForFun',
            'documentTitle' => 'FitForFun - Instellingen',
            'activePage'    => '',
            'styles'        => ['accounts.css'],
            'account'       => $account,
            'melding'       => !$isFout ? ($_SESSION['instellingen_melding'] ?? '') : '',
            'fout'          => $isFout  ? ($_SESSION['instellingen_melding'] ?? '') : ''
        ]);

        unset($_SESSION['instellingen_melding'], $_SESSION['instellingen_fout']);
    }

    public function uploadFoto()
    {
        // Upload foutafhandeling blijft in deze method, opslag zit in het model.
        if (!isset($_SESSION['account_id'])) {
            header('Location: ' . URLROOT . 'AccountsController/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['profielfoto']['name'])) {
            header('Location: ' . URLROOT . 'AccountsController/instellingen');
            exit;
        }

        $accountId = (int)$_SESSION['account_id'];
        $bestand   = $_FILES['profielfoto'];

        $toegestaan = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fout = '';

        if ($bestand['error'] !== UPLOAD_ERR_OK) {
            $fout = 'Er ging iets mis bij het uploaden.';
        } elseif (!in_array($bestand['type'], $toegestaan, true)) {
            $fout = 'Alleen JPG, PNG, GIF en WEBP bestanden zijn toegestaan.';
        } elseif ($bestand['size'] > 5 * 1024 * 1024) {
            $fout = 'Het bestand mag maximaal 5 MB zijn.';
        }

        if ($fout !== '') {
            $account = $this->accountModel->getAccountById($accountId);
            $this->view('accounts/instellingen', [
                'title'         => 'Instellingen - FitForFun',
                'documentTitle' => 'FitForFun - Instellingen',
                'activePage'    => '',
                'styles'        => ['accounts.css'],
                'account'       => $account,
                'melding'       => '',
                'fout'          => $fout
            ]);
            return;
        }

        $binaryData = file_get_contents($bestand['tmp_name']);
        if ($binaryData === false) {
            $account = $this->accountModel->getAccountById($accountId);
            $this->view('accounts/instellingen', [
                'title'         => 'Instellingen - FitForFun',
                'documentTitle' => 'FitForFun - Instellingen',
                'activePage'    => '',
                'styles'        => ['accounts.css'],
                'account'       => $account,
                'melding'       => '',
                'fout'          => 'Kon het bestand niet lezen.'
            ]);
            return;
        }

        $this->accountModel->updateProfielFoto($accountId, $binaryData, $bestand['type']);
        $_SESSION['instellingen_melding'] = 'Profielfoto succesvol geüpload.';
        header('Location: ' . URLROOT . 'AccountsController/instellingen');
        exit;
    }

    public function foto($id = 0)
    {
        // Geef raw image-data terug zodat <img src="..."> direct werkt.
        $accountId = (int)$id;
        if ($accountId <= 0) {
            http_response_code(404);
            exit;
        }

        $foto = $this->accountModel->getProfielFoto($accountId);
        if ($foto === null) {
            http_response_code(404);
            exit;
        }

        $toegestaan = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        // MIME-check voorkomt dat onverwachte content als afbeelding wordt teruggegeven.
        if (!in_array($foto['mime'], $toegestaan, true)) {
            http_response_code(403);
            exit;
        }

        header('Content-Type: ' . $foto['mime']);
        header('Content-Length: ' . strlen($foto['data']));
        header('Cache-Control: public, max-age=86400');
        echo $foto['data'];
        exit;
    }

    // Uitloggen en sessie opruimen
    public function logout()
    {
        if (defined('LOGOUT_ERROR') && LOGOUT_ERROR === true) {
            header('Location: ' . URLROOT . 'Homepages/logoutError');
            exit;
        }

        // Verwijder remember token
        setcookie('remember_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        // Sessie opruimen
        $_SESSION = [];
        session_destroy();
        header('Location: ' . URLROOT . 'AccountsController/login');
        exit;
    }
}


