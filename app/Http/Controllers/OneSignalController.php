<?php


namespace App\Http\Controllers;


use App\Category;
use App\CategoryPaperUrl;
use App\News;
use App\Newspaper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OneSignalController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('auth_api_key', ['only' => [
            'index', 'view'
        ]]);
    }


}
