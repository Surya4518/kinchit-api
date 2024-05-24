<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Session;
use Storage;
//use Mail;
use View;
use Response;
use Carbon\{Carbon};
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use App\Common;
use App\Models\{
    User
};
use App\Jobs\ResetOtpJob;
use Illuminate\Support\Facades\Bus;
use App\Mail\MyMail;
use DateTime;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class IndexController extends Controller
{
    public function UserRegister(Request $request)
    {
        try {
            $check = User::where('user_email', '=', $request->your_email)->orderby('ID','desc')->limit(1)->get();
            $rules = [
                'your_firstname' => 'required|string|max:255',
                'your_lastname' => 'required|string|max:255',
                'your_dob' => 'required|date',
                'your_gender' => 'required|string|max:255',
                'your_acharyan' => 'nullable|string|max:255',
                'your_mobile' => 'required|string|max:255',
                'your_whatsapp' => 'required|string|max:255',
                'address_line_1' => 'required|string|max:255',
                'address_line_2' => 'required|string|max:255',
                'country' => 'required|string|max:255',
                'city_town' => 'required|string|max:255',
                'pincode' => 'required|string|max:10',
                'educational_qualification' => 'required|string|max:255',
                'work_experience' => 'required|string|max:255',
                'special_interest' => 'required|string|max:255',
                'skill_set' => 'required|string|max:255',
                 'state' => 'required|string|max:255',
                'user_name' =>  'required|string|max:255|unique:wpu6_users,user_login',
                'user_password' => 'required|string|min:8',
                // 'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // adjust the max size and allowed extensions
            ];
            // dd($check);
            if($check->count() > 0){
                if($check[0]->verified_at == null){
                    $rules = [
                        'your_email' => 'required|email|max:255',
                        'user_name' =>  'required|string|max:255',
                    ];
                }else{
                    $rules = [
                        'your_email' => 'required|email|max:255|unique:wpu6_users,user_email|unique:userprofile_dt,user_email',
                        'user_name' =>  'required|string|max:255|unique:wpu6_users,user_login',
                    ];
                }
            }
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
            }
            if ($request->file('profile_photo')) {
                $image = $request->file('profile_photo');
                $filename = time() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('user-images/' . date("Y") . '/' . date("m"));
                $image->move($destinationPath, $filename);
                $file_path = 'user-images/' . date("Y") . '/' . date("m") . '/' . $filename;
            } else {
                $file_path = null;
            }
            $otp_number = mt_rand(100000 , 999999);
            Session::put('resendotp', $otp_number);

            // Retrieve stored OTP from session
            $resendotp = Session::get('resendotp');
            $arr = [
                'parent' => '7',
                'user_login' => $request->user_name,
                'user_pass' => md5($request->user_password),
                'user_nicename' => strtolower($request->user_name),
                'user_email' => $request->your_email,
                'user_registered' => date("Y-m-d H:i:s"),
                'display_name' => $request->your_firstname . ' ' . $request->your_lastname,
                'user_type' =>'general-user',
                'otp' => $otp_number
            ];
            Mail::to($request->your_email)->send(new MyMail(null , $arr,null,null));
            $insert = User::create($arr);
            $arr1 = [
                'user_id' => $insert->id,
                'first_name' => $request->your_firstname,
                'last_name' => $request->your_lastname,
                'user_email' => $request->your_email,
                'phone_number' => $request->your_mobile,
                'phone_number_wa' => $request->your_whatsapp,
                'dob' => date("Y-m-d", strtotime($request->your_dob)),
                'gender' => $request->your_gender,
                'acharyan' => $request->your_acharyan,
                'address_1' => $request->address_line_1,
                'address_2' => $request->address_line_2,
                'country' => $request->country,
                'state' => $request->state,
                'city' => $request->city_town,
                'postcode' => $request->pincode,
                'qualification' => $request->educational_qualification,
                'work_experience' => $request->work_experience,
                'hobbies' => $request->special_interest,
                'skills' => $request->skill_set,
                'created_at' => date("Y-m-d H:i:s")
            ];
            $insert1 = DB::table('userprofile_dt')->insert($arr1);
            return response()->json($insert == true && $insert1 == true ? ['status' => 200, 'message' => 'User Registered Successfully' , 'data' =>$arr] : ['status' => 400, 'message' => 'Failed to register']);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // public function UserLogin(Request $request)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'username' => 'required|string|max:255',
    //             'password' => 'required|string|max:255'
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
    //         }
    //         $user = User::where('user_login', '=', $request->username)->where('user_pass', '=', md5($request->password))->get();
    //         if ($user->count() > 0) {
    //             $token = Str::random(50);
    //             $user_update = User::where('user_login', '=', $request->username)->where('user_pass', '=', md5($request->password))->update(['web_token' => $token]);
    //             $user_details = DB::table('userprofile_dt')->where('user_id', '=', $user[0]->ID)->get();
    //         } else {
    //             $user_details = NULL;
    //         }
    //         $generatetoken = User::where('web_token', '=', $token)->get();
    //         return response()->json($user->count() > 0 ? ['status' => 200, 'message' => 'Successfully logged in.', 'user' => $generatetoken, 'user_details' => $user_details] : ['status' => 400, 'message' => 'Invalid credentials.']);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'status' => 500,
    //             'error' => $e->getMessage(),
    //         ]);
    //     }
    // }

    public function UserLogin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|max:255',
                'password' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
            }
            if(!$request->device_id){
                return response()->json(['status' => 501,'error' => 'Unknown device id']);
            }
            $user = User::where('user_login', '=', $request->username)->where('user_pass', '=', md5($request->password))->get();
            if ($user->count() > 0) {
                $token = Str::random(50);
                if($request->device_id == '1'){
                $user_update = User::where('user_login', '=', $request->username)->where('user_pass', '=', md5($request->password))->update(['web_token' => $token]);
                $user123 = User::where('web_token', '=', $token)->get();
            }elseif($request->device_id == '2'){
                $user_update = User::where('user_login', '=', $request->username)->where('user_pass', '=', md5($request->password))->update(['app_token' => $token]);
                $user123 = User::where('app_token', '=', $token)->get();
            }else{
                return response()->json(['status' => 501,'error' => 'Unknown device id']);
            }
                $user_details = DB::table('userprofile_dt')->where('user_id', '=', $user[0]->ID)->get();
                $matri_details = DB::table('m4winreg')->where('user_id', '=', $user[0]->ID)->get();
            } else {
                $user_details = NULL;
                $matri_details = NULL;
            }
            return response()->json($user->count() > 0 ? ['status' => 200, 'message' => 'Successfully logged in.', 'user' => $user123, 'user_details' => $user_details,'matri_details' => $matri_details] : ['status' => 400, 'message' => 'Invalid credentials.']);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function ForgetUpdateNewPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'new_pass' => 'required|unique:wpu6_users,user_pass',
                'confirm_pass' => 'required|string|max:255|same:new_pass', // Ensure confirm_pass matches new_pass
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
            }
            if (User::where('user_pass', md5($request->new_pass))->exists()) {
                return response()->json(['status' => 402, 'message' => 'The new password is not unique.']);
            }
            $ses_user = Session::get('user');
            // dd($ses_user[0]->ID);
            $update = User::where('remember_token', $request->remember_token)->update(['user_pass' => md5($request->new_pass)]);

           $userdata =  User::where('remember_token',  $request->remember_token)->first();
             if($userdata){
                $updatepassword = [
                    'display_name' =>  $userdata->display_name,
                    'password' => $request->new_pass
                ];
                 Mail::to($userdata->user_email)->send(new MyMail(null,null,null,$updatepassword));
             }

            return response()->json($update == true ? ['status' => 200, 'message' => 'Password successfully updated. Your password is: ' . $request->new_pass] : ['status' => 400, 'message' => 'Failed to  update password.']);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function UpdateNewPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'new_pass' => 'required|unique:wpu6_users,user_pass',
                'confirm_pass' => 'required|string|max:255|same:new_pass', // Ensure confirm_pass matches new_pass
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
            }
            if (User::where('user_pass', md5($request->new_pass))->exists()) {
                return response()->json(['status' => 402, 'message' => 'The new password is not unique.']);
            }
            $ses_user = Session::get('user');
            // dd($ses_user[0]->ID);
            $update = User::where('ID', $ses_user[0]->ID)->update(['user_pass' => md5($request->new_pass)]);

           $userdata =  User::where('ID', $ses_user[0]->ID)->first();
             if($userdata){
                $updatepassword = [
                    'display_name' =>  $userdata->display_name,
                    'password' => $request->new_pass
                ];
                 Mail::to($userdata->user_email)->send(new MyMail(null,null,null,$updatepassword));
             }

            return response()->json($update == true ? ['status' => 200, 'message' => 'Password successfully updated. Your password is: ' . $request->new_pass] : ['status' => 400, 'message' => 'Failed to  update password.']);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function UpdateTheProfile(Request $request)
    {
        try {
            $ses_user = Session::get('user');
            $rules = [
                'user_first_name' => 'required',
                'user_last_name' => 'required',
                'user_email' => 'required',
                'user_sms_no' => 'required',
                'user_wa_no' => 'required',
                'date_birth' => 'required',
                'samasar' => 'required',
                'address1' => 'required',
                'address2' => 'required',
                'city_name' => 'required',
                'state_name' => 'required',
                'country_name' => 'required',
                'pincode' => 'required',
                'qualification' => 'required',
                'workexp' => 'required',
                'special_interest' => 'required',
                'speci_skill' => 'required',
                'user_photo' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ];
            $messages = [
                'required' => 'The :attribute field is required.',
                'mimes' => 'The :attribute must be a file of type: :values.'
            ];
            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
            }
            if ($ses_user[0]->ID != $request->user_edit_id) {
                return response()->json(['status' => 400, 'message' => 'Missmatch of user']);
            }
            $user_old_data = DB::table('userprofile_dt')->where('user_id', $request->user_edit_id)->get();

            if ($request->file('user_photo')) {
                $image = $request->file('user_photo');
                $filename = time() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('user-images/' . date("Y") . '/' . date("m"));
                $image->move($destinationPath, $filename);
                $file_path = 'user-images/' . date("Y") . '/' . date("m") . '/' . $filename;
            } else {
                $file_path = $user_old_data[0]->user_image;
            }

            $user_arr = [
                'user_nicename' => $request->user_first_name,
                'display_name' => $request->user_first_name . ' ' . $request->user_last_name,
                'user_email' => $request->user_email,
                'updated_at' => date("Y-m-d H:i:s")
            ];
            $user_profile_arr = [
                'first_name' => $request->user_first_name,
                'last_name' => $request->user_last_name,
                'last_name_2' => $request->user_last_name,
                'dob' => date("Y-m-d", strtotime($request->date_birth)),
                'gender' => $request->user_gender,
                'acharyan' => $request->samasar,
                'user_email' => $request->user_email,
                'phone_number' => $request->user_sms_no,
                'phone_number_wa' => $request->user_wa_no,
                'address_1' => $request->address1,
                'address_2' => $request->address2,
                'city' => $request->city_name,
                'state' => $request->state_name,
                'postcode' => $request->pincode,
                'country' => $request->country_name,
                'qualification' => $request->qualification,
                'hobbies' => $request->special_interest,
                'skills' => $request->speci_skill,
                'work_experience' => $request->workexp,
                'user_image' => $file_path,
                'updated_at' => date("Y-m-d H:i:s")
            ];
            $update = User::where('id', $request->user_edit_id)->update($user_arr);
            $update1 = DB::table('userprofile_dt')->where('user_id', $request->user_edit_id)->update($user_profile_arr);
            return response()->json($update == true && $update1 == true ? ['status' => 200, 'message' => 'Successfully updated'] : ['status' => 400, 'message' => 'Failed to update']);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function MyProfile(Request $request){
        try{
            $ses_user = Session::get('user');
            $myprofile = DB::table('wpu6_users as w')
            ->select('w.user_login','u.first_name','u.last_name','u.dob','u.gender','u.acharyan','u.user_email'
            ,'u.landline_ph_no','u.phone_number','u.phone_number_wa','u.address_1','u.address_2','u.area','u.city'
            ,'u.state' ,'u.postcode','u.country','u.kinchit_enpani','u.payment_method')
            ->leftJoin('userprofile_dt as u', 'w.ID', '=', 'u.user_id')
            ->where('w.web_token', $request->token)
            ->get();
        
        return response()->json($myprofile->count() > 0 ? ['status' => 200, 'message' => 'My Profile Received','data' =>$myprofile ] : ['status' => 400, 'message' => 'Data Not Found']);
        }catch (Exception $e) {
            return response()->json(['status' => 500,'error' => $e->getMessage()]);
        }
    }

    public function ForgetPassword(Request $request){
 
        try{
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
            }
            $Email = $request->email;
            $user = User::where('user_email',$Email)->first();
            $token = Str::random(50);
            if(!empty($user)){
                $data = [
                    'name' =>   $user->display_name,
                    'token' =>  $token
                ];
                Mail::to($Email)->send(new MyMail($data,null,null,null));
                $user_update = User::where('user_email', $Email)->update(['remember_token' => $token]);
                
                return response()->json(['status' => 200, 'message' => 'Send Email','data' =>$data]);
            }else{
                return response()->json(['status' => 400, 'message' => 'User Does Not Exsisted']); 
            }
        }catch (Exception $e) {
            return response()->json(['status' => 500,'error' => $e->getMessage()]);
        }
    }

    public function otpVerify(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'otp' => 'required|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
            }
            $verify = User::where('otp', $request->otp)->first();

            if (!empty($verify)) {
               $otpupdated = User::where('ID', $verify->ID)->update(['otp' => $request->otp , 'verified_at' => new DateTime()]);
                return response()->json(['status' => 200, 'message' => 'Otp Verified Successfully','data' => $otpupdated]);  
            }else{
                return response()->json(['status' => 400, 'message' => 'Otp Mismatched']);  
            }
        }catch (Exception $e) {
            return response()->json(['status' => 500,'error' => $e->getMessage()]);
        }
    }

    public function logout(Request $request)
    {
   
      try{
        $user = User::where('web_token', $request->token)->first();
        if ($user) {
          
            User::where('web_token', $request->token)->update(['web_token' => null]);
            session()->flush();
            //$session = session('user');
            return response()->json(['status' => 200, 'message' => 'Successfully logged out']);
        } else {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
      }catch (Exception $e) {
        return response()->json(['status' => 500,'error' => $e->getMessage()]);
    }
    }

    public function ResendOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
            }
            $user = User::where('user_email', $request->email)->first();
    
            if ($user) {
                $otp_number = mt_rand(100000, 999999);
                $arr = [
                    'otp' => $otp_number,
                    'display_name' => $user->display_name
                ];
                 Mail::to($user->user_email)->send(new MyMail(null, $arr, null,null));

                 User::where('user_email', $request->email)
                 ->orderBy('created_at', 'desc')
                 ->limit(1)
                 ->update(['otp' =>$otp_number]);
    
                return response()->json(['status' => 200, 'message' => 'Resend Otp Successfully', 'data' => $otp_number]);
            } else {
                return response()->json(['status' => 400, 'message' => 'User not found or OTP does not match']);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 500, 'error' => $e->getMessage()]);
        }
    }

    public function UpdatedOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
            }
            $user = User::where('user_email', $request->email)->first();
    
            if ($user) {
                 User::where('user_email', $request->email)
                 ->orderBy('created_at', 'desc')
                 ->limit(1)
                 ->update(['otp' =>null]);
                return response()->json(['status' => 200, 'message' => 'Otp Updated']);
            } else {
                return response()->json(['status' => 400, 'message' => 'User not found or OTP does not match']);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 500, 'error' => $e->getMessage()]);
        }
    }
}
