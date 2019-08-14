<?php


namespace App;


use App\Http\Controllers\GraphApiController;
use Illuminate\Database\Eloquent\Model;

class TokenUserAccess extends Model
{
    protected $table="token_user_access";
    protected $guarded = ['id', 'updated_at', 'created_at'];
    static public function updateAccessToken($user_id, $new_token){
        $model = TokenUserAccess::where('user_id', 'like', $user_id)->first();
        if($model) GraphApiController::logoutAccessToken($model->access_token);
        $model = TokenUserAccess::updateOrCreate(['user_id'=>$user_id], ['access_token'=>$new_token]);
        if($model){
            return true;
        }
        return false;
    }
}
