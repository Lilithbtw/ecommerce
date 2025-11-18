<?php
namespace App;

use PDO;
use PDOException;

class DB
{
    private ?PDO $pdo = null;

    /**
     * Constructor for the Database connection class.
     * Loads the database configuration from /config.php directly.
     * * @throws PDOException if the database connection fails.
     */
    public function __construct()
    {
        // 1. Define the path to the configuration file relative to src/DB.php
        $configPath = __DIR__ . '/../config.php';

        // 2. Load the full configuration array
        if (!file_exists($configPath)) {
            throw new \Exception("Configuration file not found at: " . $configPath);
        }
        
        $fullConfig = require $configPath;
        
        // 3. Extract the database specific configuration
        if (!isset($fullConfig['db'])) {
            throw new \Exception("Database configuration section 'db' is missing in config.php");
        }
        
        $config = $fullConfig['db'];
        
        // Use configuration settings
        $host = $config['host'] ?? 'localhost';
        $database = $config['database'] ?? 'test';
        $user = $config['user'] ?? 'root';
        $password = $config['password'] ?? '';
        
        $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $password, $options);
        } catch (PDOException $e) {
            // Re-throw exception with a clearer message
            throw new PDOException("Database connection failed: " . $e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * Returns the PDO instance.
     * * @return PDO The connected PDO instance.
     */
    public function pdo(): PDO
    {
        return $this->pdo;
    }
}