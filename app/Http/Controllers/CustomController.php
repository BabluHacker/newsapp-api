<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class CustomController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth_api_key', ['only' => [
            'index', 'view'
        ]]);
    }

    public function custom_corona_stat(Request $request){
        $client = new Client(['base_uri' => 'https://coronavirus-monitor.p.rapidapi.com/', 'timeout'  => 15.0, ]);
        try {

            $res_world = $client->request('GET', 'coronavirus/worldstat.php', [
                'headers' => [
                    "x-rapidapi-host"=> "coronavirus-monitor.p.rapidapi.com",
	                "x-rapidapi-key"=> env('CORONA_API_KEY')
                ]
            ]);

            $res_bd = $client->request('GET', 'coronavirus/latest_stat_by_country.php?country=bangladesh', [
                'headers' => [
                    "x-rapidapi-host"=> "coronavirus-monitor.p.rapidapi.com",
	                "x-rapidapi-key"=> env('CORONA_API_KEY')
                ]
            ]);


        } catch (ClientException $exception){
            $res_world = $exception->getResponse();
            $res_bd = $exception->getResponse();
        }
        $res = [
            'world' => json_decode($res_world->getBody()->getContents(), true),
            'bangladesh' => json_decode($res_bd->getBody()->getContents(), true)
        ];
        return response()->json($res, $res_world->getStatusCode(), [], JSON_PRETTY_PRINT);
    }

    public function about_app(Request $request){
        $data = [
            'api_url'   => env('API_URL'),
            'asset_url' => env('AWS_S3_BASE_URL'),
            'version'   => env('APP_VERSION')
        ];
        return response()->json($data, 200, [], JSON_PRETTY_PRINT);
    }
}
