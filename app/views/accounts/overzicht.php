<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="account-page">
    <div class="account-hero">
        <div>
            <p class="account-label">Medewerkers</p>
            <h1>Accountenoverzicht</h1>
            <p>Alle geregistreerde accounts in het systeem.</p>
        </div>
        <div class="account-hero-actions">
            <a href="<?= URLROOT; ?>AccountsController/toevoegen" class="btn btn-primary">+ Nieuw account</a>
        </div>
    </div>

    <?php // Succesmelding tonen na het aanmaken van een account; daarna direct verwijderen uit de sessie. ?>
    <?php if (!empty($_SESSION['overzicht_melding'])) : ?>
        <div class="account-alert account-alert-succes"><?= htmlspecialchars($_SESSION['overzicht_melding']); ?></div>
        <?php unset($_SESSION['overzicht_melding']); ?>
    <?php endif; ?>

    <?php // null = databasefout; lege array = geen accounts; anders tabel tonen. ?>
    <?php if ($data['accounts'] === null) : ?>
        <div class="account-alert account-alert-error">
            Accounts konden niet worden geladen. Probeer het opnieuw.
        </div>
    <?php elseif (count($data['accounts']) === 0) : ?>
        <p class="account-text">Er zijn nog geen accounts gevonden.</p>
    <?php else : ?>
        <?php
        // Statistieken berekenen uit de opgehaalde data; zo hoeven we geen extra queries te doen.
        $totaal       = count($data['accounts']);
        $aantalLeden  = 0;
        $aantalMedew  = 0;
        $aantalActief = 0;
        foreach ($data['accounts'] as $a) {
            if (strtolower($a->rol) === 'medewerker') $aantalMedew++;
            else $aantalLeden++;
            if (strtolower($a->status) === 'actief') $aantalActief++;
        }
        ?>

        <div class="overzicht-stats">
            <div class="overzicht-stat">
                <span class="overzicht-stat-getal"><?= $totaal; ?></span>
                <span class="overzicht-stat-label">Totaal</span>
            </div>
            <div class="overzicht-stat">
                <span class="overzicht-stat-getal"><?= $aantalLeden; ?></span>
                <span class="overzicht-stat-label">Leden</span>
            </div>
            <div class="overzicht-stat">
                <span class="overzicht-stat-getal"><?= $aantalMedew; ?></span>
                <span class="overzicht-stat-label">Medewerkers</span>
            </div>
            <div class="overzicht-stat">
                <span class="overzicht-stat-getal"><?= $aantalActief; ?></span>
                <span class="overzicht-stat-label">Actief</span>
            </div>
        </div>

        <section class="account-card">
            <?php // Filterknopen en zoekbalk; filtering gebeurt client-side via JavaScript. ?>
            <div class="overzicht-toolbar">
                <div class="overzicht-filters">
                    <button class="overzicht-filter-knop actief" data-filter="alle">Alle</button>
                    <button class="overzicht-filter-knop" data-filter="lid">Leden</button>
                    <button class="overzicht-filter-knop" data-filter="medewerker">Medewerkers</button>
                </div>
                <input
                    type="search"
                    class="overzicht-zoek"
                    id="overzichtZoek"
                    placeholder="Zoeken op naam of e-mail..."
                    aria-label="Zoeken in accounts"
                >
            </div>

            <div class="overzicht-tabel-wrapper">
                <table class="overzicht-tabel" id="overzichtTabel">
                    <thead>
                        <tr>
                            <th>Naam</th>
                            <th>E-mail</th>
                            <th>Telefoon</th>
                            <th>Rol</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['accounts'] as $account) : ?>
                            <?php
                            // Naam samenvoegen zonder extra spaties bij een leeg tussenvoegsel.
                            $naamDelen = array_filter([$account->voornaam, $account->tussenvoegsel, $account->achternaam]);
                            $naam      = htmlspecialchars(implode(' ', $naamDelen));
                            // Kleine letters voor data-attributen zodat de JS-filter hoofdletterongevoelig werkt.
                            $rol       = strtolower($account->rol);
                            $status    = strtolower($account->status);
                            ?>
                            <?php // data-rol en data-zoek worden door de JavaScript-filter gebruikt. ?>
                            <tr data-rol="<?= htmlspecialchars($rol); ?>" data-zoek="<?= strtolower($naam . ' ' . $account->email); ?>">
                                <td><?= $naam; ?></td>
                                <td><?= htmlspecialchars($account->email); ?></td>
                                <td><?= htmlspecialchars($account->telefoon ?? '—'); ?></td>
                                <td>
                                    <span class="overzicht-badge overzicht-badge-rol-<?= htmlspecialchars($rol); ?>">
                                        <?= htmlspecialchars(ucfirst($account->rol)); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="overzicht-badge overzicht-badge-status-<?= htmlspecialchars($status); ?>">
                                        <?= htmlspecialchars(ucfirst($account->status)); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php // Lege toestand: zichtbaar als zoeken of filteren geen resultaten oplevert. ?>
            <p class="overzicht-geen" id="overzichtGeen" style="display:none;">Geen accounts gevonden voor deze zoekopdracht.</p>
        </section>
    <?php endif; ?>
</div>

<script>
(function () {
    var zoek         = document.getElementById('overzichtZoek');
    var rijen        = document.querySelectorAll('#overzichtTabel tbody tr');
    var geen         = document.getElementById('overzichtGeen');
    var knoppen      = document.querySelectorAll('.overzicht-filter-knop');
    var huidigFilter = 'alle';

    // Verbergt rijen die niet overeenkomen met het actieve filter én de zoekterm.
    function filterTabel() {
        var zoekterm  = zoek ? zoek.value.toLowerCase().trim() : '';
        var zichtbaar = 0;

        rijen.forEach(function (rij) {
            var matchRol  = huidigFilter === 'alle' || rij.dataset.rol === huidigFilter;
            var matchZoek = zoekterm === '' || rij.dataset.zoek.indexOf(zoekterm) !== -1;
            var tonen     = matchRol && matchZoek;
            rij.style.display = tonen ? '' : 'none';
            if (tonen) zichtbaar++;
        });

        // Lege-toestand tonen als er na filtering geen rijen zichtbaar zijn.
        if (geen) geen.style.display = zichtbaar === 0 ? '' : 'none';
    }

    // Filterknop activeert het bijbehorende rolfilter en herberekent de tabel.
    knoppen.forEach(function (knop) {
        knop.addEventListener('click', function () {
            knoppen.forEach(function (k) { k.classList.remove('actief'); });
            knop.classList.add('actief');
            huidigFilter = knop.dataset.filter;
            filterTabel();
        });
    });

    // Zoekbalk filtert live bij elke toetsaanslag.
    if (zoek) zoek.addEventListener('input', filterTabel);
})();
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
