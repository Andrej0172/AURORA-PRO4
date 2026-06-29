<?php require_once APPROOT . '/views/includes/header.php'; ?>

<?php
$invoer  = isset($data['invoer'])  ? $data['invoer']  : [];
$account = isset($data['account']) ? $data['account'] : [];

// Geeft de POST-waarde terug bij een fout, anders de originele accountwaarde.
function oudOfAccount($invoer, $account, $veld, $standaard = '') {
    if (!empty($invoer) && array_key_exists($veld, $invoer)) {
        return htmlspecialchars($invoer[$veld]);
    }
    return htmlspecialchars(isset($account[$veld]) ? $account[$veld] : $standaard);
}
?>

<div class="account-page">
    <div class="account-hero">
        <div>
            <p class="account-label">Medewerkers</p>
            <h1>Account bijwerken</h1>
            <p>Wijzig de gegevens van dit account.</p>
        </div>
        <div class="account-hero-actions">
            <a href="<?= URLROOT; ?>AccountsController/overzicht" class="btn btn-secundair">Terug naar overzicht</a>
        </div>
    </div>

    <section class="account-card">
        <?php if (!empty($data['foutmelding'])) : ?>
            <div class="account-alert account-alert-error"><?= htmlspecialchars($data['foutmelding']); ?></div>
        <?php endif; ?>

        <form action="<?= URLROOT; ?>AccountsController/bijwerken/<?= (int)$account['id']; ?>" method="POST" class="account-form" novalidate>

            <h2>Persoonlijke gegevens</h2>

            <div class="toevoegen-rij">
                <div class="form-groep">
                    <label for="voornaam">Voornaam <span class="toevoegen-verplicht">*</span></label>
                    <input type="text" id="voornaam" name="voornaam" value="<?= oudOfAccount($invoer, $account, 'voornaam'); ?>" placeholder="Jan">
                </div>
                <div class="form-groep toevoegen-tussenvoegsel">
                    <label for="tussenvoegsel">Tussenvoegsel</label>
                    <input type="text" id="tussenvoegsel" name="tussenvoegsel" value="<?= oudOfAccount($invoer, $account, 'tussenvoegsel'); ?>" placeholder="van">
                </div>
                <div class="form-groep">
                    <label for="achternaam">Achternaam <span class="toevoegen-verplicht">*</span></label>
                    <input type="text" id="achternaam" name="achternaam" value="<?= oudOfAccount($invoer, $account, 'achternaam'); ?>" placeholder="Janssen">
                </div>
            </div>

            <div class="toevoegen-rij">
                <div class="form-groep">
                    <label for="email">E-mailadres <span class="toevoegen-verplicht">*</span></label>
                    <?php // type="text" zodat de browser geen eigen popup toont; validatie gebeurt server-side. ?>
                    <input type="text" id="email" name="email" value="<?= oudOfAccount($invoer, $account, 'email'); ?>" placeholder="naam@voorbeeld.nl">
                </div>
                <div class="form-groep">
                    <label for="telefoon">Telefoonnummer <span class="toevoegen-verplicht">*</span></label>
                    <input type="text" id="telefoon" name="telefoon" value="<?= oudOfAccount($invoer, $account, 'telefoon'); ?>" placeholder="0612345678">
                </div>
            </div>

            <div class="toevoegen-rij">
                <div class="form-groep">
                    <label for="geboortedatum">Geboortedatum <span class="toevoegen-verplicht">*</span></label>
                    <input type="date" id="geboortedatum" name="geboortedatum" value="<?= oudOfAccount($invoer, $account, 'geboortedatum'); ?>">
                </div>
            </div>

            <h2>Lidmaatschap &amp; Status</h2>

            <div class="toevoegen-rij">
                <div class="form-groep">
                    <label for="lidmaatschap_id">Lidmaatschap</label>
                    <select id="lidmaatschap_id" name="lidmaatschap_id">
                        <?php
                        $huidigLid = !empty($invoer['lidmaatschapId'])
                            ? (int)$invoer['lidmaatschapId']
                            : (int)($account['lidmaatschap_id'] ?? 1);
                        foreach ($data['lidmaatschappen'] as $lid) : ?>
                            <option value="<?= (int)$lid->id; ?>" <?= $huidigLid === (int)$lid->id ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($lid->naam); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-groep">
                    <label for="status">Status <span class="toevoegen-verplicht">*</span></label>
                    <?php
                    $huidigStatus = !empty($invoer['status']) ? $invoer['status'] : ($account['status'] ?? 'Actief');
                    ?>
                    <select id="status" name="status">
                        <?php foreach (['Actief', 'Gepauzeerd', 'Verlopen', 'Opgezegd'] as $s) : ?>
                            <option value="<?= $s; ?>" <?= $huidigStatus === $s ? 'selected' : ''; ?>><?= $s; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="toevoegen-rij">
                <div class="form-groep">
                    <label for="start_datum">Startdatum <span class="toevoegen-verplicht">*</span></label>
                    <input type="date" id="start_datum" name="start_datum" value="<?= oudOfAccount($invoer, $account, 'startDatum', $account['start_datum'] ?? ''); ?>">
                </div>
                <div class="form-groep">
                    <label for="eind_datum">Einddatum</label>
                    <input type="date" id="eind_datum" name="eind_datum" value="<?= oudOfAccount($invoer, $account, 'eindDatum', $account['eind_datum'] ?? ''); ?>">
                </div>
            </div>

            <h2>Rol</h2>

            <div class="toevoegen-rij">
                <div class="form-groep">
                    <label for="rol">Rol <span class="toevoegen-verplicht">*</span></label>
                    <?php $huidigRol = !empty($invoer['rol']) ? $invoer['rol'] : ($account['rol'] ?? 'lid'); ?>
                    <select id="rol" name="rol">
                        <option value="lid"        <?= $huidigRol === 'lid'        ? 'selected' : ''; ?>>Lid</option>
                        <option value="medewerker" <?= $huidigRol === 'medewerker' ? 'selected' : ''; ?>>Medewerker</option>
                    </select>
                </div>
            </div>

            <h2>Wachtwoord wijzigen</h2>
            <p class="account-text">Laat leeg om het huidige wachtwoord te behouden.</p>

            <div class="toevoegen-rij">
                <div class="form-groep">
                    <label for="wachtwoord">Nieuw wachtwoord</label>
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
                    <label for="bevestig_wachtwoord">Bevestig nieuw wachtwoord</label>
                    <div class="wachtwoord-wrapper">
                        <input type="password" id="bevestig_wachtwoord" name="bevestig_wachtwoord" placeholder="Herhaal nieuw wachtwoord">
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
                <button type="submit" class="btn btn-primary">Wijzigingen opslaan</button>
                <a href="<?= URLROOT; ?>AccountsController/overzicht" class="btn btn-secundair">Annuleren</a>
            </div>
        </form>
    </section>
</div>

<script>
function toggleWachtwoord(veldId, knop) {
    var veld = document.getElementById(veldId);
    if (!veld) return;
    veld.type = veld.type === 'password' ? 'text' : 'password';
    knop.style.color = veld.type === 'text' ? '#c43b2f' : '';
}
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>