<?php
namespace App\Controllers;
use App\DB;
class AdminController{
    private $db; private $config;
    public function __construct($config){
        $this->config=$config;
        $this->db=new DB($config);
    }
    private function requireAdmin(){
        if(empty($_SESSION['user_id'])){ header('Location:/login'); exit; }
        $stmt = $this->db->pdo()->prepare('SELECT is_admin FROM users WHERE id=?');
        $stmt->execute([$_SESSION['user_id']]);
        $r = $stmt->fetch();
        if(!$r || !$r['is_admin']){ http_response_code(403); echo 'Forbidden'; exit; }
    }
    public function index(){
        $this->requireAdmin();
        $stmt = $this->db->pdo()->query('SELECT * FROM products ORDER BY id DESC');
        $products = $stmt->fetchAll();
        include __DIR__.'/../../views/admin.php';
    }

    // Inside class AdminController{ ...

// ... (other methods like index(), users(), upload(), etc.)

    public function createUser(){
        $this->requireAdmin();
        $error = null;
        $success = null;

        if($_SERVER['REQUEST_METHOD']==='POST'){
            if(empty($_POST['csrf']) || $_POST['csrf']!==($_SESSION['csrf'] ?? '')){ 
                $error = 'Token CSRF inválido.'; 
            }
            
            if(!$error){
                $name = trim($_POST['name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $isAdmin = isset($_POST['is_admin']);

                if(empty($name) || empty($email) || empty($password)){
                    $error = 'Todos los campos son obligatorios.';
                }

                if(!$error && !filter_var($email, FILTER_VALIDATE_EMAIL)){
                    $error = 'Formato de correo electrónico no válido.';
                }
                
                // Check if user already exists
                if(!$error){
                    $stmt = $this->db->pdo()->prepare('SELECT id FROM users WHERE email=?');
                    $stmt->execute([$email]);
                    if($stmt->fetch()){
                        $error = 'Ya existe un usuario con este correo electrónico.';
                    }
                }
                
                if(!$error){
                    // Hash the password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert the new user
                    // NOTE: Assumes you have 'created_at' and 'is_admin' columns
                    $stmt = $this->db->pdo()->prepare(
                        // Removed 'created_at' from the list of columns
                        'INSERT INTO users (name, email, password, is_admin) VALUES (?, ?, ?, ?)'
                    );
                    // Removed NOW() from the values
                    $stmt->execute([$name, $email, $hashedPassword, $isAdmin ? 1 : 0]);
                    
                    $success = 'Usuario creado correctamente.';
                    
                    // Clear POST data after successful creation to prevent re-submission
                    $_POST = [];
                }
            }
        }
        
        // Ensure a CSRF token exists for the form
        if(empty($_SESSION['csrf'])){
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        
        include __DIR__.'/../../views/admin_create_user.php';
    }

    // Inside class AdminController{ ...

// ... (other methods like users(), createUser(), etc.)

    public function editUser($id){
        $this->requireAdmin();
        $error = null;
        $success = null;

        // 1. Fetch the user
        $stmt = $this->db->pdo()->prepare('SELECT id, name, email, is_admin FROM users WHERE id=?');
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if(!$user){
            http_response_code(404); 
            echo 'Usuario no encontrado'; 
            return;
        }

        if($_SERVER['REQUEST_METHOD']==='POST'){
            if(empty($_POST['csrf']) || $_POST['csrf']!==($_SESSION['csrf'] ?? '')){ 
                $error = 'Token CSRF inválido.'; 
            }
            
            if(!$error){
                $name = trim($_POST['name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $isAdmin = isset($_POST['is_admin']);

                if(empty($name) || empty($email)){
                    $error = 'Nombre y Email son obligatorios.';
                }

                if(!$error && !filter_var($email, FILTER_VALIDATE_EMAIL)){
                    $error = 'Formato de correo electrónico no válido.';
                }
                
                // 2. Prepare the update query
                $sql = 'UPDATE users SET name=?, email=?, is_admin=?';
                $params = [$name, $email, $isAdmin ? 1 : 0, $id];
                
                // Handle optional password change
                if(!empty($password)){
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $sql .= ', password=?';
                    // Insert the password parameter before the ID
                    array_splice($params, count($params) - 1, 0, [$hashedPassword]);
                }
                
                $sql .= ' WHERE id=?';

                if(!$error){
                    $stmt = $this->db->pdo()->prepare($sql);
                    $stmt->execute($params);
                    
                    $success = 'Usuario actualizado correctamente.';
                    
                    // 3. Re-fetch user data after update (excluding password)
                    $stmt = $this->db->pdo()->prepare('SELECT id, name, email, is_admin FROM users WHERE id=?');
                    $stmt->execute([$id]);
                    $user = $stmt->fetch();
                }
            }
        }
        
        // Ensure CSRF token is available for the form
        if(empty($_SESSION['csrf'])){
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        
        include __DIR__.'/../../views/admin_edit_user.php';
    }

// ... }
    
    public function users(){
        $this->requireAdmin();
        $stmt = $this->db->pdo()->query('SELECT id, name, email, is_admin FROM users ORDER BY id DESC');
        $users = $stmt->fetchAll();
        include __DIR__.'/../../views/admin_users.php';
    }
    
    public function upload(){
        $this->requireAdmin();
        $error = null;
        if($_SERVER['REQUEST_METHOD']==='POST'){
            if(empty($_POST['csrf']) || $_POST['csrf']!==($_SESSION['csrf'] ?? '')){ $error = 'Token CSRF inválido.'; }
            if(!$error){
                $name = trim($_POST['name'] ?? '');
                $desc = trim($_POST['description'] ?? '');
                $price = number_format((float)$_POST['price'],2,'.','');
                
                if(!empty($_FILES['image']) && $_FILES['image']['error']===UPLOAD_ERR_OK){
                    try {
                        $up = $this->saveUploadedImage($_FILES['image']);
                    } catch (\Exception $e) {
                        $error = $e->getMessage();
                        $up = null;
                    }
                } else { 
                    $up = null; 
                }
                
                if(!$error){
                    $stmt = $this->db->pdo()->prepare('INSERT INTO products (name,description,price,image_path) VALUES (?,?,?,?)');
                    $stmt->execute([$name,$desc,$price,$up]);
                    header('Location: /admin');
                    exit;
                }
            }
        }
        include __DIR__.'/../../views/upload.php';
    }
    
    public function edit($id){
        $this->requireAdmin();
        $error = null;
        $success = null;
        
        // Obtener producto
        $stmt = $this->db->pdo()->prepare('SELECT * FROM products WHERE id=?');
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        
        if(!$product){ http_response_code(404); echo 'Producto no encontrado'; return; }
        
        if($_SERVER['REQUEST_METHOD']==='POST'){
            if(empty($_POST['csrf']) || $_POST['csrf']!==($_SESSION['csrf'] ?? '')){ $error = 'Token CSRF inválido.'; }
            if(!$error){
                $name = trim($_POST['name'] ?? '');
                $desc = trim($_POST['description'] ?? '');
                $price = number_format((float)$_POST['price'],2,'.','');
                
                $imagePath = $product['image_path'];
                
                if(!empty($_FILES['image']) && $_FILES['image']['error']===UPLOAD_ERR_OK){
                    try {
                        $imagePath = $this->saveUploadedImage($_FILES['image']);
                    } catch (\Exception $e) {
                        $error = $e->getMessage();
                    }
                }
                
                if(!$error){
                    $stmt = $this->db->pdo()->prepare('UPDATE products SET name=?, description=?, price=?, image_path=? WHERE id=?');
                    $stmt->execute([$name,$desc,$price,$imagePath,$id]);
                    $success = 'Producto actualizado correctamente';
                    
                    // Recargar producto
                    $stmt = $this->db->pdo()->prepare('SELECT * FROM products WHERE id=?');
                    $stmt->execute([$id]);
                    $product = $stmt->fetch();
                }
            }
        }
        include __DIR__.'/../../views/edit_product.php';
    }
    
    public function delete($id){
        $this->requireAdmin();
        if(empty($_POST['csrf']) || $_POST['csrf']!==($_SESSION['csrf'] ?? '')){ 
            http_response_code(400); 
            echo 'Token CSRF inválido'; 
            return; 
        }
        
        $stmt = $this->db->pdo()->prepare('DELETE FROM products WHERE id=?');
        $stmt->execute([$id]);
        
        header('Location: /admin');
        exit;
    }
    
    private function saveUploadedImage($file){
        $allowedMimes = ['image/jpeg','image/png']; 
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo,$file['tmp_name']);
        
        if(!in_array($mime,$allowedMimes)) throw new \Exception('Tipo de archivo no permitido. Solo se aceptan JPEG y PNG.');
        
        if(function_exists('exif_imagetype') && function_exists('image_type_to_extension')){
            $ext = image_type_to_extension(exif_imagetype($file['tmp_name']));
        } else {
            $ext = '.'.pathinfo($file['name'], PATHINFO_EXTENSION);
            if(empty($ext) || $ext=='.'){
                if($mime === 'image/jpeg') $ext = '.jpg';
                else if($mime === 'image/png') $ext = '.png';
                else $ext = '';
            }
        }
        
        $safe = bin2hex(random_bytes(8)).$ext;
        $dir = $this->config['upload_dir'];
        
        if(!is_dir($dir)) mkdir($dir,0755,true);
        $target = $dir . DIRECTORY_SEPARATOR . $safe;
        
        if(!move_uploaded_file($file['tmp_name'],$target)) throw new \Exception('Error al mover archivo subido.');
        
        return '/uploads/'.$safe;
    }
}