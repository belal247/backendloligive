<?php

namespace App\Http\Controllers;

use App\Mail\welcomemail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function sendEmail(){
        $moreuser = "bilal619@live.com";
        $toEmail = "bilalahmad247@yahoo.com";
        $message = "Hello, welcome to our website";
        $subject = "Welcome to card security system";

        $request = Mail::to($toEmail)->cc($moreuser)->send(new welcomemail($message,$subject));

        dd($request);
    }
}
