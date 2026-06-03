<?php
// Database wrapper voor PDO
class Database
{
    private $dbHost = DB_HOST;
    private $dbName = DB_NAME;
    private $dbUser = DB_USER;
    private $dbPass = DB_PASS;
    private $dbHandler;
    private $statement;

    public function __construct()
    {
        $conn = 'mysql:host=' . $this->dbHost . ';dbname=' . $this->dbName . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_PERSISTENT       => true,
            PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        try {
            $this->dbHandler = new PDO($conn, $this->dbUser, $this->dbPass, $options);
        } catch (PDOException $e) {
            // Database niet bereikbaar - laat dbHandler null zodat models dit afhandelen
            $this->dbHandler = null;
        }
    }

    // Prepare SQL query
    public function query($sql)
    {
        if ($this->dbHandler === null) {
            throw new Exception("Database is niet beschikbaar");
        }
        $this->statement = $this->dbHandler->prepare($sql);
    }

    // Bind waarde aan query
    public function bind($p, $v, $t)
    {
        if ($this->statement === null) {
            throw new Exception("Query niet voorbereid");
        }
        $this->statement->bindValue($p, $v, $t);
    }

    // Voer query uit en haal alle resultaten op als array van objecten.
    public function resultSet()
    {
        if ($this->statement === null) {
            throw new Exception("Query niet voorbereid");
        }
        $this->statement->execute();
        return $this->statement->fetchAll(PDO::FETCH_OBJ);
    }
}


