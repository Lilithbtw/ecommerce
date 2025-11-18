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
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'] ?? '') ?>">
            
            <?php if(empty($_SESSION['user_id'])): ?>
                <div style="text-align: left; margin-top: 2rem; margin-bottom: 1rem; padding: 10px; border: 1px solid #000;">
                    <p><strong>¡Estás comprando como invitado!</strong> Por favor, proporciona tu información de contacto:</p>
                    
                    <div class="form-group">
                        <label for="checkout_name" class="form-label">Nombre completo:</label>
                        <input 
                            type="text" 
                            id="checkout_name" 
                            name="name" 
                            required 
                            class="form-input" 
                            value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                            minlength="3"
                            maxlength="100"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="checkout_email" class="form-label">Email de contacto:</label>
                        <input 
                            type="email" 
                            id="checkout_email" 
                            name="email" 
                            required 
                            class="form-input" 
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            maxlength="255"
                        >
                    </div>
                </div>
            <?php else: ?>
                <p style="text-align: left;">
                    <strong>Tu pedido se asociará a tu cuenta de usuario</strong> (ID: <?= htmlspecialchars($_SESSION['user_id']) ?>).
                </p>
            <?php endif; ?>
            
            <button type="submit" class="btn" name="action" value="checkout">
                <i class="icon fa-solid fa-credit-card"></i> Realizar compra
            </button>
        </form>
        <?php endif; ?>
</div>
</body>
</html>