<?php

class BDHandler {
    public $DB = 'dbname';
    public $USER = 'dbuser';
    public $PWD = 'dbpassword';
    public $con;

    public function __construct() {
        $this->con = NULL;
    }

    public function connect() {
        try {
            $this->con = new PDO($this->DB, $this->USER, $this->PWD);
        } catch (PDOException $e) {
            die("Failed: ".$e->getMessage());
        }
    }
}

?>