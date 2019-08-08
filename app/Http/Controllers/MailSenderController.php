<?php


namespace App\Http\Controllers;


use App\Mail\MyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailSenderController extends Controller
{

    public function send(Request $request)
    {
        Mail::to('mehedi@technocrats.io')->send(new MyEmail());
    }
}
