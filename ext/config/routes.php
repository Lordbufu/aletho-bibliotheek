<?php

$router->get('/', 'ProtoTypeController@landing');

/* Router testing: routes */
// $router->get('/test/{id}', 'ProtoTypeController@guid');
// $router->get('/test/{id:\d+}', 'ProtoTypeController@guid');
// $router->get('/quick', function ($request, $response) {
//     $response->setContent('Hello world')->send();
// });