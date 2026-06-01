<?php
// Script om e-maildomein te vervangen voor gebruikers in de Accounts-tabel.
// Gebruik (CLI):
// php app/scripts/replace_email_domain.php --from=fitforfun.nl --to=admin.nl --mode=preview
// php app/scripts/replace_email_domain.php --from=fitforfun.nl --to=admin.nl --mode=apply
// Of voor één gebruiker:
// php app/scripts/replace_email_domain.php --old=j.jenever@fitforfun.nl --new=j.jenever@admin.nl --mode=apply

// Veiligheidsmaatregelen:
// - Maakt een CSV-backup met affected rows voordat update wordt uitgevoerd.
// - Draait updates in een transactie en rolt terug bij fouten (bijv. duplicate email).

// Lees CLI-argumenten
$options = [];
foreach ($argv as $arg) {
    if (strpos($arg, '--') === 0) {
        $parts = explode('=', substr($arg, 2), 2);
        $key = $parts[0];
        $value = isset($parts[1]) ? $parts[1] : true;
        $options[$key] = $value;
    }
}

// Laad config
require_once __DIR__ . '/../config/config.php';

$mode = isset($options['mode']) ? $options['mode'] : 'preview';
$from = isset($options['from']) ? $options['from'] : null;
$to = isset($options['to']) ? $options['to'] : null;
$old = isset($options['old']) ? $options['old'] : null;
$new = isset($options['new']) ? $options['new'] : null;
$force = isset($options['force']);

if ($old !== null && $new !== null) {
    // Single-email mode
    $action = 'single';
} elseif ($from !== null && $to !== null) {
    $action = 'domain';
} else {
    echo "Fout: je moet ofwel --old=<email> en --new=<email> opgeven, of --from=<domein> en --to=<domein>.\n";
    echo "Voorbeeld: php app/scripts/replace_email_domain.php --from=fitforfun.nl --to=admin.nl --mode=preview\n";
    exit(1);
}

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME_ACCOUNTS . ';charset=utf8mb4';
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    echo "Kan geen verbinding maken met database: " . $e->getMessage() . "\n";
    exit(1);
}

$affected = [];
if ($action === 'single') {
    // Zoek de oude e-mail
    $stmt = $pdo->prepare('SELECT Id, Email FROM Accounts WHERE Email = :email LIMIT 1');
    $stmt->execute([':email' => $old]);
    $row = $stmt->fetch();
    if (!$row) {
        echo "Geen account gevonden met e-mail: $old\n";
        exit(1);
    }
    // Controleer of new al bestaat
    $stmt2 = $pdo->prepare('SELECT Id FROM Accounts WHERE Email = :email LIMIT 1');
    $stmt2->execute([':email' => $new]);
    $exists = $stmt2->fetch();
    if ($exists && !$force) {
        echo "Doel-e-mail $new bestaat al voor account ID: " . $exists['Id'] . ". Gebruik --force om te forceren (mogelijk faalt vanwege unique constraint).\n";
        exit(1);
    }
    $affected[] = ['id' => $row['Id'], 'old' => $row['Email'], 'new' => $new];
} else {
    // domain mode: zoek alle e-mails met @from
    $like = '%@' . $from;
    $stmt = $pdo->prepare('SELECT Id, Email FROM Accounts WHERE Email LIKE :like');
    $stmt->execute([':like' => $like]);
    $rows = $stmt->fetchAll();
    if (!$rows) {
        echo "Geen accounts gevonden met domein @$from\n";
        exit(0);
    }
    foreach ($rows as $r) {
        $local = strstr($r['Email'], '@', true);
        if ($local === false) $local = $r['Email'];
        $newEmail = $local . '@' . $to;
        $affected[] = ['id' => $r['Id'], 'old' => $r['Email'], 'new' => $newEmail];
    }
}

// Toon preview
echo "Gevonden " . count($affected) . " account(s):\n";
foreach ($affected as $a) {
    echo "ID: {$a['id']} | {$a['old']} -> {$a['new']}\n";
}

// Maak backup CSV
$timestamp = date('Ymd_His');
$backupFile = __DIR__ . "/../db/email_backup_{$timestamp}.csv";
$fp = fopen($backupFile, 'w');
if ($fp) {
    fputcsv($fp, ['id', 'old', 'new']);
    foreach ($affected as $a) fputcsv($fp, [$a['id'], $a['old'], $a['new']]);
    fclose($fp);
    echo "Backup weggeschreven naar: $backupFile\n";
} else {
    echo "Kon geen backupbestand aanmaken.\n";
}

if ($mode !== 'apply') {
    echo "Preview-modus: geen wijzigingen doorgevoerd. Gebruik --mode=apply om de aanpassingen door te voeren.\n";
    exit(0);
}

// Uitvoeren
try {
    $pdo->beginTransaction();
    foreach ($affected as $a) {
        // Controleer opnieuw of doel bestaat
        $stmtCheck = $pdo->prepare('SELECT Id FROM Accounts WHERE Email = :email LIMIT 1');
        $stmtCheck->execute([':email' => $a['new']]);
        $exists = $stmtCheck->fetch();
        if ($exists && $exists['Id'] != $a['id']) {
            throw new Exception("Doel-email {$a['new']} bestaat al (ID: {$exists['Id']}); update zou unique constraint schenden.");
        }
        $stmtUpd = $pdo->prepare('UPDATE Accounts SET Email = :new WHERE Id = :id');
        $stmtUpd->execute([':new' => $a['new'], ':id' => $a['id']]);
    }
    $pdo->commit();
    echo "Wijzigingen succesvol doorgevoerd voor " . count($affected) . " account(s).\n";
    echo "Backup: $backupFile\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Fout tijdens update: " . $e->getMessage() . "\n";
    echo "Geen wijzigingen doorgevoerd. Controleer het backupbestand: $backupFile\n";
    exit(1);
}

exit(0);

