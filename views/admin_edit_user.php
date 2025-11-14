<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Editar Usuario</title>
    <link rel="stylesheet" href="/css/neobrutalist.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container">
    <h1>Admin - Editar Usuario: <?= htmlspecialchars($user['name']) ?></h1>
    <a href="/admin/users" class="back-link"><i class="icon fa-solid fa-arrow-left"></i> Volver a Usuarios</a>

    <?php if ($error): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="/admin/users/edit/<?= $user['id'] ?>" class="form-card">
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?? '' ?>">

        <label for="name">Nombre:</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? $user['name']) ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>" required>
        
        <label for="password">Cambiar Contraseña (Dejar vacío para mantener la actual):</label>
        <input type="password" id="password" name="password">
        
        <div style="display: flex; align-items: center; margin-top: 1rem;">
            <input type="checkbox" id="is_admin" name="is_admin" 
                   <?= (isset($_POST['is_admin']) || ($user['is_admin'] && !isset($_POST['name']))) ? 'checked' : '' ?> 
                   style="width: auto; margin-right: 10px;">
            <label for="is_admin" style="margin-bottom: 0;">Es Administrador</label>
        </div>

        <button type="submit" class="btn primary">Guardar Cambios</button>
    </form>
</div>
</body>
</html>