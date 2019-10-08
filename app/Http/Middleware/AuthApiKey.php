<?php


namespace App\Http\Middleware;

use App\ApiCallCount;
use App\PricingPlan;
use App\TokenApiKey;
use Closure;

class AuthApiKey
{
    public function handle($request, Closure $next)
    {
        $headers=$request->headers->all();
        if(!empty($headers['api-key'][0])) {
            $has_access = $this->findApiKey($headers['api-key'][0]);
            if(!$has_access['no_error']) return response($has_access['message'], 401);
        }
        else{
            return response('Unauthorized Api Key or Include api Key', 401);
        }

        return $next($request);
    }


    private function findApiKey($api_key){
        $error =[
            'no_error'=>true,
            'message'=>"No Error",
        ];
        $debug = env('APP_DEBUG');
        if($debug) {
            $api_key = TokenApiKey::where('api_key', 'like', $api_key)
                ->where('debug', 'like', 'true')->first();

        }
        else{
            $api_key = TokenApiKey::where('api_key', 'like', $api_key)
                ->where('debug', 'like', 'false')
                ->first();
            if($api_key) {
                if ($api_key->pricing_plan_id == 1 and $api_key->next_call != "") {
                    if ($api_key->next_call < time()){
                        $error['no_error'] = false;
                        $error['message'] = "According to FREE plan ".env('API_CALL_GAP')." seconds gap between 2 consecutive requests. Try again after";
                        $error['message'] = time()." ".(time() + env('API_CALL_GAP'));
                        return $error;
                    }else {
                        $api_key->next_call = time() + env('API_CALL_GAP');
                    }
                }else{
                    $api_key->next_call = time() + env('API_CALL_GAP');
                }
            }
        }
        if($api_key) {
            $error = $this->updateApiCall($api_key->id, $api_key->pricing_plan_id);
            if($error['no_error']) {
                $api_key->increment('total_call');
                $api_key->save();
            }
            return $error;
        }
        return $error;
    }
    private function updateApiCall($api_key_id, $pricing_id){
        $error =[
            'no_error'=>true,
            'message'=>"No Error",
        ];
        $this_month = date('Y-m');
        $api_call = ApiCallCount::firstOrCreate(['token_api_key_id'=>$api_key_id], ['month_year'=>$this_month]);
        $api_call->increment('total_call');
        //for debug true don't check pricing plan
        if(env('APP_DEBUG')) {
            $api_call->save();
            return $error;
        }
        if($api_call) {
            if ($pricing_id == 1) {
                if ($api_call->total_call > env('PRICING_MONTHLY_LIMIT_1')) {
                    $error['no_error'] = false;
                    $error['message'] = "Developer FREE Limit Exceeds";
                    return $error;
                }
            } elseif ($pricing_id == 2) {
                // Will bear extra requests cost
                if ($api_call->total_call > env('PRICING_MONTHLY_LIMIT_2')) {
                    // todo later add mail to client after every 1k extra requests
                }

            } elseif ($pricing_id == 3) {
                // Will bear extra requests cost
                if ($api_call->total_call > env('PRICING_MONTHLY_LIMIT_3')) {
                    // todo later add mail to client after every 1k extra requests
                }
            }
        }

        $api_call->save();
        return $error;
    }
}
