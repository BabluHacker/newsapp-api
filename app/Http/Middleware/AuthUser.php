<?php


namespace App\Http\Middleware;

use App\TokenUserAccess;
use App\User;
use Closure;

class AuthUser
{
    public function handle($request, Closure $next)
    {

        $headers=$request->headers->all();
        if(!empty($headers['x-access-token'][0])) {
            $has_access = $this->findUserAccessToken($headers['x-access-token'][0]);
            if(!$has_access) return response('Unauthorized  or Invalid User', 401);
        }
        else {
            return response('Unauthorized or Invalid User', 401);
        }
        return $next($request);
    }

    private function findUserAccessToken($x_access_token){
        $user_token  = TokenUserAccess::where('access_token', 'like', $x_access_token)->first();
        if($user_token) {
            $model = User::where('id', 'like', $user_token->user_id)->first();
            if($model) return true;
        }
        return false;
    }
}
