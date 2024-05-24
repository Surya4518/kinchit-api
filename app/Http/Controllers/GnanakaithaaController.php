<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Session;
use App\Models\{
    gnanakaithaa
};
use App\Mail\MyMail;
use DateTime;
use Illuminate\Support\Facades\Mail;

class GnanakaithaaController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name_of_student' => 'required|string|max:255',
                'contact_no' => 'required|regex:/^(\+\d{1,3}[- ]?)?(\d{10})$/|max:15',
                'email' => 'required|email',
                'age' => 'required|string|max:255',
                'dob' => 'required|string|max:255',
                'door_no' => 'required|string|max:255',
                'state' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'pincode' => 'required|string|max:255',
                'door_no1' => 'required|string|max:255',
                //'state1' => 'required|string|max:255',
                //'city1' => 'required|string|max:255',
                //'pincode1' => 'required|string|max:255',
                'name_of_parent' => 'required|string|max:255',
                'email1' => 'required|email',
                'aadhaar_no' => 'required|string|max:255',
                'no_of_siblings' => 'required|string|max:255',
                'family_annual_Income' => 'required|string|max:255',
                'parent_work' => 'required|string|max:255',
                'from_education' => 'required|string|max:255',
                'from_school' => 'required|string|max:255',
                'from_mark_sheet' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'to_education' => 'required|string|max:255',
                'to_school' => 'required|string|max:255',
                'to_mark_sheet' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'to_college_name' => 'required|string|max:255',
                'bagavathar_name' => 'required|string|max:255',
                'volunteer_contact_number' => 'required|regex:/^(\+\d{1,3}[- ]?)?(\d{10})$/|max:15',
                'declaration' => 'required|string|max:255',
                'payment_type' => 'required|string|max:255',
            ]);


            if ($validator->fails()) {
                return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
            }

            if ($request->file('from_mark_sheet')) {
                $image = $request->file('from_mark_sheet');
                $filename = time() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('gnanakaithaa-images/' . date("Y") . '/' . date("m"));
                $image->move($destinationPath, $filename);
                $file_path = 'gnanakaithaa-images/' . date("Y") . '/' . date("m") . '/' . $filename;
            } else {
                $file_path = null;
            }

            if ($request->file('to_mark_sheet')) {
                $image = $request->file('to_mark_sheet');
                $filename = time() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('gnanakaithaa-images/' . date("Y") . '/' . date("m"));
                $image->move($destinationPath, $filename);
                $file_path1 = 'gnanakaithaa-images/' . date("Y") . '/' . date("m") . '/' . $filename;
            } else {
                $file_path1 = null;
            }

            $gnanakaithaa = [
                'name_of_student' => $request->name_of_student,
                'contact_no' => $request->contact_no,
                'email' => $request->email,
                'age' => $request->age,
                'dob' => $request->dob,
                'door_no' => $request->door_no,
                'state' => $request->state,
                'city' => $request->city,
                'pincode' => $request->pincode,
                'door_no1' => $request->door_no1,
                'state1' => $request->state1,
                'city1' => $request->city1,
                'pincode1' => $request->pincode1,
                'name_of_parent' => $request->name_of_parent,
                'email1' => $request->email1,
                'aadhaar_no' => $request->aadhaar_no,
                'no_of_siblings' => $request->no_of_siblings,
                'family_annual_Income' => $request->family_annual_Income,
                'parent_work' => $request->parent_work,
                'from_education' => $request->name_of_student,
                'from_school' => $request->from_school,
                'from_mark_sheet' => $file_path,
                'to_education' => $request->to_education,
                'to_school' => $request->to_school,
                'to_mark_sheet' => $file_path1,
                'to_college_name' => $request->to_college_name,
                'bagavathar_name' => $request->bagavathar_name,
                'volunteer_contact_number' => $request->volunteer_contact_number,
                'declaration' => $request->declaration,
                'payment_type' => $request->payment_type,
            ];
            Mail::to($request->email)->send(new MyMail(null, null, null, null, null, null, null, $gnanakaithaa));
            $data = gnanakaithaa::insert($gnanakaithaa);
            return response()->json(['status' => 200, 'message' => 'Gnanakaithaa Created Successfully', 'data' => $data]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
