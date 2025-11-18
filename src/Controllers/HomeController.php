<?php
namespace App\Controllers;
use App\DB;
class HomeController {
    private $db;
    private $config;
    public function __construct($config){
        $this->config = $config;
        $this->db = new DB($config);
    }
    public function index(){
        $stmt = $this->db->pdo()->query('SELECT id,name,description,price,image_path FROM products');
        $products = $stmt->fetchAll();
        include __DIR__.'/../../views/home.php';
    }
}
