<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="onderhoud-wrapper">
    <div class="onderhoud-logo">
        <span class="logo-fit">AURORA</span><span class="logo-for">Theater</span>
    </div>

    <div class="onderhoud-box">
        <div class="onderhoud-icon">&#9888;&#65039;</div>
        <?php // Foutmelding bij mislukt uitloggen; sessie blijft actief zodat gebruiker ingelogd blijft. ?>
        <h1>Uitloggen mislukt</h1>
        <p>Uitloggen mislukt. Probeer het opnieuw.</p>
        <a href="<?= URLROOT; ?>AccountsController/logout" class="btn btn-primary">Opnieuw proberen</a>
    </div>
</div>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
