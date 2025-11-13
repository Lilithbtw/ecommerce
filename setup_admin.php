<?php
// setup_admin.php

// This script reads ADMIN_EMAIL and ADMIN_PASSWORD from environment variables,
// hashes the password securely, and updates the admin user in the database.

// Assuming this script is run from the project root.
require __DIR__ . '/vendor/autoload.php';

// Load config (to get DB parameters)
$config = require __DIR__ . '/config.php';

// --- Environment Variable Checks ---
$adminEmail = getenv('ADMIN_EMAIL');
$adminPassword = getenv('ADMIN_PASSWORD');

if (!$adminEmail || !$adminPassword) {
    // Fallback to the hardcoded email if variables are not set, but skip if password is not set
    if (getenv('ADMIN_EMAIL')) {
        echo "ADMIN_PASSWORD environment variable not set. Skipping admin setup for security.\n";
    } else {
        echo "ADMIN_EMAIL or ADMIN_PASSWORD environment variables not set. Skipping custom admin setup.\n";
    }
    exit(0);
}

// 1. Hash the password using the application's required algorithm (PASSWORD_DEFAULT)
$hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

// 2. Initialize DB connection
try {
    $dbClass = 'App\\DB';
    // Use the DB class logic to connect
    $db = new $dbClass($config);
    $pdo = $db->pdo();
} catch (\Exception $e) {
    error_log("Failed to connect to DB for admin setup: " . $e->getMessage());
    echo "Failed to connect to DB. Check DB configuration in config.php.\n";
    exit(1);
}

// 3. Insert or Update the admin user
// This ensures that if the admin user exists, their password is updated; otherwise, a new one is created.
$stmt = $pdo->prepare("
    INSERT INTO users (email, password, is_admin, name) 
    VALUES (?, ?, 1, 'Site Admin')
    ON DUPLICATE KEY UPDATE 
        password = VALUES(password), 
        is_admin = VALUES(is_admin),
        name = VALUES(name);
");

try {
    $stmt->execute([$adminEmail, $hashedPassword]);
    echo "Admin user '$adminEmail' created/updated successfully using environment variables.\n";
} catch (\PDOException $e) {
    error_log("Failed to update admin user: " . $e->getMessage());
    echo "Failed to update admin user. Check the database schema.\n";
    exit(1);
}

?>