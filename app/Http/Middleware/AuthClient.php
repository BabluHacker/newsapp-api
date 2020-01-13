<?php


namespace App\Http\Middleware;

use App\Client;
use App\TokenClientAccess;
use App\TokenUserAccess;
use App\User;
use Closure;

class AuthClient
{
    public function handle($request, Closure $next)
    {

        $headers=$request->headers->all();
        if(!empty($headers['c-access-token'][0])) {
            $has_access = $this->findUserAccessToken($headers['c-access-token'][0]);
            if(!$has_access) return response('Unauthorized  or Invalid Client', 401);
        }
        else {
            return response('Unauthorized or Invalid Client', 401);
        }
        return $next($request);
    }

    private function findUserAccessToken($c_access_token){
        $user_token  = TokenClientAccess::where('access_token', 'like', $c_access_token)
            ->where('expires_at', '>', time())->first();
        if($user_token) {
            $model = Client::where('id', 'like', $user_token->client_id)
                ->where('is_email_confirmed', 'like', 'True')->first();
            if($model) return true;
        }
        return false;
    }
}
