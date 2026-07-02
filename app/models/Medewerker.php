<?php
// Model voor het beheren van medewerkers in de database
class Medewerker
{
    private $db;

    // Maak verbinding met de database via de Database-wrapper
    public function __construct()
    {
        try {
            $this->db = new Database();
        } catch (Exception $e) {
            $this->db = null;
        }
    }

    // Haal alle medewerkers op: theater-medewerkers (AuroraDb) + account-medewerkers (AuroraAccountsDb).
    // Valt terug op alleen account-medewerkers als de Medewerkers-tabel nog niet bestaat (migratie niet uitgevoerd).
    public function getAll()
    {
        if ($this->db === null) {
            return null;
        }

        try {
            $this->db->query(
                "SELECT Naam, Functie, Afdeling
                 FROM Medewerkers
                 UNION ALL
                 SELECT TRIM(CONCAT(Voornaam, ' ', COALESCE(CONCAT(Tussenvoegsel, ' '), ''), Achternaam)),
                        'Beheer', 'Administratie'
                 FROM " . DB_NAME_ACCOUNTS . ".Accounts
                 WHERE Rol = 'medewerker'
                 ORDER BY Naam ASC"
            );
            return $this->db->resultSet();
        } catch (Exception $e) {
            // Medewerkers-tabel bestaat nog niet; toon alleen account-medewerkers
            try {
                $this->db->query(
                    "SELECT TRIM(CONCAT(Voornaam, ' ', COALESCE(CONCAT(Tussenvoegsel, ' '), ''), Achternaam)) AS Naam,
                            'Beheer' AS Functie,
                            'Administratie' AS Afdeling
                     FROM " . DB_NAME_ACCOUNTS . ".Accounts
                     WHERE Rol = 'medewerker'
                     ORDER BY Naam ASC"
                );
                return $this->db->resultSet();
            } catch (Exception $e2) {
                return null;
            }
        }
    }

    // Voeg een nieuwe medewerker toe en geef true/terug bij succes
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

    // Controleer of een medewerker met dezelfde naam al bestaat
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

    public function existsByNaamExcludingId($naam, $excludeId)
    {
        try {
            if ($this->db === null) {
                return false;
            }
            $this->db->query("SELECT COUNT(*) AS cnt FROM Medewerkers WHERE Naam = :naam AND Id != :id");
            $this->db->bind(':naam', $naam, PDO::PARAM_STR);
            $this->db->bind(':id', $excludeId, PDO::PARAM_INT);
            $result = $this->db->resultSet();
            return (int)$result[0]->cnt > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getById($id)
    {
        try {
            if ($this->db === null) {
                return null;
            }
            $this->db->query("SELECT Id, Naam, Functie, Afdeling FROM Medewerkers WHERE Id = :id");
            $this->db->bind(':id', $id, PDO::PARAM_INT);
            $result = $this->db->resultSet();
            return !empty($result) ? $result[0] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function update($id, $data)
    {
        try {
            if ($this->db === null) {
                return false;
            }
            $this->db->query("UPDATE Medewerkers SET Naam = :naam, Functie = :functie, Afdeling = :afdeling WHERE Id = :id");
            $this->db->bind(':naam', $data['naam'], PDO::PARAM_STR);
            $this->db->bind(':functie', $data['functie'], PDO::PARAM_STR);
            $this->db->bind(':afdeling', $data['afdeling'], PDO::PARAM_STR);
            $this->db->bind(':id', $id, PDO::PARAM_INT);
            return $this->db->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            if ($this->db === null) {
                return false;
            }
            $this->db->query("DELETE FROM Medewerkers WHERE Id = :id");
            $this->db->bind(':id', $id, PDO::PARAM_INT);
            return $this->db->execute();
        } catch (Exception $e) {
            return false;
        }
    }
}
