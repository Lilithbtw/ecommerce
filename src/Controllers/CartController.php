<?php
namespace App\Controllers;
use App\DB;

class CartController {
    private $db;
    private $config;
    
    public function __construct($config){
        $this->config = $config;
        $this->db = new DB($config);
    }
    
    /**
     * Ver el carrito (GET /cart)
     */
    public function view(){
        $cart = $_SESSION['cart'] ?? [];
        include __DIR__.'/../../views/cart.php';
    }
    
    /**
     * Agregar producto al carrito (POST /cart/add)
     */
    public function add(){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            http_response_code(405);
            die('Método no permitido');
        }
        
        // Validar que el producto_id existe
        $product_id = intval($_POST['add_id'] ?? 0);
        if($product_id <= 0){
            http_response_code(400);
            die('ID de producto inválido');
        }
        
        // Obtener el producto de la BD
        $stmt = $this->db->pdo()->prepare('SELECT id, name, price FROM products WHERE id = ?');
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if(!$product){
            http_response_code(404);
            die('Producto no encontrado');
        }
        
        // Validar cantidad
        $qty = intval($_POST['qty'] ?? 1);
        if($qty <= 0 || $qty > 999){
            $qty = 1;
        }
        
        // Obtener o inicializar carrito
        $cart = $_SESSION['cart'] ?? [];
        
        // Agregar o actualizar producto en el carrito
        if(isset($cart[$product_id])){
            $cart[$product_id]['qty'] += $qty;
        } else {
            $cart[$product_id] = [
                'id'    => $product['id'],
                'name'  => $product['name'],
                'price' => (float)$product['price'],
                'qty'   => $qty
            ];
        }
        
        // Guardar en sesión
        $_SESSION['cart'] = $cart;
        
        // Redirigir al carrito
        header('Location: /cart');
        exit;
    }
    
    /**
     * Eliminar producto del carrito (POST /cart/remove/{id})
     */
    public function remove($id){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            http_response_code(405);
            die('Método no permitido');
        }
        
        $product_id = intval($id);
        
        if($product_id > 0 && isset($_SESSION['cart'][$product_id])){
            unset($_SESSION['cart'][$product_id]);
        }
        
        header('Location: /cart');
        exit;
    }
    
    /**
     * Vaciar carrito (POST /cart/clear)
     */
    public function clear(){
        $_SESSION['cart'] = [];
        header('Location: /cart');
        exit;
    }
}