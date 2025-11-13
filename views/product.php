<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=htmlspecialchars($product['name'])?></title>
    <link rel="stylesheet" href="/css/neobrutalist.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container">
    <a href="/" class="back-link"><i class="icon fa-solid fa-arrow-left"></i> Volver</a>
    
    <div class="product-detail">
        <div>
            <img src="<?=htmlspecialchars($product['image_path']?:'/placeholder.png')?>" alt="<?=htmlspecialchars($product['name'])?>" class="product-detail-img">
        </div>
        <div>
            <h1><?=htmlspecialchars($product['name'])?></h1>
            <p class="product-desc"><?=nl2br(htmlspecialchars($product['description']))?></p>
            <p class="product-price"><?=number_format($product['price'],2)?>€</p>
            
            <form method="post" action="/cart" class="form">
                <input type="hidden" name="add_id" value="<?=$product['id']?>">
                <div class="form-group" style="display: flex; align-items: center; gap: 1rem;">
                    <label for="qty" class="form-label">Cantidad:</label> 
                    <input id="qty" name="qty" value="1" size="2" class="form-input" style="width: 80px; text-align: center;">
                </div>
                <button class="btn" type="submit"><i class="icon fa-solid fa-cart-shopping"></i> Agregar al carrito</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>