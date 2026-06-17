<?php
// MVC Router - laadt de juiste controller en methode op basis van de URL
class Core
{
    protected $currentController = 'Homepages';
    protected $currentMethod = 'index';
    protected $params = [];

    public function __construct()
    {
        $url = $this->getURL();

        // Controleer of de controller bestaat
        if (file_exists(APPROOT . '/controllers/' . ucwords($url[0]) . '.php')) {
            $this->currentController = ucwords($url[0]);
            unset($url[0]);
        } else {
            $this->renderNotFound();
            return;
        }

        // Laad de controller
        require_once APPROOT . '/controllers/' . $this->currentController . '.php';
        $this->currentController = new $this->currentController();

        // Controleer of de methode bestaat
        if (isset($url[1]) && method_exists($this->currentController, $url[1])) {
            $this->currentMethod = $url[1];
            unset($url[1]);
        } elseif (isset($url[1])) {
            $this->renderNotFound();
            return;
        }

        // Verzamel parameters en roep de methode aan
        $this->params = $url ? array_values($url) : [];
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    private function renderNotFound()
    {
        $data = [
            'title'         => '404 - Aurora Theater',
            'documentTitle' => 'Aurora Theater - 404',
            'message'       => 'De gevraagde pagina of route bestaat niet.',
            'activePage'    => '',
            'styles'        => ['errors.css']
        ];

        require_once APPROOT . '/views/errors/notfound.php';
    }

    public function getURL()
    {
        if (isset($_GET['url']) && $_GET['url'] !== '') {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return explode('/', $url);
        }

        // Geen URL? Dan homepages/index
        return ['homepages', 'index'];
    }
}

