<?php

namespace App\Http\Controllers;

use App\ClientDetail;
use Auth;
use App\User;
use App\AuthorizationCodes;
use App\AccessToken;
use App\UserAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use NumberFormatter;


class UserController extends Controller
{

    public function __construct(Request $request)
    {
        $this->middleware('auth');
    }

    public function auth(Request $request)
    {

    }


}

?>
