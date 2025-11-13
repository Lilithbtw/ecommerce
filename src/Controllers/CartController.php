<?php
namespace App\Controllers;
class CartController{
    private $config;
    public function __construct($config){ $this->config = $config; }
    public function index(){
        $cart = $_SESSION['cart'] ?? [];
        include __DIR__.'/../../views/cart.php';
    }
}
