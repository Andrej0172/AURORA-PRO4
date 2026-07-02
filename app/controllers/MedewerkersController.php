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
                'id'       => (int)$m->Id,
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

    // Toon het wijzigformulier (GET) of verwerk de wijziging (POST)
    public function wijzigen($id = null)
    {
        // Alleen ingelogde medewerkers
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

            // Controleer op duplicaten (naam mag niet al bestaan bij een andere medewerker)
            if ($foutmelding === '') {
                if ($this->medewerkerModel->existsByNaamExcludingId($naam, $id)) {
                    $foutmelding = 'Deze naam is al in gebruik door een andere medewerker.';
                }
            }

            // Geen fouten? Probeer bij te werken in de database
            if ($foutmelding === '') {
                $succes = $this->medewerkerModel->update($id, [
                    'naam'     => $naam,
                    'functie'  => $functie,
                    'afdeling' => $afdeling,
                ]);

                if ($succes) {
                    $_SESSION['medewerker_melding'] = 'Medewerker succesvol gewijzigd.';
                    $_SESSION['medewerker_melding_fout'] = false;
                    header('Location: ' . URLROOT . 'MedewerkersController/index');
                    exit;
                }

                // Update mislukt (bijv. databasefout)
                $foutmelding = 'Medewerker kon niet worden gewijzigd. Probeer opnieuw.';
            }

            // Toon het formulier opnieuw met de foutmelding en ingevulde waarden
            $this->view('medewerkers/wijzigen', [
                'title'      => 'Medewerker wijzigen - Aurora Theater',
                'activePage' => 'medewerkers',
                'styles'     => ['medewerkers.css'],
                'foutmelding' => $foutmelding,
                'invoer'     => compact('naam', 'functie', 'afdeling'),
                'medewerkerId' => $id
            ]);
            return;
        }

        // GET-verzoek: haal de huidige gegevens op en toon het formulier
        $medewerker = $this->medewerkerModel->getById($id);

        if (!$medewerker) {
            $_SESSION['medewerker_melding'] = 'Medewerker niet gevonden.';
            $_SESSION['medewerker_melding_fout'] = true;
            header('Location: ' . URLROOT . 'MedewerkersController/index');
            exit;
        }

        $this->view('medewerkers/wijzigen', [
            'title'      => 'Medewerker wijzigen - Aurora Theater',
            'activePage' => 'medewerkers',
            'styles'     => ['medewerkers.css'],
            'foutmelding' => '',
            'invoer'     => [
                'naam'     => $medewerker->Naam,
                'functie'  => $medewerker->Functie,
                'afdeling' => $medewerker->Afdeling,
            ],
            'medewerkerId' => $id
        ]);
    }

    // Verwijder een medewerker (alleen POST)
    public function verwijderen($id = null)
    {
        // Alleen ingelogde medewerkers
        if (!isset($_SESSION['account_id']) || strtolower($_SESSION['rol'] ?? '') !== 'medewerker') {
            header('Location: ' . URLROOT . 'AccountsController/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Controleer of de medewerker nog bestaat (voorkom dubbel verwijderen in 2 tabs)
            $medewerker = $this->medewerkerModel->getById($id);

            if (!$medewerker) {
                $_SESSION['medewerker_melding'] = 'Medewerker kon niet worden verwijderd omdat deze niet meer bestaat.';
                $_SESSION['medewerker_melding_fout'] = true;
                header('Location: ' . URLROOT . 'MedewerkersController/index');
                exit;
            }

            $succes = $this->medewerkerModel->delete($id);

            if ($succes) {
                $_SESSION['medewerker_melding'] = 'Medewerker succesvol verwijderd.';
                $_SESSION['medewerker_melding_fout'] = false;
            } else {
                $_SESSION['medewerker_melding'] = 'Medewerker kon niet worden verwijderd. Probeer opnieuw.';
                $_SESSION['medewerker_melding_fout'] = true;
            }
        }

        header('Location: ' . URLROOT . 'MedewerkersController/index');
        exit;
    }
}
