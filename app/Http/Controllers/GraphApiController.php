<?php


namespace App\Http\Controllers;


use App\AccessToken;
use Illuminate\Http\Request;

class GraphApiController extends Controller
{
    static public function getUserAccountDetails($token){
        $ch = curl_init();
        $url = 'https://graph.accountkit.com/v1.1/me/?access_token='.$token;
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$url);
        $result=curl_exec($ch);
        curl_close($ch);
        $final = json_decode($result, true);
        return $final;
    }

    static public function logoutAccessToken($token){
        /*
         * Remove access token*/
        AccessToken::where('access_token', 'like', $token)->delete();
        /*
         * Curl request*/
        $ch = curl_init();
        $url = 'https://graph.accountkit.com/v1.1/logout/?access_token='.$token;
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$url);
        $result=curl_exec($ch);
        curl_close($ch);
        $final = json_decode($result, true);
        return $final;
    }
}