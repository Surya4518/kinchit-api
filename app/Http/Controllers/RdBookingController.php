<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Validator;
use Exception;
use Session;
use Storage;
use Mail;
use View;
use Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use App\Common;
use App\Models\{
    RdBooking
}
;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class RdBookingController extends Controller
{
    public function StoreTheBookingFromRD(Request $request)
    {
        try {
            $rules = [
                'booker_name' => 'required',
                'booker_lastname' => 'required',
                 'divyadesam' => 'required',
                'no_of_ppl' => 'required',
                'date_from' => 'required',
                'date_to' => 'required',
                'phone_no' => 'required|numeric|digits:10',
                'email' => 'required|email',
                'address1' => 'required',
                'address2' => 'required',
                'state_name' => 'required',
                'city_name' => 'required',
                'pincode' => 'required',
                'country_name' => 'required',
                'kkt_or_not' => 'required',
            ];

            if ($request->kkt_or_not == "yes") {
                $rules['membership_id'] = 'required';
            }
            if ($request->has('no_of_ppl')) {
                $count = (int)$request->input('no_of_ppl');
                for ($i = 0; $i < $count; $i++) {
                    $rules["ppl_names.{$i}"] = 'required|string|max:255';
                }
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
            }
            $entry_arr = [
                'booker_name' => $request->booker_name,
                'booker_lastname' => $request->booker_lastname,
                'no_of_people' => $request->no_of_ppl,
                'people_names' => json_encode($request->ppl_names, true),
                'divyadesam' => $request->divyadesam,
                'date_from' => date("Y-m-d", strtotime($request->date_from)),
                'date_to' => date("Y-m-d", strtotime($request->date_to)),
                'phone_no' => $request->phone_no,
                'email' => $request->email,
                'address_1' => $request->address1,
                'address_2' => $request->address2,
                'city' => $request->city_name,
                'state' => $request->state_name,
                'pincode' => $request->pincode,
                'country' => $request->country_name,
                'kkt_or_not' => $request->kkt_or_not,
                'membership_id' => $request->membership_id,
            ];
            $insert = RdBooking::create($entry_arr);
            return response()->json($insert == true ? ["status" => 200, "message" => "Successfully booked"] : ["status" => 400, "message" => "Failed to book"]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
