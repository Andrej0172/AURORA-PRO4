<?php
class Voorstelling
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
            $this->db->query("SELECT Id, Titel, Datum, Tijd, Zaal FROM Voorstellingen ORDER BY Datum ASC");
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
            $this->db->query("INSERT INTO Voorstellingen (Titel, Datum, Tijd, Zaal) VALUES (:titel, :datum, :tijd, :zaal)");
            $this->db->bind(':titel', $data['titel'], PDO::PARAM_STR);
            $this->db->bind(':datum', $data['datum'], PDO::PARAM_STR);
            $this->db->bind(':tijd', $data['tijd'], PDO::PARAM_STR);
            $this->db->bind(':zaal', $data['zaal'], PDO::PARAM_STR);
            return $this->db->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    public function existsByDetails($titel, $datum, $tijd, $zaal)
    {
        try {
            if ($this->db === null) {
                return false;
            }
            $this->db->query("SELECT COUNT(*) AS cnt FROM Voorstellingen WHERE Titel = :titel AND Datum = :datum AND Tijd = :tijd AND Zaal = :zaal");
            $this->db->bind(':titel', $titel, PDO::PARAM_STR);
            $this->db->bind(':datum', $datum, PDO::PARAM_STR);
            $this->db->bind(':tijd', $tijd, PDO::PARAM_STR);
            $this->db->bind(':zaal', $zaal, PDO::PARAM_STR);
            $result = $this->db->resultSet();
            return (int)$result[0]->cnt > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}
