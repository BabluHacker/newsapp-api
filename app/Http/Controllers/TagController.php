<?php


namespace App\Http\Controllers;


use App\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('auth_api_key', ['only' => [
            'index', 'view'
        ]]);
        $this->middleware('auth_dev_editor', ['only' => [
            'create', 'update'
        ]]);
        $this->middleware('auth_dev_superadmin', ['only' => [
            'delete'
        ]]);
    }
    public function index(Request $request)
    {
        $response = Tag::search($request);
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }
    public function view(Request $request, $id)
    {
        $model = $this->findModel($request, $id);
        return response()->json($model, 200, [], JSON_PRETTY_PRINT);
    }



    public function create(Request $request)
    {
        $this->validate($request, Tag::rules() );
        $data_to_insert = $request->all();
        /* todo here logics*/
        $model = Tag::create($data_to_insert);

        $response = [
            'status' => 1,
            'data' => $model
        ];
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }


    public function update(Request $request, $id)
    {
        $this->validate($request, Tag::rules($id) );

        $data_to_insert = $request->all();
        $model = Tag::where("id", $id)
            ->update($data_to_insert);

        return response()->json($model, 200, [], JSON_PRETTY_PRINT);
    }

    public function delete(Request $request, $id)
    {
        $model = $this->findModel($request, $id);
        $model->delete();
        $response = [
            'status' => 1,
            'message'=>'Removed successfully.'
        ];
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }

    public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = [])
    {
        $validator = $this->getValidationFactory()->make($request->all(), $rules, $messages, $customAttributes);
        if ($validator->fails()) {
            $response = [
                'status' => 0,
                'errors' => $validator->errors()
            ];
            response()->json($response, 400, [], JSON_PRETTY_PRINT)->send();
            die();
        }
        return true;
    }

    public function findModel(Request $request, $id)
    {
        $model = Tag::find($id);
        if (!$model) {
            $response = [
                'status' => 0,
                'errors' => "Invalid Record"
            ];
            response()->json($response, 400, [], JSON_PRETTY_PRINT)->send();
            die;
        }
        return $model;
    }
}
