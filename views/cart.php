<!doctype html><html><head><meta charset="utf-8"><title>Carrito</title></head><body>
<a href="/">Volver</a>
<h1>Carrito</h1>
<?php if(empty($cart)): ?><p>Carrito vacío</p><?php else: ?>
  <table border=1 cellpadding=6>
    <tr><th>Producto</th><th>Cantidad</th><th>Precio</th></tr>
    <?php foreach($cart as $c): ?>
      <tr><td><?=htmlspecialchars($c['name'])?></td><td><?=intval($c['qty'])?></td><td><?=number_format($c['price'],2)?>€</td></tr>
    <?php endforeach; ?>
  </table>
  <form method="post" action="/checkout">
    <input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf']??'')?>">
    <button>Realizar compra</button>
  </form>
<?php endif; ?>
</body></html>
