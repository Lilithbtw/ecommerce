<?php
namespace App;
use setasign\Fpdi\Fpdf\Fpdf;
class Invoice {
    private $db;
    public function __construct($config){
        $this->db = (new DB($config))->pdo();
    }
    public function generate($orderId, $path){
        // fetch order and items
        $stmt = $this->db->prepare('SELECT o.*, u.name,u.email FROM orders o JOIN users u ON u.id=o.user_id WHERE o.id=?');
        $stmt->execute([$orderId]); $order = $stmt->fetch();
        $it = $this->db->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?');
        $it->execute([$orderId]); $items = $it->fetchAll();
        $pdf = new \setasign\Fpdf\Fpdf();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(0,10,'Factura - Pedido #'.$orderId,0,1);
        $pdf->SetFont('Arial','',12);
        $pdf->Cell(0,8,'Cliente: '.$order['name'],0,1);
        $pdf->Cell(0,8,'Email: '.$order['email'],0,1);
        $pdf->Ln(5);
        foreach($items as $i){
            $pdf->Cell(120,8,$i['name'],0,0);
            $pdf->Cell(30,8,$i['quantity'].' x '.number_format($i['price'],2).'€',0,0,'R');
            $pdf->Ln(8);
        }
        $pdf->Ln(5);
        $pdf->Cell(0,8,'Total: '.number_format($order['total'],2).'€',0,1);
        $pdf->Output('F',$path);
    }
}
