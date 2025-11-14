<?php
require __DIR__ . '/../vendor/autoload.php';

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax'
]);

// Load config
$config = require __DIR__ . '/../config.php';

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r){
    // Rutas públicas
    $r->addRoute('GET', '/', 'App\\Controllers\\HomeController::index');
    $r->addRoute('GET', '/product/{id:\\d+}', 'App\\Controllers\\ProductController::view');
    
    // ⬇️ RUTAS DE CARRITO
    $r->addRoute('GET', '/cart', 'App\\Controllers\\CartController::view');
    $r->addRoute('POST', '/cart/add', 'App\\Controllers\\CartController::add');
    $r->addRoute('POST', '/cart/remove/{id:\\d+}', 'App\\Controllers\\CartController::remove');
    $r->addRoute('POST', '/cart/clear', 'App\\Controllers\\CartController::clear');
    // ⬆️ FIN RUTAS DE CARRITO
    
    $r->addRoute('POST', '/checkout', 'App\\Controllers\\CheckoutController::createOrder');
    $r->addRoute('GET', '/order-success', 'App\\Controllers\\CheckoutController::success');
    $r->addRoute('GET', '/invoice', 'App\\Controllers\\CheckoutController::invoice');
    
    // Rutas de autenticación
    $r->addRoute(['GET','POST'], '/login', 'App\\Controllers\\AuthController::login');
    $r->addRoute('GET', '/logout', 'App\\Controllers\\AuthController::logout');
    
    // Rutas de Administración
    $r->addRoute('GET', '/admin', 'App\\Controllers\\AdminController::index');
    $r->addRoute(['GET','POST'], '/admin/upload', 'App\\Controllers\\AdminController::upload');
    
    // Administración de usuarios
    $r->addRoute('GET', '/admin/users', 'App\\Controllers\\AdminController::users');
    $r->addRoute(['GET','POST'], '/admin/users/create', 'App\\Controllers\\AdminController::createUser');
    $r->addRoute(['GET','POST'], '/admin/users/edit/{id:\\d+}', 'App\\Controllers\\AdminController::editUser');
    $r->addRoute('POST', '/admin/users/delete/{id:\\d+}', 'App\\Controllers\\AdminController::deleteUser');
    
    // Administración de productos
    $r->addRoute(['GET','POST'], '/admin/edit/{id:\\d+}', 'App\\Controllers\\AdminController::edit');
    $r->addRoute('POST', '/admin/delete/{id:\\d+}', 'App\\Controllers\\AdminController::delete');
});

// Fetch method and URI
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}

$uri = rawurldecode($uri);
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo '404 - Not Found';
        break;
        
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo '405 - Method Not Allowed';
        break;
        
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        
        if (is_string($handler) && strpos($handler, '::') !== false) {
            list($class, $method) = explode('::', $handler, 2);
            $controller = new $class($config);
            call_user_func_array([$controller, $method], $vars);
        } else {
            http_response_code(500);
            echo 'Handler not callable';
        }
        break;
}