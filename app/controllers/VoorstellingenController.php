<?php
class VoorstellingenController extends BaseController
{
    private $voorstellingModel;

    public function __construct()
    {
        $this->voorstellingModel = $this->model('Voorstelling');
    }

    public function index()
    {
        $melding = '';
        $fout = false;

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

    public function data()
    {
        header('Content-Type: application/json; charset=utf-8');

        $voorstellingen = $this->voorstellingModel->getAll();

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

    public function toevoegen()
    {
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

            if ($titel === '' || $datum === '' || $tijd === '' || $zaal === '') {
                $foutmelding = 'Vul alle verplichte velden in.';
            } elseif (!strtotime($datum)) {
                $foutmelding = 'Voer een geldige datum in.';
            } elseif (!strtotime($tijd)) {
                $foutmelding = 'Voer een geldige tijd in.';
            }

            if ($foutmelding === '') {
                if ($this->voorstellingModel->existsByDetails($titel, $datum, $tijd, $zaal)) {
                    $foutmelding = 'De voorstelling bestaat al en kan niet worden toegevoegd.';
                }
            }

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

                $foutmelding = 'Voorstelling kon niet worden toegevoegd. Probeer opnieuw.';
            }

            $this->view('voorstellingen/toevoegen', [
                'title'      => 'Voorstelling toevoegen - Aurora Theater',
                'activePage' => 'voorstellingen',
                'styles'     => ['voorstellingen.css'],
                'foutmelding' => $foutmelding,
                'invoer'     => compact('titel', 'datum', 'tijd', 'zaal')
            ]);
            return;
        }

        $this->view('voorstellingen/toevoegen', [
            'title'      => 'Voorstelling toevoegen - Aurora Theater',
            'activePage' => 'voorstellingen',
            'styles'     => ['voorstellingen.css'],
            'foutmelding' => '',
            'invoer'     => ['titel' => '', 'datum' => '', 'tijd' => '', 'zaal' => '']
        ]);
    }
}
