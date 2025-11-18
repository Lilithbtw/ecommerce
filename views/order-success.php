<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado</title>
    <link rel="stylesheet" href="/css/neobrutalist.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .success-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            border: 2px solid #000;
            background: #f0f0f0;
        }
        .success-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .success-icon {
            font-size: 3rem;
            color: #28a745;
            margin-bottom: 1rem;
        }
        .order-details {
            background: white;
            padding: 1rem;
            border: 1px solid #000;
            margin: 1rem 0;
        }
        .order-details p {
            margin: 0.5rem 0;
            display: flex;
            justify-content: space-between;
        }
        .order-details strong {
            min-width: 150px;
        }
        .order-items {
            margin: 1.5rem 0;
        }
        .order-items table {
            width: 100%;
            border-collapse: collapse;
        }
        .order-items th,
        .order-items td {
            border: 1px solid #000;
            padding: 0.5rem;
            text-align: left;
        }
        .order-items th {
            background: #ddd;
        }
        .total-row {
            font-weight: bold;
            font-size: 1.2rem;
            margin-top: 1rem;
            text-align: right;
        }
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        .btn-primary {
            background: #000;
            color: white;
            padding: 0.75rem 1.5rem;
            border: 2px solid #000;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-primary:hover {
            background: #333;
        }
        .btn-secondary {
            background: white;
            color: #000;
            padding: 0.75rem 1.5rem;
            border: 2px solid #000;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-secondary:hover {
            background: #f0f0f0;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="/" class="back-link"><i class="icon fa-solid fa-arrow-left"></i> Volver al inicio</a>
    
    <div class="success-container">
        <div class="success-header">
            <div class="success-icon">
                <i class="fa-solid fa-check-circle"></i>
            </div>
            <h1>¡Pedido Confirmado!</h1>
            <p>Gracias por tu compra</p>
        </div>
        
        <div class="order-details">
            <p>
                <strong>Número de pedido:</strong>
                <span>#<?= htmlspecialchars($order['id']) ?></span>
            </p>
            <p>
                <strong>Fecha:</strong>
                <span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
            </p>
            <p>
                <strong>Estado:</strong>
                <span><?= htmlspecialchars($order['status']) ?></span>
            </p>
        </div>
        
        <div class="order-details">
            <p>
                <strong>Nombre:</strong>
                <span><?= htmlspecialchars($order['client_name']) ?></span>
            </p>
            <p>
                <strong>Email:</strong>
                <span><?= htmlspecialchars($order['client_email']) ?></span>
            </p>
        </div>
        
        <div class="order-items">
            <h3>Artículos del pedido</h3>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= intval($item['quantity']) ?></td>
                        <td><?= number_format($item['price'], 2) ?>€</td>
                        <td><?= number_format($item['price'] * $item['quantity'], 2) ?>€</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="total-row">
                Total: <?= number_format($order['total'], 2) ?>€
            </div>
        </div>
        
        <p style="text-align: center; margin-top: 1.5rem; color: #666;">
            Se ha enviado un email de confirmación a <strong><?= htmlspecialchars($order['client_email']) ?></strong>
        </p>
        
        <div class="action-buttons">
            <a href="/" class="btn-primary">Seguir comprando</a>
            <a href="/invoice?id=<?= $order['id'] ?>" class="btn-secondary">
                <i class="fa-solid fa-file-pdf"></i> Descargar factura
            </a>
        </div>
    </div>
</div>
</body>
</html>