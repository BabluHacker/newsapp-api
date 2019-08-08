<?php


namespace App\Http\Controllers;


use App\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function __construct(Request $request)
    {

    }
    public function index(Request $request)
    {
        $response = News::search($request);
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }
}