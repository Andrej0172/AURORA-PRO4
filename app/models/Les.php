<?php
// Model voor lessen uit de AuroraDb-database via de Database-wrapper.
class Les
{
	private $db;

	public function __construct()
	{
		try {
			$this->db = new Database();
		} catch (Exception $e) {
			// Database niet beschikbaar; methodes geven lege resultaten terug.
			$this->db = null;
		}
	}

	// Haal een beperkte selectie lessen op voor de homepage-weergave.
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


