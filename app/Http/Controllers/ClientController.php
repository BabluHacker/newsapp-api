<?php


namespace App\Http\Controllers;



use App\Client;
use App\Mail\emailConfirm;
use App\Mail\notifyMail;
use App\TokenApiKey;
use App\TokenClientAccess;
use http\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;


class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth_client', ['only' => [
            'me', 'update_email', 'update_password', 'update_profile',
            'delete', 'signout',
            'get_own_key', 'api_key_details', 'request_change_plan',
            'generate_refresh_own_key'

        ]]);

        $this->middleware('auth_client_not_confirmed', ['only' => [
            'resend_confirmation_link'
        ]]);
    }

    public function signup(Request $request){ // form & send mail to confirm
        DB::beginTransaction();
        $this->validate($request, Client::rules() );
        $attributes = $request->all();
        $attributes['password'] = Hash::make($attributes['password']);
        unset($attributes['confirm_password']);
        $attributes['confirmation_token'] = md5(uniqid());
        $model = Client::create($attributes);

        /* Generate Mailing confirmation url link*/
        $data_mail = [];
        $data_mail['first_name'] = $model->first_name;
        $data_mail['last_name'] = $model->last_name;
        $data_mail['url'] = route('confirm', ['token'=>$model->confirmation_token]);

        //route('confirm', ['token'=>23232])
        try {
            Mail::to($model->email)->send(new emailConfirm($data_mail));
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json("error in sending mail", 400, [], JSON_PRETTY_PRINT);
        }
        DB::commit();

        $response = [
            'status' => 1,
            'data' => 'Success'
        ];
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }

    public function signin(Request $request){
        $this->validate($request, Client::signin_rules() );
        if ($model = Client::authorize($request->all())) {

            $auth_code = $this->createAccessToken($model->id);

            $data = [];
            $data['token'] = $auth_code->access_token;
            $data['mail_confirmed'] = $model->is_email_confirmed;

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
    public function send_password_to_mail(Request $request){
        $this->validate($request, ['email'=>'required|email'] );
        $clientModel = Client::where('email', 'like', $request->input('email'))->first();
        if(!$clientModel){
            $response = [
                'status' => 0,
                'data' => 'Invalid Email or email is not registered yet'
            ];
            return response()->json($response, 400, [], JSON_PRETTY_PRINT);
        }
        $data_mail = [];
        $data_mail['first_name'] = $clientModel->first_name;
        $data_mail['last_name'] = $clientModel->last_name;
        $pass = strval(uniqid());

        $data_mail['msg'] = "Your new Password is: ".$pass."     **Please Reset your password after login";

        $clientModel->password = Hash::make($pass);
        Mail::to($clientModel->email)->send(new notifyMail($data_mail));
        $clientModel->save();
        $response = [
            'status' => 1,
            'data' => 'Password sent to your email address'
        ];
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }

    public function confirm_mail(Request $request, $token){
        $model = Client::where('confirmation_token', 'like', $token)->first();
        if(!$model){
            // todo redirect to not confirmed url
            return response()->json('not found client', 400, [], JSON_PRETTY_PRINT);
        }
        $model->is_email_confirmed = 'True';
        //logout all session
        $this->deleteOtherAccessToken($model->id);
        $model->save();
        // todo redirect to confirmed page
        return response()->json('success', 200, [], JSON_PRETTY_PRINT);
    }

    public function resend_confirmation_link(Request $request){

        $model = $this->findModelFromAccessToken($request->headers->all()['c-access-token'][0]);
        $model->confirmation_token = md5(uniqid());
        $data_mail = [];
        $data_mail['first_name'] = $model->first_name;
        $data_mail['last_name'] = $model->last_name;
        $data_mail['url'] = route('confirm', ['token'=>$model->confirmation_token]);

        try {
            Mail::to($model->email)->send(new emailConfirm($data_mail));
            $model->save();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json("error in sending mail", 400, [], JSON_PRETTY_PRINT);
        }
        return response()->json('success', 200, [], JSON_PRETTY_PRINT);
    }
    // get identity from c_access_token
    public function me(Request $request){
        $headers = $request->headers->all();
        $c_access_token = $headers['c-access-token'][0];

        $clientModel = $this->findModelFromAccessToken($c_access_token)->toArray();

        unset($clientModel['password']);
        unset($clientModel['reset_password_token']);
        unset($clientModel['confirmation_token']);

        $response = [
            'status' => 1,
            'data' => $clientModel
        ];
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }
    // get identity from c_access_token
    public function update_email(Request $request){
        $this->validate($request, ['email'=>'required|email'] );
        $headers = $request->headers->all();
        $c_access_token = $headers['c-access-token'][0];

        $clientModel = $this->findModelFromAccessToken($c_access_token);
        $clientModel->email = $request->input('email');
        $clientModel->is_email_confirmed = 'False';

        $clientModel->confirmation_token = md5(uniqid());
        $data_mail = [];
        $data_mail['first_name'] = $clientModel->first_name;
        $data_mail['last_name'] = $clientModel->last_name;
        $data_mail['url'] = route('confirm', ['token'=>$clientModel->confirmation_token]);
        try {
            Mail::to($clientModel->email)->send(new emailConfirm($data_mail));
            $clientModel->save();
        } catch (Exception $e) {

            return response()->json("error in sending mail", 400, [], JSON_PRETTY_PRINT);
        }
        return response()->json("Success", 200, [], JSON_PRETTY_PRINT);
    }
    // get identity from c_access_token
    public function update_password(Request $request){
        $headers = $request->headers->all();
        $c_access_token = $headers['c-access-token'][0];
        $this->validate($request, Client::pass_change_rules() );
        $attributes = $request->all();
        $clientModel = $this->findModelFromAccessToken($c_access_token);
        if(Hash::check($attributes['old_password'],$clientModel->password)) {
            $clientModel->password = Hash::make($attributes['password']);
            $this->deleteOtherAccessToken($clientModel->id);
            $clientModel->save();
            $response = [
                'status' => 1,
                'message' => "Password Changed"
            ];
            return response()->json($response, 200, [], JSON_PRETTY_PRINT);
        }
        $response = [
            'status' => 0,
            'message' => "Invalid Current Password"
        ];
        return response()->json($response, 400, [], JSON_PRETTY_PRINT);

    }
    // get identity from c_access_token
    public function update_profile(Request $request){
        $headers = $request->headers->all();
        $c_access_token = $headers['c-access-token'][0];
        $attributes = $request->all();
        $clientModel = $this->findModelFromAccessToken($c_access_token);
        $this->validate($request, Client::rules($clientModel->id) );
        $clientModel->update($attributes);
        $response = [
            'status' => 1,
            'message' => "Profile Updated"
        ];
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }
    // get identity from c_access_token
    public function deleteApiKey(Request $request){
        $headers = $request->headers->all();
        $c_access_token = $headers['c-access-token'][0];
        $this->validate($request, Client::pass_change_rules() );
        $attributes = $request->all();
        $clientModel = $this->findModelFromAccessToken($c_access_token);
        TokenApiKey::where('client_id', 'like', $clientModel->id)->delete();
        $response = [
            'status' => 1,
            'message' => "Success delete api key"
        ];
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }
    // get identity from c_access_token
    public function signout(Request $request){
        $headers = $request->headers->all();
        $token = $headers['c-access-token'][0];

        $model = TokenClientAccess::where(['access_token' => $token])->first();
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

    /**
    api_key & Client related
     *
     */
    // get identity from c_access_token
    public function get_own_key(Request $request){
        $model = $this->findModelFromAccessToken($request->headers->all()['c-access-token'][0]);
        $apiKeyModel = TokenApiKey::where('client_id', 'like', $model->id)->first();
        if(!$apiKeyModel){
            $response = [
                'status' => 0,
                'errors' => "No Api Key Registered"
            ];
            return response()->json($response, 400, [], JSON_PRETTY_PRINT);
        }
        $response = [
            'status' => 1,
            'data' => ['api_key'=> $apiKeyModel->api_key]
        ];
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }
    // get identity from c_access_token
    public function api_key_details(Request $request){
        $model = $this->findModelFromAccessToken($request->headers->all()['c-access-token'][0]);
        $apiKeyModel = TokenApiKey::where('client_id', 'like', $model->id)
            ->select('pricing_plan_id', 'requested_pricing_plan', 'total_call', 'created_at')->first();
        if(!$apiKeyModel){
            $response = [
                'status' => 0,
                'errors' => "No Api Key Registered"
            ];
            return response()->json($response, 400, [], JSON_PRETTY_PRINT);
        }
        $response = [
            'status' => 1,
            'data' => ['api_key'=> $apiKeyModel->api_key]
        ];
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }
    // get identity from c_access_token
    public function request_change_plan(Request $request){
        $this->validate($request, ['pricing_plan_id'=>'required'] );
        $modelClient = $this->findModelFromAccessToken($request->headers->all()['c-access-token'][0]);
        $modelClient->requested_pricing_plan = $request->input('pricing_plan_id');
        $modelClient->save();
    }
    // get identity from c_access_token
    public function generate_refresh_own_key(Request $request){
        $modelClient = $this->findModelFromAccessToken($request->headers->all()['c-access-token'][0]);
        $data = [];
        $data['api_key'] = md5(uniqid());
        $data['debug'] = 'false';
        $model = TokenApiKey::updateOrCreate(['client_id'=> $modelClient->id], $data);
        $response = [
            'status' => 1,
            'data' => $model
        ];
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }

    /**
    Supporting Functions
     */
    public function createAccessToken($client_id){
        $this->deleteOtherAccessToken($client_id);
        $model             = new TokenClientAccess();
        $model->access_token      = md5(uniqid());
        $model->expires_at      = time() + env('ACCESS_TOKEN_EXP');
        $model->client_id    = $client_id;

        $model->save();
        return ($model);
    }
    private function deleteOtherAccessToken($client_id){
        TokenClientAccess::where('client_id', 'like', $client_id)->delete();
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
        $model = Client::find($id);
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
    private function findModelFromAccessToken($c_access_token){
        $client_token = TokenClientAccess::where('access_token', 'like', $c_access_token)
            ->where('expires_at', '>', time())->first();
        $model = Client::where('id', 'like', $client_token->client_id)->first();
        if(!$model){
            $response = [
                'status' => 0,
                'errors' => "Invalid Token"
            ];
            response()->json($response, 400, [], JSON_PRETTY_PRINT)->send();
            die;
        }
        return $model;
    }

}
