<?php
class Les
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

	public function getLessenSamenvatting()
	{
		try {
			if ($this->db === null) {
				return [];
			}
			$this->db->query("SELECT Emoji, Naam, Beschrijving FROM Lessen LIMIT 8");
			return $this->db->resultSet();
		} catch (Exception $e) {
			return [];
		}
	}
}


