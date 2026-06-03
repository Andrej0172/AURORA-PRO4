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
		if (file_exists('../app/views/' . $view . '.php')) {
			require_once('../app/views/' . $view . '.php');
		} else {
			echo 'View bestaat niet';
		}
	}
}

