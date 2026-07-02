<?php require_once APPROOT . '/views/includes/header.php'; ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" />

<div class="container medewerkers-container">

    <div class="topbar">
        <h1><i class="ti ti-edit"></i> Medewerker wijzigen</h1>
        <a href="<?= URLROOT ?>MedewerkersController/index" class="btn btn-secondary">
            <i class="ti ti-arrow-left"></i> Terug naar overzicht
        </a>
    </div>

    <?php if ($data['foutmelding'] !== '') : ?>
        <div class="alert alert-error"><?= htmlspecialchars($data['foutmelding']); ?></div>
    <?php endif; ?>

    <div class="form-wrap">
        <form action="<?= URLROOT ?>MedewerkersController/wijzigen/<?= $data['medewerkerId']; ?>" method="POST" class="medewerker-form">
            <div class="form-group">
                <label for="naam">Naam</label>
                <input type="text" id="naam" name="naam" value="<?= htmlspecialchars($data['invoer']['naam']); ?>" required>
            </div>
            <div class="form-group">
                <label for="functie">Functie</label>
                <input type="text" id="functie" name="functie" value="<?= htmlspecialchars($data['invoer']['functie']); ?>" required>
            </div>
            <div class="form-group">
                <label for="afdeling">Afdeling</label>
                <input type="text" id="afdeling" name="afdeling" value="<?= htmlspecialchars($data['invoer']['afdeling']); ?>" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy"></i> Opslaan
                </button>
                <a href="<?= URLROOT ?>MedewerkersController/index" class="btn btn-secondary">Annuleren</a>
            </div>
        </form>
    </div>

</div>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>