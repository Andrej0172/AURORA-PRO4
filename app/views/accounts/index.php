<?php require_once APPROOT . '/views/includes/header.php'; ?>

<?php if (!empty($data['foutmelding'])) : ?>
<div class="account-page">
    <div class="account-alert account-alert-error"><?= htmlspecialchars($data['foutmelding']); ?></div>
</div>
<?php require_once APPROOT . '/views/includes/footer.php'; ?>
<?php return; ?>
<?php endif; ?>

<?php
$voornaam = isset($data['account']['voornaam']) ? $data['account']['voornaam'] : '';
$email = isset($data['account']['email']) ? $data['account']['email'] : '';
$rol = isset($data['account']['rol']) ? $data['account']['rol'] : '';
$volledigeNaam = trim(isset($data['volledigeNaam']) ? $data['volledigeNaam'] : $voornaam);
if ($volledigeNaam === '') {
    $volledigeNaam = 'Mijn account';
}
?>

<div class="account-page">
    <div class="account-hero">
        <div>
            <p class="account-label">Aurora Theater</p>
            <h1><?= htmlspecialchars($volledigeNaam); ?></h1>
            <p>Hier zie je de basisgegevens van je account en kun je snel in- of uitloggen.</p>
        </div>
        <div class="account-hero-actions">
            <a href="<?= URLROOT; ?>AccountsController/instellingen" class="btn btn-primary">Instellingen</a>
            <a href="<?= URLROOT; ?>AccountsController/logout" class="btn btn-secundair">Uitloggen</a>
        </div>
    </div>

    <div class="account-grid">
        <section class="account-card">
            <h2>Accountgegevens</h2>
            <table class="account-table">
                <tr>
                    <th>Naam</th>
                    <td><?= htmlspecialchars($volledigeNaam); ?></td>
                </tr>
                <tr>
                    <th>E-mail</th>
                    <td><?= htmlspecialchars($email); ?></td>
                </tr>
                <tr>
                    <th>Rol</th>
                    <td><?= htmlspecialchars($rol !== '' ? $rol : 'lid'); ?></td>
                </tr>
                <tr>
                    <th>Account-ID</th>
                        <td><?= (int)(isset($data['account']['id']) ? $data['account']['id'] : 0); ?></td>
                </tr>
            </table>
        </section>

        <section class="account-card">
            <h2>Snelle acties</h2>
            <p class="account-text">Gebruik onderstaande knoppen om direct terug te gaan naar het theater of je account af te sluiten.</p>
            <div class="account-actions">
                <a href="<?= URLROOT; ?>Homepages/index" class="btn btn-primary">Terug naar home</a>
                <a href="<?= URLROOT; ?>AccountsController/logout" class="btn btn-secundair">Uitloggen</a>
            </div>
        </section>
    </div>
</div>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>

