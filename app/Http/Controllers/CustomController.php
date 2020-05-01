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
        $client = new Client(['base_uri' => 'https://coronavirus-map.p.rapidapi.com/', 'timeout'  => 15.0, ]);
        try {

            $res = $client->request('GET', 'v1/summary/latest', [
                'headers' => [
                    "x-rapidapi-host"=> "coronavirus-map.p.rapidapi.com",
	                "x-rapidapi-key"=> env('CORONA_API_KEY')
                ]
            ]);
        } catch (ClientException $exception){
            $res = $exception->getResponse();
        }
        return response()->json(json_decode($res->getBody()->getContents(), true), $res->getStatusCode(), [], JSON_PRETTY_PRINT);
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
