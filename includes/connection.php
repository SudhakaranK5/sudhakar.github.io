<?php

class DbConnect {
    private $host = "localhost";
    private $user = "root";
    private $password = "";
    private $database = "bbjewels";
    private $conn;

    // Constructor
    public function __construct() {
        $this->open();
    }

    // Open a connection to the database
    public function open() {
        // Create a new mysqli connection
        $this->conn = new mysqli($this->host, $this->user, $this->password, $this->database);

        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        return true;
    }

    // Close the connection
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    // Get the connection
    public function getConnection() {
        return $this->conn;
    }
}

/* Class to perform query */
class DbQuery extends DbConnect {
    private $result = '';
    private $sql;

    // Constructor
    public function __construct($sql1) {
        parent::__construct(); // Call the parent constructor to open the connection
        $this->sql = $sql1;
    }

    // Execute the query
    public function query() {
        $this->result = $this->getConnection()->query($this->sql);
        return $this->result;
    }

    // Get number of affected rows
    public function affectedRows() {
        return $this->getConnection()->affected_rows;
    }

    // Get number of rows in result
    public function numRows() {
        return $this->result ? $this->result->num_rows : 0;
    }

    // Fetch as an object
    public function fetchObject() {
        return $this->result->fetch_object();
    }

    // Fetch as an array
    public function fetchArray() {
        return $this->result->fetch_array();
    }

    // Fetch as an associative array
    public function fetchAssoc() {
        return $this->result->fetch_assoc();
    }

    // Free the result
    public function freeResult() {
        if ($this->result) {
            $this->result->free();
        }
    }
}

?>
