<?php
/**
 * Database Configuration and Connection Class
 * RV BUILDERS - Backend API
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'rv_builders';
    private $username = 'root';
    private $password = '';
    private $conn = null;
    private $charset = 'utf8mb4';

    /**
     * Get database connection
     * @return PDO|null
     */
    public function getConnection() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
        return $this->conn;
    }

    /**
     * Close database connection
     */
    public function closeConnection() {
        $this->conn = null;
    }
}
