<?php
require __DIR__ . '/../vendor/autoload.php';

use App\DB;
use App\Services\RedsysService; // Necesitamos el servicio de Redsys aquí para inyectarlo

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax'
]);

// Load config
$config = require __DIR__ . '/../config.php';

// Instantiate DB and RedsysService globally with the full $config array.
// App\DB expects the full config array to find the 'db' key.
$db = new App\DB($config);
$redsysService = new App\Services\RedsysService($config);


$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r){
    // Rutas públicas
    $r->addRoute('GET', '/', 'App\\Controllers\\HomeController::index');
    $r->addRoute('GET', '/product/{id:\d+}', 'App\\Controllers\\ProductController::view');
    
    // ⬇️ RUTAS DE CARRITO
    $r->addRoute('GET', '/cart', 'App\\Controllers\\CartController::view');
    $r->addRoute('POST', '/cart/add', 'App\\Controllers\\CartController::add');
    $r->addRoute('POST', '/cart/remove/{id:\d+}', 'App\\Controllers\\CartController::remove');
    $r->addRoute('POST', '/cart/clear', 'App\\Controllers\\CartController::clear');
    // ⬆️ FIN RUTAS DE CARRITO
    
    $r->addRoute('POST', '/checkout', 'App\\Controllers\\CheckoutController::createOrder');
    $r->addRoute('GET', '/order-success', 'App\\Controllers\\CheckoutController::success');
    $r->addRoute('GET', '/invoice', 'App\\Controllers\\CheckoutController::invoice');
    
    // ⬇️ RUTAS DE PAGO (REDSYS)
    $r->addRoute('GET', '/payment/redsys/init/{id:\d+}', 'App\\Controllers\\PaymentController::initPayment');       
    $r->addRoute('POST', '/payment/redsys/response', 'App\\Controllers\\PaymentController::processResponse');
    $r->addRoute('POST', '/payment/redsys/notification', 'App\\Controllers\\PaymentController::processNotification');
    
    // Rutas de autenticación
    $r->addRoute(['GET','POST'], '/login', 'App\\Controllers\\AuthController::login');
    $r->addRoute('GET', '/logout', 'App\\Controllers\\AuthController::logout');
    $r->addRoute(['GET','POST'], '/register', 'App\\Controllers\\AuthController::register');
    
    // Rutas de administración (protegidas)
    $r->addGroup('/admin', function (FastRoute\RouteCollector $r) {
        $r->addRoute('GET', '', 'App\\Controllers\\AdminController::index');
        $r->addRoute('GET', '/users', 'App\\Controllers\\AdminController::listUsers');
        $r->addRoute(['GET','POST'], '/users/edit/{id:\d+}', 'App\\Controllers\\AdminController::editUser');
        $r->addRoute('POST', '/users/delete/{id:\d+}', 'App\\Controllers\\AdminController::deleteUser');
        $r->addRoute(['GET','POST'], '/edit/{id:\d+}', 'App\\Controllers\\AdminController::edit');
        $r->addRoute('POST', '/delete/{id:\d+}', 'App\\Controllers\\AdminController::delete');
        $r->addRoute('GET', '/orders', 'App\\Controllers\\AdminController::listOrders');
        $r->addRoute('GET', '/orders/{id:\d+}', 'App\\Controllers\\AdminController::viewOrder');
    });
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
            list($class, $method) = explode('::', $handler);

            // 1. DI Logic: Explicitly inject required dependencies
            switch ($class) {
                case 'App\\Controllers\\PaymentController':
                    // PaymentController requires DB and RedsysService
                    $controller = new $class($db, $redsysService);
                    // The PaymentController methods expect the full $vars array as one argument, 
                    // so we package it as an array inside another array for call_user_func_array
                    $args = [$vars];
                    break;
                case 'App\\Controllers\\HomeController':
                case 'App\\Controllers\\CheckoutController':
                    // Controllers that need DB and the full config
                    $controller = new $class($db, $config);
                    $args = array_values($vars); // Pass route parameters as values
                    break;
                default:
                    // Default: assume the controller needs only DB 
                    $controller = new $class($db);
                    $args = array_values($vars); // Pass route parameters as values
                    break;
            }

            // 2. The Fix: Convert associative route parameters into numerically indexed values
            // This satisfies PHP 8+ when the method signature uses named arguments (e.g., view($id)).
            // We only use the $args determined in the switch block above.
            
            call_user_func_array([$controller, $method], $args);
        }
        break;
}