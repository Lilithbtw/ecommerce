<?php
namespace App;
use PDO;
class DB {
    private $pdo;

    public function __construct($config){
        $dbConfig = $config['db']; // Assuming config is loaded under 'db' key
        
        // Build the DSN string using correct keys: host, port, and database
        $dsn = 'mysql:host=' . $dbConfig['host'] . 
               ';port=' . ($dbConfig['port'] ?? 3306) . // Use default if port is not defined
               ';dbname=' . $dbConfig['database'];
        
        // Connect using the correct keys: user and password
        $this->pdo = new PDO(
            $dsn, 
            $dbConfig['user'], 
            $dbConfig['password'], // This is the corrected key!
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        );
    }
    
    public function pdo(){
        return $this->pdo;
    }
}