<?php require_once APPROOT . '/views/includes/header.php'; ?>

<main class="inhoud">
    <section class="waarom">
        <p class="sectie-sub" style="margin-top:60px;">Over Aurora</p>
        <h2 class="sectie-titel">Alles geregeld voor jouw theateravond</h2>
        <p class="sectie-sub">Aurora biedt een veilige en gebruiksvriendelijke manier om tickets te reserveren en je theateravond volledig online in te regelen.</p>

        <div class="voordelen">
            <div class="voordeel">
                <div class="icoon">
                    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 32c0-11 9-20 20-20s20 9 20 20-9 20-20 20-20-9-20-20z" stroke="#c43b2f" stroke-width="3" fill="none"/>
                        <path d="M32 20v12l8 5" stroke="#c43b2f" stroke-width="3" stroke-linecap="round" fill="none"/>
                    </svg>
                </div>
                <span class="tekst">Eenvoudig en<br>snel reserveren</span>
            </div>

            <div class="voordeel">
                <div class="icoon">
                    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="12" y="16" width="40" height="32" rx="2" stroke="#c43b2f" stroke-width="3" fill="none"/>
                        <path d="M12 28h40M24 16v32M40 16v32" stroke="#c43b2f" stroke-width="2"/>
                    </svg>
                </div>
                <span class="tekst">Veilige barcode<br>scanning</span>
            </div>

            <div class="voordeel">
                <div class="icoon">
                    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M32 12c11 0 20 9 20 20s-9 20-20 20S12 43 12 32s9-20 20-20z" stroke="#c43b2f" stroke-width="3" fill="none"/>
                        <path d="M32 24v8l6 4" stroke="#c43b2f" stroke-width="2" stroke-linecap="round" fill="none"/>
                    </svg>
                </div>
                <span class="tekst">Gegevens van u<br>beveiligd opgeslagen</span>
            </div>
        </div>
    </section>

    <section class="lessen" id="bezoekers">
        <div class="lessen-header">
            <h2>Aurora voor u</h2>
            <a href="#medewerkers">Bekijk alle rollen &rarr;</a>
        </div>

        <div class="raster">
            <div class="les-chip">
                <div class="les-chip-emoji">🎫</div>
                <div class="les-chip-body">
                    <span class="les-chip-naam">Bezoekers</span>
                    <span class="les-chip-omschrijving">Maak een account aan, reserveer tickets en beheer je aankopen.</span>
                </div>
            </div>

            <div class="les-chip" id="medewerkers">
                <div class="les-chip-emoji">✓</div>
                <div class="les-chip-body">
                    <span class="les-chip-naam">Medewerkers</span>
                    <span class="les-chip-omschrijving">Scan tickets en beheer de toegang tot het theater efficiënt.</span>
                </div>
            </div>

            <div class="les-chip">
                <div class="les-chip-emoji">⚙️</div>
                <div class="les-chip-body">
                    <span class="les-chip-naam">Beheerder</span>
                    <span class="les-chip-omschrijving">Beheer voorstellingen, medewerkers en het volledige systeem.</span>
                </div>
            </div>

            <div class="les-chip">
                <div class="les-chip-emoji">⭐</div>
                <div class="les-chip-body">
                    <span class="les-chip-naam">Feedback</span>
                    <span class="les-chip-omschrijving">Deel je ervaring en help ons elke voorstelling te verbeteren.</span>
                </div>
            </div>
        </div>
    </section>

    <section class="actie">
        <h2>Klaar voor een zorgeloze entree?</h2>
        <p>Maak in minuten een account aan en regel je tickets volledig online.</p>
        <a href="#bezoekers" class="btn btn-primary">Reserveer nu</a>
    </section>
</main>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
