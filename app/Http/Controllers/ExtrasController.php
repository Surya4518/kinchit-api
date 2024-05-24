<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Session;
use App\Mail\MyMail;
use Illuminate\Support\Facades\Mail;

class ExtrasController extends Controller
{
    public function getState(Request $request){                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    
      try{
        $array = $request->all();
        if (array_key_exists('search', $array)) {
            $search = $array['search'];

            if ($search == null) {
                $states = DB::table('state')->where('country','=',101)->get();
            } else {
                $states = DB::table('state')->orderby('name', 'asc')->select('*')->where('name', 'like', '%' . $search . '%')->where('country','=',101)->limit(6)->get();
            }
            return response()->json(['status' => 200, 'message' => 'Get State', 'count' => $states->count(), 'data' =>$states]);
        } else {
            $states =  DB::table('state')->where('country','=',101)->get();
            return response()->json(['status' => 200, 'message' => 'Get State', 'count' => $states->count(), 'data' =>$states]);
        }
      }catch (Exception $e) {
        return response()->json(['status' => 500,'error' => $e->getMessage()]);
    }
    }

    public function getCity(Request $request){
      try{
        $array = $request->all();
        if (array_key_exists('search', $array)) {
            $search = $array['search'];

            if ($search == null) {
                $city = DB::table('city')->where('state',$request->state_id)->get();
            } else {
                $city = DB::table('city')->orderby('name', 'asc')->select('*')->where('name', 'like', '%' . $search . '%')->where('state',$request->state_id)->limit(6)->get();
            }
            return response()->json(['status' => 200, 'message' => 'Get State', 'count' => $city->count(), 'data' =>$city]);
        } else {
            $city = DB::table('city')->where('state',$request->state_id)->get();
            return response()->json(['status' => 200, 'message' => 'Get State', 'count' => $city->count(), 'data' =>$city]);
        }
      }catch (Exception $e) {
        return response()->json(['status' => 500,'error' => $e->getMessage()]);
    }
    }

    public function getReligion(Request $request){
        try{
            $array = $request->all();
        if (array_key_exists('search', $array)) {
            $search = $array['search'];

            if ($search == null) {
                $Religion = DB::table('religion')->where('status','=','Active')->orderBy('id','ASC')->get();
            } else {
                $Religion = DB::table('religion')->where('status','=','Active')->orderby('name', 'asc')->select('*')->where('name', 'like', '%' . $search . '%')->where('state',$request->state_id)->limit(6)->get();
            }
            return response()->json(['status' => 200, 'message' => 'Get Religion','data' =>$Religion]);
        } else {
            $Religion = DB::table('religion')->where('status','=','Active')->orderBy('id','ASC')->get();
            return response()->json(['status' => 200, 'message' => 'Get Religion','data' =>$Religion]);
        }
        
        }catch (Exception $e) {
          return response()->json(['status' => 500,'error' => $e->getMessage()]);
      }
      }

      public function getCaste(Request $request){
        try{
            $array = $request->all();
        if (array_key_exists('search', $array)) {
            $search = $array['search'];

            if ($search == null) {
                $caste = DB::table('caste')->where('religion',$request->community)->orderBy('id','ASC')->get();
            } else {
                $caste = DB::table('caste')->where('religion',$request->community)->orderby('name', 'asc')->select('*')->where('name', 'like', '%' . $search . '%')->where('state',$request->state_id)->limit(6)->get();
            }
            return response()->json(['status' => 200, 'message' => 'Get Religion','data' =>$caste]);
        } else {
            $caste = DB::table('caste')->where('religion',$request->community)->orderBy('id','ASC')->get();
            return response()->json(['status' => 200, 'message' => 'Get Religion','data' =>$caste]);
        }
          }catch (Exception $e) {
            return response()->json(['status' => 500,'error' => $e->getMessage()]);
        }
      }
      
    //   public function ContactFormSubmit(Request $request)
    //   {
    //       $validator = Validator::make($request->all(), [
    //           'name' => 'required',
    //           'email' => 'required',
    //           'message' => 'required',
    //       ]);
      
    //       if ($validator->fails()) {
    //           return response()->json(['status'=>401,'message' =>'Validation Failed','errors' => $validator->errors()]);
    //       }
      
    //       $contactus = [
    //           'name' => $request->name,
    //           'email' => $request->email,
    //           'message' => $request->message,
    //       ];
      
    //       // Send email
    //       Mail::to($request->email)->send(new MyMail(null,null,null,$contactus,null));
      
    //       // Insert data into the database
    //       $insert =  DB::table('contact_us')->insert($contactus);
    //       $insert =  DB::table('email_log')->insert($contactus);
      
    //       // Return JSON response with the cookie set in the headers
    //       return response()->json(['status'=>200,'message' => 'Contact form submitted successfully' ,'data'=> $contactus])
    //           ->withHeaders([
    //               'Cookie' => 'humans_21909=1',
    //           ]);
    //   }
      
