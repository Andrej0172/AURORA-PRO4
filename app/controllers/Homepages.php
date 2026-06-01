<?php
// Homepage controller voor Aurora Theater
class Homepages extends BaseController
{
    // Homepagina met een statische theater-homepage
    public function index()
    {
        $this->view('homepages/index', [
            'title'         => 'Aurora Theater - Home',
            'documentTitle' => 'Aurora Theater - Veilig Reserveren',
            'activePage'    => 'home',
            'styles'        => ['home.css']
        ]);
    }

    // Onderhoudspagina
    public function onderhoud()
    {
        $this->view('errors/onderhoud', [
            'title'         => 'Onderhoud - Aurora Theater',
            'documentTitle' => 'Aurora Theater - Onderhoud',
            'activePage'    => '',
            'styles'        => ['errors.css']
        ]);
    }
}



