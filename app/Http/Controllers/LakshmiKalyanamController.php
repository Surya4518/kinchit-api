<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;
use Session;
use Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\MyMail;
use View;
use Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use App\Common;
use App\Models\{
    ManageMatimonyProfiles,
    SuccessStory,
    User
};
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class LakshmiKalyanamController extends Controller
{
   public function CreateAccount(Request $request){
       try{
           $ses_user = Session::get('user');
       $rules = [
            'profilecreatedby' => 'required|string|max:255',
        ];
        if($request->profilecreatedby == 'Self'){
            $rules = [
            'marital_status' => 'required'
        ];
        }else{
            $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'gender' => 'required',
            'phone_number' => 'required|string|regex:/^[0-9]{10}$/',
            'date_of_birth' => 'required|date',
            'marital_status' => 'required'
        ];
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
        }
       $user_details = DB::table('userprofile_dt')->where('user_id', $ses_user[0]->ID)->get();
    //   dd($user_details);
       $matri = [];
       if($request->profilecreatedby == 'Self'){
           $dateOfBirth = Carbon::createFromFormat('Y-m-d', date('Y-m-d', strtotime($user_details[0]->dob)));
           $age = $dateOfBirth->age;
           $matri = [
              'user_id' => $ses_user[0]->ID,
              'Prefix' => "K",
              'ConfirmEmail' => $user_details[0]->user_email,
              'Name' => $user_details[0]->first_name,
              'Profilecreatedby' => $request->profilecreatedby,
              'Gender' => $user_details[0]->gender,
              'DOB' => date("Y-m-d", strtotime($user_details[0]->dob)),
              'Age' => $age,
              'Maritalstatus' => $request->marital_status,
              'Phone' => '91-'.$user_details[0]->phone_number,
              'Mobile' => $user_details[0]->phone_number_wa,
              'mobilecode' => '91',
              'Address' => $user_details[0]->address_1.','.$user_details[0]->address_2.','.$user_details[0]->area,
              'country' => $user_details[0]->country,
              'state' => $user_details[0]->state,
              'city' => $user_details[0]->city,
              'Postal' => $user_details[0]->postcode,
              'Status' => 'Active',
              'Subcaste' => $user_details[0]->acharyan,
              'created_at' => date("Y-m-d")
              ];
       }else{
           $dateOfBirth = Carbon::createFromFormat('Y-m-d', date('Y-m-d', strtotime($request->date_of_birth)));
           $age = $dateOfBirth->age;
           $matri = [
              'user_id' => $ses_user[0]->ID,
              'Prefix' => "K",
              'ConfirmEmail' => $request->email,
              'Name' => $request->name,
              'Profilecreatedby' => $request->profilecreatedby,
              'Gender' => $request->gender,
              'DOB' => date("Y-m-d", strtotime($request->date_of_birth)),
              'Age' => $age,
              'Maritalstatus' => $request->marital_status,
              'Mobile' => $request->phone_number,
              'mobilecode' => '91',
              'Status' => 'Active',
              'created_at' => date("Y-m-d")
              ];
       }
          Mail::to($request->email)->send(new MyMail(null, null, null, null, null, null, null, null,null,$matri));
       $insert = DB::table('m4winreg')->insertGetId( $matri);
       if($insert){
           $update = DB::table('m4winreg')->where('ID',$insert)->update(['MatriID' => 'K'.$insert]);
           return response()->json($update ? ['status' => 200, 'message' => 'Account created'] : ['status' => 400, 'message' => 'Failed update user..!']);
       }else{
           return response()->json(['status' => 400, 'message' => 'Failed create account..!']);
       }
       }catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
   }
   
   public function GetProfileData(Request $request)
    {
        try {
            $ses_user = Session::get('user');
             $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            $profile = DB::table('m4winreg')
                ->select('m4winreg.*','wpu6_users.display_name', 'religion.name AS religion_name', 'caste.name AS caste_name', 'state.name AS state_name', 'city.name AS city_name', 'education.name AS EducationName', 'occupation.name AS OccupationName', 'country.name AS CountryName', 'pcaste.name AS PecastName', 'pereligion.name AS PereligionName')
                ->leftJoin('religion', 'religion.id', '=', 'm4winreg.Religion')
                ->leftJoin('religion as pereligion', 'pereligion.id', '=', 'm4winreg.PE_Religion')
                ->leftJoin('caste', 'caste.id', '=', 'm4winreg.Caste')
                ->leftJoin('caste as pcaste', 'pcaste.id', '=', 'm4winreg.PE_Caste')
                ->leftJoin('state', 'state.id', '=', 'm4winreg.State')
                ->leftJoin('city', 'city.id', '=', 'm4winreg.City')
                ->leftJoin('education', 'education.id', '=', 'm4winreg.Education')
                ->leftJoin('occupation', 'occupation.id', '=', 'm4winreg.Occupation')
                ->leftJoin('country', 'country.id', '=', 'm4winreg.PE_Countrylivingin')
                 ->leftJoin('wpu6_users', 'wpu6_users.ID', '=', 'm4winreg.user_id')
                ->where('user_id', $user->ID)->get();
                $caste = DB::table('caste')->where('status','=','Active')->get();
                $religion = DB::table('religion')->where('status','=','Active')->get();
            return response()->json([
                'status' => 200,
                'count' => $profile->count(),
                'data' => $profile,
                'caste' =>  $caste,
                'religion' =>  $religion,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    // public function GetMatchedProfiles(Request $request)
    // {
    //     try {
    //         $user = DB::table('wpu6_users')->where('web_token', $request->token)->first();
    //         $profile = DB::table('m4winreg')->where('user_id', $user->ID)->first();
    //         //$profile = DB::table('m4winreg')->where('MatriID', $request->for_user)->first();

    //         $religionsql = "Religion = '{$profile->PE_Religion}'";
    //         $castesql = "Caste = '{$profile->PE_Caste}'";
    //         $gendersql = ($profile->Gender == "Male") ? "Gender = 'Female'" : "Gender = 'Male'";
    //         $agesql = "AGE BETWEEN '{$profile->PE_FromAge}' AND '{$profile->PE_ToAge}'";

    //         $lookingArray = explode(",", $profile->Looking);
    //         $count = count($lookingArray);
    //         if ($count == 0) {
    //             //$maritalsql = "";
    //             $maritalsql = "Maritalstatus = 'Unmarried'" || "Maritalstatus = 'Divorced'" || "Maritalstatus = ''";
    //         } elseif ($count == 1) {
    //             if (empty($lookingArray[0])) {
    //                 $maritalsql = "Maritalstatus = 'Unmarried'";
    //             } else {
    //                 $maritalsql = "Maritalstatus = '{$lookingArray[0]}'";
    //             }
    //         } else {
    //             $marital = implode("','", $lookingArray);
    //             $maritalsql = "Maritalstatus IN ('{$marital}')";
    //         }

    //         $totalCount = DB::table('m4winreg')
    //             ->whereRaw($religionsql)
    //             ->whereRaw($castesql)
    //             ->whereRaw($gendersql)
    //             ->whereRaw($agesql)
    //             ->whereRaw($maritalsql)
    //             ->count();
    //         $searchresults = DB::table('m4winreg')
    //             ->select('*', DB::raw("DATE_FORMAT(Lastlogin, '%d-%M-%Y') as lastlogindate"))
    //             ->whereRaw($religionsql)
    //             ->whereRaw($castesql)
    //             ->whereRaw($gendersql)
    //             ->whereRaw($agesql)
    //             ->whereRaw($maritalsql)
    //             ->orderBy('id', 'desc')
    //             ->get();
    //         $profile_array = $searchresults->map(function ($searchresult) {
    //             return $searchresult;
    //         });
    //         return response()->json([
    //             'status' => 200,
    //             'count' => $profile_array->count(),
    //             'data' => $profile_array,
    //         ]);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'status' => 500,
    //             'error' => $e->getMessage(),
    //         ]);
    //     }
    // }
    
   public function GetMatchedProfiles(Request $request)
    {
        try {
            $ses_user = Session::get('user');
            $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            $profile = DB::table('m4winreg')->where('user_id', $user->ID)->first();
            //$profile = DB::table('m4winreg')->where('MatriID', $request->for_user)->first();

            $religionsql = "m4winreg.Religion = '{$profile->PE_Religion}'";
            $castesql = "Caste = '{$profile->PE_Caste}'";
            $gendersql = ($profile->Gender == "Male") ? "Gender = 'Female'" : "Gender = 'Male'";
            $agesql = "AGE BETWEEN '{$profile->PE_FromAge}' AND '{$profile->PE_ToAge}'";

            $lookingArray = explode(",", $profile->Looking);
            $count = count($lookingArray);
            if ($count == 0) {
                //$maritalsql = "";
                $maritalsql = "Maritalstatus = 'Unmarried'" || "Maritalstatus = 'Divorced'" || "Maritalstatus = ''";
            } elseif ($count == 1) {
                if (empty($lookingArray[0])) {
                    $maritalsql = "Maritalstatus = 'Unmarried'";
                } else {
                    $maritalsql = "Maritalstatus = '{$lookingArray[0]}'";
                }
            } else {
                $marital = implode("','", $lookingArray);
                $maritalsql = "Maritalstatus IN ('{$marital}')";
            }

            $totalCount = DB::table('m4winreg')
                ->whereRaw($religionsql)
                ->whereRaw($castesql)
                ->whereRaw($gendersql)
                ->whereRaw($agesql)
                ->whereRaw($maritalsql)
                ->count();
            $searchresults = DB::table('m4winreg')
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(Lastlogin, '%d-%M-%Y') as lastlogindate"),
                    DB::raw('religion.name as ReligionName'),
                    DB::raw('caste.name as CasteName'),
                    DB::raw('education.name as EducationName'),
                    DB::raw('city.name as CityName'),
                    DB::raw('occupation.name as OccupationName'),
                    DB::raw('pcaste.name as PecastName'),
                    DB::raw('pereligion.name as PereligionName')
                )
                ->whereRaw($religionsql)
                ->whereRaw($castesql)
                ->whereRaw($gendersql)
                ->whereRaw($agesql)
                ->whereRaw($maritalsql)
                ->leftJoin('religion', 'm4winreg.Religion', '=', 'religion.id')
                ->leftJoin('caste', 'm4winreg.Caste', '=', 'caste.id')
                ->leftJoin('education', 'm4winreg.Education', '=', 'education.id')
                ->leftJoin('occupation', 'm4winreg.Occupation', '=', 'occupation.id')
                ->leftJoin('city', 'city.id', '=', 'm4winreg.City')
                ->leftJoin('caste as pcaste', 'pcaste.id', '=', 'm4winreg.PE_Caste')
                ->leftJoin('religion as pereligion', 'pereligion.id', '=', 'm4winreg.PE_Religion')
                ->orderBy('m4winreg.id', 'desc')
                ->get();
            $profile_array = $searchresults->map(function ($searchresult) {
                return $searchresult;
            });
            return response()->json([
                'status' => 200,
                'count' => $profile_array->count(),
                'data' => $profile_array,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function GetShortlistedProfiles(Request $request)
    {
        try {
            $ses_user = Session::get('user');
            $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            $profile = DB::table('m4winreg')->where('user_id', $user->ID)->first();
            //$profile = DB::table('m4winreg')->where('MatriID', $request->for_user)->first();
            $shortlists = DB::table('shortlist_profile')->where('log_id', '=', $profile->MatriID)->get();
            $shortlist_array = $shortlists->map(function ($shortlist) {
                return $shortlist;
            });
            return response()->json([
                'status' => 200,
                'count' => $shortlist_array->count(),
                'data' => $shortlist_array,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function GetSuccessStories(Request $request)
    {
        try {
            $success_stories = SuccessStory::whereNull('deleted_at')
                ->get();
            $success_story_array = $success_stories->map(function ($success_story) {
                return $success_story;
            });
            return response()->json([
                'status' => 200,
                'count' => $success_story_array->count(),
                'data' => $success_story_array,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // public function GetAddressViewedProfiles(Request $request)
    // {
    //     try {
    //         $user = DB::table('wpu6_users')->where('web_token', $request->token)->first();
    //         $profile = DB::table('m4winreg')->where('user_id', $user->ID)->first();
    //         //$profile = DB::table('m4winreg')->where('MatriID', $request->for_user)->first();
    //         $add_viewed_profiles = DB::table('viewedaddress')->where('who1', '=', $profile->MatriID)->get();
    //         $add_viewed_array = $add_viewed_profiles->map(function ($add_viewed) {
    //             return $add_viewed;
    //         });
    //         return response()->json([
    //             'status' => 200,
    //             'count' => $add_viewed_array->count(),
    //             'data' => $add_viewed_array,
    //         ]);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'status' => 500,
    //             'error' => $e->getMessage(),
    //         ]);
    //     }
    // }
    
     public function GetAddressViewedProfiles(Request $request)
{
    try {
        $ses_user = Session::get('user');
            $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
        $profile = DB::table('m4winreg')->where('user_id', $user->ID)->first();
        //$profile = DB::table('m4winreg')->where('MatriID', $request->for_user)->first();
        $add_viewed_profiles = DB::table('viewedaddress')->where('who1', '=', $profile->MatriID)->get();

        $profile1 = $add_viewed_profiles->map(function ($add_viewed) {
            return DB::table('m4winreg')
            ->select('m4winreg.*', 'religion.name AS ReligionName', 'caste.name AS CasteName','city.name AS CityName')
            ->leftJoin('religion', 'religion.id', '=', 'm4winreg.Religion')
            ->leftJoin('caste', 'caste.id', '=', 'm4winreg.Caste')
            ->leftJoin('city', 'city.id', '=', 'm4winreg.City')
            ->where('MatriID', $add_viewed->whom1)->get();
        });

        return response()->json([
            'status' => 200,
            'count' => $add_viewed_profiles->count(),
            'data' => $add_viewed_profiles,
            'profiles' => $profile1,
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 500,
            'error' => $e->getMessage(),
        ]);
    }
}

    public function MyProfile(Request $request)
    {
        try {
            $ses_user = Session::get('user');
            $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            $profile = DB::table('m4winreg')
                ->select('m4winreg.*', 'religion.name AS ReligionName', 'caste.name AS CasteName', 'education.name AS EducationName', 'occupation.name AS OccupationName', 'country.name AS CountryName', 'pcaste.name AS PecastName', 'pereligion.name AS PereligionName', 'peducation.name AS peducation','city.name AS CityName')
                ->where('MatriID', $request->matriid)
                ->leftJoin('religion', 'religion.id', '=', 'm4winreg.Religion')
                ->leftJoin('caste', 'caste.id', '=', 'm4winreg.Caste')
                ->leftJoin('state', 'state.id', '=', 'm4winreg.State')
                ->leftJoin('education', 'education.id', '=', 'm4winreg.Education')
                ->leftJoin('occupation', 'occupation.id', '=', 'm4winreg.Occupation')
                ->leftJoin('country', 'country.id', '=', 'm4winreg.PE_Countrylivingin')
                ->leftJoin('caste as pcaste', 'pcaste.id', '=', 'm4winreg.PE_Caste')
                ->leftJoin('religion as pereligion', 'pereligion.id', '=', 'm4winreg.PE_Religion')
                ->leftJoin('education as peducation', 'peducation.id', '=', 'm4winreg.Education')
                ->leftJoin('city', 'city.id', '=', 'm4winreg.City')
                ->first();
            return response()->json(['status' => 200, 'data' => $profile]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }


     public function SendMessage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'to_id' => 'required',
                'msg' => 'required|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
            }
            $ses_user = Session::get('user');
            $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            $profile = DB::table('m4winreg')->where('user_id', $user->ID)->first();
            $matriid = DB::table('m4winreg')->where('MatriID',$request->to_id)->first();
            $conformail = [
                'FromID' => $profile->MatriID,
                'ToId' => $matriid->MatriID,
                'Msg' => $request->msg
            ];
            Mail::to($matriid->ConfirmEmail)->send(new MyMail(null,null,null,null,null,null,$conformail));
            $insert = DB::table('sentmessage')->insert($conformail);

            return response()->json(['status' => 200, 'message' => 'Send Message Successfully', 'data' => $insert]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function MessagesList(Request $request)
    {
        try {
            $ses_user = Session::get('user');
            $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            $profile = DB::table('m4winreg')->where('user_id', $user->ID)->first();

            $sentMessages =  DB::table('sentmessage')->where('FromID', $profile->MatriID)->get();

            $receivedMessages =  DB::table('sentmessage')->where('ToId', $profile->MatriID)->get();

            $data = [
                'sentMessages' => $sentMessages,
                'receivedMessages' => $receivedMessages,
            ];

            return response()->json(['status' => 200, 'message' => 'Message checked successfully.', 'data' => $data]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function BasicInformationModify(Request $request)
    {
        try {
            if ($request->form_key == '1') {
                $rules = [
                    //EDIT BASIC INFORMATION//

                    'name' => 'required|string|max:255',
                    'gender' => 'required|string|max:255',
                    'mother_tongue' => 'required|string|max:255',
                    'marital_status' => 'required|string|max:255',
                    'community' => 'required|string|max:255',
                    'sub_section' => 'required|string|max:255',
                    //'acharayan' => '',
                    //'special_category' => '',
                    'date_of_birth' => 'required|string|max:255',
                    //'profile_created_by' => '',
                    //'reference_by' => '',
                    'email' => 'required|unique:m4winreg,ConfirmEmail,' . $request->id,
                    //------------------------------------------------------------------------------------------------//

                    //EDIT SOCIO RELIGIOUS ATTRIBUTES//

                    // 'gothram' => '',
                    // 'manglik'=> '',
                    // 'moonsign'=> '',
                    // 'star_nakshatra'=> '',
                    // 'padham'=> '',
                    // 'horoscope_match'=> '',
                    // 'place_of_birth'=> '',
                    // 'time_of_birth'=> '',
                    // 'dasa_type'=> '',
                    // 'thosham'=> '',
                    // 'birth_balance_dasa'=> '',
                    //------------------------------------------------------------------------------------------------//



                    //EDIT PHYSICAL ATTRIBUTES//
                    // 'height' => '',
                    // 'weight' => '',
                    // 'blood_group' => '',
                    // 'complexion' => '',
                    // 'body_type' => '',
                    // 'special_cases' => '',
                    // 'diet' => '',
                    // 'smoke' => '',
                    // 'drink' => '',
                    //------------------------------------------------------------------------------------------------//





                    //EDIT FAMILY DETAILS//
                    // 'family_details' => '',
                    // 'family_values' => '',
                    // 'family_type' => '',
                    // 'family_status' => '',
                    // 'family_origin' => '',
                    //------------------------------------------------------------------------------------------------//


                    //EDIT HOBBIES AND INTEREST//
                    //'HOBBIES' => '',
                ];
            } elseif ($request->form_key == '2') {
                $rules = [];
            }elseif ($request->form_key == '3') {
                $rules = [
                    //EDIT EDUCATION AND OCCUPATION//

                    'education' => 'required|string|max:255',
                    'occupation' => 'required|string|max:255',
                    'occupation_details' => 'required|string|max:255',
                    'annual_income' => 'required|string|max:255',
                    //'employed_in' => '',
                    //'education_details' => '',

                    //------------------------------------------------------------------------------------------------//
                ];
            }elseif ($request->form_key == '4') {
                $rules = [];
            }elseif ($request->form_key == '5') {
                $rules = [
                    //EDIT CONTACT DETAILS//
                    'address' => 'required|string|max:255',
                    'whatsapp_number' => 'required|string|max:255|regex:/^(\+)?[0-9]{10}$/',
                    'mobile' => 'required|string|max:255|regex:/^(\+)?[0-9]{10}$/',
                    // 'country' => '',
                    // 'state' => '',
                    // 'city' => '',
                    // 'postal' => '',
                    //'residence_status' => '',
                    //------------------------------------------------------------------------------------------------//
                ];
            } elseif ($request->form_key == '6') {
                $rules = [
                    //EDIT PROFILE//
                    'profile_description' => 'required|string|max:255',
                    //------------------------------------------------------------------------------------------------// 
                ];
            } elseif ($request->form_key == '7') {
                $rules = [];
            }elseif ($request->form_key == '8') {
                $rules = [
                    //EDIT PARTNER PREFERENCE//
                   'looking_for' => 'required|array',
                    'looking_for.*' => 'string|max:255',
                    'from_age' => 'required|integer|min:18',
                    'to_age' => 'required|integer|min:19',
                    'pe_religion' => 'required|string|max:255',
                    'pe_caste' => 'required|string|max:255',
                    //------------------------------------------------------------------------------------------------//

                ];
            }elseif ($request->form_key == '9') {
                $rules = [
                    'hobbies' => 'nullable|array',
                    'hobbies.*' => 'string|max:255',
                    'interests' => 'nullable|array',
                    'interests.*' => 'string|max:255',
                ];
            }
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
            }

            $ses_user = Session::get('user');
            $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            $profile = DB::table('m4winreg')->where('user_id', $user->ID)->first();

            if($request->form_key == '1'){
                $basicprofile = [
                    'Name' => $request->name,
                    'Gender' => $request->gender,
                    'Language' => $request->mother_tongue,
                    'Maritalstatus' => $request->marital_status,
                    'Religion' => $request->community,
                    'Caste' => $request->sub_section,
                    'Subcaste' => $request->acharayan,
                    'specialcategory' => $request->special_category,
                    'DOB' => $request->date_of_birth,
                    'Profilecreatedby' => $request->profile_created_by,
                    'Referenceby' => $request->reference_by,
                    'ConfirmEmail' => $request->email,  
                ];
                $update = DB::table('m4winreg')->where('user_id', $profile->user_id)->update($basicprofile);
    
                return response()->json(['status' => 200, 'message' => 'Basic Information Updated Successfully Successfully', 'data' => $update]);  
            }if($request->form_key == '2'){
                $socioreligious = [
                    'Gothram' => $request->gothram,
                    'Manglik' => $request->manglik,
                    'Moonsign' => $request->moonsign,
                    'Star' => $request->star_nakshatra,
                    'padham' => $request->padham,
                    'Horosmatch' => $request->horoscope_match,
                    'POB' => $request->place_of_birth,
                    'TOB' => $request->time_of_birth,
                    'dasatype' => $request->dasa_type,
                    'thosam' => $request->thosham,
                    'birth_balance_dasa' => $request->birth_balance_dasa, 
                ];
                $update = DB::table('m4winreg')->where('user_id', $profile->user_id)->update($socioreligious);
    
                return response()->json(['status' => 200, 'message' => 'Socio Religious Updated Successfully Successfully', 'data' => $update]);  
            }if($request->form_key == '3'){
                $Education = [
                    'Education' => $request->education,
                    'EducationDetails' => $request->education_details,
                    'Occupation' => $request->occupation,
                    'occupation_details' => $request->occupation_details,
                    'Annualincome' => $request->annual_income,
                    'Employedin' => $request->employed_in,
                ];
                $update = DB::table('m4winreg')->where('user_id', $profile->user_id)->update($Education);
    
                return response()->json(['status' => 200, 'message' => 'Education and Occupation Updated Successfully', 'data' => $update]);  
            }if($request->form_key == '4'){
                $PHYSICALATTRIBUTES = [
                    'Height' => $request->height,
                    'Weight' => $request->weight,
                    'BloodGroup' => $request->blood_group,
                    'Complexion' => $request->complexion,
                    'Bodytype' => $request->body_type,
                    'spe_cases' => $request->special_cases,
                    'Diet' => $request->diet,
                    'Smoke' => $request->smoke,
                    'Drink' => $request->drink,
                ];
                $update = DB::table('m4winreg')->where('user_id', $profile->user_id)->update($PHYSICALATTRIBUTES);
    
                return response()->json(['status' => 200, 'message' => 'Physical Attributes Updated Successfully', 'data' => $update]);  
            }if($request->form_key == '5'){
                $CONTACTDETAILS = [
                    'Address' => $request->address,
                    'Country' => $request->country,
                    'State' => $request->state,
                    'City' => $request->city,
                    'Postal' => $request->postal,
                    'whatsapp_number' => $request->whatsapp_number,
                    'Mobile' => $request->mobile,
                    'Residencystatus' => $request->residence_status,
                ];
                $update = DB::table('m4winreg')->where('user_id', $profile->user_id)->update($CONTACTDETAILS);
    
                return response()->json(['status' => 200, 'message' => 'Contact Details Updated Successfully', 'data' => $update]);  
            }if($request->form_key == '6'){
                $CONTACTDETAILS = [
                    'Profile' => $request->profile_description,
                ];
                $update = DB::table('m4winreg')->where('user_id', $profile->user_id)->update($CONTACTDETAILS);
    
                return response()->json(['status' => 200, 'message' => 'Profile Updated Successfully', 'data' => $update]);  
            }if($request->form_key == '7'){
                $FAMILYDETAILS = [
                    'FamilyDetails' => $request->family_details,
                    'Familyvalues' => $request->family_values,
                    'FamilyType' => $request->family_type,
                    'FamilyStatus' => $request->family_status,
                    'FamilyOrigin' => $request->family_origin,
                    'noofbrothers' => $request->noof_bro,
                    'noofsisters' => $request->noof_sis,
                    'noyubrothers' => $request->noyu_bro,
                    'noyusisters' => $request->noyu_sis,
                    'nbm' => $request->nbm,
                    'nsm' => $request->nsm,
                    'Fathername' => $request->father_name,
                    'Fatherlivingstatus' => $request->father_livin_status,
                    'Fathersoccupation' => $request->father_occ,
                    'Mothersname' => $request->mother_name,
                    'Motherlivingstatus' => $request->mother_livin_status,
                    'Mothersoccupation' => $request->mother_occ,
                ];
                $update = DB::table('m4winreg')->where('user_id', $profile->user_id)->update($FAMILYDETAILS);
    
                return response()->json(['status' => 200, 'message' => 'Family Details Updated Successfully', 'data' => $update]);  
            }if($request->form_key == '8'){
               $lookingValue = implode(',', $request->looking_for);
                $PARTNERPREFERENCE = [
                    'Looking' =>  $lookingValue,
                    'PE_FromAge' => $request->from_age,
                    'PE_ToAge' => $request->to_age,
                    'PartnerExpectations' => $request->partner_expectations,
                    'PE_Countrylivingin' => $request->pe_country,
                    'PE_Height' => $request->pe_height,
                    'PE_Height2' => $request->pe_height2,
                    'PE_Complexion' => $request->pe_complexion,
                    'PE_Education' => $request->pe_education,
                    'PE_Religion' => $request->pe_religion,
                    'PE_Caste' => $request->pe_caste,
                    'PE_Residentstatus' => $request->pe_residentsstatus,
                ];
                $update = DB::table('m4winreg')->where('user_id', $profile->user_id)->update($PARTNERPREFERENCE);
    
                return response()->json(['status' => 200, 'message' => 'Partner Preference Updated Successfully', 'data' => $update]);  
            }if($request->form_key == '9'){
                 $hobbiesValue = implode(',', $request->hobbies);
                $intrestValue = implode(',', $request->interests);
                $HOBBIES = [
                    'Hobbies' => $hobbiesValue,
                    'OtherHobbies' => $request->other_hobbies,
                    'Interests' => $intrestValue,
                    'OtherInterests' => $request->other_interests,
                ];
                $update = DB::table('m4winreg')->where('user_id', $profile->user_id)->update($HOBBIES);
    
                return response()->json(['status' => 200, 'message' => 'Hobbies and Interest Updated Successfully', 'data' => $update]);  
            }

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    
    
     public function faq(Request $request){
        try{
           $faq = DB::table('faq')->get();
            return response()->json(['status' => 200, 'count' => $faq->count() , 'data'=>  $faq]);
        }catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    
     public function AbuseForm(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'abuse_type' => 'required|string|max:255',
                'profile_id' => 'required|string|max:255',
                'email_id' => 'required|email',
                'subject' => 'required|string|max:255',
                'complaint_details' => 'required|string|max:255',
                'email' => 'required|email',
            ]);

            
            if ($validator->fails()) {
                return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
            }
            //$user = DB::table('wpu6_users')->where('web_token', $request->token)->first();

            $arr = [
                "abuse_category"=>$request->abuse_category,
                "profile_id"=>$request->profile_id,
                "email_id"=>$request->email_id,
                "subject"=>$request->subject,
                "com_details"=>$request->complaint_details,
                "email"=>$request->email
            ];
            $abuseform = DB::table('abuse_form')->insert($arr);
            return response()->json(['status' => 200, 'message' => 'Abuse Form Submitted Successfully' , 'data'=>  $abuseform]);
        }catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
      public function SuccessStory(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'bride_name' => 'required|string|max:255',
                'bride_profile_id' => 'required|string|max:255',
                'groom_name' => 'required|string|max:255',
                'groom_profile_id' => 'required|string|max:255',
                'email' => 'required|email',
                'mobile_number' => 'required|regex:/^\d{10}$/',   
                'contact_address' => 'required|string|max:255',
                'your_comments' => 'required|string|max:255',
                'weding_date' => 'required|string|max:255',
                'wedding_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'verify_code' => 'required|regex:/^\d{6}$/',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
            }

            if ($request->file('wedding_photo')) {
                $image = $request->file('wedding_photo');
                $filename = time() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('wedding-images/' . date("Y") . '/' . date("m"));
                $image->move($destinationPath, $filename);
                $file_path = 'wedding-images/' . date("Y") . '/' . date("m") . '/' . $filename;
            } else {
                $file_path = null;
            }

            $arr = [
                'weddingphoto' =>  $file_path,
                'bridename' => $request->bride_name,
                'brideid' => $request->bride_profile_id,
                'groomname' => $request->groom_name,
                'groomid' => $request->groom_profile_id,
                'email' => $request->email,
                'mobile' => $request->mobile_number,
                'address' => $request->contact_address,
                'weddingphoto'=>$file_path,
                //'marriagedate' => Carbon::createFromFormat('d-m-Y', $request->input('weding_date'))->format('Y-m-d'),
                'marriagedate' => $request->weding_date,
                'successmessage' => $request->your_comments,
                'verification_code'=>$request->verify_code,
            ];
            $success_stories = DB::table('successstory')->insert($arr);
            return response()->json(['status' => 200, 'message' => 'Created Successfully' , 'data'=>  $success_stories]);
        }catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    

      public function AddPhoto(Request $request)
{
    try {
        $rules = [];

        if ($request->has('Addphoto1')) {
            $rules['Addphoto1'] = 'required|image|max:2048';
        }
        
        if ($request->has('Addphoto2')) {
            $rules['Addphoto2'] = 'required|image|max:2048';
        }
        
        if ($request->has('Addphoto3')) {
            $rules['Addphoto3'] = 'required|image|max:2048';
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
        }
        $ses_user = Session::get('user');
            $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
        $profile = DB::table('m4winreg')->where('user_id', $user->ID)->first();
        
        $file_paths = [];
        
        if ($request->file('Addphoto1')) {
            $image = $request->file('Addphoto1');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('user-images/' . date("Y") . '/' . date("m"));
            $image->move($destinationPath, $filename);
            $file_paths['Photo1'] = 'user-images/' . date("Y") . '/' . date("m") . '/' . $filename;
        }
        
        if ($request->file('Addphoto2')) {
            $image = $request->file('Addphoto2');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('user-images/' . date("Y") . '/' . date("m"));
            $image->move($destinationPath, $filename);
            $file_paths['Photo2'] = 'user-images/' . date("Y") . '/' . date("m") . '/' . $filename;
        }
        
        if ($request->file('Addphoto3')) {
            $image = $request->file('Addphoto3');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('user-images/' . date("Y") . '/' . date("m"));
            $image->move($destinationPath, $filename);
            $file_paths['Photo3'] = 'user-images/' . date("Y") . '/' . date("m") . '/' . $filename;
        }
        
        $updateData = [];
        
        if (!empty($file_paths)) {
            foreach ($file_paths as $key => $value) {
                $updateData[$key] = $value;
            }
        }
        
        if (!empty($updateData)) {
            $update = DB::table('m4winreg')->where('user_id', $profile->user_id)->update($updateData);
            return response()->json(['status' => 200, 'message' => 'Photos uploaded successfully']);
        } else {
            return response()->json(['status' => 400, 'message' => 'No photos uploaded']);
        }
    } catch (Exception $e) {
        return response()->json([
            'status' => 500,
            'error' => $e->getMessage(),
        ]);
    }
}

public function VerificationDocument(Request $request)
{
    try {
        $rules = [
            'document' => 'required|string|max:255',
            'identity_id' => 'required|string|max:255',
            'identity_image' => 'required|image|max:2048',
        ];

        if ($request->identity == '1') {
            $validator = Validator::make($request->all(), $rules);
        } elseif ($request->identity == '2') {
            $validator = Validator::make($request->all(), $rules);
        }

        if ($validator->fails()) {
            return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
        }

        $ses_user = Session::get('user');
            $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
        $profile = DB::table('m4winreg')->where('user_id', $user->ID)->first();

        if ($request->file('identity_image')) {
            $image = $request->file('identity_image');
            if ($image->isValid()) {
                $filename = time() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('user-images/' . date("Y") . '/' . date("m"));
                $image->move($destinationPath, $filename);
                $file_path = 'user-images/' . date("Y") . '/' . date("m") . '/' . $filename;
            } else {
                return response()->json(['status' => 400, 'message' => 'Invalid file'], 400);
            }
        }

        if ($request->identity == '1') {
            $proofofidentity = [
                'proof_identity_document' => $request->document,
                'proof_identity_id' => $request->identity_id,
                'proof_identity_image' => $file_path ?? null, // Use null if file upload failed
            ];
            $update = DB::table('m4winreg')->where('user_id', $profile->user_id)->update($proofofidentity);

            return response()->json(['status' => 200, 'message' => 'Verification Document Updated Successfully', 'data' => $update]);
        } elseif ($request->identity == '2') {
            $proofofaddress = [
                'proof_address_document' => $request->document,
                'proof_address_id' => $request->identity_id,
                'proof_address_image' => $file_path ?? null, // Use null if file upload failed
            ];
            $update = DB::table('m4winreg')->where('user_id', $profile->user_id)->update($proofofaddress);

            return response()->json(['status' => 200, 'message' => 'Verification Document Updated Successfully', 'data' => $update]);
        }

        return response()->json(['status' => 400, 'message' => 'Invalid request'], 400);
    } catch (Exception $e) {
        return response()->json([
            'status' => 500,
            'error' => $e->getMessage(),
        ]);
    }
}

public function EditHoroscope(Request $request)
    {

        try {
            $ses_user = Session::get('user');
            $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            $profile = DB::table('m4winreg')->where('user_id', $user->ID)->first();
            $proofofidentity = [
                'r1' => $request->rassi1,
                'r2' => $request->rassi2,
                'r3' => $request->rassi3,
                'r4' => $request->rassi4,
                'r5' => $request->rassi5,
                'r6' => $request->rassi6,
                'r7' => $request->rassi7,
                'r8' => $request->rassi8,
                'r9' => $request->rassi9,
                'r10' => $request->rassi10,
                'r11' => $request->rassi11,
                'r12' => $request->rassi12,

                'a1' => $request->amsam1,
                'a2' => $request->amsam2,
                'a3' => $request->amsam3,
                'a4' => $request->amsam4,
                'a5' => $request->amsam5,
                'a6' => $request->amsam6,
                'a7' => $request->amsam7,
                'a8' => $request->amsam8,
                'a9' => $request->amsam9,
                'a10' => $request->amsam10,
                'a11' => $request->amsam11,
                'a12' => $request->amsam12,

            ];
            $update = DB::table('m4winreg')->where('user_id', $profile->user_id)->update($proofofidentity);

            return response()->json(['status' => 200, 'message' => 'Rasi & Amsam  Updated Successfully', 'data' => $update]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
      
    public function Lakshmisearch(Request $request)
    {
                $data = DB::table('m4winreg')
        ->select('m4winreg.*','religion.name as ReligionName','caste.name as CasteName','state.name as StateName','education.name as EducationName','occupation.name as OccupationName','city.name as CityName','country.name as CountryName')
        ->leftJoin('religion', 'religion.id', '=', 'm4winreg.Religion')
        ->leftJoin('caste', 'caste.id', '=', 'm4winreg.Caste')
        ->leftJoin('state', 'state.id', '=', 'm4winreg.State')
        ->leftJoin('education', 'education.id', '=', 'm4winreg.Education')
        ->leftJoin('occupation', 'occupation.id', '=', 'm4winreg.Occupation')
        ->leftJoin('city', 'city.id', '=', 'm4winreg.City')
        ->leftJoin('country', 'country.id', '=', 'm4winreg.Country');

 if ($request->gender) {
            $data->where('m4winreg.Gender', $request->gender);
        }
        if ($request->ageform && $request->ageto) {
            $data->whereBetween('m4winreg.Age', [$request->ageform, $request->ageto]);
        }
        if ($request->community) {
            $data->where('m4winreg.Religion', $request->community);
        }
        if ($request->caste) {
            $data->where('m4winreg.caste', $request->caste);
        }
        if ($request->country) {
            $data->where('m4winreg.Country', $request->country);
        }
        if ($request->state) {
            $data->where('m4winreg.State', $request->state);
        }
        if ($request->city) {
            $data->where('m4winreg.City', $request->city);
        }
        if ($request->education) {
            $data->where('m4winregEducation', $request->education);
        }
        if ($request->occupation) {
            $data->where('m4winreg.Occupation', $request->occupation);
        }
        if ($request->maritalstatus) {
            $data->where('m4winreg.Maritalstatus', 'LIKE', '%' . $request->maritalstatus . '%');
        }
        if ($request->star) {
            $data->where('m4winreg.Star', 'LIKE', '%' . $request->star . '%');
        }
        if ($request->spe_cases) {
            $data->where('m4winreg.spe_cases', 'LIKE', $request->spe_cases);
        }
        if ($request->matri_id) {
            $data->where('m4winreg.MatriID', 'LIKE', $request->matri_id);
        }

        
        $results = $data->get();
        // return response()->json([
        //     'status' => 200,
        //     'message'=>'Data Not Found',
        //     'data' => $results,
        // ]);
       return response()->json(count($results) > 0 ? ['status' => 200, 'data' => $results] : ['status' => 400, 'message' => 'Data not found..!']);
        //return response()->json($results);
    }

    public function InboxMessage(Request $request)
    {
        try {
            $ses_user = Session::get('user');
            // $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            $user = User::where('ID', $ses_user[0]->ID)->first();       
            $m4winreg = DB::table('m4winreg')->where('user_id', $user->ID)->first();
            if($m4winreg){
                $sendmessage = DB::table('sentmessage')
                    ->select(DB::raw('ANY_VALUE(pid) as pid'))
                    ->where('ToID', $m4winreg->MatriID)
                    ->groupBy('FromID')
                    ->get();
                
                $pidValues = $sendmessage->pluck('pid');
                
                $data = DB::table('sentmessage')
                    ->whereIn('pid', $pidValues)
                    ->get();
                    
                $count = $data->count(); 
                
                return response()->json($count > 0 ? ['status' => 200, 'data' => $data, 'count' => $count] : ['status' => 400, 'message' => 'Data not found..!']);
            } else {
                return response()->json(['status' => 400, 'message' => 'Matrimony account not found..!']);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
public function ReceivedMessages(Request $request)
{
    try {
        $ses_user = Session::get('user');
            // $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            $user = User::where('ID', $ses_user[0]->ID)->first();       
        $m4winreg = DB::table('m4winreg')->where('user_id', $user->ID)->first();
        if($m4winreg){
            $received = DB::table('sentmessage')
                ->where('ToID', $m4winreg->MatriID)
                ->where('FromID', $request->fromid)
                ->get();
        return response()->json($received->count() > 0 ? ['status' => 200, 'data' => $received] : ['status' => 400, 'message' => 'Data not found..!']);
        }else{
            return response()->json(['status' => 400, 'message' => 'Matrimony account not found..!']);
        }
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'error' => $e->getMessage(),
        ]);
    }
}



public function InboxMessages(Request $request)
{
    try {
        $ses_user = Session::get('user');
            // $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            $user = User::where('ID', $ses_user[0]->ID)->first();       
        $m4winreg = DB::table('m4winreg')->where('user_id', $user->ID)->first();
        if($m4winreg){
            $received = DB::table('sentmessage')
                ->where('FromID', $m4winreg->MatriID)
                ->where('ToID', $request->toid)
                ->get();
        return response()->json($received->count() > 0 ? ['status' => 200, 'data' => $received] : ['status' => 400, 'message' => 'Data not found..!']);
        }else{
            return response()->json(['status' => 400, 'message' => 'Matrimony account not found..!']);
        }
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'error' => $e->getMessage(),
        ]);
    }
}
  public function SendInboxMessage(Request $request)
    {
        try {
            $ses_user = Session::get('user');
            // $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            $user = User::where('ID', $ses_user[0]->ID)->first();      
            $m4winreg = DB::table('m4winreg')->where('user_id', $user->ID)->first();
            if($m4winreg){
                $sendmessage = DB::table('sentmessage')
                    ->select(DB::raw('ANY_VALUE(pid) as pid'))
                    ->where('FromID', $m4winreg->MatriID)
                    // ->groupBy('FromID')
                    ->get();
                
                $pidValues = $sendmessage->pluck('pid');
                
                $data = DB::table('sentmessage')
                    ->whereIn('pid', $pidValues)
                    ->get();
                    
                $count = $data->count(); 
                
                return response()->json($count > 0 ? ['status' => 200, 'data' => $data, 'count' => $count] : ['status' => 400, 'message' => 'Data not found..!']);
            } else {
                return response()->json(['status' => 400, 'message' => 'Matrimony account not found..!']);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function ReceivedInboxMessage(Request $request)
    {
        try {
            $ses_user = Session::get('user');
            // $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            $user = User::where('ID', $ses_user[0]->ID)->first();        
            $m4winreg = DB::table('m4winreg')->where('user_id', $user->ID)->first();
            if($m4winreg){
                $sendmessage = DB::table('sentmessage')
                    ->select(DB::raw('ANY_VALUE(pid) as pid'))
                    ->where('ToID', $m4winreg->MatriID)
                    // ->groupBy('ToID')
                    ->get();
                
                $pidValues = $sendmessage->pluck('pid');
                
                $data = DB::table('sentmessage')
                    ->whereIn('pid', $pidValues)
                    ->get();
                    
                $count = $data->count(); 
                
                return response()->json($count > 0 ? ['status' => 200, 'data' => $data, 'count' => $count] : ['status' => 400, 'message' => 'Data not found..!']);
            } else {
                return response()->json(['status' => 400, 'message' => 'Matrimony account not found..!']);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }

public function DeleteMessage(Request $request)
{
    try {
        $deleted = DB::table('sentmessage')->where('pid', $request->pid)->delete();

        if ($deleted) {
            return response()->json(['status' => 200, 'message' => 'Message deleted successfully.']);
        } else {
            return response()->json(['status' => 400, 'message' => 'Failed to delete message.']);
        }
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'error' => $e->getMessage(),
        ]);
    }
}

 public function ExpressIntrestSender(Request $request){
        try{
            $ses_user = Session::get('user');
            // $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            $user = User::where('ID', $ses_user[0]->ID)->first();       
            $m4winreg = DB::table('m4winreg')->where('user_id', $user->ID)->first();
            if($m4winreg){
                $sendintrest= DB::table('expressinterest')
                    ->where('eisender', $m4winreg->MatriID)
                    ->get();
    
                $pendingCount = $sendintrest->where('eisender_accept', 'Pending')->count(); 
                $acceptCount = $sendintrest->where('eisender_accept', 'Accept')->count(); 
                $declineCount = $sendintrest->where('eisender_accept', 'Decline')->count(); 
                return response()->json([
                    'status' => 200, 
                    'data' => $sendintrest, 
                    'pending_count' => $pendingCount,
                    'accept_count' => $acceptCount,
                    'decline_count' => $declineCount,
                    //'total_count' => $sendintrest->count()
                ]);
            }
        }catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function ExpressIntrestReceiver(Request $request){
        try{
            $ses_user = Session::get('user');
            // $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            $user = User::where('ID', $ses_user[0]->ID)->first();        
            $m4winreg = DB::table('m4winreg')->where('user_id', $user->ID)->first();
            if($m4winreg){
                $receiveintrest= DB::table('expressinterest')
                    ->where('eireceiver', $m4winreg->MatriID)
                    ->get();
    
                $pendingCount = $receiveintrest->where('eirec_accept', 'Pending')->count(); 
                $acceptCount = $receiveintrest->where('eirec_accept', 'Accept')->count(); 
                $declineCount = $receiveintrest->where('eirec_accept', 'Decline')->count(); 
                return response()->json([
                    'status' => 200, 
                    'data' => $receiveintrest, 
                    'pending_count' => $pendingCount,
                    'accept_count' => $acceptCount,
                    'decline_count' => $declineCount,
                    //'total_count' => $sendintrest->count()
                ]);
            }
        }catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    public function ShortListDelete(Request $request){
        try {
            $deleted = DB::table('shortlist_profile')
            ->where('short_id', $request->short_delete)
            ->update(['deleted_at' => now()]);
            if ($deleted) {
                return response()->json(['status' => 200, 'message' => 'Deleted Successfully.']);
            } else {
                return response()->json(['status' => 400, 'message' => 'Failed to delete message.']);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    public function GetViewProfile(Request $request){
       try{
        $ses_user = Session::get('user');
            $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            // $user = User::where('ID', $ses_user[0]->ID)->first(); 
        $profile = DB::table('m4winreg')->where('user_id', $user->ID)->first();
      $ViewProfile = DB::table('viewedprofile')
        ->leftJoin('m4winreg', 'm4winreg.MatriID', '=', 'viewedprofile.viewedids')
        ->where('viewedprofile.matriid', $profile->MatriID)->get();
        return response()->json([
            'status' => 200,
            'data' => $ViewProfile,
        ]);
       }catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function AddShortList(Request $request)
    {
        try {
            $ses_user = Session::get('user');
            $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            // $user = User::where('ID', $ses_user[0]->ID)->first(); 
            $profile = DB::table('m4winreg')->where('user_id', $user->ID)->first();
            $toprofile = DB::table('m4winreg')->where('MatriID', $request->match_id)->first();
            
            $arr = [
                'mat_id' => $request->match_id,
                'mat_name' => $toprofile->Name,
                'log_id' => $profile->MatriID
                ];
                
                $ins = DB::table('shortlist_profile')->insert($arr);
            
            return response()->json(['status' => 200, 'message' => 'Successfully added to heartlist.']);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
     public function UpdateHoroscopeDocument(Request $request)
    {
        try {
            $ses_user = Session::get('user');
            $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            // $user = User::where('ID', $ses_user[0]->ID)->first(); 
            $profile = DB::table('m4winreg')->where('user_id', $user->ID)->first();

            if ($request->file('horoscopeimage')) {
                $image = $request->file('horoscopeimage');
                $filename = time() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('horo-scope/' . date("Y") . '/' . date("m"));
                $image->move($destinationPath, $filename);
                $file_path = 'horo-scope/' . date("Y") . '/' . date("m") . '/' . $filename;
            }
            $arr = [
                'Horoscheck' => $file_path,
                'HorosApprove' => 'No',
            ];


            $update = DB::table('m4winreg')->where('user_id', $user->ID)->update($arr);

            return response()->json(['status' => 200, 'message' => 'Horoscope Updated Successfully.', 'data' => $update]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    


}
