<?php
class Medewerker
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

    public function getAll()
    {
        try {
            if ($this->db === null) {
                return null;
            }
            $this->db->query("SELECT Id, Naam, Functie, Afdeling FROM Medewerkers ORDER BY Naam ASC");
            return $this->db->resultSet();
        } catch (Exception $e) {
            return null;
        }
    }

    public function create($data)
    {
        try {
            if ($this->db === null) {
                return false;
            }
            $this->db->query("INSERT INTO Medewerkers (Naam, Functie, Afdeling) VALUES (:naam, :functie, :afdeling)");
            $this->db->bind(':naam', $data['naam'], PDO::PARAM_STR);
            $this->db->bind(':functie', $data['functie'], PDO::PARAM_STR);
            $this->db->bind(':afdeling', $data['afdeling'], PDO::PARAM_STR);
            return $this->db->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    public function existsByNaam($naam)
    {
        try {
            if ($this->db === null) {
                return false;
            }
            $this->db->query("SELECT COUNT(*) AS cnt FROM Medewerkers WHERE Naam = :naam");
            $this->db->bind(':naam', $naam, PDO::PARAM_STR);
            $result = $this->db->resultSet();
            return (int)$result[0]->cnt > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}
