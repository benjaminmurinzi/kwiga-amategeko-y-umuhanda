<?php
/**
 * Database Configuration
 * Update these values with your database credentials
 */

// Database connection settings
define('DB_HOST', 'localhost');        // Database host (usually 'localhost')
define('DB_NAME', 'traffic_learning'); // Database name
define('DB_USER', 'root');             // Database username
define('DB_PASS', '');                 // Database password
define('DB_CHARSET', 'utf8mb4');       // Character set

// Database connection class
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;
    public $conn;

    /**
     * Get database connection
     * @return PDO|null Database connection object
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            die("Database connection failed. Please check your configuration.");
        }

        return $this->conn;
    }

    /**
     * Test database connection
     * @return bool Connection status
     */
    public function testConnection() {
        try {
            $this->getConnection();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

// Test connection on include (optional, comment out in production)
// $db = new Database();
// if ($db->testConnection()) {
//     echo "Database connected successfully!";
// } else {
//     echo "Database connection failed!";
// }
?>

