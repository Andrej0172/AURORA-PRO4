<?php
// Controller voor het beheren van medewerkers (overzicht en toevoegen)
class MedewerkersController extends BaseController
{
    private $medewerkerModel;

    // Laad het Medewerker-model bij instantiatie
    public function __construct()
    {
        $this->medewerkerModel = $this->model('Medewerker');
    }

    // Toon de overzichtspagina met flash-meldingen uit de sessie (alleen voor medewerkers)
    public function index()
    {
        if (!isset($_SESSION['account_id']) || strtolower($_SESSION['rol'] ?? '') !== 'medewerker') {
            header('Location: ' . URLROOT . 'AccountsController/login');
            exit;
        }

        $melding = '';
        $fout = false;

        // Lees en verwijder eventuele flash-melding uit de sessie
        if (isset($_SESSION['medewerker_melding'])) {
            $melding = $_SESSION['medewerker_melding'];
            $fout = !empty($_SESSION['medewerker_melding_fout']);
            unset($_SESSION['medewerker_melding'], $_SESSION['medewerker_melding_fout']);
        }

        $this->view('medewerkers/index', [
            'title'      => 'Medewerkers - Aurora Theater',
            'activePage' => 'medewerkers',
            'styles'     => ['medewerkers.css'],
            'melding'    => $melding,
            'fout'       => $fout
        ]);
    }

    // JSON-endpoint: haal alle medewerkers op voor de JavaScript-tabel
    public function data()
    {
        // Alleen ingelogde medewerkers mogen de data zien
        if (!isset($_SESSION['account_id']) || strtolower($_SESSION['rol'] ?? '') !== 'medewerker') {
            http_response_code(403);
            echo json_encode(['error' => 'Geen toegang']);
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');

        $medewerkers = $this->medewerkerModel->getAll();

        // null = databasefout, lege array = geen data
        if ($medewerkers === null) {
            http_response_code(500);
            echo json_encode(['error' => 'Medewerkers konden niet worden geladen. Probeer opnieuw.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $result = array_map(function ($m) {
            return [
                'naam'     => $m->Naam,
                'functie'  => $m->Functie,
                'afdeling' => $m->Afdeling,
            ];
        }, $medewerkers);

        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Toon het toevoegformulier (GET) of verwerk de inzending (POST)
    public function toevoegen()
    {
        // Alleen ingelogde medewerkers mogen medewerkers toevoegen
        if (!isset($_SESSION['account_id']) || strtolower($_SESSION['rol'] ?? '') !== 'medewerker') {
            header('Location: ' . URLROOT . 'AccountsController/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $naam     = trim($_POST['naam'] ?? '');
            $functie  = trim($_POST['functie'] ?? '');
            $afdeling = trim($_POST['afdeling'] ?? '');

            $foutmelding = '';

            // Validatie: alle velden zijn verplicht
            if ($naam === '' || $functie === '' || $afdeling === '') {
                $foutmelding = 'Vul alle verplichte velden in.';
            }

            // Controleer op duplicaten voordat we opslaan
            if ($foutmelding === '') {
                if ($this->medewerkerModel->existsByNaam($naam)) {
                    $foutmelding = 'De medewerker bestaat al en kan niet worden toegevoegd.';
                }
            }

            // Geen fouten? Probeer op te slaan in de database
            if ($foutmelding === '') {
                $succes = $this->medewerkerModel->create([
                    'naam'     => $naam,
                    'functie'  => $functie,
                    'afdeling' => $afdeling,
                ]);

                if ($succes) {
                    $_SESSION['medewerker_melding'] = 'Medewerker succesvol toegevoegd.';
                    $_SESSION['medewerker_melding_fout'] = false;
                    header('Location: ' . URLROOT . 'MedewerkersController/index');
                    exit;
                }

                // Opslaan mislukt (bijv. databasefout)
                $foutmelding = 'Medewerker kon niet worden toegevoegd. Probeer opnieuw.';
            }

            // Toon het formulier opnieuw met de foutmelding en ingevulde waarden
            $this->view('medewerkers/toevoegen', [
                'title'      => 'Medewerker toevoegen - Aurora Theater',
                'activePage' => 'medewerkers',
                'styles'     => ['medewerkers.css'],
                'foutmelding' => $foutmelding,
                'invoer'     => compact('naam', 'functie', 'afdeling')
            ]);
            return;
        }

        // GET-verzoek: toon leeg formulier
        $this->view('medewerkers/toevoegen', [
            'title'      => 'Medewerker toevoegen - Aurora Theater',
            'activePage' => 'medewerkers',
            'styles'     => ['medewerkers.css'],
            'foutmelding' => '',
            'invoer'     => ['naam' => '', 'functie' => '', 'afdeling' => '']
        ]);
    }
}
