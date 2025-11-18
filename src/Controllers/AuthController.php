<?php
namespace App\Controllers;
use App\DB;
class AuthController{
    private $db; private $config;
    public function __construct($config){
        $this->config=$config;
        $this->db=new DB($config);
    }
    public function login(){
        if($_SERVER['REQUEST_METHOD']==='POST'){
            // basic rate limiting: track attempts in session
            $_SESSION['attempts'] = ($_SESSION['attempts'] ?? 0) + 1;
            if($_SESSION['attempts'] > 10){ echo 'Demasiados intentos'; return; }
            $email = $_POST['email'] ?? '';
            $pw = $_POST['password'] ?? '';
            $stmt = $this->db->pdo()->prepare('SELECT id,password FROM users WHERE email=?');
            $stmt->execute([$email]);
            $u = $stmt->fetch();
            if($u && password_verify($pw,$u['password'])){
                $_SESSION['user_id'] = $u['id'];
                $_SESSION['csrf'] = bin2hex(random_bytes(16));
                header('Location: /admin');
                exit;
            } else {
                echo 'Credenciales inválidas';
            }
        }
        include __DIR__.'/../../views/login.php';
    }
    public function logout(){
        session_unset(); session_destroy();
        header('Location: /');
    }
}
