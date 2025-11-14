<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Brutal Shop - Productos</title>
  <link rel="stylesheet" href="/css/neobrutalist.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="container">
    <!-- Navigation -->
    <nav class="nav">
      <a href="/"><i class="fas fa-home icon"></i>Inicio</a>
      <a href="/cart"><i class="fas fa-shopping-cart icon"></i>Carrito</a>
      <?php if(!empty($_SESSION['user_id'])): ?>
        <a href="/logout"><i class="fas fa-sign-out-alt icon"></i>Cerrar Sesión</a>
        <?php
          // Verificar si es admin
          $stmt = (new App\DB(require __DIR__.'/../config.php'))->pdo()->prepare('SELECT is_admin FROM users WHERE id=?');
          $stmt->execute([$_SESSION['user_id']]);
          $user = $stmt->fetch();
          if($user && $user['is_admin']):
        ?>
          <a href="/admin"><i class="fas fa-cog icon"></i>Admin</a>
        <?php endif; ?>
      <?php else: ?>
        <a href="/register"><i class="fas fa-user-plus icon"></i>Registrarse</a>
        <a href="/login"><i class="fas fa-sign-in-alt icon"></i>Login</a>
      <?php endif; ?>
    </nav>

    <!-- Hero Section -->
    <div class="card">
      <h1><i class="fas fa-bolt"></i> Brutal Shop</h1>
      <p style="font-size: 1.2rem; margin-bottom: 0;">Productos únicos con estilo brutal. Sin florituras, solo calidad.</p>
    </div>

    <!-- Products Grid -->
    <div class="product-grid">
      <?php foreach($products as $p): ?>
        <div class="product-card">
          <img src="<?= htmlspecialchars($p['image_path'] ?: '/placeholder.png') ?>" 
               alt="<?= htmlspecialchars($p['name']) ?>" 
               class="product-img">
          
          <div class="product-body">
            <h3 class="product-title"><?= htmlspecialchars($p['name']) ?></h3>
            <p class="product-desc"><?= htmlspecialchars(substr($p['description'], 0, 100)) ?>...</p>
            <div class="product-price"><?= number_format($p['price'], 2) ?>€</div>
            <a href="/product/<?= $p['id'] ?>" class="btn btn-small" style="display: inline-block; text-decoration: none;">
              <i class="fas fa-eye icon"></i>Ver Producto
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>