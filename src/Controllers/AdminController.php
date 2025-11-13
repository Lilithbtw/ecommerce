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
        $stmt = $this->db->pdo()->query('SELECT * FROM products');
        $products = $stmt->fetchAll();
        include __DIR__.'/../../views/admin.php';
    }
    public function upload(){
        $this->requireAdmin();
        if($_SERVER['REQUEST_METHOD']==='POST'){
            // CSRF
            if(empty($_POST['csrf']) || $_POST['csrf']!==($_SESSION['csrf'] ?? '')){ echo 'CSRF invalid'; return; }
            $name = trim($_POST['name'] ?? '');
            $desc = trim($_POST['description'] ?? '');
            $price = number_format((float)$_POST['price'],2,'.','');
            // file upload handling
            if(!empty($_FILES['image']) && $_FILES['image']['error']===UPLOAD_ERR_OK){
                $up = $this->saveUploadedImage($_FILES['image']);
            } else { $up = null; }
            $stmt = $this->db->pdo()->prepare('INSERT INTO products (name,description,price,image_path) VALUES (?,?,?,?)');
            $stmt->execute([$name,$desc,$price,$up]);
            header('Location: /admin');
            exit;
        }
        include __DIR__.'/../../views/upload.php';
    }
    private function saveUploadedImage($file){
        $allowed = ['image/jpeg','image/png','image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo,$file['tmp_name']);
        if(!in_array($mime,$allowed)) throw new \Exception('Tipo de archivo no permitido');
        $ext = image_type_to_extension(exif_imagetype($file['tmp_name']));
        $safe = bin2hex(random_bytes(8)).$ext;
        $dir = $this->config['upload_dir'];
        if(!is_dir($dir)) mkdir($dir,0755,true);
        $target = $dir . DIRECTORY_SEPARATOR . $safe;
        if(!move_uploaded_file($file['tmp_name'],$target)) throw new \Exception('Error al mover archivo');
        return '/uploads/'.$safe;
    }
}
