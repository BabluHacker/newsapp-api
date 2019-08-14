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

    //testing
    $app->get('nested','NewsController@getJoined');

    $router->group( ['prefix' => 'user' ], function($app)
    {
        $app->post('/logout','UserController@logout');
        //todo update user form
    });

    //Categories
    $router->group( ['prefix' => 'categories' ], function($app)
    {
        $app->get('/','CategoryController@index');
        $app->get('/{id}','CategoryController@view');
        $app->post('/','CategoryController@create');
        $app->put('/{id}','CategoryController@update');
        $app->delete('/{id}','CategoryController@delete');
    });

    //Newspapers
    $router->group( ['prefix' => 'newspapers' ], function($app)
    {
        $app->get('/','NewspaperController@index');
        $app->get('/{id}','NewspaperController@view');
        $app->post('/','NewspaperController@create');
        $app->put('/{id}','NewspaperController@update');
        $app->delete('/{id}','NewspaperController@delete');
    });

    //tags
    $router->group( ['prefix' => 'tags' ], function($app)
    {
        $app->get('/','TagController@index');
        $app->get('/{id}','TagController@view');
        $app->post('/','TagController@create');
        $app->put('/{id}','TagController@update');
        $app->delete('/{id}','TagController@delete');
    });

    //category_paper_urls
    $router->group( ['prefix' => 'category_paper_urls' ], function($app)
    {
        $app->get('/','CategoryPaperUrlController@index');
        $app->get('/{id}','CategoryPaperUrlController@view');
        $app->post('/','CategoryPaperUrlController@create');
        $app->put('/{id}','CategoryPaperUrlController@update');
        $app->delete('/{id}','CategoryPaperUrlController@delete');
    });

    //Newses
    $router->group( ['prefix' => 'newses' ], function($app)
    {
        $app->get('/','NewsController@index');
        $app->get('/{id}','NewsController@view');
    });

    //Developers
    $router->group( ['prefix' => 'developers' ], function($app) use ($router)
    {
        // Developer Portal
        $router->group( ['prefix' => 'action' ], function($app)
        {
            $app->post('/authorize','DeveloperController@auth');
            $app->get('/refresh','DeveloperController@refresh');
            $app->get('/me','DeveloperController@me');
            $app->post('/logout','DeveloperController@logout');
        });
        $router->group( ['prefix' => 'portal' ], function($app)
        {
            $app->get('/','DeveloperController@index');
            $app->get('/{id}','DeveloperController@view');
            $app->post('/','DeveloperController@create');
            $app->put('/{id}','DeveloperController@update');
            $app->delete('/{id}','DeveloperController@delete');
        });
    });



});
