<?php
class BaseController
{
	// laad een model en geef een instantie terug (bijv. $this->model('Les') -> nieuw Les-object)
	public function model($model)
	{
		require_once APPROOT . '/models/' . $model . '.php';
		return new $model();
	}

	// laad een view en geef de $data-array mee zodat de view die kan gebruiken
	public function view($view, $data = [])
	{
		$viewFile = APPROOT . '/views/' . $view . '.php';
		if (file_exists($viewFile)) {
			require_once($viewFile);
		} else {
			echo 'View bestaat niet: ' . $view;
		}
	}
}

