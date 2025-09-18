<?php
/* User-Managment routes */
$router->get(       '/',                'ViewController@landing');
$router->get(       '/login',           'AuthController@showform');
$router->post(      '/login',           'AuthController@authenticate');
$router->post(      '/logout',          'AuthController@logout');

/* App main view routes */
$router->get(       '/home',            'ViewController@home');

/* App interaction routes */
$router->patch(    '/resetPassword',   'AuthController@resetPassword');