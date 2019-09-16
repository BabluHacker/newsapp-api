<?php


namespace App\Http\Controllers;


use App\Developer;
use App\TokenDevAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class DeveloperController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth_dev_superadmin', ['only' => [
            'index', 'view', 'update', 'delete'
        ]]);
        $this->middleware('auth_dev_editor', ['only' => [
            'me', 'logout', 'refresh'
        ]]);
    }

    public function auth(Request $request){ // no header
        if ($model = Developer::authorize($request->all())) {

            $auth_code = $this->createAccessToken($model->id);

            $data = [];
            $data['token'] = $auth_code->access_token;

            $response = [
                'status' => 1,
                'data' => $data
            ];
            return response()->json($response, 200, [], JSON_PRETTY_PRINT);
        } else {
            $response = [
                'status' => 0,
                'error' => "Email or Password is wrong"
            ];
            return response()->json($response, 400, [], JSON_PRETTY_PRINT);
        }
    }


    public function me(Request $request){
        $headers = $request->headers->all();
        $d_access_token = $headers['d-access-token'][0];
        $token = TokenDevAccess::where('access_token', 'like', $d_access_token)->first();
        $devModel = Developer::find($token->developer_id)->toArray();
        unset($devModel['password']);
        $response = [
            'status' => 1,
            'data' => $devModel
        ];
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }

    public function logout(Request $request){
        $headers = $request->headers->all();
        if (!empty($headers['d-access-token'][0])) {
            $token = $headers['d-access-token'][0];
        } else if ($request->input('access_token')) {
            $token = $request->input('access_token');
        }
        $model = TokenDevAccess::where(['access_token' => $token])->first();
        if ($model->delete()) {
            $response = [
                'status' => 1,
                'message' => "Logged Out Successfully"
            ];
            return response()->json($response, 200, [], JSON_PRETTY_PRINT);
        } else {
            $response = [
                'status' => 0,
                'message' => "Invalid request"
            ];
            return response()->json($response, 400, [], JSON_PRETTY_PRINT);
        }
    }
    public function refresh(Request $request){
        $headers = $request->headers->all();

        if (!$access_token = $this->refreshAccesstoken($headers['d-access-token'])) {
            $response = [
                'status' => 0,
                'error' => "Invalid Access token"
            ];
            return response()->json($response, 400, [], JSON_PRETTY_PRINT);
        }

        $data = [];
        $data['access_token'] = $access_token->access_token;
        $response = [
            'status' => 1,
            'data' => $data
        ];

        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }

    public function index(Request $request)
    {
        $response = Developer::search($request);
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }
    public function view(Request $request, $id)
    {
        $model = $this->findModel($request, $id);
        return response()->json($model, 200, [], JSON_PRETTY_PRINT);
    }

    public function create(Request $request)
    {
        $this->validate($request, Developer::rules() );

        $attributes = $request->all();
        $attributes['password'] = Hash::make($attributes['password']);
        $model = Developer::create($attributes);

        $response = [
            'status' => 1,
            'data' => $model
        ];
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }


    public function update(Request $request, $id)
    {

        $this->validate($request, Developer::rules($id) );

        $data_to_insert = $request->all();
        /**
        Password also can be changed by super admin*/
        $data_to_insert['password'] = Hash::make($data_to_insert['password']);
        $model = Developer::where("id", 'like', $id)
            ->update($data_to_insert);

        $response = [
            'status' => 1,
            'data'=> $model
        ];
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
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
        $model = Developer::find($id);
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

    public function createAccessToken($dev_id){
        $this->deleteOtherAccessToken($dev_id);
        $model             = new TokenDevAccess();
        $model->access_token      = md5(uniqid());
        $model->expires_at      = time() + env('ACCESS_TOKEN_EXP');
        $model->developer_id    = $dev_id;

        $model->save();
        return ($model);
    }
    private function deleteOtherAccessToken($dev_id){
        TokenDevAccess::where('developer_id', 'like', $dev_id)->delete();
    }
    public function refreshAccesstoken($token)
    {
        $access_token = TokenDevAccess::where(['access_token' => $token])->first();
        if ($access_token) {


            $new_access_token = $this->createAccesstoken($access_token->developer_id);
            $access_token->delete();
            return ($new_access_token);
        } else {

            return false;
        }
    }
}
