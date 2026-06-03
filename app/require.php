<?php
// Sessie instellingen (httponly + lax voor beveiliging)
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly'  => true,
    'samesite' => 'Lax'
]);
session_start();

// Laad core bestanden
require_once 'config/config.php';
require_once 'libraries/Core.php';
require_once 'libraries/BaseController.php';
require_once 'libraries/Database.php';

// Auto-login via remember-me cookie (met try-catch voor database errors)
if (!isset($_SESSION['account_id']) && !empty($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $hashedToken = hash('sha256', $token);

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME_ACCOUNTS . ';charset=utf8mb4';
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ]);

        // Zoek geldige remember token
        $sql = 'SELECT A.Id AS id, A.Voornaam AS voornaam, A.Achternaam AS achternaam, A.Rol AS rol
                FROM   RememberTokens AS RT
                JOIN   Accounts AS A ON RT.AccountId = A.Id
                WHERE  RT.Token = :token
                  AND  RT.VerlooptOp > NOW()
                LIMIT 1';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':token', $hashedToken, PDO::PARAM_STR);
        $stmt->execute();
        $account = $stmt->fetch();

        if ($account) {
            // Zet sessie variabelen
            $_SESSION['account_id'] = (int)$account['id'];
            $_SESSION['voornaam']   = $account['voornaam'];
            $_SESSION['tussenvoegsel'] = '';
            $_SESSION['achternaam'] = $account['achternaam'];
            $_SESSION['rol']        = $account['rol'];
        } else {
            // Token ongeldig, verwijder cookie
            setcookie('remember_token', '', ['expires' => time() - 3600, 'path' => '/']);
        }
    } catch (PDOException $e) {
        // Database nog niet klaar, negeer - website werkt toch door
    }
}

// Onderhoudsmodus check
if (ONDERHOUD) {
    $data = [
        'title'         => 'Aurora Theater - Onderhoud',
        'documentTitle' => 'Aurora Theater - Onderhoud',
        'activePage'    => '',
        'styles'        => ['errors.css']
    ];
    require_once '../app/views/errors/onderhoud.php';
    exit;
}

// Start de MVC router
$init = new Core();


