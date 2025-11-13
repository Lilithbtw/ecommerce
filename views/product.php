<!doctype html><html><head><meta charset="utf-8"><title><?=htmlspecialchars($product['name'])?></title></head><body>
<a href="/">Volver</a>
<h1><?=htmlspecialchars($product['name'])?></h1>
<img src="<?=htmlspecialchars($product['image_path']?:'/placeholder.png')?>" style="max-width:300px">
<p><?=nl2br(htmlspecialchars($product['description']))?></p>
<p><strong><?=number_format($product['price'],2)?>€</strong></p>
<form method="post" action="/cart">
  <input type="hidden" name="add_id" value="<?=$product['id']?>">
  Cantidad: <input name="qty" value="1" size="2">
  <button>Agregar al carrito</button>
</form>
</body></html>
