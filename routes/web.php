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

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

$router->get('/', function () use ($router) {
    //return $router->app->version();
    $response = [
        'status' => 1,
        'data' => "Technocrats NewsBoard App RESTful API--- v2"
    ];
    return response()->json($response, 200, [], JSON_PRETTY_PRINT);
});
use App\News;    // Use at top of web.php

$router->get('article', function() {

    News::createIndex($shards = null, $replicas = null); // Using this code create command
    News::reindex(); // Reindex article indices

    $articles = News::searchByQuery(['match' => ['headline' => 'Coronavirus']]);
    return response()->json($articles, 200, [], JSON_PRETTY_PRINT);
});

$router->group(['prefix' => 'v1'], function ($app) use ($router) {

    //testing
    $app->get('nested','NewsController@getJoined');



    $router->group( ['prefix' => 'token_api_key' ], function($app) use ($router)
    {
        /** API KEY controller only for Super Admin*/
        $router->group( ['prefix' => 'crd' ], function($app)
        {
            $app->get('/','TokenApiKeyController@index');
            $app->get('/{id}','TokenApiKeyController@view');
            $app->post('/','TokenApiKeyController@create');
            $app->delete('/{id}','TokenApiKeyController@delete');
        });

        /** API KEY controller only for Super Admin*/
        $router->group( ['prefix' => 'change_plan' ], function($app)
        {
            $app->get('/requests','TokenApiKeyController@requests');
            $app->post('/{id}','TokenApiKeyController@change_plan');
        });
    });

    // For App user
    $router->group( ['prefix' => 'user' ], function($app)
    {
        $app->post('authorize','UserController@auth'); // no header
        $app->post('user_form','UserController@createOrUpdate'); // no header
        $app->get('me','UserController@me');
        $app->post('logout','UserController@logout');
        //todo update user form
    });

    //Developer
    $router->group( ['prefix' => 'developers' ], function($app) use ($router)
    {
        // All Developer Actions [Login, me, logout]
        $router->group( ['prefix' => 'action' ], function($app)
        {
            $app->post('/authorize','DeveloperController@auth');
            $app->get('/refresh','DeveloperController@refresh');
            $app->get('/me','DeveloperController@me');
            $app->post('/logout','DeveloperController@logout');
        });
        //Only For Super Admin
        $router->group( ['prefix' => 'manage_devs' ], function($app)
        {
            $app->get('/','DeveloperController@index');
            $app->get('/{id}','DeveloperController@view');
            $app->post('/','DeveloperController@create');
            $app->put('/{id}','DeveloperController@update');
            $app->delete('/{id}','DeveloperController@delete');
        });
    });

    //For Clients
    $router->group( ['prefix' => 'clients' ], function($app) use ($router)
    {
        $router->group( ['prefix' => 'action' ], function($app)
        {
            $app->post('/signup','ClientController@signup');
            $app->post('/signin','ClientController@signin');
            $app->get('/confirm_mail/{token}',['as' => 'confirm', 'uses' => 'ClientController@confirm_mail']);
            $app->post('/resend_conf_link','ClientController@resend_confirmation_link');

            $app->get('/me','ClientController@me'); // auth_client
            $app->post('/update_email','ClientController@update_email'); //auth_client
            $app->post('/update_password','ClientController@update_password'); //auth_client
            $app->post('/update_profile','ClientController@update_profile'); //auth_client

            $app->post('/signout','ClientController@signout'); //auth_client

            //forgot password
            $app->post('/forgot_password','ClientController@send_password_to_mail'); //{param: email}

        });
        // auth_client
        $router->group( ['prefix' => 'api_key' ], function($app)
        {
            $app->get('/','ClientController@get_own_key'); // only to get api_key string
            $app->get('/detail','ClientController@api_key_details'); // except api_key itself
            $app->post('/request_change_plan','ClientController@request_change_plan'); // (param: pricing_plan_id)todo fill up a form and update it to token_api_key ..
            $app->post('/generate_or_refresh','ClientController@generate_refresh_own_key'); // generate new one if not exists if exists then refresh it...
            $app->delete('/','ClientController@deleteApiKey'); //auth_client

        });
    });


    $router->group( ['prefix' => 'pricing_plan' ], function($app)
    {
        $app->get('/','PricingPlanController@index');
        $app->get('/{id}','PricingPlanController@view');
        $app->post('/','PricingPlanController@create'); //super_admin access
        $app->put('/{id}','PricingPlanController@update'); //super_admin access
        $app->delete('/{id}','PricingPlanController@delete'); //super_admin access
    });

    //Categories
    $router->group( ['prefix' => 'categories' ], function($app)
    {
        $app->get('/','CategoryController@index');
        $app->get('/{id}','CategoryController@view');
        $app->post('/create','CategoryController@create');
        $app->post('/update/{id}','CategoryController@update');
        $app->delete('/{id}','CategoryController@delete');
    });

    //Newspapers
    $router->group( ['prefix' => 'newspapers' ], function($app)
    {
        $app->get('/','NewspaperController@index');
        $app->get('/{id}','NewspaperController@view');
        $app->post('/create','NewspaperController@create');
        $app->post('/update/{id}','NewspaperController@update');
        $app->delete('/{id}','NewspaperController@delete');
    });

    //tags
    $router->group( ['prefix' => 'tags' ], function($app)
    {
        $app->get('/','TagController@index');
        $app->get('/{id}','TagController@view');
        $app->post('/','TagController@create');
        $app->post('/search', 'TagController@search');  // keywords
        $app->put('/{id}','TagController@update');
        $app->delete('/{id}','TagController@delete');
        $app->post('/del_request/{id}','TagController@delete_request');
        $app->post('/reset_del_request/{id}','TagController@reset_delete_request');
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
        $app->put('/{id}','NewsController@update');
        $app->delete('/{id}','NewsController@delete');

        $app->get('/related/{news_id}','NewsController@related');
        $app->get('/global/search','NewsController@global_search');
    });
    $router->group( ['prefix' => 'custom' ], function($app)
    {

        $app->get('/corona_stat','CustomController@custom_corona_stat');
        $app->get('/about_app','CustomController@about_app');
        $app->get('/tts','CustomController@tts');

    });
    $router->group( ['prefix' => 'stat' ], function($app)
    {
        $app->post('/news/{id}','StatController@inc_read_count');

    });

    /** One Signal api*/
    $router->group( ['prefix' => 'one_signal' ], function($app)
    {
        $app->post('/','TestController@image_resize');
        $app->post('/del_s3_image','TestController@delete_s3_image');
        $app->get('/time','TestController@timestamp');
        $app->get('/s3_summary','TestController@get_s3_summary');
        $app->get('/logo_refine','TestController@refine_logo');
        $app->get('/mysql_json','TestController@mysql_json');
        $app->get('/redis','TestController@redis');
        $app->get('/agent','TestController@useragent');
    });
    $router->group( ['prefix' => 'test' ], function($app)
    {
        $app->post('/','TestController@image_resize');
        $app->post('/del_s3_image','TestController@delete_s3_image');
        $app->get('/time','TestController@timestamp');
        $app->get('/s3_summary','TestController@get_s3_summary');
        $app->get('/logo_refine','TestController@refine_logo');
        $app->get('/mysql_json','TestController@mysql_json');
        $app->get('/redis','TestController@redis');
        $app->get('/agent','TestController@useragent');
    });

});
