<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito</title>
    <link rel="stylesheet" href="/css/neobrutalist.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container">
    <a href="/" class="back-link"><i class="icon fa-solid fa-arrow-left"></i> Volver</a>
    <h1>Carrito</h1>
    <?php if(empty($cart)): ?>
        <div class="cart-empty">
            <p>Carrito vacío</p>
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr><th>Producto</th><th>Cantidad</th><th>Precio</th></tr>
            </thead>
            <tbody>
                <?php $total = 0; ?>
                <?php foreach($cart as $c): ?>
                    <tr>
                        <td><?=htmlspecialchars($c['name'])?></td>
                        <td><?=intval($c['qty'])?></td>
                        <td><?=number_format($c['price'],2)?>€</td>
                    </tr>
                    <?php $total += $c['price'] * $c['qty']; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="cart-total">
            Total: <?=number_format($total, 2)?>€
        </div>
        
        <form method="post" action="/checkout" style="text-align: right; margin-top: 1rem;">
            <input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf']??'')?>">
            <button class="btn"><i class="icon fa-solid fa-credit-card"></i> Realizar compra</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>