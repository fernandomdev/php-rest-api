<?php

// es porque estamos declarando el tipo de parámetro en los métodos:
declare(strict_types=1);

// Autoload para no require() cada archivo de clase uno a uno
spl_autoload_register(function ($class) {
    require __DIR__ . "/src/$class.php";
});

// para que lo que retorne lo detecte como JSON no cómo texto plano / html
header("Content-type: application/json; charset=UTF-8");

// manejar los errores como lo haría una REST API, así no devuelve el error con formato PHP
set_error_handler("ErrorHandler::handleError");
set_exception_handler("ErrorHandler::handleException");

// Destructurar la URI
$request_uri = explode("/", $_SERVER["REQUEST_URI"]);

if ($request_uri[1] !== 'products') {
    http_response_code(404);
    exit;
}


// conexión con la base de datos
$database = new Database("localhost", "php-rest-api", "root", "");

$gateway = new ProductGateway($database);


// trabajar con los controladores
$id = $request_uri[2] ?? null;

$controller = new ProductController($gateway);
$controller->processRequest($_SERVER["REQUEST_METHOD"], $id);