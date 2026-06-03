<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="account-page">
    <div class="account-hero">
        <div>
            <p class="account-label">Aurora Theater</p>
            <h1>Accountenoverzicht</h1>
            <p>Beheer alle geregistreerde accounts in het systeem.</p>
        </div>
    </div>

    <?php // null = databasefout; lege array = geen accounts gevonden; anders tabel tonen. ?>
    <?php if ($data['accounts'] === null) : ?>
        <?php // Unhappy scenario: database kon niet worden bereikt. ?>
        <div class="account-alert account-alert-error">
            Accounts konden niet worden geladen. Probeer opnieuw.
        </div>
    <?php elseif (count($data['accounts']) === 0) : ?>
        <p class="account-text">Er zijn nog geen accounts gevonden.</p>
    <?php else : ?>
        <section class="account-card">
            <h2>Alle accounts</h2>
            <table class="account-table">
                <thead>
                    <tr>
                        <th>Naam</th>
                        <th>E-mail</th>
                        <th>Rol</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['accounts'] as $account) : ?>
                        <?php
                        // Naam opbouwen zonder extra spaties bij leeg tussenvoegsel.
                        $naamDelen = array_filter([$account->voornaam, $account->tussenvoegsel, $account->achternaam]);
                        $naam = htmlspecialchars(implode(' ', $naamDelen));
                        ?>
                        <tr>
                            <td><?= $naam; ?></td>
                            <td><?= htmlspecialchars($account->email); ?></td>
                            <td><?= htmlspecialchars($account->rol); ?></td>
                            <td><?= htmlspecialchars($account->status); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endif; ?>
</div>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
