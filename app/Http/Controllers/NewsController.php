<?php


namespace App\Http\Controllers;


use App\Category;
use App\CategoryPaperUrl;
use App\News;
use App\Newspaper;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('auth_api_key', ['only' => [
            'index', 'view'
        ]]);
    }
    public function getJoined(Request $request){
        $newses = News::with('category')->with('newspaper')->get();
        $newses2 = CategoryPaperUrl::find(2)->category;
        $cat = Newspaper::find(9)->url()->with('newspaper')->with('category')->get();
        $cat2 = Newspaper::find(9)->news()->with('newspaper')->with('category')->get();
        $cat3 = Category::find(4)->news()->get();

        return response()->json($cat3, 200, [], JSON_PRETTY_PRINT);
    }
    public function index(Request $request)
    {
        $response = News::search($request);
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }

    public function view(Request $request, $id)
    {
        $model = $this->findModel($request, $id);
        return response()->json($model, 200, [], JSON_PRETTY_PRINT);
    }

    public function findModel(Request $request, $id)
    {
        $model = News::find($id);
        if (!$model) {
            $response = [
                'status' => 0,
                'errors' => "Invalid Record"
            ];
            response()->json($response, 400, [], JSON_PRETTY_PRINT)->send();
            die;
        }
        $params = $request->all();
        if(isset($params['with'])){
            $withs = explode(',', $params['with']);
            foreach ($withs as $with){
                $model->$with;
            }
        }
        return $model;
    }
}
