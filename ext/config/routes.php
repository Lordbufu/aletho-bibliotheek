<?php
/* User-Managment routes */
$router->get(       '/',                'ViewController@landing');
$router->get(       '/login',           'AuthController@showform');
$router->post(      '/login',           'AuthController@authenticate');
$router->post(      '/logout',          'AuthController@logout');

/* App view routes */
$router->get(       '/home',            'ViewController@userLogin');

/* App update routes */
$router->patch(    '/resetPassword',   'AuthController@resetPassword');


/* Router testing: routes */
    // $router->get('/test/{id}', 'ProtoTypeController@guid');
    // $router->get('/test/{id:\d+}', 'ProtoTypeController@guid');
    // $router->get('/quick', function ($request, $response) {
    //     $response->setContent('Hello world')->send();
    // });