<?php
class MedewerkersController extends BaseController
{
    private $medewerkerModel;

    public function __construct()
    {
        $this->medewerkerModel = $this->model('Medewerker');
    }

    public function index()
    {
        if (!isset($_SESSION['account_id']) || strtolower($_SESSION['rol'] ?? '') !== 'medewerker') {
            header('Location: ' . URLROOT . 'AccountsController/login');
            exit;
        }

        $melding = '';
        $fout = false;

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

    public function data()
    {
        if (!isset($_SESSION['account_id']) || strtolower($_SESSION['rol'] ?? '') !== 'medewerker') {
            http_response_code(403);
            echo json_encode(['error' => 'Geen toegang']);
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');

        $medewerkers = $this->medewerkerModel->getAll();

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

    public function toevoegen()
    {
        if (!isset($_SESSION['account_id']) || strtolower($_SESSION['rol'] ?? '') !== 'medewerker') {
            header('Location: ' . URLROOT . 'AccountsController/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $naam     = trim($_POST['naam'] ?? '');
            $functie  = trim($_POST['functie'] ?? '');
            $afdeling = trim($_POST['afdeling'] ?? '');

            $foutmelding = '';

            if ($naam === '' || $functie === '' || $afdeling === '') {
                $foutmelding = 'Vul alle verplichte velden in.';
            }

            if ($foutmelding === '') {
                if ($this->medewerkerModel->existsByNaam($naam)) {
                    $foutmelding = 'De medewerker bestaat al en kan niet worden toegevoegd.';
                }
            }

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

                $foutmelding = 'Medewerker kon niet worden toegevoegd. Probeer opnieuw.';
            }

            $this->view('medewerkers/toevoegen', [
                'title'      => 'Medewerker toevoegen - Aurora Theater',
                'activePage' => 'medewerkers',
                'styles'     => ['medewerkers.css'],
                'foutmelding' => $foutmelding,
                'invoer'     => compact('naam', 'functie', 'afdeling')
            ]);
            return;
        }

        $this->view('medewerkers/toevoegen', [
            'title'      => 'Medewerker toevoegen - Aurora Theater',
            'activePage' => 'medewerkers',
            'styles'     => ['medewerkers.css'],
            'foutmelding' => '',
            'invoer'     => ['naam' => '', 'functie' => '', 'afdeling' => '']
        ]);
    }
}
