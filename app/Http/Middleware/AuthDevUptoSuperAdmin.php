<?php


namespace App\Http\Middleware;

use App\Developers;
use App\TokenDevAccess;
use Closure;

class AuthDevUptoSuperAdmin
{
    public function handle($request, Closure $next)
    {

        $headers=$request->headers->all();
        if(!empty($headers['d-access-token'][0])) {
            $has_access = $this->findDeveloperAccessToken($headers['d-access-token'][0]);
            if(!$has_access) return response('Unauthorized', 401);
        }
        else{
            return response('Unauthorized', 401);
        }
        return $next($request);

    }
    private function findDeveloperAccessToken($d_access_token){
        $dev_token  = TokenDevAccess::where('access_token', 'like', $d_access_token)
            ->where('expires_at', '>', time())->first();
        if($dev_token) {
            $model = Developers::where('id', 'like', $dev_token->developer_id)
                ->where('type', 'like', 'super_admin')->first();
            if($model) return true;
        }
        return false;
    }
}
