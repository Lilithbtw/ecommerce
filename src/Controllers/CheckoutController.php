<?php
namespace App\Controllers;
use App\DB;

class CheckoutController {
    private $db;
    private $config;
    
    public function __construct($config){
        $this->config = $config;
        $this->db = new DB($config);
    }
    
    /**
     * Crear una orden (POST /checkout)
     */
    public function createOrder(){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            http_response_code(405);
            die('Método no permitido');
        }
        
        // Validar CSRF
        if(empty($_POST['csrf']) || $_POST['csrf'] !== ($_SESSION['csrf'] ?? '')){
            http_response_code(403);
            die('Token CSRF inválido');
        }
        
        // Validar que hay items en el carrito
        $cart = $_SESSION['cart'] ?? [];
        if(empty($cart)){
            http_response_code(400);
            die('El carrito está vacío');
        }
        
        // Obtener datos del usuario
        $user_id = $_SESSION['user_id'] ?? null;
        $customer_name = null;
        $customer_email = null;
        
        // Si es invitado, validar datos
        if(empty($user_id)){
            $customer_name = trim($_POST['name'] ?? '');
            $customer_email = trim($_POST['email'] ?? '');
            
            if(strlen($customer_name) < 3){
                http_response_code(400);
                die('El nombre debe tener al menos 3 caracteres');
            }
            
            if(!filter_var($customer_email, FILTER_VALIDATE_EMAIL)){
                http_response_code(400);
                die('Email inválido');
            }
        }
        
        // Calcular total
        $total = 0;
        foreach($cart as $item){
            $total += $item['price'] * $item['qty'];
        }
        
        try {
            // Iniciar transacción
            $pdo = $this->db->pdo();
            $pdo->beginTransaction();
            
            // 1. Crear la orden
            $stmt = $pdo->prepare('
                INSERT INTO orders (user_id, client_name, client_email, total, status, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ');
            $stmt->execute([
                $user_id,
                $customer_name ?? '',
                $customer_email ?? '',
                $total,
                'pending'
            ]);
            
            $order_id = $pdo->lastInsertId();
            
            // 2. Crear items de la orden
            $stmt = $pdo->prepare('
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ');
            
            foreach($cart as $item){
                $stmt->execute([
                    $order_id,
                    $item['id'],
                    $item['qty'],
                    $item['price']
                ]);
            }
            
            // Confirmar transacción
            $pdo->commit();
            
            // Limpiar carrito
            $_SESSION['cart'] = [];
            
            // Redirigir a página de éxito
            header('Location: /order-success?id=' . $order_id);
            exit;
            
        } catch(\Exception $e){
            $pdo->rollBack();
            http_response_code(500);
            die('Error al crear la orden: ' . htmlspecialchars($e->getMessage()));
        }
    }
    
    /**
     * Mostrar página de éxito (GET /order-success?id=X)
     */
    public function success(){
        $order_id = intval($_GET['id'] ?? 0);
        
        if($order_id <= 0){
            http_response_code(400);
            die('ID de orden inválido');
        }
        
        // Obtener datos de la orden
        $stmt = $this->db->pdo()->prepare('
            SELECT o.*, 
                   GROUP_CONCAT(oi.product_id) as product_ids,
                   GROUP_CONCAT(oi.quantity) as quantities,
                   GROUP_CONCAT(oi.price) as prices
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.id = ?
            GROUP BY o.id
        ');
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();
        
        if(!$order){
            http_response_code(404);
            die('Orden no encontrada');
        }
        
        // Obtener items detallados
        $stmt = $this->db->pdo()->prepare('
            SELECT oi.*, p.name
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ');
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll();
        
        include __DIR__.'/../../views/order-success.php';
    }
    
    /**
     * Descargar factura en PDF (GET /invoice?id=X)
     */
    public function invoice(){
        define('EURO', chr(128));
        $order_id = intval($_GET['id'] ?? 0);
        
        if($order_id <= 0){
            http_response_code(400);
            die('ID de orden inválido');
        }
        
        // Obtener datos de la orden
        $stmt = $this->db->pdo()->prepare('SELECT * FROM orders WHERE id = ?');
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();
        
        if(!$order){
            http_response_code(404);
            die('Orden no encontrada');
        }
        
        // Obtener items de la orden
        $stmt = $this->db->pdo()->prepare('
            SELECT oi.*, p.name
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ');
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll();
        
        // Crear PDF
        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        
        // Encabezado
        $pdf->Cell(0, 10, 'FACTURA', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Ln(5);
        
        // Información de la orden
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(50, 7, 'Numero de Factura:');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 7, '#' . $order['id'], 0, 1);
        
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(50, 7, 'Fecha:');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 7, date('d/m/Y H:i', strtotime($order['created_at'])), 0, 1);
        
        $pdf->Ln(5);
        
        // Datos del cliente
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 7, 'DATOS DEL CLIENTE', 0, 1);
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(50, 7, 'Nombre:');
        $pdf->Cell(0, 7, $order['client_name'], 0, 1);
        
        $pdf->Cell(50, 7, 'Email:');
        $pdf->Cell(0, 7, $order['client_email'], 0, 1);
        
        $pdf->Ln(5);
        
        // Tabla de productos
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell(80, 7, 'Producto', 1, 0, 'L', true);
        $pdf->Cell(30, 7, 'Cantidad', 1, 0, 'C', true);
        $pdf->Cell(40, 7, 'Precio Unit.', 1, 0, 'R', true);
        $pdf->Cell(40, 7, 'Subtotal', 1, 1, 'R', true);
        
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetFillColor(255, 255, 255);
        
        foreach($items as $item){
            $subtotal = $item['price'] * $item['quantity'];
            $pdf->Cell(80, 6, substr($item['name'], 0, 40), 1, 0, 'L');
            $pdf->Cell(30, 6, $item['quantity'], 1, 0, 'C');
            $pdf->Cell(40, 6, number_format($item['price'], 2) . EURO, 1, 0, 'R');
            $pdf->Cell(40, 6, number_format($subtotal, 2) . EURO, 1, 1, 'R');
        }
        
        // Total
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell(140, 7, 'TOTAL:', 1, 0, 'R', true);
        $pdf->Cell(40, 7, number_format($order['total'], 2) . EURO, 1, 1, 'R', true);
        
        $pdf->Ln(10);
        
        // Pie de página
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 7, 'Gracias por tu compra', 0, 1, 'C');
        
        // Descargar PDF
        $filename = 'Factura_' . $order['id'] . '.pdf';
        $pdf->Output('D', $filename);
        exit;
    }
}