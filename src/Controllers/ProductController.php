<?php
namespace App\Controllers;
use App\DB;
class ProductController{
    private $db; private $config;
    public function __construct($config){
        $this->config = $config;
        $this->db = new DB($config);
    }
    public function view($id){
        $stmt = $this->db->pdo()->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        if(!$product){ http_response_code(404); echo 'Producto no encontrado'; return; }
        include __DIR__.'/../../views/product.php';
    }
}
