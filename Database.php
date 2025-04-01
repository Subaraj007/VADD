<?php
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $this->connection = new mysqli(
            'localhost', 
            'root', 
            '', 
            'devicedb'
        );
        
        if ($this->connection->connect_error) {
            throw new Exception("Connection failed");
        }
    }
    
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}
?>