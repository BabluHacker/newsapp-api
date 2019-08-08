<?php


namespace App\Http\Controllers;


use App\Newspaper;
use Illuminate\Http\Request;

class NewspaperController extends Controller
{
    public function __construct(Request $request)
    {

    }
    public function index(Request $request)
    {
        $response = Newspaper::search($request);
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }
}