<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Session;
use Storage;

use View;
use Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use App\Common;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use App\Mail\MyMail;
use Illuminate\Support\Facades\Mail;

class ContactusController extends Controller
{
 
    public function Contactus(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required',
        'email' => 'required',
        'message' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json(['status'=>401,'message' =>'Validation Failed','errors' => $validator->errors()]);
    }

    $contactus = [
        'name' => $request->name,
        'email' => $request->email,
        'message' => $request->message,
    ];
    Mail::to($request->email)->send(new MyMail(null,null,null,$contactus,null));
    $insert =  DB::table('contact_us')->insert($contactus);
    $insert =  DB::table('email_log')->insert($contactus);

    return response()->json(['status'=>200,'message' => 'Contact form submitted successfully' ,'data'=> $contactus]);
}
    




}
