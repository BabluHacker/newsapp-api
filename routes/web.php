<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/



$router->get('/', function () use ($router) {
    //return $router->app->version();
    $response = [
        'status' => 1,
        'data' => "Technocrats NewsBoard App RESTful API"
    ];

    return response()->json($response, 200, [], JSON_PRETTY_PRINT);
});


$router->group(['prefix' => 'v1'], function ($app) use ($router) {

    $app->post('authorize','UserController@auth'); // no header
    $app->post('user_form','UserController@createOrUpdate'); // no header
    $app->get('me','UserController@me');

    $router->group( ['prefix' => 'user' ], function($app)
    {
        $app->post('/logout','UserController@logout');
    });

    //Categories
    $router->group( ['prefix' => 'categories' ], function($app)
    {
        $app->get('/','CategoryController@index');
    });

    //Newspapers
    $router->group( ['prefix' => 'newspapers' ], function($app)
    {

    });

    //tags
    $router->group( ['prefix' => 'tags' ], function($app)
    {
        $app->get('/','TagController@index');
    });

    //category_paper_urls
    $router->group( ['prefix' => 'category_paper_urls' ], function($app)
    {
        $app->get('/','CategoryPaperUrlController@index');
    });

    //Newses
    $router->group( ['prefix' => 'newses' ], function($app)
    {

    });
    // Mail Sending
    $router->group( ['prefix' => 'send_mail' ], function($app)
    {
        $app->get('/','MailSenderController@send');
    });
});
