<?php
/**
 * API Rest crud contactos
 * end points
 * Añadir:      POST    /contactos
 * Leer:        GET     /contactos/{id}
 * Modificar:   PUT     /contactos/{id}
 * Borrar:      DELETE  /contactos/{id}
 */
require "../bootstrap.php";

use App\Controllers\AuthController;
use App\Core\Router;
use App\Controllers\ContactosController;
use \Firebase\JWT\Key;
use \Firebase\JWT\JWT;

header('Access-Control-Allow-Origin: http://localhost:4200');
header('Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Allow: GET, POST, PUT, DELETE');

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $request);

$Id = null;
if (isset($uri[2])) {
    $Id = (int) $uri[2];
}

$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod == 'OPTIONS') {
    die();
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method == "OPTIONS") {
    die();
}

if (defined('APIPRIVADA') && APIPRIVADA) {
    if ($request == '/login') { 
        $auth = new AuthController($requestMethod);
        if (!$auth->loginFromRequest()) { 
            exit(http_response_code(401));
        };
    }
    
    $input = (array) json_decode(file_get_contents('php://input'), TRUE); 
    $autHeader = $_SERVER['HTTP_AUTHORIZATION'];
    $arr = explode(" ", $autHeader);
    $jwt = $arr[1];
    
    if ($jwt) { 
        try {
            $decoded = (JWT::decode($jwt, new KEY(KEY, 'HS256')));
        } catch (Exception $e) {
            echo json_encode(array(
                "message" => "Access denied",
                "error" => $e->getMessage()));
            exit(http_response_code(401));
        }
    }
}

$router = new Router();
$router->add(array(
    'name'=>'home',
    'path'=>'/^\/contactos\/([0-9]*)?$/',
    'action'=>ContactosController::class),
);

//Comprobamos ruta válida
$route = $router->match($request);
if ($route) {
    // como hay ruta creamos el objeto controlador
    $controllerName = $route['action'];
    $controller = new $controllerName($requestMethod, $Id);
    // llamada al metodo del controlador que procesa la peticion
    $controller->processRequest();
} else {
    //ruta no válida
    $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
    $response['body'] = null;
    echo json_encode($response);
}
