<?php namespace App;


use App\Http\Controllers\GraphApiController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AccessToken extends Model
{
    protected $guarded = ['id','created_at','updated_at'];
    static public function rules($id=NULL)
    {
        return [
            'user_id' => 'required',
            'token' => 'required|unique:access_tokens,token,'.$id,
            'auth_code' => 'required',

        ];
    }

    static public function updateAccessToken($user_id, $new_token){
        $model = AccessToken::where('user_id', 'like', $user_id)->first();
        if($model) GraphApiController::logoutAccessToken($model->access_token);
        $model = AccessToken::updateOrCreate(['user_id'=>$user_id], ['access_token'=>$new_token]);
        if($model){
            return true;
        }
        return false;
    }
}
?>