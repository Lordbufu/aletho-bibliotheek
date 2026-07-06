<?php
return [
    /** App main view routes */
    ['GET',     '/',                    'ViewController@landing'],                  // re-factored
    ['GET',     '/home',                'ViewController@home'],                     // re-factored

    /** App User-Managment routes */
    ['POST',   '/login',                'AuthController@login'],                    // re-factored
    ['POST',   '/logout',               'AuthController@logout'],                   // re-factored
    ['PATCH',  '/resetPassword',        'AuthController@resetPassword'],            // re-factored

    /** App book related routes */
    ['GET',    '/bookData',             'BookController@bookData'],                 // re-factored
    ['POST',   '/addBook',              'BookController@addBook'],                  // re-factored
    ['PATCH',  '/editBook',             'BookController@editBook'],                 // re-factered
    ['DELETE', '/delBook',              'BookController@deleteBook'],               // re-factored

    /** Status period popin routes */
    ['PATCH',  '/editStatusPeriod',     'StatusController@editStatusPeriod'],       // re-factored
    ['PATCH',  '/changeStatus',         'StatusController@changeStatus'],           // 
    
    // XHR requests for frontend scripts:
    ['GET',    '/requestStatus',        'StatusController@requestStatus'],          // re-factored
    ['GET',    '/requestPopinStatus',   'StatusController@requestPopinStatus'],     // re-factored
    ['GET',    '/requestLoanerForBook', 'LoanerController@requestLoanerForBook'],   // re-factored
    ['GET',    '/requestLoaners',       'LoanerController@requestLoaners'],         // re-factored

    // Re-factor status: Potentially redundant now
    // ['GET',    '/requestBookStatus',    'StatusController@requestBookStatus'],
];