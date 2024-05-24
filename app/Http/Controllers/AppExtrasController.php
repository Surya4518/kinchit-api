<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response as HttpStatusCode;
use App\Mail\MyMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Pusher\Pusher;

class AppExtrasController extends Controller
{
    public function GalleryCategories(Request $request){
        try{
        $gallery_cat = DB::table('gallery_category')
        ->where('status', '=', 'Active')
        ->whereNull('deleted_at')
        ->orderBy('sort_order','ASC')
        ->get();
        $arr = [];
        if($gallery_cat->isNotEmpty()){
            for($i = 0; $i < $gallery_cat->count(); $i++){
                $gallery_image = DB::table('gallery_images')
                ->where('category_id','=',$gallery_cat[$i]->id)
                ->get();
                $arr[] = [
                    'id' => $gallery_cat[$i]->id,
                    'category' => $gallery_cat[$i]->category_name,
                    'thumbimage' => $gallery_image[0]->image,
                     'description' => $gallery_image[0]->description
                    ];
            }
        }
        return response()->json(count($arr) ? ['status' => 200, 'data' => $arr] : ['status' => 400, 'message' => 'No Data..!']);
        }catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    public function GalleryCategoryImages(Request $request){
        try{
        $gallery_cat = DB::table('gallery_category')
        ->where('id','=',$request->cat_id)
        ->where('status', '=', 'Active')
        ->whereNull('deleted_at')
        ->get();
        $gallery_images = DB::table('gallery_images')
        ->where('category_id','=',$request->cat_id)
        ->where('status', '=', 'Active')
        ->whereNull('deleted_at')
        ->orderBy('sort_order','ASC')
        ->get();
        return response()->json($gallery_images->isNotEmpty() ? ['status' => 200, 'Category Data' => $gallery_cat, 'data' => $gallery_images] : ['status' => 400, 'message' => 'No Data..!']);
        }catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    public function GetLanguages(Request $request){
        try{
        $language = DB::table('language')
            ->whereNull('deleted_at')
            ->orderBy('sort_order', 'ASC')
            ->get();
        return response()->json($language->isNotEmpty() ? ['status' => 200, 'data' => $language] : ['status' => 400, 'message' => 'No Data..!']);
        }catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    public function KinchitSanchika(Request $request){
        try{
        $sanchika_cat = DB::table('sanchika_category')
            ->whereNull('deleted_at')
            ->orderBy('sort_order', 'ASC')
            ->get();

        $data = [];
        foreach ($sanchika_cat as $category) {
            if ($sanchika_cat->isNotEmpty()) {
                $data[] = [
                    'category_id' => $category->id,
                    'category_name' => $category->category_name,
                    'sanchika_data' => $this->getSanchika($category->id),
                ];
            }
        }
        return response()->json(!empty($data) ? ['status' => 200, 'data' => $data] : ['status' => 400, 'message' => 'No Data..!']);
        }catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    public function KinchitSanchikaPDF(Request $request){
        try{
        $sanchika = DB::table('kinchit_sanchika')
            ->whereNull('deleted_at')
            ->orderBy('sort_order', 'ASC')
            ->get();
        return response()->json($sanchika->isNotEmpty() ? ['status' => 200, 'data' => $sanchika] : ['status' => 400, 'message' => 'No Data..!']);
        }catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    public function SanchikaPDFByLanguage(Request $request){
        try{
        $sanchika = DB::table('kinchit_sanchika')
            ->select('kinchit_sanchika.*', 'sanchika_category.category_name', 'language.name as language_name')
            ->leftJoin('sanchika_category', 'kinchit_sanchika.category_id', '=', 'sanchika_category.id')
            ->leftJoin('language', 'kinchit_sanchika.language', '=', 'language.id')
            ->when($request->category_id, function ($query) use ($request) {
                return $query->where('kinchit_sanchika.category_id', $request->category_id);
            })
            ->when($request->language_id, function ($query) use ($request) {
                return $query->where('kinchit_sanchika.language', $request->language_id);
            })
            ->whereNull('kinchit_sanchika.deleted_at')
            ->orderBy('kinchit_sanchika.id','desc')
            ->get();
        return response()->json($sanchika->isNotEmpty() ? ['status' => 200, 'data' => $sanchika] : ['status' => 400, 'message' => 'No Data..!']);
        }catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    function getSanchika($value){
        return  DB::table('kinchit_sanchika')
        ->select('kinchit_sanchika.title', 'kinchit_sanchika.pdf_url', 'language.name as language')
        ->leftjoin('language', 'language.id', '=', 'kinchit_sanchika.language')
        ->where('kinchit_sanchika.category_id',$value)
        ->whereNull('kinchit_sanchika.deleted_at')
        ->get();
    }
    
    public function SpecialCategories(Request $request){
        try{
        $special_cat = DB::table('special_pro_category')
        ->where('status', '=', 'Active')
        ->whereNull('deleted_at')
        ->orderBy('sort_order','ASC')
        ->get();
        return response()->json($special_cat->isNotEmpty() ? ['status' => 200, 'data' => $special_cat] : ['status' => 400, 'message' => 'No Data..!']);
        }catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    public function SpecialCategoryContents(Request $request){
        try{
        $special_cat = DB::table('special_pro_category')
        ->where('id','=',$request->cat_id)
        ->where('status', '=', 'Active')
        ->whereNull('deleted_at')
        ->get();
        $special_contents = DB::table('special_pro_contents')
        ->where('category_id','=',$request->cat_id)
        ->where('status', '=', 'Active')
        ->whereNull('deleted_at')
        ->orderBy('sort_order','ASC')
        ->get();
        return response()->json($special_contents->isNotEmpty() ? ['status' => 200, 'Category Data' => $special_cat, 'data' => $special_contents] : ['status' => 400, 'message' => 'No Data..!']);
        }catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        } 
    }
    
    public function notifyPush(Request $request){
        $userId = $request->user_id;
        $message = $request->message;
        $title = $request->title;

        $result = $this->SendPushNotification($userId, $message, $title);

        if ($result) {
            // Notification sent successfully
            return response()->json(['success' => true]);
        } else {
            // Failed to send notification
            return response()->json(['success' => false]);
        }
    }
    
    function SendPushNotification($userId = NULL, $message = NULL, $title = NULL){
        
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        $fcmToken = \Illuminate\Support\Facades\DB::table('wpu6_users')
            ->where('ID', '=', $userId)
            ->value('fcm_token');
        
        if (!$fcmToken) {
            return false;
        }
        
        $notification = [
            'title' => $title ?: 'Test',
            'body' => $message
        ];
        
        $extraData = [
            'message' => $notification,
            'moredata' => 'Nothing'
        ];
        
        $fcmNotification = [
            'to' => $fcmToken,
            'notification' => $notification,
            'data' => $extraData
        ];
        
        $response = Http::withHeaders([
            'Authorization' => 'key='.env('FIREBASE_API_KEY'),
            'Content-Type' => 'application/json',
        ])
        ->post($fcmUrl, $fcmNotification);
        
        echo $response->body();

    }
    
    public function notifyUsePush(Request $request){
        
        try{
        
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        $tokens = \Illuminate\Support\Facades\DB::table('wpu6_users')
        ->select('fcm_token')
        ->where('user_type', '=', $request->user_type)
        ->where('fcm_token', '!=', '')
        ->whereNull('deleted_at')
        // ->value('fcm_token');
        ->get();
        $fcmTokens = $tokens->pluck('fcm_token')->toArray();
        $fcmToken = implode(', ', $fcmTokens);
        
        if (!$fcmToken) {
            return false;
        }
        
        $title = $request->title;
        $message = $request->message;
        $image = 'http://kinchit-front.kinchit.org/public/assets/images/kalakshepam/nammazhwar.jpg';
        
        $notification = [
            'title' => $title ?: 'Test',
            'body' => $message,
            'image' => $image,
        ];
        
        $extraData = [
            'message' => $notification,
            'moredata' => 'Nothing'
        ];
        
        $fcmNotification = [
            // 'to' => $fcmToken,
            'registration_ids' => $fcmTokens,
            'notification' => $notification,
            'data' => $extraData
        ];
        
        // dd($fcmNotification);
        
        $response = Http::withHeaders([
            'Authorization' => 'key='.env('FIREBASE_API_KEY'),
            'Content-Type' => 'application/json',
        ])
        ->post($fcmUrl, $fcmNotification);
        
        echo $response->body();
        
        
            
        }catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }

    }
    
    // public function notifyUsePusher(Request $request){
    //     $message = 'Hi! Welcome.';

    //     $options = array(
    //         'cluster' => env('PUSHER_APP_CLUSTER')
    //     );

    //     $pusher = new Pusher(
    //         env('PUSHER_APP_KEY'),
    //         env('PUSHER_APP_SECRET'),
    //         env('PUSHER_APP_ID'),
    //         $options
    //     );

    //     $pusher->trigger('tracking-notification', 'live-event', $message);

    //     $response = [
    //         'request_status' => true
    //     ];

    //     if($response){
    //         return response($response, HttpStatusCode::HTTP_OK);
    //     }

    // }
    
}
