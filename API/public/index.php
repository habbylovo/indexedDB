<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
//use Slim\Factory\AppFactory;

require '../vendor/autoload.php';
require '../src/config/db.php';


$c = new \Slim\Container();
$c['errorHandler'] = function($c){
    return function($request, $response, $exception) use ($c){
        $error = array('error'=> $exception->getMessage());
        return $c['response']->withStatus(500)
                            ->withHeader('Content-Type', 'application/json')
                            ->write(json_encode($error));
    };
};

$app = new \Slim\App([
	'settings' =>[
		'displayErrorDetails' => true
	]
]);



// $app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
//     $name = $args['name'];
//     $response->getBody()->write("Hello, $name");
//     return $response;
// });
//ruta clientes
require '../src/rutas/clientes.php';

$app->run();