      public function ContactFormSubmit(Request $request){
        //   dd($request->all());
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
          $insert =  DB::table('contact_us')->insert($contactus);
          $insert =  DB::table('email_log')->insert($contactus);
      
          // Return JSON response with the cookie set in the headers
          return response()->json(['status'=>200,'message' => 'Contact form submitted successfully' ,'data'=> $contactus]);
      }
      
      
      public function getEducation(Request $request)
  {
    try {
      $array = $request->all();
      if (array_key_exists('search', $array)) {
        $search = $array['search'];

        if ($search == null) {
          $education = DB::table('education')->where('status','=','Active')->get();
        } else {
          $education = DB::table('education')->orderby('name', 'asc')->select('*')->where('name', 'like', '%' . $search . '%')->where('status','=','Active')->get();
        }
        return response()->json(['status' => 200, 'message' => 'Get Education', 'count' => $education->count(), 'data' => $education]);
      } else {
        $education = DB::table('education')->where('status','=','Active')->get();
        return response()->json(['status' => 200, 'message' => 'Get Education', 'count' => $education->count(), 'data' => $education]);
      }
    } catch (Exception $e) {
      return response()->json([
        'status' => 500,
        'error' => $e->getMessage(),
      ]);
    }
  }

  public function getOccupation(Request $request)
  {
    try {
      $array = $request->all();
      if (array_key_exists('search', $array)) {
        $search = $array['search'];

        if ($search == null) {
          $occupation = DB::table('occupation')->where('status','=','Active')->get();
        } else {
          $occupation = DB::table('occupation')->orderby('name', 'asc')->select('*')->where('name', 'like', '%' . $search . '%')->where('status','=','Active')->get();
        }
        return response()->json(['status' => 200, 'message' => 'Get Occupation', 'count' => $occupation->count(), 'data' => $occupation]);
      } else {
        $occupation = DB::table('occupation')->where('status','=','Active')->get();
        return response()->json(['status' => 200, 'message' => 'Get Occupation', 'count' => $occupation->count(), 'data' => $occupation]);
      }
    } catch (Exception $e) {
      return response()->json([
        'status' => 500,
        'error' => $e->getMessage(),
      ]);
    }
  }
  
  public function SentWADoc(Request $request){                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    
      try{
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.interakt.ai/v1/public/message/',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
            "countryCode": "+91",
            
            "fullPhoneNumber": "919345553521", 
            "callbackData": "some text here",
            "type": "Template",
            "template": {
                "name": "renew_23_24_v2_2",
                "languageCode": "en",
                "headerValues": [
                    "https://interaktprodstorage.blob.core.windows.net/mediaprodstoragecontainer/2f4b4c6e-8828-4458-a95d-546bfd53d0d8/message_template_media/M4ZHAJAdVWZ0/KKT%20Members%20Renew%202023-24.pdf?se=2029-03-03T13%3A40%3A14Z&sp=rt&sv=2019-12-12&sr=b&sig=PpPrFNCRvLUki0JVXEVcjWaLqBL%2B8veo1GEQsfw%2B8xE%3D"
                ],
                "fileName": "file_name.pdf",
                "bodyValues": [
                    "body_variable_value"
                ]
            }
        }',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Basic UUI2ZFVPR3YyTW5aVENNLVJkYm9wX2d5enhoT3FQRlN1QkdWclVLS3V4UTo=',
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        echo $response;
        // return response()->json($response);
      }catch (Exception $e) {
        return response()->json(['status' => 500,'error' => $e->getMessage()]);
    }
    }

 public function KinchitServices(Request $request)
  {
    try {
      $KinchitService =  DB::table('kinchit_services')->whereNull('deleted_at')->where('status','Active')->get();
      return response()->json(['status' => 200, 'message' => 'Get Services','data' => $KinchitService]);
    } catch (Exception $e) {
      return response()->json(['status' => 500, 'error' => $e->getMessage()]);
    }
  }

  public function OurService(Request $request)
  {
    try {
      $OurService =  DB::table('home_banner')->whereNull('deleted_at')->where('type','our-service')->where('status','Active')->orderBy('shord_order', 'ASC')->get();
      return response()->json(['status' => 200, 'message' => 'Get Our Services','data' => $OurService]);
    } catch (Exception $e) {
      return response()->json(['status' => 500, 'error' => $e->getMessage()]);
    }
  }

  public function HomeBannerImage(Request $request)
  {
    try {
      $HomeImage =  DB::table('home_banner_image')->whereNull('deleted_at')->where('status','Active')->orderBy('shord_order', 'ASC')->get();
      return response()->json(['status' => 200, 'message' => 'Get Home Banner Image','data' => $HomeImage]);
    } catch (Exception $e) {
      return response()->json(['status' => 500, 'error' => $e->getMessage()]);
    }
  }

  public function AboutUs(){
    try {
      $HomeImage =  DB::table('home_banner')->whereNull('deleted_at')->where('type','about-us')->where('status','Active')->orderBy('shord_order', 'ASC')->get();
      return response()->json(['status' => 200, 'message' => 'Get About Us ','data' => $HomeImage]);
    } catch (Exception $e) {
      return response()->json(['status' => 500, 'error' => $e->getMessage()]);
    }
  }

  public function HomePageBanner(){
    try {
      $HomeImage =  DB::table('home_page_banner')->whereNull('deleted_at')->where('status','Active')->orderBy('shord_order', 'ASC')->get();
      return response()->json(['status' => 200, 'message' => 'Get Banner Image ','data' => $HomeImage]);
    } catch (Exception $e) {
      return response()->json(['status' => 500, 'error' => $e->getMessage()]);
    }
  }
}
