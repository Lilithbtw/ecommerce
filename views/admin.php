<!doctype html><html><head><meta charset="utf-8"><title>Admin</title></head><body>
<h1>Panel Admin</h1>
<a href="/admin/upload">Subir producto</a> | <a href="/logout">Logout</a>
<table border=1 cellpadding=6>
<tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Imagen</th></tr>
<?php foreach($products as $p): ?>
<tr>
<td><?= $p['id'] ?></td>
<td><?= htmlspecialchars($p['name']) ?></td>
<td><?= number_format($p['price'],2) ?>€</td>
<td><?php if($p['image_path']): ?><img src="<?=htmlspecialchars($p['image_path'])?>" style="max-width:80px"><?php endif;?></td>
</tr>
<?php endforeach; ?>
</table>
</body></html>
