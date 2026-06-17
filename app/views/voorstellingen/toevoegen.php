<?php require_once APPROOT . '/views/includes/header.php'; ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" />

<!-- Formulier om een nieuwe voorstelling toe te voegen -->
<div class="container voorstellingen-container">

    <div class="topbar">
        <h1><i class="ti ti-plus"></i> Voorstelling toevoegen</h1>
        <a href="<?= URLROOT ?>VoorstellingenController/index" class="btn btn-secondary">
            <i class="ti ti-arrow-left"></i> Terug naar overzicht
        </a>
    </div>

    <!-- Toon foutmelding bij validatie-/databasefout -->
    <?php if ($data['foutmelding'] !== '') : ?>
        <div class="alert alert-error"><?= htmlspecialchars($data['foutmelding']); ?></div>
    <?php endif; ?>

    <div class="form-wrap">
        <form action="<?= URLROOT ?>VoorstellingenController/toevoegen" method="POST" class="voorstelling-form">
            <div class="form-group">
                <label for="titel">Titel</label>
                <input type="text" id="titel" name="titel" value="<?= htmlspecialchars($data['invoer']['titel']); ?>" required>
            </div>
            <div class="form-group">
                <label for="datum">Datum</label>
                <input type="date" id="datum" name="datum" value="<?= htmlspecialchars($data['invoer']['datum']); ?>" required>
            </div>
            <div class="form-group">
                <label for="tijd">Tijd</label>
                <input type="time" id="tijd" name="tijd" value="<?= htmlspecialchars($data['invoer']['tijd']); ?>" required>
            </div>
            <div class="form-group">
                <label for="zaal">Zaal</label>
                <input type="text" id="zaal" name="zaal" value="<?= htmlspecialchars($data['invoer']['zaal']); ?>" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy"></i> Opslaan
                </button>
                <a href="<?= URLROOT ?>VoorstellingenController/index" class="btn btn-secondary">Annuleren</a>
            </div>
        </form>
    </div>

</div>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
