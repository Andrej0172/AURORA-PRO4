<?php
class MedewerkersController extends BaseController
{
    public function index()
    {
        // Alleen ingelogde medewerkers mogen het medewerkersoverzicht zien.
        if (!isset($_SESSION['account_id']) || strtolower($_SESSION['rol'] ?? '') !== 'medewerker') {
            header('Location: ' . URLROOT . 'AccountsController/login');
            exit;
        }

        $this->view('medewerkers/index', [
            'title'      => 'Medewerkers - Aurora Theater',
            'activePage' => 'medewerkers',
            'styles'     => ['medewerkers.css'],
        ]);
    }

    public function data()
    {
        // JSON-endpoint: alleen toegankelijk voor ingelogde medewerkers.
        if (!isset($_SESSION['account_id']) || strtolower($_SESSION['rol'] ?? '') !== 'medewerker') {
            http_response_code(403);
            echo json_encode(['error' => 'Geen toegang']);
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');

        // Tijdelijke data — later te vervangen door databasequery.
        $medewerkers = [
            ['naam' => 'Sophie de Vries',   'functie' => 'Regisseur',       'afdeling' => 'Artistiek'],
            ['naam' => 'Lars Bakker',       'functie' => 'Acteur',           'afdeling' => 'Artistiek'],
            ['naam' => 'Noor van den Berg', 'functie' => 'Lichtontwerper',   'afdeling' => 'Techniek'],
            ['naam' => 'Daan Janssen',      'functie' => 'Geluidsontwerper', 'afdeling' => 'Techniek'],
            ['naam' => 'Emma Smit',         'functie' => 'Kostuumontwerper', 'afdeling' => 'Kostuums'],
            ['naam' => 'Tim Visser',        'functie' => 'Productieleider',  'afdeling' => 'Productie'],
            ['naam' => 'Lisa Meijer',       'functie' => 'Marketingmanager', 'afdeling' => 'Marketing'],
        ];

        echo json_encode($medewerkers, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
