<?php
namespace App\Controllers;
use App\DB;
use App\Invoice;
use PHPMailer\PHPMailer\PHPMailer;
class CheckoutController{
    private $db; private $config;
    public function __construct($config){
        $this->config=$config;
        $this->db=new DB($config);
    }
    public function createOrder(){
        // CSRF simple check
        if(empty($_POST['csrf']) || $_POST['csrf']!==($_SESSION['csrf'] ?? '')){ http_response_code(400); echo 'Invalid CSRF'; return; }
        $userId = $_SESSION['user_id'] ?? null;
        if(!$userId){ header('Location: /login'); exit; }
        $cart = $_SESSION['cart'] ?? [];
        if(empty($cart)){ echo 'Carrito vacío'; return; }
        // calculate total
        $total = 0.0;
        foreach($cart as $item){ $total += $item['price'] * $item['qty']; }
        $pdo = $this->db->pdo();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO orders (user_id,total,status) VALUES (?,?,?)');
            $stmt->execute([$userId,$total,'pending']);
            $orderId = $pdo->lastInsertId();
            $ins = $pdo->prepare('INSERT INTO order_items (order_id,product_id,quantity,price) VALUES (?,?,?,?)');
            foreach($cart as $item){
                $ins->execute([$orderId,$item['id'],$item['qty'],$item['price']]);
            }
            // Here you would create PayPal order and redirect. For scaffold: mark completed immediately.
            $pdo->commit();
            // generate invoice PDF
            $invoicePath = __DIR__.'/../../public/uploads/invoice_'.$orderId.'.pdf';
            $inv = new Invoice($this->config);
            $inv->generate($orderId,$invoicePath);
            // send email to user
            $stmt = $pdo->prepare('SELECT email,name FROM users WHERE id=?');
            $stmt->execute([$userId]); $u = $stmt->fetch();
            $mail = new PHPMailer(true);
            $mail->setFrom($this->config['mail']['from'],$this->config['mail']['from_name'] ?? 'E-Commerce');
            $mail->addAddress($u['email'],$u['name']);
            $mail->Subject = 'Confirmación de pedido #'.$orderId;
            $mail->Body = 'Gracias por su compra. Adjuntamos la factura.';
            $mail->addAttachment($invoicePath);
            // For real use, set SMTP params here (omitted in scaffold)
            $mail->isMail();
            $mail->send();
            // clear cart
            unset($_SESSION['cart']);
            echo 'Pedido creado y correo enviado. Pedido ID: '.$orderId;
        } catch (\Exception $e){
            $pdo->rollBack();
            error_log('Order error: '.$e->getMessage());
            echo 'Error creando el pedido.';
        }
    }
}
