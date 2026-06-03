<?php require_once APPROOT . '/views/includes/header.php'; ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" />

<div class="container voorstellingen-container">

    <div class="topbar">
        <h1><i class="ti ti-theater"></i> Overzicht voorstellingen</h1>
        <div class="search-bar">
            <i class="ti ti-search"></i>
            <input type="text" id="searchInput" placeholder="Zoek op titel, datum of locatie..." oninput="filterTable()" />
        </div>
    </div>

    <div class="stats">
        <div class="stat-card">
            <div class="label">Totaal voorstellingen</div>
            <div class="value" id="totalCount">—</div>
        </div>
        <div class="stat-card">
            <div class="label">Locaties</div>
            <div class="value" id="locatieCount">—</div>
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
                    <th>Titel</th>
                    <th>Datum</th>
                    <th>Locatie</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
        <div class="empty-state" id="emptyState" style="display:none;">
            <i class="ti ti-mood-empty"></i>
            <p>Geen voorstellingen gevonden.</p>
        </div>
    </div>

</div>

<script>const dataUrl = '<?= URLROOT ?>VoorstellingenController/data';</script>
<script src="<?= URLROOT ?>js/voorstellingen.js"></script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
