<?php
// Controller voor het beheren van voorstellingen (overzicht en toevoegen)
class VoorstellingenController extends BaseController
{
    private $voorstellingModel;

    // Laad het Voorstelling-model bij instantiatie
    public function __construct()
    {
        $this->voorstellingModel = $this->model('Voorstelling');
    }

    // Toon de overzichtspagina met flash-meldingen uit de sessie
    public function index()
    {
        $melding = '';
        $fout = false;

        // Lees en verwijder eventuele flash-melding uit de sessie
        if (isset($_SESSION['voorstelling_melding'])) {
            $melding = $_SESSION['voorstelling_melding'];
            $fout = !empty($_SESSION['voorstelling_melding_fout']);
            unset($_SESSION['voorstelling_melding'], $_SESSION['voorstelling_melding_fout']);
        }

        $this->view('voorstellingen/index', [
            'title'      => 'Voorstellingen - Aurora Theater',
            'activePage' => 'voorstellingen',
            'styles'     => ['voorstellingen.css'],
            'melding'    => $melding,
            'fout'       => $fout
        ]);
    }

    // JSON-endpoint: haal alle voorstellingen op voor de JavaScript-tabel
    public function data()
    {
        header('Content-Type: application/json; charset=utf-8');

        $voorstellingen = $this->voorstellingModel->getAll();

        // null = databasefout, lege array = geen data (beide zijn ok)
        if ($voorstellingen === null) {
            http_response_code(500);
            echo json_encode(['error' => 'Voorstellingen konden niet worden geladen. Probeer opnieuw.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $result = array_map(function ($v) {
            return [
                'titel' => $v->Titel,
                'datum' => $v->Datum,
                'tijd'  => $v->Tijd,
                'zaal'  => $v->Zaal,
            ];
        }, $voorstellingen);

        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Toon het toevoegformulier (GET) of verwerk de inzending (POST)
    public function toevoegen()
    {
        // Alleen ingelogde medewerkers mogen voorstellingen toevoegen
        if (!isset($_SESSION['account_id']) || strtolower($_SESSION['rol'] ?? '') !== 'medewerker') {
            header('Location: ' . URLROOT . 'AccountsController/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titel = trim($_POST['titel'] ?? '');
            $datum = trim($_POST['datum'] ?? '');
            $tijd  = trim($_POST['tijd'] ?? '');
            $zaal  = trim($_POST['zaal'] ?? '');

            $foutmelding = '';

            // Validatie van invoervelden
            if ($titel === '' || $datum === '' || $tijd === '' || $zaal === '') {
                $foutmelding = 'Vul alle verplichte velden in.';
            } elseif (!strtotime($datum)) {
                $foutmelding = 'Voer een geldige datum in.';
            } elseif (!strtotime($tijd)) {
                $foutmelding = 'Voer een geldige tijd in.';
            }

            // Controleer op duplicaten voordat we opslaan
            if ($foutmelding === '') {
                if ($this->voorstellingModel->existsByDetails($titel, $datum, $tijd, $zaal)) {
                    $foutmelding = 'De voorstelling bestaat al en kan niet worden toegevoegd.';
                }
            }

            // Geen fouten? Probeer op te slaan in de database
            if ($foutmelding === '') {
                $succes = $this->voorstellingModel->create([
                    'titel' => $titel,
                    'datum' => $datum,
                    'tijd'  => $tijd,
                    'zaal'  => $zaal,
                ]);

                if ($succes) {
                    $_SESSION['voorstelling_melding'] = 'Voorstelling succesvol toegevoegd.';
                    $_SESSION['voorstelling_melding_fout'] = false;
                    header('Location: ' . URLROOT . 'VoorstellingenController/index');
                    exit;
                }

                // Opslaan mislukt (bijv. databasefout)
                $foutmelding = 'Voorstelling kon niet worden toegevoegd. Probeer opnieuw.';
            }

            // Toon het formulier opnieuw met de foutmelding en ingevulde waarden
            $this->view('voorstellingen/toevoegen', [
                'title'      => 'Voorstelling toevoegen - Aurora Theater',
                'activePage' => 'voorstellingen',
                'styles'     => ['voorstellingen.css'],
                'foutmelding' => $foutmelding,
                'invoer'     => compact('titel', 'datum', 'tijd', 'zaal')
            ]);
            return;
        }

        // GET-verzoek: toon leeg formulier
        $this->view('voorstellingen/toevoegen', [
            'title'      => 'Voorstelling toevoegen - Aurora Theater',
            'activePage' => 'voorstellingen',
            'styles'     => ['voorstellingen.css'],
            'foutmelding' => '',
            'invoer'     => ['titel' => '', 'datum' => '', 'tijd' => '', 'zaal' => '']
        ]);
    }
}
