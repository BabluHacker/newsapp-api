<?php


namespace App\Http\Controllers;


use App\Client;
use App\Mail\notifyMail;
use App\TokenApiKey;
use http\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class TokenApiKeyController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth_dev_superadmin');
    }

    // Only Control for super admin
    /**
     * 1. index
     * 2. view
     * 4. delete
     * 5. create (by default in debug=true )
     * */



    public function index(Request $request)
    {
        $response = TokenApiKey::search($request);
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }

    public function view(Request $request, $id)
    {
        $model = $this->findModel($id);
        return response()->json($model, 200, [], JSON_PRETTY_PRINT);
    }



    public function create(Request $request)
    {
        $this->validate($request, TokenApiKey::rules() );
        $data_to_insert = [];
        $data_to_insert['debug'] = $request->input('debug');
        $data_to_insert['api_key'] = md5(uniqid());

        $model = TokenApiKey::create($data_to_insert);

        $response = [
            'status' => 1,
            'data' => $model
        ];
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }

    public function delete(Request $request, $id)
    {
        $model = $this->findModel( $id);
        $model->delete();
        $response = [
            'status' => 1,
            'message'=>'Removed successfully.'
        ];
        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }

    public function findModel( $id)
    {
        $model = TokenApiKey::find($id);
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

    /*
         * For getting Client's request about changing plan*/
    public function requests(Request $request){
        $modelRequ = TokenApiKey::select('id', 'pricing_plan_id', 'requested_pricing_plan')
            ->where('debug', 'like', 'false')
            ->where('requested_pricing_plan', 'not like', 0)->paginate(10);

        return response()->json($modelRequ, 200, [], JSON_PRETTY_PRINT);
    }
    /*
     * For executing Client's request about changing plan*/
    public function change_plan(Request $request, $id){ // approved => true/false
        $this->validate($request, TokenApiKey::rules('change_plan') );
        $model = $this->findModel( $id);
        if($model->requested_pricing_plan == 0){
            $response = [
                'status' => 0,
                'errors' => "Invalid Change Plan Request"
            ];
            response()->json($response, 400, [], JSON_PRETTY_PRINT)->send();
            die;
        }
        if($request->input('approved') == 'true') {
            $model->pricing_plan_id = $model->requested_pricing_plan;
            $model->requested_pricing_plan = 0;
            $modelClient = Client::find($model->client_id);

            $data_mail = [];
            $data_mail['first_name'] = $modelClient->first_name;
            $data_mail['last_name'] = $modelClient->last_name;
            if($modelClient){
                $data_mail['msg'] = 'Congratulations! Your Pricing Plan has been changed';
                Mail::to($modelClient->email)->send(new notifyMail($data_mail));
                $model->save();
            }
            return response()->json('success', 200, [], JSON_PRETTY_PRINT);
        }
        else{

            $model->requested_pricing_plan = 0;
            $modelClient = Client::find($model->client_id);

            $data_mail = [];
            $data_mail['first_name'] = $modelClient->first_name;
            $data_mail['last_name'] = $modelClient->last_name;
            if($modelClient){
                $data_mail['msg'] = 'Sorry! Your Pricing Plan can\'t be been changed';
                Mail::to($modelClient->email)->send(new notifyMail($data_mail));
                $model->save();
            }
        }
        return response()->json('success', 200, [], JSON_PRETTY_PRINT);
    }
}
