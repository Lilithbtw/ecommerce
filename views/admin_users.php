<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Usuarios</title>
    <link rel="stylesheet" href="/css/neobrutalist.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
    </style>
</head>
<body>
<div class="container">
    <h1>Admin - Usuarios</h1>
    <a href="/admin" class="back-link"><i class="icon fa-solid fa-arrow-left"></i> Volver al admin</a>
    
    <a href="/admin/users/create" class="btn primary" style="margin-bottom: 20px;">
        <i class="icon fa-solid fa-user-plus"></i> Crear Nuevo Usuario
    </a>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Es Admin</th>
                <th>Acciones</th> 
            </tr>
        </thead>
        <tbody>
            <?php foreach($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= $user['is_admin'] ? 'Sí' : 'No' ?></td>
                <td>
                    <a href="/admin/users/edit/<?= $user['id'] ?>" class="btn secondary small">
                        <i class="icon fa-solid fa-pen-to-square"></i> Editar
                    </a>
                    </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>