<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Validator;
use Exception;
use Session;
use Storage;
use Carbon\Carbon;
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
use App\Mail\MyMail;
use Illuminate\Support\Facades\Mail;

class MemberController extends Controller
{
    public function GetVolunteerProfile(Request $request)
    {
        try {
            $ses_user = Session::get('user');
            $member = User::where('user_type', '=', 'member')
                ->where('ID', 'LIKE', $ses_user[0]->ID)
                ->where('user_status', '=', '0')
                ->whereNull('deleted_at')
                ->orderBy('ID', 'desc')
                ->get();

            $volunteers = User::leftJoin('userprofile_dt', 'wpu6_users.ID', '=', 'userprofile_dt.user_id')
                ->leftJoin('state', 'state.id', '=', 'userprofile_dt.state')
                ->leftJoin('city', 'city.id', '=', 'userprofile_dt.city')
                ->select('wpu6_users.*','userprofile_dt.*','state.name as state_name','city.name as city_name')
                ->where('wpu6_users.user_type', '=', 'volunteer')
                ->where('user_login', 'LIKE', $member[0]->parent)
                ->where('wpu6_users.user_status', '=', '0')
                ->whereNull('wpu6_users.deleted_at')
                ->orderBy('wpu6_users.ID', 'desc')
                ->get();

            $volunteer_array = $volunteers->map(function ($volunteer) {
                return $volunteer;
            });

            return response()->json([
                'status' => 200,
                'count' => $volunteer_array->count(),
                'data' => $volunteer_array,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }

    }

    public function GetVolunteersList(Request $request)
      {
          try {
              $ses_user = Session::get('user');
              $array = $request->all();
              if($request->city){
                  $volunteers = User::leftJoin('userprofile_dt', 'wpu6_users.ID', '=', 'userprofile_dt.user_id')
                          ->leftjoin('city', 'userprofile_dt.city', '=', 'city.id')
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
                              'userprofile_dt.city',
                              'userprofile_dt.state',
                              'userprofile_dt.postcode',
                              'city.name'
                          ])
                          ->where('wpu6_users.user_type', '=', 'volunteer')
                          ->where('parent', '=', '0')
                          ->where('wpu6_users.user_status', '=', '0')
                          ->where('userprofile_dt.city', '=', $request->city)
                          ->whereNull('wpu6_users.deleted_at')
                          ->orderBy('wpu6_users.ID', 'ASC')
                          ->get();
                          return response()->json(['status' => 200, 'message' => 'Success', 'count' => $volunteers->count(), 'data' => $volunteers]);
              }
              if (array_key_exists('search', $array)) {
                  $search = $array['search'];
      
                  if ($search == null) {
                      $volunteers = User::leftJoin('userprofile_dt', 'wpu6_users.ID', '=', 'userprofile_dt.user_id')
                          ->leftjoin('city', 'userprofile_dt.city', '=', 'city.id')
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
                              'userprofile_dt.city',
                              'userprofile_dt.state',
                              'userprofile_dt.postcode',
                              'city.name'
                          ])
                          ->where('wpu6_users.user_type', '=', 'volunteer')
                          ->where('parent', '=', '0')
                          ->where('wpu6_users.user_status', '=', '0')
                          ->whereNull('wpu6_users.deleted_at')
                          ->orderBy('wpu6_users.ID', 'ASC')
                          ->get();
                  } else {
                      $volunteers = User::leftJoin('userprofile_dt', 'wpu6_users.ID', '=', 'userprofile_dt.user_id')
                          ->leftjoin('city', 'userprofile_dt.city', '=', 'city.id')
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
                              'userprofile_dt.city',
                              'userprofile_dt.state',
                              'userprofile_dt.postcode',
                              'city.name'
                          ])
                          ->where('wpu6_users.user_type', '=', 'volunteer')
                          ->where('parent', '=', '0')
                          ->where('wpu6_users.user_status', '=', '0')
                          ->where(function ($query) use ($search) {
                              $query->where('wpu6_users.user_nicename', 'like', '%' . $search . '%')
                                  ->orWhere('wpu6_users.user_login', 'like', '%' . $search . '%')
                                  ->orWhere('userprofile_dt.first_name', 'like', '%' . $search . '%')
                                  ->orWhere('userprofile_dt.city', '=', $search)
                                  ->orWhere('city.name', 'like', '%' . $search . '%');
                          })
                          ->whereNull('wpu6_users.deleted_at')
                          ->orderBy('wpu6_users.ID', 'ASC')
                          ->get();
                  }
                  return response()->json(['status' => 200, 'message' => 'Success', 'count' => $volunteers->count(), 'data' => $volunteers]);
              } else {
                  $volunteers = User::leftJoin('userprofile_dt', 'wpu6_users.ID', '=', 'userprofile_dt.user_id')
                      ->leftjoin('city', 'userprofile_dt.city', '=', 'city.id')
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
                          'userprofile_dt.city',
                          'userprofile_dt.state',
                          'userprofile_dt.postcode',
                          'city.name'
                      ])
                      ->where('wpu6_users.user_type', '=', 'volunteer')
                      ->where('parent', '=', '0')
                      ->where('wpu6_users.user_status', '=', '0')
                      ->whereNull('wpu6_users.deleted_at')
                      ->orderBy('wpu6_users.ID', 'ASC')
                      ->get();
                  return response()->json(['status' => 200, 'message' => 'Success', 'count' => $volunteers->count(), 'data' => $volunteers]);
              }
          } catch (Exception $e) {
              return response()->json([
                  'status' => 500,
                  'error' => $e->getMessage(),
              ]);
          }
      }

    public function RequestChangeTheVolunteer(Request $request)
    {
        try {
            $ses_user = Session::get('user');
            $validator = Validator::make($request->all(), [
                'to_id' => 'required',
                'address_1' => 'required',
                'address_2' => 'required',
                'state' => 'required',
                'city' => 'required',
                'postcode' => 'required',
                'country' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
            }
            $arr = [
                'from_id' => $ses_user[0]->ID,
                'to_id' => $request->to_id,
                'request_type' => '3',
                'request_reason' => 'Change the Volunteer',
                'request_from' => $ses_user[0]->ID,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ];
            $ins_app_request = DB::table('approval_requests')->insert($arr);
            if($ins_app_request){
                $det_array = [
                    'address_1' => $request->address_1,
                    'address_2' => $request->address_2,
                    'city' => $request->city,
                    'state' => $request->state,
                    'postcode' => $request->postcode,
                    'country' => $request->country,
                ];
                 $userdetails = DB::table('userprofile_dt')->where('user_id',$ses_user[0]->ID)->first();
                $changevoluteer= [
                    'first_name'=> $userdetails->first_name,
                    'last_name'=> $userdetails->last_name,
                    'user_email'=> $userdetails->user_email,
                    'phone_number'=> $userdetails->phone_number
                ];
                Mail::to($userdetails->user_email)->send(new MyMail(null, null, null, null, null, null, null, null,null,null,null,null,null,$changevoluteer));
                $update_det = UserDetails::where('user_id','=',$ses_user[0]->ID)->update($det_array);
            }
            return response()->json($ins_app_request == true ? ['status' => 200, 'message' => 'Request has been successfully sent','userdetails'=> $changevoluteer] : ['status' => 400, 'message' => 'Failed']);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    public function RequestToBeMember(Request $request)
    {
        try {
            $ses_user = Session::get('user');
            $arr = [
                'from_id' => $ses_user[0]->user_login,
                'request_type' => '6',
                'request_reason' => 'Apply to be a member',
                'request_from' => $ses_user[0]->user_login,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ];
            $ins_app_request = DB::table('approval_requests')->insert($arr);
            return response()->json($ins_app_request == true ? ['status' => 200, 'message' => 'Request has been successfully sent'] : ['status' => 400, 'message' => 'Failed']);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    public function MemberRenewal(Request $request)
    {
        try {
            // dd($request->all());
            $ses_user = Session::get('user');
            $currentDate = Carbon::now();
            $oneYearLater = $currentDate->addYear();
            $arr = [
                'valid_to' => date("Y-m-d H:i:s", strtotime($oneYearLater)),
                'updated_at' => date("Y-m-d H:i:s"),
            ];
            // dd($arr);
            $update = DB::table('wpu6_users')->where('ID',$ses_user[0]->ID)->update($arr);
            return response()->json($update == true ? ['status' => 200, 'message' => 'Member Renewed success'] : ['status' => 400, 'message' => 'Failed']);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }

}
