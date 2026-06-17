<?php require_once APPROOT . '/views/includes/header.php'; ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" />

<div class="container medewerkers-container">

    <div class="topbar">
        <h1><i class="ti ti-users"></i> Overzicht medewerkers</h1>
        <div class="topbar-actions">
            <div class="search-bar">
                <i class="ti ti-search"></i>
                <input type="text" id="searchInput" placeholder="Zoek op naam..." oninput="filterTable()" />
            </div>
            <a href="<?= URLROOT ?>MedewerkersController/toevoegen" class="btn btn-primary">
                <i class="ti ti-plus"></i> Medewerker toevoegen
            </a>
        </div>
    </div>

    <?php if (isset($data['melding']) && $data['melding'] !== '') : ?>
        <div class="alert <?= !empty($data['fout']) ? 'alert-error' : 'alert-success'; ?>">
            <?= htmlspecialchars($data['melding']); ?>
        </div>
    <?php endif; ?>

    <div class="stats">
        <div class="stat-card">
            <div class="label">Totaal medewerkers</div>
            <div class="value" id="totalCount">—</div>
        </div>
        <div class="stat-card">
            <div class="label">Afdelingen</div>
            <div class="value" id="afdelingCount">—</div>
        </div>
        <div class="stat-card">
            <div class="label">Resultaten</div>
            <div class="value" id="resultCount">—</div>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Naam</th>
                    <th>Functie</th>
                    <th>Afdeling</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
        <div class="empty-state" id="emptyState" style="display:none;">
            <i class="ti ti-mood-empty"></i>
            <p>Geen medewerkers gevonden.</p>
        </div>
    </div>

</div>

<script>const dataUrl = '<?= URLROOT ?>MedewerkersController/data';</script>
<script src="<?= URLROOT ?>js/medewerkers.js"></script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
