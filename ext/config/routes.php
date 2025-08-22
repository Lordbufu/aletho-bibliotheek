<?php
/* User-Managment routes */
$router->get(   '/',        'LoginController@landing');
$router->get(   '/login',   'LoginController@showform');
$router->post(  '/login',   'LoginController@authenticate');
$router->post(  '/logout',  'LoginController@logout');

/* App view routes */
$router->get(   '/home',     'ViewController@landing');

/* Router testing: routes */
// $router->get('/test/{id}', 'ProtoTypeController@guid');
// $router->get('/test/{id:\d+}', 'ProtoTypeController@guid');
// $router->get('/quick', function ($request, $response) {
//     $response->setContent('Hello world')->send();
// });