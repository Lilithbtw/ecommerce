<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="/css/neobrutalist.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- ESTILOS PARA FORZAR LAS LÍNEAS GRUESAS Y CONSISTENTES DE LA TABLA -->
    <style>
        .table {
            border-collapse: collapse; 
            width: 100%;
        }
        .table th {
            background-color: #000;
            color: #fff;
            padding: 1rem;
            text-align: left;
            border-bottom: 3px solid #000;
        }
        .table td {
            padding: 1rem;
            border-bottom: 3px solid #000;
            vertical-align: middle;
        }
        .actions-cell {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        .actions-cell form {
            margin: 0;
            display: inline-block;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Panel Admin</h1>
    <div class="nav">
        <a href="/admin/upload"><i class="icon fa-solid fa-cloud-arrow-up"></i> Subir producto</a>
        <a href="/admin/users"><i class="icon fa-solid fa-users"></i> Ver Usuarios</a>
        <a href="/logout" class="btn btn-secondary btn-small"><i class="icon fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Imagen</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($products as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= number_format($p['price'],2) ?>€</td>
                <td><?php if($p['image_path']): ?><img src="<?=htmlspecialchars($p['image_path'])?>" alt="Product Image" class="table-img"><?php endif;?></td>
                
                <td class="actions-cell">
                    <a href="/admin/edit/<?= $p['id'] ?>" class="btn btn-small">
                        <i class="icon fa-solid fa-pencil"></i> Editar
                    </a>
                    
                    <form method="post" action="/admin/delete/<?= $p['id'] ?>" onsubmit="return confirm('¿Estás seguro de que quieres borrar este producto?');">
                        <input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf']??'')?>">
                        <button type="submit" class="btn btn-small btn-danger">
                            <i class="icon fa-solid fa-trash"></i> Borrar
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>