<?php


namespace App\Http\Controllers;


use App\Category;
use App\CategoryPaperUrl;
use App\News;
use App\Newspaper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('auth_api_key', ['only' => [
            'index', 'view'
        ]]);
    }


    public function inc_read_count(Request $request, $id){
        $model = News::find($id);
        if($model){
            $model->increment('read_count');
        }
        return response()->json('success', 200, [], JSON_PRETTY_PRINT);
    }
}
