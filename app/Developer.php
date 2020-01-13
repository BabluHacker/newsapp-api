<?php


namespace App;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Developer extends Model
{
    protected $guarded = ['id', 'updated_at', 'created_at'];


    public static function rules($id=null)
    {
        if($id == null) {
            return [
                'email' => 'required|unique:developers,email,',
                'password' => 'required',
                'type' => 'required|in:super_admin,editor',
            ];
        }
        else{
            return [
                'email' => 'required|unique:developers,email,'.$id,
                'type' => 'required|in:super_admin,editor',
            ];
        }
    }
    static public function search($request)
    {
        $params = $request->all();
        $limit  = isset($params['limit']) ? $params['limit'] : 10;
        $query  = isset($params['fields'])? Developer::select(explode(",", $params['fields'])):Developer::select();


        $data = $query->paginate($limit);

        return [
            'status'=>1,
            'data' => $data
        ];
    }

    public static function authorize($attributes){

        $model=Developer::where(['email'=>$attributes['email']])->select(['id','email','password'])->first();

        if(!$model)
            return false;

        if(Hash::check($attributes['password'],$model->password)) {
            return $model;
            // Right password
        } else {
            // Wrong one
        }

        return false;
    }



}
