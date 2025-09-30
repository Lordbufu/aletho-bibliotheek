<?php
/* User-Managment routes */
$router->get(   '/',                'ViewController@landing');
$router->get(   '/login',           'AuthController@showform');
$router->post(  '/login',           'AuthController@authenticate');
$router->post(  '/logout',          'AuthController@logout');
$router->patch( '/resetPassword',   'AuthController@resetPassword');

/* App main view routes */
$router->get(   '/home',            'ViewController@home');

/* App book related routes */
$router->get(   '/bookdata',        'BookController@bookdata');
$router->patch( '/editBook',        'BookController@edit');