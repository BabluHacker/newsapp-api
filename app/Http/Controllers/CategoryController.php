<?php


namespace App\Http\Controllers;


use App\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(Request $request)
    {

    }
    public function index(Request $request)
    {
        $response = Category::search($request);
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }
}