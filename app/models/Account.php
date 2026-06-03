<?php
class Account
{
	private $db;

	public function __construct()
	{
		try {
			$this->db = new Database();
		} catch (Exception $e) {
			$this->db = null;
		}
	}

	public function getLidmaatschappenVolledig()
	{
		try {
			if ($this->db === null) {
				return [];
			}
			// Dit is een stub implementatie
			return [];
		} catch (Exception $e) {
			return [];
		}
	}
}


