<?php


namespace App\Http\Middleware;

use App\TokenApiKey;
use Closure;

class AuthApiKey
{
    public function handle($request, Closure $next)
    {
        $headers=$request->headers->all();
        if(!empty($headers['api-key'][0])) {
            $has_access = $this->findApiKey($headers['api-key'][0]);
            if(!$has_access) return response('Unauthorized Api Key', 401);
        }
        else{
            return response('Unauthorized Api Key', 401);
        }
        return $next($request);
    }
    private function findApiKey($api_key){
        $debug = env('APP_DEBUG');
        if($debug) {
            $api_key = TokenApiKey::where('api_key', 'like', $api_key)
                ->where('debug', 'like', 'true')->first();
        }
        else{
            $api_key = TokenApiKey::where('api_key', 'like', $api_key)
                ->where('debug', 'like', 'false')->first()->increment('total_call');
        }
        if($api_key) {

            return true;
        }
        return false;
    }
}
