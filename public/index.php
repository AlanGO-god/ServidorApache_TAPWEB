<?php
require_once '../core/Router.php';
require_once '../config/database.php';
require_once '../resources/v1/UserResources.php';
require_once '../resources/v1/ProductoResources.php';
require_once '../core/Middleware/AuthMiddleware.php';
require_once '../resources/v2/AuthResource.php';
require_once '../models/Tarea.php';
require_once '../resources/v1/TareaResource.php';
require_once '../models/PasswordPolicy.php';
require_once '../core/services/PasswordGenerator.php';
require_once '../core/services/PasswordValidator.php';
require_once '../resources/v1/PasswordResource.php';
require_once '../resources/v1/PasswordAuthResource.php';

$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$basePath = $scriptName;

$userResource = new UserResource();
$productResource = new ProductoResources();
$authResource = new AuthResource();
$tareaResource = new TareaResource();
$passwordResource = new PasswordResource();
$passwordAuthResource = new PasswordAuthResource();

// --- v1 (sin cambios, como ya la entregaste) ---
$routerV1 = new Router('v1', $basePath);
$routerV1->addRoute('GET', '/users', [$userResource, 'index']);
$routerV1->addRoute('GET', '/users/{id}', [$userResource, 'show']);
$routerV1->addRoute('POST', '/users', [$userResource, 'store']);
$routerV1->addRoute('PUT', '/users/{id}', [$userResource, 'update']);
$routerV1->addRoute('DELETE', '/users/{id}', [$userResource, 'destroy']);
$routerV1->addRoute('GET', '/productos', [$productResource, 'index']);
$routerV1->addRoute('GET', '/productos/{id}', [$productResource, 'show']);
$routerV1->addRoute('POST', '/productos', [$productResource, 'store']);
$routerV1->addRoute('PUT', '/productos/{id}', [$productResource, 'update']);
$routerV1->addRoute('DELETE', '/productos/{id}', [$productResource, 'destroy']);

// --- v1 open routes for TareaResource
$routerV1->addRoute('GET', '/tareas', [$tareaResource, 'index']);
$routerV1->addRoute('GET', '/tareas/{id}', [$tareaResource, 'show']);
$routerV1->addRoute('POST', '/tareas', [$tareaResource, 'store']);
$routerV1->addRoute('PUT', '/tareas/{id}', [$tareaResource, 'update']);

// --- v1 open routes for PasswordResource and PasswordAuthResource
$routerV1->addRoute('POST', '/passwords/generate', [$passwordResource, 'generate']);
$routerV1->addRoute('POST', '/passwords/validate', [$passwordResource, 'validate']);
$routerV1->addRoute('GET', '/passwords/policy', [$passwordResource, 'policy']);
$routerV1->addRoute('POST', '/auth/register', [$passwordAuthResource, 'register']);
$routerV1->addRoute('POST', '/auth/login', [$passwordAuthResource, 'login']);

// --- v2 (con autenticación) ---
$routerV2 = new Router('v2', $basePath);
$auth = [AuthMiddleware::class, 'handle'];

$routerV2->addRoute('POST', '/login', [$authResource, 'login']);   // pública
$routerV2->addRoute('POST', '/logout', [$authResource, 'logout'], $auth);
$routerV2->addRoute('GET', '/me', [$authResource, 'me'], $auth);

$routerV2->addRoute('GET', '/users', [$userResource, 'index'], $auth);
$routerV2->addRoute('GET', '/users/{id}', [$userResource, 'show'], $auth);
$routerV2->addRoute('POST', '/users', [$userResource, 'store'], $auth);
$routerV2->addRoute('PUT', '/users/{id}', [$userResource, 'update'], $auth);
$routerV2->addRoute('DELETE', '/users/{id}', [$userResource, 'destroy'], $auth);

$routerV2->addRoute('GET', '/productos', [$productResource, 'index'], $auth);
$routerV2->addRoute('GET', '/productos/{id}', [$productResource, 'show'], $auth);
$routerV2->addRoute('POST', '/productos', [$productResource, 'store'], $auth);
$routerV2->addRoute('PUT', '/productos/{id}', [$productResource, 'update'], $auth);
$routerV2->addRoute('DELETE', '/productos/{id}', [$productResource, 'destroy'], $auth);

// Probar v1 primero, si no matchea, probar v2
if (!$routerV1->dispatch()) {
    if (!$routerV2->dispatch()) {
        http_response_code(404);
        header("Content-Type: application/json");
        echo json_encode(["message" => "Ruta no encontrada"]);
    }
}

// -- v1 open routes for TareaResource

?>