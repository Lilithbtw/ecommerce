<?php
return [
'db' => [
        'host' => getenv('MYSQL_HOST') ?: 'localhost', // Reads 'db' from env
        'user' => getenv('MYSQL_USER') ?: 'root',
        'password' => getenv('MYSQL_PASSWORD') ?: 'secret',
        'database' => getenv('MYSQL_DATABASE') ?: 'ecommerce_db',
    ],
  'paypal' => [
    'client_id' => 'PAYPAL_SANDBOX_CLIENT_ID',
    'secret' => 'PAYPAL_SANDBOX_SECRET',
    'mode' => 'sandbox' // or 'live'
  ],
  'mail' => [
    'from' => 'no-reply@example.com',
    'from_name' => 'Mi E-Commerce'
  ],
  'upload_dir' => __DIR__ . '/public/uploads'
];
