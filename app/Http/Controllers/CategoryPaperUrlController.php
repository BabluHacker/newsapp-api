<?php


namespace App\Http\Controllers;


use App\CategoryPaperUrl;
use Illuminate\Http\Request;

class CategoryPaperUrlController extends Controller
{
    public function __construct(Request $request)
    {

    }
    public function index(Request $request)
    {
        $response = CategoryPaperUrl::search($request);
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }

}