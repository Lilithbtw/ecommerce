<!doctype html><html><head><meta charset="utf-8"><title>Subir producto</title></head><body>
<h1>Subir nuevo producto</h1>
<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf']??'')?>">
  Nombre: <input name="name"><br>
  Descripción: <textarea name="description"></textarea><br>
  Precio: <input name="price"><br>
  Imagen: <input type="file" name="image" accept="image/*"><br>
  <button>Subir</button>
</form>
</body></html>
