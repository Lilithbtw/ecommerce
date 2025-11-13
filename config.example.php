<?php
return [
  'db' => [
    'host' => '127.0.0.1',
    'port' => 3306,
    'user' => 'root',
    'pass' => '',
    'name' => 'ecommerce_db'
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
