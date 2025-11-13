<!doctype html>
<html><head><meta charset="utf-8"><title>Tienda</title>
<style>body{font-family:Arial,Helvetica,sans-serif;padding:20px} .prod{border:1px solid #ddd;padding:10px;margin:10px;display:inline-block;width:220px;vertical-align:top}</style>
</head><body>
<h1>Productos</h1>
<a href="/admin">Admin</a> | <a href="/cart">Carrito</a> | <a href="/login">Login</a>
<div>
<?php foreach($products as $p): ?>
  <div class="prod">
    <img src="<?= htmlspecialchars($p['image_path']?:'/placeholder.png') ?>" style="max-width:200px;height:120px;object-fit:cover">
    <h3><?= htmlspecialchars($p['name']) ?></h3>
    <p><?= htmlspecialchars(substr($p['description'],0,100)) ?>...</p>
    <p><strong><?= number_format($p['price'],2) ?>€</strong></p>
    <a href="/product/<?= $p['id'] ?>">Ver</a>
  </div>
<?php endforeach; ?>
</div>
</body></html>
