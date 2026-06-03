<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?= URLROOT ?>img/favicon.ico">
    <link rel="stylesheet" href="<?= URLROOT ?>css/style.css">
    <?php // Pagina-specifieke styles worden door controllers in $data['styles'] gezet. ?>
    <?php if (!empty($data['styles']) && is_array($data['styles'])) : ?>
        <?php foreach ($data['styles'] as $styleFile) : ?>
            <link rel="stylesheet" href="<?= URLROOT ?>css/<?= htmlspecialchars($styleFile); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <title><?= htmlspecialchars(isset($data['documentTitle']) && $data['documentTitle'] !== '' ? $data['documentTitle'] : (isset($data['title']) && $data['title'] !== '' ? $data['title'] : 'Aurora Theater')); ?></title>
</head>
<body>

<?php
$activePage    = isset($data['activePage']) ? $data['activePage'] : '';

// Sessiegegevens gebruiken we voor de profielknop rechtsboven.
$isIngelogd    = isset($_SESSION['account_id']);
$voornaam      = isset($_SESSION['voornaam']) ? $_SESSION['voornaam'] : '';
$tussenvoegsel = isset($_SESSION['tussenvoegsel']) ? $_SESSION['tussenvoegsel'] : '';
$achternaam    = isset($_SESSION['achternaam']) ? $_SESSION['achternaam'] : '';
$rol           = strtolower(isset($_SESSION['rol']) ? $_SESSION['rol'] : '');
$accountId     = isset($_SESSION['account_id']) ? (int)$_SESSION['account_id'] : 0;

$initialen = '';
if ($voornaam !== '' || $achternaam !== '') {
    $initialen = strtoupper(mb_substr($voornaam, 0, 1) . mb_substr($achternaam, 0, 1));
}

$accountUrl = URLROOT . 'AccountsController/index';
// Naam opbouwen zonder extra spaties bij leeg tussenvoegsel.
$dropdownNaam = trim(implode(' ', array_filter([$voornaam, $tussenvoegsel, $achternaam], function ($deel) {
    return $deel !== null && $deel !== '';
})));
?>

<header class="header">
    <div class="header-rij">
        <div class="logo">
            <a href="<?= URLROOT ?>Homepages/index">
                <span class="logo-fit">AURORA</span><span class="logo-for">Theater</span>
            </a>
        </div>

        <button class="menu-knop" aria-label="Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="nav">
            <?php // Hoofdmenu met actieve markering op basis van de huidige pagina. ?>
            <ul>
                <li><a href="<?= URLROOT ?>Homepages/index" <?= $activePage === 'home' ? 'class="active"' : '' ?>>Home</a></li>
                <li><a href="<?= URLROOT ?>VoorstellingenController/index" <?= $activePage === 'voorstellingen' ? 'class="active"' : '' ?>>Voorstellingen</a></li>
                <?php if ($rol === 'medewerker') : ?>
                    <li><a href="<?= URLROOT ?>MedewerkersController/index" <?= $activePage === 'medewerkers' ? 'class="active"' : '' ?>>Medewerkers</a></li>
                <?php endif; ?>
                <?php if ($isIngelogd) : ?>
                    <li><a href="#" class="nav-knop">Dashboard</a></li>
                <?php else : ?>
                    <li><a href="<?= URLROOT; ?>AccountsController/login" class="nav-knop">Inloggen</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <div class="profiel-wrapper">
            <?php // Profielmenu toont andere opties voor ingelogde en anonieme bezoekers. ?>
            <button class="profiel" title="Mijn account" id="profielKnop">
                <?php if ($isIngelogd) : ?>
                    <img src="<?= URLROOT ?>AccountsController/foto/<?= $accountId; ?>" alt="<?= htmlspecialchars($initialen); ?>" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                    <span class="profiel-initialen" style="display:none;"><?= htmlspecialchars($initialen); ?></span>
                <?php elseif ($initialen !== '') : ?>
                    <?= htmlspecialchars($initialen); ?>
                <?php else : ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="white">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                    </svg>
                <?php endif; ?>
            </button>

            <div class="profiel-dropdown" id="profielDropdown">
                <?php if ($isIngelogd) : ?>
                    <span class="dropdown-naam"><?= htmlspecialchars($dropdownNaam); ?></span>
                    <a href="<?= $accountUrl; ?>">Profiel bekijken</a>
                    <a href="<?= URLROOT; ?>AccountsController/instellingen">Instellingen</a>
                    <hr>
                    <a href="<?= URLROOT; ?>AccountsController/logout" class="dropdown-uitloggen">Uitloggen</a>
                <?php else : ?>
                    <a href="<?= URLROOT; ?>AccountsController/login">Inloggen</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<div class="page-main">




