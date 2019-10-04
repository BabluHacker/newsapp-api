<?php


namespace App;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Client extends Model
{
    protected $guarded = ['id', 'updated_at', 'created_at'];

    static public function rules($id=null){
        if($id == null)
            return [
                'first_name'    => 'required',
                'last_name'     => 'required',
                'gender'        => 'required|in:Male,Female,Other',
                /*'mobile_no' => array('required', 'regex:/^01(1|3|4|5|6|7|8|9)\d{8}$/'),*/
                'dob'           => 'required',
                'type'          => 'required|in:personal,professional',
                'company_name'  => 'required_if:type,professional',
                'email'         => 'required|email|unique:clients,email,' . $id,
                'password'      => 'required|min:8',
                'confirm_password'  => 'required|same:password',
            ];
        else
            return [
                'first_name'    => 'required',
                'last_name'     => 'required',
                'gender'        => 'required|in:Male,Female,Other',
                /*'mobile_no' => array('required', 'regex:/^01(1|3|4|5|6|7|8|9)\d{8}$/'),*/
                'dob'           => 'required',
                'type'          => 'required|in:personal,professional',
                'company_name'  => 'required_if:type,professional',
            ];
    }
    public static function signin_rules()
    {
        return [
            'email'         => 'required|email',
            'password'      => 'required'
        ];
    }
    public static function pass_change_rules()
    {
        return [
            'old_password'      => 'required',
            'password'          => 'required|min:8',
            'confirm_password'  => 'required|same:password',
        ];
    }
    static public function search($request)
    {
        $params = $request->all();
        $limit  = isset($params['limit']) ? $params['limit'] : 10;
        $query  = isset($params['fields'])? User::select(explode(",", $params['fields'])):User::select();
        if(isset($params['is_email_confirmed']) and $params['is_email_confirmed']!="" and $params['is_email_confirmed']!="null"){
            $query->where('is_email_confirmed', 'like', $params['is_email_confirmed']);
        }
        if(isset($params['gender']) and $params['gender']!="" and $params['gender']!="null"){
            $query->where('gender', 'like', $params['gender']);
        }
        if(isset($params['type']) and $params['type']!="" and $params['type']!="null"){
            $query->where('type', 'like', $params['type']);
        }


        $data = $query->paginate($limit);

        return [
            'status'=>1,
            'data' => $data
        ];
    }

    public static function authorize($attributes){

        $model=Client::where(['email'=>$attributes['email']])->select(['id','email','password', 'is_email_confirmed'])->first();

        if(!$model)
            return false;

        if(Hash::check($attributes['password'],$model->password)) {
            return $model;
            // Right password
        }

        return false;
    }



}
