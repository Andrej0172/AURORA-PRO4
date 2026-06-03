<?php
// Controller voor het overzicht van theatervoorstellingen.
class VoorstellingenController extends BaseController
{
    // Toon de voorstellingenpagina (HTML shell; data wordt via AJAX opgehaald).
    public function index()
    {
        $this->view('voorstellingen/index', [
            'title'      => 'Voorstellingen - Aurora Theater',
            'activePage' => 'voorstellingen',
            'styles'     => ['voorstellingen.css'],
        ]);
    }

    // JSON-endpoint dat de voorstellingenlijst teruggeeft voor de frontend.
    public function data()
    {
        header('Content-Type: application/json; charset=utf-8');

        // Tijdelijke data — later te vervangen door databasequery op AuroraDb.
        $voorstellingen = [
            ['titel' => 'De Storm',         'datum' => '2026-09-12', 'locatie' => 'Grote Zaal'],
            ['titel' => 'Zwanenmeer',       'datum' => '2026-09-20', 'locatie' => 'Stadsschouwburg Amsterdam'],
            ['titel' => 'Hamlet',           'datum' => '2026-10-03', 'locatie' => 'Kleine Zaal'],
            ['titel' => 'De Verwachting',   'datum' => '2026-10-18', 'locatie' => 'Zuiderpershuis Antwerpen'],
            ['titel' => 'Licht & Schaduw',  'datum' => '2026-11-01', 'locatie' => 'Grote Zaal'],
            ['titel' => 'Nachtmerrie',      'datum' => '2026-11-14', 'locatie' => 'Theater Rotterdam'],
            ['titel' => 'Het Afscheid',     'datum' => '2026-12-05', 'locatie' => 'Kleine Zaal'],
        ];

        echo json_encode($voorstellingen, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
