<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir producto</title>
    <link rel="stylesheet" href="/css/neobrutalist.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container">
    <h1>Subir nuevo producto</h1>
    <a href="/admin" class="back-link"><i class="icon fa-solid fa-arrow-left"></i> Volver</a>
    
    <?php if (isset($error) && $error): ?>
    <script>
        // Muestra la alerta con el mensaje de error y permite volver a intentar.
        alert('Error al subir el producto: <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>');
        // Opcionalmente, puedes redirigir si no quieres que el usuario edite el formulario con errores.
        // window.location.href = '/upload'; 
    </script>
    <div style="padding: 10px; background-color: #fdd; border: 1px solid #f99; margin-bottom: 15px;">
        Error: <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <div class="upload-form">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf']??'')?>">
            
            <div class="form-group">
                <label for="name" class="form-label">Nombre:</label>
                <input id="name" name="name" class="form-input" value="<?=htmlspecialchars($_POST['name']??'')?>">
            </div>
            
            <div class="form-group">
                <label for="description" class="form-label">Descripción:</label>
                <textarea id="description" name="description" class="form-textarea"><?=htmlspecialchars($_POST['description']??'')?></textarea>
            </div>
            
            <div class="form-group">
                <label for="price" class="form-label">Precio:</label>
                <input id="price" name="price" class="form-input" type="number" step="0.01" value="<?=htmlspecialchars($_POST['price']??'')?>">
            </div>
            
            <div class="form-group">
                <label for="image" class="form-label">Imagen:</label>
                <input id="image" type="file" name="image" accept="image/jpeg, image/png" class="form-input">
            </div>
            
            <button class="btn btn-secondary" type="submit"><i class="icon fa-solid fa-upload"></i> Subir</button>
        </form>
    </div>
</div>
</body>
</html>