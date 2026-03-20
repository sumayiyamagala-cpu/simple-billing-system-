<?php
/**
 * Database Configuration and Setup
 * MySQL version
 * 
 * IMPORTANT: Configure these settings for your MySQL server
 */

class DatabaseConfig {
    
    // MySQL Configuration
    private static $host = 'localhost';
    private static $db = 'billing_system';
    private static $user = 'root';
    private static $pass = '';
    private static $charset = 'utf8mb4';
    
    private static $pdo = null;
    
    /**
     * Get PDO connection
     */
    public static function getConnection() {
        if (self::$pdo === null) {
            try {
                $dsn = 'mysql:host=' . self::$host . ';charset=' . self::$charset;
                
                // First, try to connect without specifying database
                $pdo = new PDO($dsn, self::$user, self::$pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Create database if it doesn't exist
                $pdo->exec("CREATE DATABASE IF NOT EXISTS " . self::$db . " CHARACTER SET " . self::$charset);
                
                // Now connect to the specific database
                $dsn = 'mysql:host=' . self::$host . ';dbname=' . self::$db . ';charset=' . self::$charset;
                self::$pdo = new PDO($dsn, self::$user, self::$pass);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
            } catch (PDOException $e) {
                die("Connection Error: " . $e->getMessage() . "\n\nPlease verify your MySQL credentials in config.php");
            }
        }
        return self::$pdo;
    }
    
    /**
     * Initialize database tables
     */
    public static function initializeDatabase() {
        $pdo = self::getConnection();
        
        // Check if clients table exists
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.TABLES WHERE TABLE_SCHEMA = '" . self::$db . "' AND TABLE_NAME = 'clients'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            return; // Tables already exist
        }
        
        // Create clients table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS clients (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                phone VARCHAR(20),
                address TEXT,
                city VARCHAR(100),
                country VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Create invoices table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS invoices (
                id INT AUTO_INCREMENT PRIMARY KEY,
                invoice_number VARCHAR(50) UNIQUE NOT NULL,
                client_id INT NOT NULL,
                invoice_date DATE NOT NULL,
                due_date DATE NULL,
                subtotal DECIMAL(10, 2) DEFAULT 0,
                tax_amount DECIMAL(10, 2) DEFAULT 0,
                tax_rate DECIMAL(5, 2) DEFAULT 0,
                total DECIMAL(10, 2) DEFAULT 0,
                status VARCHAR(20) DEFAULT 'draft',
                notes LONGTEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
                INDEX idx_client_id (client_id),
                INDEX idx_invoice_date (invoice_date),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Create invoice items table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS invoice_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                invoice_id INT NOT NULL,
                description TEXT NOT NULL,
                quantity DECIMAL(10, 2) DEFAULT 1,
                unit_price DECIMAL(10, 2) NOT NULL,
                amount DECIMAL(10, 2) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
                INDEX idx_invoice_id (invoice_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
}

// Initialize database when this file is included
DatabaseConfig::initializeDatabase();
?>
