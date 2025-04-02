<?php
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new mysqli(
                getenv('DB_HOST') ?: 'localhost',
                getenv('DB_USER') ?: 'root',
                getenv('DB_PASS') ?: '',
                getenv('DB_NAME') ?: 'video_project_db'
            );
            
            if ($this->connection->connect_error) {
                throw new Exception("DB Connection failed: " . $this->connection->connect_error);
            }
            
            // Optional: Set charset
            $this->connection->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }
    
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        if (!$this->connection || !$this->connection->ping()) {
            $this->__construct(); // Reconnect if dead
        }
        return $this->connection;
    }
    
    // Prevent cloning and serialization
    private function __clone() {}
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
?>