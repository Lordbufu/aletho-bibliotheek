<?php
return [
    /** App main view routes */
    ['GET',     '/',                    'ViewController@landing'],
    ['GET',     '/home',                'ViewController@home'],

    /** App User-Managment routes */
    ['POST',   '/login',                'AuthController@login'],
    ['POST',   '/logout',               'AuthController@logout'],
    ['PATCH',  '/resetPassword',        'AuthController@resetPassword'],

    /** App book related routes */
    ['GET',    '/bookData',             'BookController@bookData'],
    ['POST',   '/addBook',              'BookController@addBook'],
    ['PATCH',  '/editBook',             'BookController@editBook'],
    ['DELETE', '/delBook',              'BookController@deleteBook'],

    /** Status period popin routes */
    ['PATCH',  '/editStatusPeriod',     'StatusController@editStatusPeriod'],
    ['PATCH',  '/changeStatus',         'StatusController@changeStatus'],    
    

    // XHR requests for frontend scripts:
    ['GET',    '/requestStatus',        'StatusController@requestStatus'],
    ['GET',    '/requestBookStatus',    'StatusController@requestBookStatus'],
    ['GET',    '/requestPopinStatus',   'StatusController@requestPopinStatus'],
    ['GET',    '/requestLoanerForBook', 'LoanerController@requestLoanerForBook'],
    ['GET',    '/requestLoaners',       'LoanerController@requestLoaners'],
];