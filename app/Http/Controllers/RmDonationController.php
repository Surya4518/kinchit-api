<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Validator;
use Exception;
use Session;
use Storage;
use App\Helpers;
use View;
use Response;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use App\Models\{
    RmDonation,
    RmDelivery,
    User
};
use App\Mail\MyMail;
use Illuminate\Support\Facades\Mail;

class RmDonationController extends Controller
{
    public function RmDonation(Request $request){
        try{
            $rules = [
                'kkt' => 'required|string|max:255',
                // 'kkt_rs' => 'required|string|max:255', 
              $request->merge(['kkt_rs' => $request->kkt === 'None' ? null : $request->kkt_rs]),
                'kds' => 'required|string|max:255',
                // 'kds_rs' => 'required|string|max:255',
                 $request->merge(['kds_rs' => $request->kds === 'None' ? null : $request->kds_rs]),
                'payment_type' => 'required|string|max:255',
            ];
            
            if ($request->payment_type == 'cheque') {
                $rules = array_merge($rules, [
                    'bank_name' => 'required|string|max:255',
                    'cheque_number' => 'required|string|max:255',
                    'cheque_date' => 'required|date',
                ]);
            }
            
            if ($request->payment_type == 'upi' || $request->payment_type == 'online') {
                $rules = array_merge($rules, [
                    'bank_transaction_id' => 'required|string|max:255',
                    'transaction_date' => 'required|date',
                ]);
            }
            
            $rules = array_merge($rules, [
                'direct_payment_type' => 'required|string|max:255',
                'material_given' => 'required|string|max:255',
                'is_which_half' => 'required|array',
                'is_which_half.*' => 'required',
            ]);
            
            $validator = Validator::make($request->all(), $rules);
            
            if ($validator->fails()) {
                return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
            }
            $ses_user = Session::get('user');

            $user = User::where('ID', $ses_user[0]->ID)
                    ->where('user_type', 'volunteer')
                    ->first();
            // dd($user);
            $member = DB::table('wpu6_users')
                    ->select('wpu6_users.*','userprofile_dt.first_name','userprofile_dt.phone_number')
                    ->leftjoin('userprofile_dt','userprofile_dt.user_id','=','wpu6_users.ID')
                    ->where('wpu6_users.ID', $request->member_pri_id)
                    ->get();
            if(!empty($user)){
                if($member->count() > 0){
                    $check = RmDonation::where('user_id',$user->ID)->where('member_id',$member[0]->ID)->where('material_given',$request->material_given)->where('is_which_half',$request->is_which_half)->count();
                    if($check > 0){
                        return response()->json(['status' => 400, 'message' => 'Existed Data']);
                    }
                    $trans_id = Str::random(16);
                    $arr = [
                    'user_id' => $user->ID,
                    'member_id' =>$member[0]->ID,
                    'member_ref' =>$member[0]->user_login,
                    'name' =>$member[0]->first_name,
                    'phone_number' =>$member[0]->phone_number,
                    'kkt' =>$request->kkt,
                    'kkt_rs' =>$request->kkt_rs,
                    'kds' =>$request->kds,
                    'kds_rs' =>$request->kds_rs,
                    'payment_type' =>$request->payment_type,
                    'direct_payment_type' =>$request->direct_payment_type,
                    'material_given' =>$request->material_given,
                    'is_which_half' => implode(',',$request->is_which_half),
                    'trans_id' => $trans_id
                ];
                // if ($request->is_which_half == 'h1') {
                //     $arr = array_merge($arr, [
                //         'h1' => 'yes',
                //     ]);
                // }
                // if ($request->is_which_half == 'h2') {
                //     $arr = array_merge($arr, [
                //         'h2' => 'yes',
                //     ]);
                // }$arr = [];

                foreach ($request->is_which_half as $value) {
                    if ($value == 'h1') {
                        $arr['h1'] = 'yes';
                    }
                    if ($value == 'h2') {
                        $arr['h2'] = 'yes';
                    }
                }
                if ($request->payment_type == 'cheque') {
                    $arr = array_merge($arr, [
                        'bank_name' => $request->bank_name,
                        'cheque_number' => $request->cheque_number,
                        'cheque_date' => date("Y-m-d", strtotime($request->cheque_date))
                    ]);
                }
                
                if ($request->payment_type == 'upi' || $request->payment_type == 'online') {
                    $arr = array_merge($arr, [
                        'bank_transaction_id' => $request->bank_transaction_id,
                        'transaction_date' => date("Y-m-d H:i:s", strtotime($request->transaction_date))
                    ]);
                }
                // dd($arr);
                $data = RmDonation::insert($arr);
                return response()->json(['status' => 200, 'message' => 'Successfully updated..!' , 'data'=> $data]);
                }else{
                return response()->json(['status' => 400, 'message' => 'Member Does not Exsisted']);  
                }
            }else{
                return response()->json(['status' => 400, 'message' => 'Volunteer Does not Exsisted']);  
            }
        }catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    public function RmDeliveryDetails(Request $request){
        
        try{
            $ses_user = Session::get('user');
            // $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            $user = User::where('ID', $ses_user[0]->ID)->where('user_type', 'volunteer')->first(); 
            // $user = User::where(function($query) use ($request) {
            //             $query->where('web_token', $request->token)
            //                   ->orWhere('app_token', $request->token);
            //         })
            //         ->where('user_type', 'volunteer')
            //         ->first();
            if($user->count() > 0){
            // $members = DB::table('wpu6_users')
            // ->select('wpu6_users.ID AS mem_id', 'wpu6_users.user_login as mem_userlogin', 'wpu6_users.user_email AS mem_usermail', 'rmsm_donation.*')
            // ->leftJoin('rmsm_donation', 'rmsm_donation.member_id', '=', 'wpu6_users.ID')
            // ->where('wpu6_users.parent', $user->user_login)
            // ->where('wpu6_users.user_status', '0')
            // ->where('wpu6_users.user_type', 'member')
            // ->get();
            $members = User::with('rmsmdetails','userdetails')
            ->where('parent', $user->user_login)
            ->where('user_status', '0')
            ->where('user_type', 'member')
            ->orderByDesc('ID')
            ->get();
            return response()->json($members->count() > 0 ? ['status' => 200, 'count' => $members->count(), 'data' => $members] : ['status' => 400, 'data' => []]);
            }else{
                return response()->json(['status' => 400, 'message' => 'Volunteer not found']);
            }
            
        }catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
        
    }
    
    public function RmDeposit(Request $request){
        try{
            $rules = [
                'deposit_to' => 'required|string|max:255',
                'donation_year' => 'required|string|max:255',
                'no_of_members' => 'required|string|max:255',
                'ttl_amt_deposited' => 'required|string|max:255',
                'payment_type' => 'required|string|max:255',
                'payment_date' => 'required|string|max:255'
            ];
            if ($request->payment_type == 'upi') {
                $rules = array_merge($rules, [
                    'upi_transaction_id' => 'required|string|max:255'
                ]);
            }else{
                $rules = array_merge($rules, [
                    'challan_number' => 'required|string|max:255'
                ]);
            }
            
            $validator = Validator::make($request->all(), $rules);
            
            if ($validator->fails()) {
                return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
            }

            $ses_user = Session::get('user');
            // $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            $user = User::where('ID', $ses_user[0]->ID)->where('user_type', 'volunteer')->first(); 
            // dd($user);
            $userdetails = DB::table('userprofile_dt')->where('user_id',$user->ID)->first();
            $depositdetails= [
                'first_name'=> $userdetails->first_name,
                'last_name'=> $userdetails->last_name,
                'user_email'=> $userdetails->user_email,
                'phone_number'=> $userdetails->phone_number
            ];
            Mail::to($userdetails->user_email)->send(new MyMail(null, null, null, null, null, null, null, null,null,null,null,null,$depositdetails));
            if(!empty($user)){
                    if ($request->payment_type == 'upi') {
                        $check = RmDelivery::where('user_id',$user->ID)->where('deposit_to',$request->deposit_to)->where('donation_year',$request->donation_year)->where('ttl_amt_deposited',$request->ttl_amt_deposited)->where('upi_transaction_id',$request->upi_transaction_id)->count();
                    }else{
                        $check = RmDelivery::where('user_id',$user->ID)->where('deposit_to',$request->deposit_to)->where('donation_year',$request->donation_year)->where('ttl_amt_deposited',$request->ttl_amt_deposited)->where('challan_number',$request->challan_number)->count();
                    }
                    if($check > 0){
                        return response()->json(['status' => 400, 'message' => 'Existed Data']);
                    }
                    $trans_id = Str::random(16);
                    $arr = [
                    'user_id' => $user->ID,
                    'deposit_to' =>$request->deposit_to,
                    'donation_year' =>$request->donation_year,
                    'no_of_members' =>$request->no_of_members,
                    'ttl_amt_deposited' =>$request->ttl_amt_deposited,
                    'payment_type' =>$request->payment_type,
                    'payment_date' =>date("Y-m-d", strtotime($request->payment_date)),
                    'trans_id' => $trans_id,
                    'created_at' => date("Y-m-d H:i:s")
                ];
                
                if ($request->payment_type == 'upi') {
                    $arr = array_merge($arr, [
                        'upi_transaction_id' => $request->upi_transaction_id
                    ]);
                }else{
                    $arr = array_merge($arr, [
                        'challan_number' => $request->challan_number
                    ]);
                }
                // dd($arr);
                $data = RmDelivery::insert($arr);
                return response()->json(['status' => 200, 'message' => 'Successfully updated..!' , 'data'=> $data,'depositdetails'=> $depositdetails]);
            }else{
                return response()->json(['status' => 400, 'message' => 'Volunteer Does not Exsisted']);  
            }
        }catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    
    public function RmDepositDetails(Request $request){
        
        try{
            
           $ses_user = Session::get('user');
            // $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            $user = User::where('ID', $ses_user[0]->ID)->where('user_type', 'volunteer')->first(); 
            if($user->count() > 0){
            $data = RmDelivery::with('user','userdetails')->where('user_id',$user->ID)->whereNull('deleted_at')->get();
            return response()->json($data->count() > 0 ? ['status' => 200, 'count' => $data->count(), 'data' => $data] : ['status' => 400, 'data' => []]);
            }else{
                return response()->json(['status' => 400, 'message' => 'Volunteer not found']);
            }
            
        }catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
        
    }
    
    public function GetDepositDetail(Request $request){
        
        try{
            
            $ses_user = Session::get('user');
            // $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            $user = User::where('ID', $ses_user[0]->ID)->where('user_type', 'volunteer')->first(); 
            if($user->count() > 0){
            $data = RmDelivery::where('id',$request->id)->whereNull('deleted_at')->get();
            return response()->json($data->count() > 0 ? ['status' => 200, 'count' => $data->count(), 'data' => $data] : ['status' => 400, 'data' => []]);
            }else{
                return response()->json(['status' => 400, 'message' => 'Volunteer not found']);
            }
            
        }catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
        
    }
    
    public function RmDepositUpdate(Request $request){
        try{
            $rules = [
                'deposit_to' => 'required|string|max:255',
                'donation_year' => 'required|string|max:255',
                'no_of_members' => 'required|string|max:255',
                'ttl_amt_deposited' => 'required|string|max:255',
                'payment_type' => 'required|string|max:255',
                'payment_date' => 'required|string|max:255'
            ];
            if ($request->payment_type == 'upi') {
                $rules = array_merge($rules, [
                    'upi_transaction_id' => 'required|string|max:255'
                ]);
            }else{
                $rules = array_merge($rules, [
                    'challan_number' => 'required|string|max:255'
                ]);
            }
            
            $validator = Validator::make($request->all(), $rules);
            
            if ($validator->fails()) {
                return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
            }

            $ses_user = Session::get('user');
            // $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
            $user = User::where('ID', $ses_user[0]->ID)->where('user_type', 'volunteer')->first(); 
            // dd($user);
            if(!empty($user)){
                    $trans_id = Str::random(16);
                    $arr = [
                    'user_id' => $user->ID,
                    'deposit_to' =>$request->deposit_to,
                    'donation_year' =>$request->donation_year,
                    'no_of_members' =>$request->no_of_members,
                    'ttl_amt_deposited' =>$request->ttl_amt_deposited,
                    'payment_type' =>$request->payment_type,
                    'payment_date' =>date("Y-m-d", strtotime($request->payment_date)),
                    'trans_id' => $trans_id,
                    'updated_at' => date("Y-m-d H:i:s")
                ];
                
                if ($request->payment_type == 'upi') {
                    $arr = array_merge($arr, [
                        'upi_transaction_id' => $request->upi_transaction_id
                    ]);
                }else{
                    $arr = array_merge($arr, [
                        'challan_number' => $request->challan_number
                    ]);
                }
                // dd($arr);
                $data = RmDelivery::where('id',$request->deposit_id)->update($arr);
                return response()->json(['status' => 200, 'message' => 'Successfully updated..!' , 'data'=> $data]);
            }else{
                return response()->json(['status' => 400, 'message' => 'Volunteer Does not Exsisted']);  
            }
        }catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    public function DonationCategories(Request $request){
        
        try{
            $ses_user = Session::get('user');
            $data = DB::table('donation_categories')->where('status','=','Active')->whereNull('deleted_at')->orderBy('sort_order','asc')->get();
            return response()->json($data->count() > 0 ? ['status' => 200, 'count' => $data->count(), 'data' => $data] : ['status' => 400, 'data' => []]);
        }catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
        
    }
    
    public function SentSMS(Request $request){
    try{
         return $this->SendSMS($request->mobile, $request->message);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    function SendSMS($mobile, $message){
        $url = 'http://api.nsite.in/api/v2/SendSMS';
        $apiKey = 'crWZ3EezAeJIZW3qDgPeOUq2jQYpGlGUQk7GQi+wrpo=';
        $clientId = '6efe925d-12c7-49ba-a649-b4bbe3d2d93d';
    
        $response = Http::get($url, [
            'SenderId' => 'KINCIT',
            'Is_Unicode' => false,
            'Is_Flash' => false,
            'Message' => $message,
            'MobileNumbers' => $mobile,
            'ApiKey' => $apiKey,
            'ClientId' => $clientId,
        ]);
    
        return $response->json();
    }
        
    }
