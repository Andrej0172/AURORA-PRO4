<?php require_once APPROOT . '/views/includes/header.php'; ?>

<?php
// Eerder ingevulde waarden ophalen zodat het formulier bij een fout niet leeg is.
$invoer = isset($data['invoer']) ? $data['invoer'] : [];

// Hulpfunctie: geeft de eerder ingevulde waarde terug, of een standaard als het veld leeg was.
function oud($invoer, $veld, $standaard = '') {
    return htmlspecialchars(isset($invoer[$veld]) ? $invoer[$veld] : $standaard);
}
?>

<div class="account-page">
    <div class="account-hero">
        <div>
            <p class="account-label">Medewerkers</p>
            <h1>Account toevoegen</h1>
            <p>Maak een nieuw account aan voor een lid of medewerker.</p>
        </div>
        <div class="account-hero-actions">
            <a href="<?= URLROOT; ?>AccountsController/overzicht" class="btn btn-secundair">Terug naar overzicht</a>
        </div>
    </div>

    <section class="account-card">
        <?php // Foutmelding tonen bij validatiefouten of een duplicate in de database. ?>
        <?php if (!empty($data['foutmelding'])) : ?>
            <div class="account-alert account-alert-error"><?= htmlspecialchars($data['foutmelding']); ?></div>
        <?php endif; ?>

        <?php // novalidate zodat de browser-validatie wordt uitgeschakeld en onze eigen foutmeldingen getoond worden. ?>
        <form action="<?= URLROOT; ?>AccountsController/toevoegen" method="POST" class="account-form" novalidate>

            <h2>Persoonlijke gegevens</h2>

            <div class="toevoegen-rij">
                <div class="form-groep">
                    <label for="voornaam">Voornaam <span class="toevoegen-verplicht">*</span></label>
                    <input type="text" id="voornaam" name="voornaam" value="<?= oud($invoer, 'voornaam'); ?>" placeholder="Jan">
                </div>
                <div class="form-groep toevoegen-tussenvoegsel">
                    <label for="tussenvoegsel">Tussenvoegsel</label>
                    <input type="text" id="tussenvoegsel" name="tussenvoegsel" value="<?= oud($invoer, 'tussenvoegsel'); ?>" placeholder="van">
                </div>
                <div class="form-groep">
                    <label for="achternaam">Achternaam <span class="toevoegen-verplicht">*</span></label>
                    <input type="text" id="achternaam" name="achternaam" value="<?= oud($invoer, 'achternaam'); ?>" placeholder="Janssen">
                </div>
            </div>

            <div class="toevoegen-rij">
                <div class="form-groep">
                    <label for="email">E-mailadres <span class="toevoegen-verplicht">*</span></label>
                    <?php // type="text" zodat de browser geen eigen popup toont; validatie gebeurt server-side. ?>
                    <input type="text" id="email" name="email" value="<?= oud($invoer, 'email'); ?>" placeholder="naam@voorbeeld.nl">
                </div>
                <div class="form-groep">
                    <label for="telefoon">Telefoonnummer <span class="toevoegen-verplicht">*</span></label>
                    <?php // Formaat: 06 gevolgd door 8 cijfers; validatie in de controller met regex. ?>
                    <input type="text" id="telefoon" name="telefoon" value="<?= oud($invoer, 'telefoon'); ?>" placeholder="0612345678">
                </div>
            </div>

            <div class="toevoegen-rij">
                <div class="form-groep">
                    <label for="geboortedatum">Geboortedatum <span class="toevoegen-verplicht">*</span></label>
                    <input type="date" id="geboortedatum" name="geboortedatum" value="<?= oud($invoer, 'geboortedatum'); ?>">
                </div>
            </div>

            <h2>Rol</h2>

            <div class="toevoegen-rij">
                <div class="form-groep">
                    <label for="rol">Rol <span class="toevoegen-verplicht">*</span></label>
                    <?php // Standaard 'lid'; de geselecteerde waarde blijft bewaard bij een fout. ?>
                    <select id="rol" name="rol">
                        <option value="lid"        <?= oud($invoer, 'rol', 'lid') === 'lid'        ? 'selected' : ''; ?>>Lid</option>
                        <option value="medewerker" <?= oud($invoer, 'rol', 'lid') === 'medewerker' ? 'selected' : ''; ?>>Medewerker</option>
                    </select>
                </div>
            </div>

            <h2>Wachtwoord</h2>

            <div class="toevoegen-rij">
                <div class="form-groep">
                    <label for="wachtwoord">Wachtwoord <span class="toevoegen-verplicht">*</span></label>
                    <div class="wachtwoord-wrapper">
                        <input type="password" id="wachtwoord" name="wachtwoord" placeholder="Minimaal 6 tekens">
                        <button type="button" class="wachtwoord-toggle" aria-label="Wachtwoord tonen" onclick="toggleWachtwoord('wachtwoord', this)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="form-groep">
                    <label for="bevestig_wachtwoord">Bevestig wachtwoord <span class="toevoegen-verplicht">*</span></label>
                    <div class="wachtwoord-wrapper">
                        <input type="password" id="bevestig_wachtwoord" name="bevestig_wachtwoord" placeholder="Herhaal wachtwoord">
                        <button type="button" class="wachtwoord-toggle" aria-label="Wachtwoord tonen" onclick="toggleWachtwoord('bevestig_wachtwoord', this)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="toevoegen-acties">
                <button type="submit" class="btn btn-primary">Account aanmaken</button>
                <a href="<?= URLROOT; ?>AccountsController/overzicht" class="btn btn-secundair">Annuleren</a>
            </div>
        </form>
    </section>
</div>

<script>
// Wisselt het wachtwoordveld tussen verborgen en zichtbaar tekst.
function toggleWachtwoord(veldId, knop) {
    var veld = document.getElementById(veldId);
    if (!veld) return;
    veld.type = veld.type === 'password' ? 'text' : 'password';
    // Knopkleur geeft visueel aan of het wachtwoord zichtbaar is.
    knop.style.color = veld.type === 'text' ? '#c43b2f' : '';
}
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
