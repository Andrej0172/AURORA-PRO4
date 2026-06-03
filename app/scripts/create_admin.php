<?php
// Script om een admin-account aan te maken voor Aurora Theater.
// Gebruik:
// - via CLI: php app/scripts/create_admin.php
// - via browser: bezoek /app/scripts/create_admin.php (zorg dat dit niet publiek blijft op productie)

require_once __DIR__ . '/../config/config.php';

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME_ACCOUNTS . ';charset=utf8mb4';

$email = 'j.jenever@admin.nl';
$passwordPlain = 'admin';
$voornaam = 'Jan';
$achternaam = 'Jenever';
$rol = 'medewerker'; // of 'admin' indien gewenst

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Zorg dat de Accounts-tabel bestaat
    $pdo->exec("CREATE TABLE IF NOT EXISTS `Accounts` (
        `Id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `Voornaam` VARCHAR(100) DEFAULT NULL,
        `Tussenvoegsel` VARCHAR(20) DEFAULT NULL,
        `Achternaam` VARCHAR(100) DEFAULT NULL,
        `Email` VARCHAR(255) NOT NULL UNIQUE,
        `Telefoon` VARCHAR(50) DEFAULT NULL,
        `Geboortedatum` DATE DEFAULT NULL,
        `LidmaatschapId` INT DEFAULT NULL,
        `StartDatum` DATE DEFAULT NULL,
        `EindDatum` DATE DEFAULT NULL,
        `Status` VARCHAR(30) DEFAULT 'Actief',
        `Rol` VARCHAR(50) DEFAULT 'lid',
        `ProfielFoto` MEDIUMBLOB DEFAULT NULL,
        `ProfielFotoMime` VARCHAR(50) DEFAULT NULL,
        `Wachtwoord` VARCHAR(255) DEFAULT NULL,
        `AangemaaktOp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Zorg dat de RememberTokens-tabel bestaat
    $pdo->exec("CREATE TABLE IF NOT EXISTS `RememberTokens` (
        `Id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `AccountId` INT NOT NULL,
        `Token` VARCHAR(255) NOT NULL,
        `AangemaaktOp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `VerlooptOp` DATETIME DEFAULT NULL,
        INDEX(`AccountId`),
        FOREIGN KEY (`AccountId`) REFERENCES `Accounts`(`Id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Controleer of admin al bestaat
    $stmt = $pdo->prepare('SELECT Id FROM Accounts WHERE Email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $exists = $stmt->fetch();

    if ($exists) {
        echo "Account met e-mail $email bestaat al. ID: " . $exists['Id'] . "\n";
        exit(0);
    }

    // Maak het account aan
    $hashed = password_hash($passwordPlain, PASSWORD_DEFAULT);
    $insert = $pdo->prepare('INSERT INTO Accounts (Voornaam, Achternaam, Email, Rol, StartDatum, Status, Wachtwoord) VALUES (:voornaam, :achternaam, :email, :rol, CURDATE(), "Actief", :wachtwoord)');
    $insert->execute([
        ':voornaam' => $voornaam,
        ':achternaam' => $achternaam,
        ':email' => $email,
        ':rol' => $rol,
        ':wachtwoord' => $hashed
    ]);

    $newId = (int)$pdo->lastInsertId();
    echo "Admin-account aangemaakt: $email (ID: $newId)\n";
    echo "Wachtwoord is: $passwordPlain\n";
    echo "Verwijder dit script of bescherm het zodra het account is aangemaakt.\n";

} catch (PDOException $e) {
    echo "Fout bij database-operatie: " . $e->getMessage() . "\n";
    exit(1);
}


