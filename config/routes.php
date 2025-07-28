<?php
use App\Core\Router;
use App\Controllers\ProtoTypeController;

$router = new Router();

$router->get('/', function() { return (new ProtoTypeController)->home(); });
$router->get('/test', function() { return (new ProtoTypeController)->test(); });

return $router;