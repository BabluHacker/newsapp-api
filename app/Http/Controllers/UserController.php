<?php

namespace App\Http\Controllers;


use App\TokenUserAccess;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function __construct(Request $request)
    {
        $this->middleware('auth_dev_superadmin', ['only' => [
            'index', 'view', 'delete'
        ]]);
        $this->middleware('auth_user', ['only' => [
            'me', 'logout'
        ]]);
    }

    public function auth(Request $request)
    {
        /*
         * pass param : fb kit token*/
        /*
         * verify :
         * 1. if token exists in table-> send user_details
         * 2. if token doesn't exist (new user or new login)->
         *      a. check id of fb_kit and check fb_kit_id exists (graph api call valid ) <==> if graph api failed (send error)
         *          1) if exists (new login) -> replace with old access_token -> send user_details
         *          2) if doesn't (new user) -> send ask_for detail_form (don't store user fb_kit details and Access Token)

         * */
        $this->validate($request, User::authorizeRules());
        $accessTokenModel = TokenUserAccess::where('access_token', 'like', $request->input('token'))->first();
        if($accessTokenModel){
            $userModel = User::find($accessTokenModel->user_id);
            $response = [
                'status' => 1,
                'data'  => $userModel
            ];
            return response()->json($response, 200, [], JSON_PRETTY_PRINT);
        }
        else{
            $userAccount = GraphApiController::getUserAccountDetails($request->input('token'));

            if(isset($userAccount['error'])) {
                $response = [
                    'status' => 0,
                    'error' => 'Invalid Access Token'
                ];
                return response()->json($response, 400, [], JSON_PRETTY_PRINT);
            }
            else{
                $userModel = User::where('kit_user_id', 'like', $userAccount['id'])->first();
                if($userModel){ //new login, old user
                    $res = TokenUserAccess::updateAccessToken($userModel->id, $request->input('token'));
                    if($res){
                        $response = [
                            'status' => 1,
                            'data'  => $userModel
                        ];
                        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
                    }
                    else{
                        $response = [
                            'status' => 0,
                            'error' => 'Error Occurred in Updating Token'
                        ];
                        return response()->json($response, 400, [], JSON_PRETTY_PRINT);
                    }
                }
                else{ // new user
                    $response = [
                        'status' => 2, // new user
                        'error' => 'FB Kit User ID doesn\'t exist. New User'
                    ];
                    return response()->json($response, 400, [], JSON_PRETTY_PRINT);
                }
            }
        }

    }

    public function createOrUpdate(Request $request)
    {
        /*
         * params : fb kit token, {USER_DETAIL FORM}*/
        /*
         * verify :
         *  1. check valid access_token or not (graph api call)
         *      a. if valid -> createOrUpdate user details in user table and createOrUpdate access_token in access_tokens table
         *      b. if not valid -> send error*/
        $this->validate($request, User::rules());

        $data_all = $request->all();
        $token = $data_all['token'];
        $userAccount = GraphApiController::getUserAccountDetails($data_all['token']);
        if(isset($userAccount['error'])){
            $response = [
                'status' => 0,
                'error' => 'Invalid Access Token'
            ];
            return response()->json($response, 400, [], JSON_PRETTY_PRINT);
        }
        else{

            unset($data_all['token']);
            $data_query = [];
            $data_query['kit_user_id'] = $userAccount['id'];
            $data_query['phone_no'] = $userAccount['phone']['number'];
            $modelUser = User::updateOrCreate($data_query, $data_all);
            if($modelUser){

                $modelAT = TokenUserAccess::updateOrCreate(['user_id'=>$modelUser->id], ['access_token'=>$token]);
                $response = [
                    'status' => 1,
                    'data'  => $modelUser,
                    'at' => $modelAT
                ];
                return response()->json($response, 200, [], JSON_PRETTY_PRINT);
            }
        }
        $response = [
            'status' => 0,
            'error' => 'User Creation failed'
        ];
        return response()->json($response, 400, [], JSON_PRETTY_PRINT);
    }

    public function me(Request $request){
        $headers = $request->headers->all();
        $modelAT = TokenUserAccess::where('access_token', 'like', $headers['x-access-token'][0])->first();
        $modelUser = User::find($modelAT->user_id);
        $response = [
            'status' => 1,
            'data' => $modelUser
        ];
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }

    public function logout(Request $request){
        $headers = $request->headers->all();
        $res = GraphApiController::logoutAccessToken($headers['x-access-token'][0]);
        if(isset($res['error'])){
            $response = [
                'status' => 0,
                'data' => $res
            ];
            return response()->json($response, 400, [], JSON_PRETTY_PRINT);
        }
        $response = [
            'status' => 1,
            'data' => $res
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

}

?>
