<?php require_once APPROOT . '/views/includes/header.php'; ?>

<?php
$volledigeNaam = trim(isset($data['volledigeNaam']) ? $data['volledigeNaam'] : '');
if ($volledigeNaam === '') {
    $volledigeNaam = 'Instellingen';
}
?>

<div class="account-page">
    <div class="account-hero">
        <div>
            <p class="account-label">Accountinstellingen</p>
            <h1><?= htmlspecialchars($volledigeNaam); ?></h1>
            <p>Hier kun je je accountgegevens bekijken. De login- en logout-flow blijft hetzelfde als in de eerdere site.</p>
        </div>
        <div class="account-hero-actions">
            <a href="<?= URLROOT; ?>AccountsController/index" class="btn btn-primary">Mijn account</a>
            <a href="<?= URLROOT; ?>AccountsController/logout" class="btn btn-secundair">Uitloggen</a>
        </div>
    </div>

    <div class="account-card">
        <h2>Gegevens</h2>
        <table class="account-table">
            <tr>
                <th>Naam</th>
                <td><?= htmlspecialchars($volledigeNaam); ?></td>
            </tr>
            <tr>
                <th>E-mail</th>
                <td><?= htmlspecialchars(isset($data['account']['email']) ? $data['account']['email'] : ''); ?></td>
            </tr>
            <tr>
                <th>Rol</th>
                <td><?= htmlspecialchars(isset($data['account']['rol']) ? $data['account']['rol'] : 'lid'); ?></td>
            </tr>
        </table>
    </div>
</div>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>


