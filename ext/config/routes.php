<?php
return [
    /*  App User-Managment routes */
    ['GET',    '/',                     'ViewController@landing'],
    ['GET',    '/login',                'AuthController@showform'],
    ['POST',   '/login',                'AuthController@authenticate'],
    ['POST',   '/logout',               'AuthController@logout'],
    ['PATCH',  '/resetPassword',        'AuthController@resetPassword'],
    /*  App main view route */
    ['GET',    '/home',                 'ViewController@home'],
    /*  App book related routes */
    ['GET',    '/bookdata',             'BookController@bookdata'],
    ['POST',   '/addBook',              'BookController@add'],
    ['PATCH',  '/editBook',             'BookController@edit'],
    ['DELETE', '/delBook',              'BookController@delete'],
    /*  App status related routes */
    ['PATCH',  '/setStatusPeriod',      'StatusController@setStatusPeriod'],
    ['PATCH',  '/changeStatus',         'StatusController@changeStatus'],
    ['GET',    '/requestStatus',        'StatusController@requestStatus'],
    ['GET',    '/requestPopinStatus',   'StatusController@requestPopStatus'],
    ['GET',    '/requestBookStatus',    'StatusController@requestBookStatus'],
    /*  App loaner related routes */
    ['GET',    '/requestLoaners',       'LoanerController@requestLoaners'],
    ['GET',    '/requestLoanerForBook', 'LoanerController@requestLoanerForBook'],
];