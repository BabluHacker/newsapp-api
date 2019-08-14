<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

use Illuminate\Support\Facades\Hash;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    protected $table="users";
    protected $guarded = ['id', 'updated_at', 'created_at'];

    static public function search($request)
    {
        $params = $request->all();
        $limit  = isset($params['limit']) ? $params['limit'] : 10;
        $query  = isset($params['fields'])? User::select(explode(",", $params['fields'])):User::select();


        $data = $query->paginate($limit);

        return [
            'status'=>1,
            'data' => $data
        ];
    }

    static public function authorizeRules()
    {
        return [
            'token' => 'required'
        ];
    }
    static public function rules()
    {
        return [
            'token' => 'required'
        ];
    }

}
