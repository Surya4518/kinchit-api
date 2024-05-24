<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class UserValidate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // public function handle(Request $request, Closure $next): Response
    // {
    //      //dd($request->all());
    //     if($request->token == ''){
    //         return response()->json(['status' => 501,'error' => 'Unauthorized']);
    //     }
    //     $user_check = DB::table('wpu6_users')->where('web_token',$request->token)->get();
    //     if ($user_check->count() > 0) {
    //         session()->put('user', $user_check);
    //         return $next($request);
    //     }else{
    //         return response()->json(['status' => 501,'error' => 'Unauthorized']);
    //     }
    // }

    public function handle(Request $request, Closure $next): Response
    {
        // dd($request->all());
        if($request->token == ''){
            return response()->json(['status' => 501,'error' => 'Unauthorized']);
        }
        if(!$request->device_id){
            return response()->json(['status' => 501,'error' => 'Unknown device id']);
        }
        if($request->device_id == '1'){
            $user_check = DB::table('wpu6_users')->where('web_token',$request->token)->get();
        }elseif($request->device_id == '2'){
            $user_check = DB::table('wpu6_users')->where('app_token',$request->token)->get();
        }else{
            return response()->json(['status' => 501,'error' => 'Unknown device id']);
        }
        if ($user_check->count() > 0) {
            session()->put('user', $user_check);
            return $next($request);
        }else{
            return response()->json(['status' => 501,'error' => 'Unauthorized']);
        }
    }
}
