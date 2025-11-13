<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="/css/neobrutalist.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container">
    <h1>Panel Admin</h1>
    <div class="nav">
        <a href="/admin/upload"><i class="icon fa-solid fa-cloud-arrow-up"></i> Subir producto</a>
        <a href="/logout" class="btn btn-secondary btn-small"><i class="icon fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Imagen</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($products as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= number_format($p['price'],2) ?>€</td>
                <td><?php if($p['image_path']): ?><img src="<?=htmlspecialchars($p['image_path'])?>" alt="Product Image" class="table-img"><?php endif;?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>