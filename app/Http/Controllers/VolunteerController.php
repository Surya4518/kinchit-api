<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Validator;
use Exception;
use Session;
use Storage;
use App\Mail\MyMail;
use Illuminate\Support\Facades\Mail;
use View;
use Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use App\Common;
use App\Models\{
    User,
    UserDetails
}
;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class VolunteerController extends Controller
{
    public function GetActiveMembers(Request $request)
    {
        try {
            $ses_user = Session::get('user');
            $members = User::leftJoin('userprofile_dt', 'wpu6_users.ID', '=', 'userprofile_dt.user_id')
                ->leftJoin('city', 'userprofile_dt.city', '=', 'city.id')
                ->leftJoin('state', 'userprofile_dt.state', '=', 'state.id')
                ->select([
                    'wpu6_users.ID',
                    'wpu6_users.parent',
                    'wpu6_users.user_login',
                    'wpu6_users.user_pass',
                    'wpu6_users.user_nicename',
                    'wpu6_users.user_email',
                    'wpu6_users.user_url',
                    'wpu6_users.user_registered',
                    'wpu6_users.user_activation_key',
                    'wpu6_users.user_type',
                    'wpu6_users.user_status',
                    'wpu6_users.display_name',
                    'wpu6_users.spam',
                    'wpu6_users.deleted',
                    'userprofile_dt.id as userdetails_id',
                    'userprofile_dt.first_name',
                    'userprofile_dt.last_name',
                    'userprofile_dt.last_name_2',
                    'userprofile_dt.dob',
                    'userprofile_dt.gender',
                    'userprofile_dt.landline_ph_no',
                    'userprofile_dt.phone_number',
                    'userprofile_dt.address_1',
                    'userprofile_dt.address_2',
                    'userprofile_dt.area',
                    // 'userprofile_dt.city',
                    // 'userprofile_dt.state',
                    'userprofile_dt.postcode',
                    'state.name as state',
                    'city.name as city'
                ])
                ->where('wpu6_users.user_type', '=', 'member')
                ->where('wpu6_users.parent', 'LIKE', $ses_user[0]->user_login)
                ->where('wpu6_users.user_status', '=', '0')
                ->whereNull('wpu6_users.deleted_at')
                ->orderBy('wpu6_users.ID', 'desc')
                ->get();

            $member_array = $members->map(function ($member) {
                return $member;
            });

            return response()->json([
                'status' => 200,
                'count' => $member_array->count(),
                'data' => $member_array,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }

    }

    public function GetInActiveMembers(Request $request)
    {
        try {
            $ses_user = Session::get('user');
            $members = User::leftJoin('userprofile_dt', 'wpu6_users.ID', '=', 'userprofile_dt.user_id')
                ->leftJoin('city', 'userprofile_dt.city', '=', 'city.id')
                ->leftJoin('state', 'userprofile_dt.state', '=', 'state.id')
                ->select([
                    'wpu6_users.ID',
                    'wpu6_users.parent',
                    'wpu6_users.user_login',
                    'wpu6_users.user_pass',
                    'wpu6_users.user_nicename',
                    'wpu6_users.user_email',
                    'wpu6_users.user_url',
                    'wpu6_users.user_registered',
                    'wpu6_users.user_activation_key',
                    'wpu6_users.user_type',
                    'wpu6_users.user_status',
                    'wpu6_users.display_name',
                    'wpu6_users.spam',
                    'wpu6_users.deleted',
                    'userprofile_dt.id as userdetails_id',
                    'userprofile_dt.first_name',
                    'userprofile_dt.last_name',
                    'userprofile_dt.last_name_2',
                    'userprofile_dt.dob',
                    'userprofile_dt.gender',
                    'userprofile_dt.landline_ph_no',
                    'userprofile_dt.phone_number',
                    'userprofile_dt.address_1',
                    'userprofile_dt.address_2',
                    'userprofile_dt.area',
                    // 'userprofile_dt.city',
                    // 'userprofile_dt.state',
                    'userprofile_dt.postcode',
                    'state.name as state',
                    'city.name as city'
                ])
                ->where('wpu6_users.user_type', '=', 'member')
                ->where('wpu6_users.parent', 'LIKE', $ses_user[0]->user_login)
                ->where('wpu6_users.user_status', '=', '1')
                ->whereNull('wpu6_users.deleted_at')
                ->orderBy('wpu6_users.ID', 'desc')
                ->get();

            $member_array = $members->map(function ($member) {
                return $member;
            });

            return response()->json([
                'status' => 200,
                'count' => $member_array->count(),
                'data' => $member_array,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function RequestToBeAVolunteer(Request $request)
    {
        try {
            $ses_user = Session::get('user');
            // dd($ses_user);
           $mobilenumber = DB::table('userprofile_dt')->select('phone_number')->where('user_id', $ses_user[0]->ID)->get();
            $validator = Validator::make($request->all(), [
                'question_1' => 'required',
                'question_2' => 'required',
                'question_3' => 'required',
                'question_4' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
            }
            $arr = [
                'from_id' => $ses_user[0]->user_login,
                'request_type' => '5',
                'request_reason' => 'Apply to be a volunteer',
                'request_from' => $ses_user[0]->user_login,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ];
            $ins_app_request = DB::table('approval_requests')->insert($arr);
            if ($ins_app_request) {
                $det_array = [
                    'volunteer_ques_1' => $request->question_1,
                    'volunteer_ques_2' => $request->question_2,
                    'volunteer_ques_3' => $request->question_3,
                    'volunteer_ques_4' => $request->question_4
                ];
                $update_det = UserDetails::where('user_id', '=', $ses_user[0]->ID)->update($det_array);
            }
             $userdetails = [
                'user_login' => $ses_user[0]->user_login,
                'user_email' => $ses_user[0]->user_email,
            ];
            Mail::to($ses_user[0]->user_email)->send(new MyMail(null, null, null, null, null, null, null, null,null,null,$userdetails));
            return response()->json($ins_app_request == true ? ['status' => 200, 'message' => 'Request has been successfully sent','data'=> $mobilenumber] : ['status' => 400, 'message' => 'Failed']);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
      public function AddMember(Request $request)
    {
        try {
            $check = User::where('user_email', '=', $request->email)->orderby('ID', 'desc')->limit(1)->get();
            $rules = [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'dob' => 'required|date',
                'gender' => 'required|string|max:255',
                'acharyan' => 'required|string|max:255',
                'mobile' => 'required|string|regex:/^[0-9]{10}$/|max:10',
                'whatsapp' => 'required|string|regex:/^[0-9]{10}$/|max:10',
                'landline' => 'nullable|string|max:255',
                'address_line_1' => 'required|string|max:255',
                'address_line_2' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'state' => 'required|string|max:255',
                'country' => 'required|string|max:255',
                'native' => 'required|string|max:255',
                'special_interest' => 'required|string|max:255',
                'enpani' => 'required|string|max:255',
                'tech_savvy' => 'required|string|max:255',
                'cd' => 'required|string|max:255',
                'rm_audio' => 'required|string|max:255',
                'signed_date' => 'required|date',
                'signed_place' => 'required|string|max:255',
                'family_members' => 'required|string|max:255',
            ];

            if ($check->count() > 0 && $check[0]->verified_at == null) {
                $rules['email'] = 'required|email|max:255';
                // $rules['user_name'] = 'required|string|max:255';
            } else {
                $rules['email'] = 'required|email|max:255|unique:wpu6_users,user_email|unique:userprofile_dt,user_email';
                // $rules['user_name'] = 'required|string|max:255|unique:wpu6_users,user_login';
            }

            // Add rules for family members if they are present
            // if ($request->has('family_members')) {
            //     $familyCount = (int)$request->input('family_members');
            //     for ($i = 0; $i < $familyCount; $i++) {
            //         $rules["family_members_name.{$i}"] = 'required|string|max:255';
            //         $rules["family_members_relationship.{$i}"] = 'required|string|max:255';
            //         $rules["family_members_gender.{$i}"] = 'required|string|max:255';
            //         $rules["family_members_email.{$i}"] = 'required|email|max:255';
            //         $rules["family_members_mobile.{$i}"] = 'required|string|regex:/^[0-9]{10}$/|max:10';
            //         $rules["family_members_landline.{$i}"] = 'nullable|string|max:255';
            //         $rules["family_members_dob.{$i}"] = 'required|date';
            //         $rules["family_members_acharyan.{$i}"] = 'required|string|max:255';
            //         $rules["family_members_occupation.{$i}"] = 'required|string|max:255';
            //         $rules["family_members_city.{$i}"] = 'required|string|max:255';
            //     }
            // }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
            }

            // dd($request->all());
            //$otp_number = mt_rand(100000 , 999999);
            // $password = 'kinchit@123';

            $familyCount = (int)$request->input('family_members');

            $family_members_data = [];
            for ($i = 0; $i < $familyCount; $i++) {
                $family_members_data[] = [
                    'name' => $request['family_members_name'][$i],
                    'relation' => $request['family_members_relationship'][$i],
                    'gender' => $request['family_members_gender'][$i],
                    'email' => $request['family_members_email'][$i],
                    'mobile' => $request['family_members_mobile'][$i],
                    'landline' => $request['family_members_landline'][$i],
                    'dob' => date("Y-m-d", strtotime($request['family_members_dob'][$i])),
                    'acharyan' => $request['family_members_acharyan'][$i],
                    'ocuupation' => $request['family_members_occupation'][$i],
                    'city' => $request['family_members_city'][$i],
                ]; 
            }
            $family_members_json = json_encode($family_members_data);
            // dd($family_members_json);
            
            $password = Str::random(12);
            $ses_user = Session::get('user');
            // $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            $user = User::where('ID', $ses_user[0]->ID)->first();
            $member = [
                'parent' => $user->user_login,
                'user_login' =>  $user->user_login,
                'user_pass' => md5($password),
                'user_nicename' => strtolower($request->user_name),
                'user_email' => $request->email,
                'user_registered' => date("Y-m-d H:i:s"),
                'display_name' => $request->first_name . ' ' . $request->last_name,
                'user_type' => 'member',
                //'otp' => $otp_number
            ];
            // Mail::to($request->email)->send(new MyMail(null, null, $member, null, null, $password));
            $insert = User::create($member);
            $addmember = [
                'user_id' => $insert->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'user_email' => $request->email,
                'dob' => date("Y-m-d", strtotime($request->dob)),
                'gender' => $request->gender,
                'acharyan' => $request->acharyan,
                'phone_number' => $request->mobile,
                'phone_number_wa' => $request->whatsapp,
                'landline_ph_no' => $request->landline,
                'address_1' => $request->address_line_1,
                'address_2' => $request->address_line_2,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'native' => $request->native,
                'hobbies' => $request->special_interest,
                'kinchit_enpani' => $request->enpani,
                'tech_savvy' => $request->tech_savvy,
                'listen_cd' => $request->cd,
                'rm_method' => $request->rm_audio,
                'form_signed_date' => $request->signed_date,
                'form_signed_place' => $request->signed_place,
                'family_members' => $request->family_members,
                'family_member_details' => $family_members_json,
                'created_at' => date("Y-m-d H:i:s")
            ];
            //   Mail::to($request->email)->send(new MyMail(null, null, null, null, null, null, null, null,null,null,$addmember,null,null,null));
            Mail::to($request->email)->send(new MyMail(
                $data ?? null, 
                $arr ?? null, 
                $member ?? null, 
                $contactus ?? null, 
                $updatepassword ?? null, 
                $password ?? null, 
                $conformail ?? null, 
                $gnanakaithaa ?? null, 
                $assigncourse ?? null, 
                $matri ?? null, 
                $userdetails ?? null, 
                $addmember ?? null, 
                $depositdetails ?? null, 
                $changevoluteer ?? null, 
                $paymentoption ?? null
            ));
            $insert1 = DB::table('userprofile_dt')->insert($addmember);
            $approve_request_entry = DB::table('approval_requests')->insert([
                'from_id' => $user->user_login,
                'to_id' => $request->user_name,
                'request_type' => '6',
                'request_reason' => 'Become a member',
                'request_from' => $user->user_login,
                'created_at' => date('Y-m-d H:i:s')
                ]);
            return response()->json($insert == true && $insert1 == true && $approve_request_entry == true ? ['status' => 200, 'message' => 'Request has sent..!'] : ['status' => 400, 'message' => 'Failed to create']);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    public function RequestChallan(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'volunteer_code' => 'required',
                'volunteer_email' => 'required',
                'challan_in_hand' => 'required'
            ]);
    
            if ($validator->fails()) {
                return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
            }
    
            $ses_user = Session::get('user');
            // $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            $user = User::where('ID', $ses_user[0]->ID)->where('user_type', 'volunteer')->first(); 
    
            if (!$user) {
                return response()->json(['status' => 404, 'message' => 'User not found.']);
            }
    
            $volunteerCode = $request->input('volunteer_code');
            $volunteerEmail = $request->input('volunteer_email');
    
            $matchingRecordsExist = DB::table('wpu6_users')
                ->where('wpu6_users.user_login', $volunteerCode)
                ->where('wpu6_users.user_email', $volunteerEmail)
                ->exists();
    
              
            if ($matchingRecordsExist) {
                DB::table('challan_request')->insert([
                    'request_from' => $user->ID,
                    'volunteer_code' => $volunteerCode,
                    'volunteer_email' => $volunteerEmail,
                    'challan_in_hand' => $request->input('challan_in_hand'),

                ]);
    
                return response()->json([
                    'status' => 200,
                    'message' => 'Form submitted successfully.',
                ]);
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => 'User login and volunteer code, or user email and volunteer email, do not match.',
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
     public function MemberUpdate(Request $request){

        try{
            $user_arr = [
                'user_login' => $request->user_login,
                'user_nicename' => $request->first_name,
                'user_email' => $request->user_email,
                'updated_at' => date("Y-m-d H:i:s")
            ];
            $user_profile_arr = [
                'first_name' => $request->first_name,
                'phone_number' => $request->phone_number,
                'user_email' => $request->user_email,
                'address_1'=>$request->address1,
                'address_2'=>$request->address2,
                'area'=>$request->area,
                'city'=>$request->city,
                'state'=>$request->state,
                'postcode'=>$request->pincode,
                'updated_at' => date("Y-m-d H:i:s")
            ];
            $update = User::where('id', $request->user_id)->update($user_arr);
            $update1 = DB::table('userprofile_dt')->where('user_id', $request->user_id)->update($user_profile_arr);
            return response()->json($update == true && $update1 == true ? ['status' => 200, 'message' => 'Successfully updated'] : ['status' => 400, 'message' => 'Failed to update']);
        }catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
       
    }
       

}
