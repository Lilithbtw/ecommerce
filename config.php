<?php
// config.php

// This file centralizes all application configuration, reading values from environment variables
// (e.g., set via docker-compose) or providing safe fallbacks.

return [
    // Database Configuration
    'db' => [
        'host' => getenv('MYSQL_HOST') ?: 'db', 
        'database' => getenv('MYSQL_DATABASE') ?: 'ecommerce_db',
        'user' => getenv('MYSQL_USER') ?: 'root',
        'password' => getenv('MYSQL_PASSWORD') ?: 'secret',
    ],
    
    // Redsys Configuration
    'redsys' => [
        // The Secret Key, typically provided in Hexadecimal format by Redsys.
        // You MUST define this as an environment variable (e.g., REDSYS_SECRET_KEY)
        'secret_key' => getenv('REDSYS_SECRET_KEY') ?: '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF', 
        
        // FUC (Código Comercio) - Environment variable or Test Code (999008881 is for testing)
        'fuc' => getenv('REDSYS_FUC') ?: '999008881', 
        
        // Terminal ID - Standard '001' (Environment variable or fallback)
        'terminal_id' => getenv('REDSYS_TERMINAL_ID') ?: '001',
        
        // Currency: 978 (EUR), 840 (USD), etc.
        'currency' => getenv('REDSYS_CURRENCY') ?: '978',
        
        // Transaction Type: 0 (Authorization)
        'transaction_type' => getenv('REDSYS_TRANSACTION_TYPE') ?: '0',
        
        // Consumer Language: 001 (Spanish), 002 (English), etc.
        'consumer_language' => getenv('REDSYS_CONSUMER_LANGUAGE') ?: '001',

        // API URL - Use test URL for development (sis-t.redsys.es)
        'api_url' => getenv('REDSYS_API_URL') ?: 'https://sis-t.redsys.es:25443/sis/realizarPago', 
        // For production, this should be: 'https://sis.redsys.es/sis/realizarPago'
    ],
];