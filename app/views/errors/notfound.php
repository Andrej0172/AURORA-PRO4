<?php require_once APPROOT . '/views/includes/header.php'; ?>

<!-- 404-pagina voor ongeldige routes -->
<div class="onderhoud-wrapper">
    <div class="onderhoud-logo">
        <span class="logo-fit">AURORA</span><span class="logo-for">Theater</span>
    </div>

    <div class="onderhoud-box">
        <h1>404 - Pagina niet gevonden</h1>
        <p><?= htmlspecialchars($data['message'] ?? 'De gevraagde pagina bestaat niet.'); ?></p>
        <a href="<?= URLROOT; ?>Homepages/index" class="btn btn-primary" style="display:inline-block;margin-top:1rem;">Naar home</a>
    </div>
</div>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
