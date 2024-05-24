<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    IndexController,
    ServicesController,
    TutorialsController,
    EnpaniController,
    DharmaSandhehaController,
    VolunteerController,
    KalakshepamController,
    RmOnlineUpanyasamController,
    MemberController,
    LakshmiKalyanamController,
    RdBookingController,
    ExtrasController,
    GnanakaithaaController,
    ContactusController,
    RmDonationController,
    CCAvenueGateway,
    AppExtrasController,
    VideoController
};
// use DB;
use Illuminate\Support\Facades\DB;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('updatecity', function(){
    // dd('test');
    $vol = DB::table('wpu6_users')
    ->select('ID', 'user_login')
    ->where('parent', '=', '0')
    ->where('user_login', 'LIKE', '%UDU%')
    ->get();

    $userIds = $vol->pluck('ID')->toArray();
    $det = DB::table('userprofile_dt')->whereIn('user_id', $userIds)->update(['city'=>'4347']);
    dd($det);
});

Route::get('updateparent', function(){
    $vol = DB::table('wpu6_users')
    ->select('ID', 'user_login')
    ->where('parent','0')
    ->get();

$i = 1;
    foreach ($vol as $user) {
            $det =  DB::table('wpu6_users')
            ->where('parent','=', $user->user_login)
            ->update(['parent' => $user->ID]);
            if($det){
                echo $i.'--yes'.'<br>';
            }else{
                echo $i.'--no'.'<br>';
            }
            $i++;
    }
});

Route::post('usercreate', [IndexController::class, 'UserRegister']);
Route::post('user-login', [IndexController::class, 'UserLogin'])->name('user-login.index');
Route::post('user-login-update', [IndexController::class, 'UserLoginUpdate']);
Route::post('book-the-rd-booking', [RdBookingController::class, 'StoreTheBookingFromRD']);
Route::post('forget-password', [IndexController::class, 'ForgetPassword']);
Route::post('verify-otp', [IndexController::class, 'otpVerify']);
Route::post('resend-otp', [IndexController::class, 'ResendOtp']);
Route::post('updated-otp', [IndexController::class, 'UpdatedOtp']);
Route::post('forgot-updated-password', [IndexController::class, 'ForgetUpdateNewPassword']);

Route::post('get-state', [ExtrasController::class, 'getState']);
Route::post('get-city', [ExtrasController::class, 'getCity']);
Route::post('get-religion', [ExtrasController::class, 'getReligion']);
Route::post('get-caste', [ExtrasController::class, 'getCaste']);
Route::post('send-enquiry', [ExtrasController::class, 'ContactFormSubmit']);
Route::post('education', [ExtrasController::class, 'getEducation']);
Route::post('occupation', [ExtrasController::class, 'getOccupation']);

Route::post('our-service', [ExtrasController::class, 'OurService']);
Route::post('home-image', [ExtrasController::class, 'HomeBannerImage']);
Route::post('about-us', [ExtrasController::class, 'AboutUs']);
Route::post('home-page-banner', [ExtrasController::class, 'HomePageBanner']);

Route::post('kinchit-service', [ExtrasController::class, 'KinchitServices']);

Route::post('get-services-list', [ServicesController::class, 'GetServicesList'])->name('get-services-list.services');
Route::post('get-services-contents-list', [ServicesController::class, 'GetServiceContentList'])->name('get-services-contents-list.services');

Route::post('get-tutorial-audios', [TutorialsController::class, 'GetTheAudiosList'])->name('get-tutorial-audios.tutorial');
Route::post('get-tutorial-videos', [TutorialsController::class, 'GetTheVideosList'])->name('get-tutorial-videos.tutorial');

Route::post('questions', [LakshmiKalyanamController::class, 'faq']);

Route::get('gallery-categories', [AppExtrasController::class, 'GalleryCategories']);
Route::post('gallery-category-images', [AppExtrasController::class, 'GalleryCategoryImages']);

Route::get('special-categories', [AppExtrasController::class, 'SpecialCategories']);
Route::post('special-category-contents', [AppExtrasController::class, 'SpecialCategoryContents']);

Route::post('kinchit-sanchika', [AppExtrasController::class, 'KinchitSanchika']);
Route::get('get-language', [AppExtrasController::class, 'KinchitSanchikaCategory']);

Route::post('notify-push', [AppExtrasController::class, 'notifyPush']);

Route::post('sent-sms', [RmDonationController::class, 'SentSMS']);
Route::post('sent-wa-doc', [ExtrasController::class, 'SentWADoc']);
    
Route::middleware(['user.check'])->group(function () {
    Route::post('update-password', [IndexController::class, 'UpdateNewPassword'])->name('update-password.index');
    Route::post('update-the-profile', [IndexController::class, 'UpdateTheProfile'])->name('update-the-profile.index');
    Route::post('my-profile', [IndexController::class, 'MyProfile']);
    Route::post('logout', [IndexController::class, 'logout']);
    Route::post('get-user-profile', [IndexController::class, 'GetTheUserProfile']);


    Route::post('get-enpani-audios', [EnpaniController::class, 'GetTheAudiosList'])->name('get-enpani-audios.enpani');

    Route::post('get-threads', [DharmaSandhehaController::class, 'GetThreadsList'])->name('get-threads.dharma');
    Route::post('get-replies', [DharmaSandhehaController::class, 'GetRepliesList'])->name('get-replies.dharma');
    Route::post('reply', [DharmaSandhehaController::class, 'Reply'])->name('get-replies.dharma');

    Route::post('get-active-members', [VolunteerController::class, 'GetActiveMembers'])->name('get-active-members.volunteer');
    Route::post('get-inactive-members', [VolunteerController::class, 'GetInActiveMembers'])->name('get-inactive-members.volunteer');
    Route::post('become-volunteer', [VolunteerController::class, 'RequestToBeAVolunteer'])->name('become-volunteer.volunteer');
    Route::post('add-member', [VolunteerController::class, 'AddMember']);
    Route::post('request-bank-challan', [VolunteerController::class, 'RequestChallan']);
    Route::post('member-update', [VolunteerController::class, 'MemberUpdate']);

    Route::post('get-courses', [KalakshepamController::class, 'GetTheCoursesList'])->name('get-courses.kalakshepam');
    Route::post('get-course-data', [KalakshepamController::class, 'GetTheCourseContent'])->name('get-course-data.kalakshepam');
    Route::post('get-lesson-data', [KalakshepamController::class, 'GetTheLessonContent'])->name('get-lesson-data.kalakshepam');
    Route::post('assign-course', [KalakshepamController::class, 'SriPancharatra']);
    Route::post('get-course-for-user', [KalakshepamController::class, 'GetTheCoursesForUser']);
    Route::post('get-ongoing-courses', [KalakshepamController::class, 'GetTheOngoingCoursesList'])->name('get-ongoing-courses.kalakshepam');
    Route::post('get-completed-courses', [KalakshepamController::class, 'GetTheCompletedCoursesList'])->name('get-completed-courses.kalakshepam');

    Route::post('get-upanyasam-audios', [RmOnlineUpanyasamController::class, 'GetTheUpanyasamAudioList'])->name('get-upanyasam-audios.kalakshepam');
    Route::post('get-upanyasam-videos', [RmOnlineUpanyasamController::class, 'GetTheUpanyasamVideoList'])->name('get-upanyasam-videos.kalakshepam');

    Route::post('get-volunteer-profile', [MemberController::class, 'GetVolunteerProfile'])->name('get-volunteer-profile.member');
    Route::post('get-volunteers', [MemberController::class, 'GetVolunteersList'])->name('get-volunteers.member');
    Route::post('change-the-volunteer', [MemberController::class, 'RequestChangeTheVolunteer'])->name('change-the-volunteer.member');
    Route::post('become-a-member', [MemberController::class, 'RequestToBeMember'])->name('become-a-member.member');
    Route::post('member-renewal', [MemberController::class, 'MemberRenewal']);
    
    Route::post('create-account', [LakshmiKalyanamController::class, 'CreateAccount']);
    Route::post('get-profile-data', [LakshmiKalyanamController::class, 'GetProfileData'])->name('get-profile-data.matri_x');
    Route::post('get-matched-profile', [LakshmiKalyanamController::class, 'GetMatchedProfiles'])->name('get-matched-profile.matri_x');
    Route::post('get-shortlisted-profile', [LakshmiKalyanamController::class, 'GetShortlistedProfiles'])->name('get-shortlisted-profile.matri_x');
    Route::post('get-success-stories', [LakshmiKalyanamController::class, 'GetSuccessStories'])->name('get-success-stories.matri_x');
    Route::post('get-addressviewed-profiles', [LakshmiKalyanamController::class, 'GetAddressViewedProfiles'])->name('get-addressviewed-profiles.matri_x');
    Route::post('get-myprofiles', [LakshmiKalyanamController::class, 'MyProfile']);
    Route::post('send-message', [LakshmiKalyanamController::class, 'SendMessage']);
    Route::post('message-list', [LakshmiKalyanamController::class, 'MessagesList']);
    Route::post('modify-myprofile', [LakshmiKalyanamController::class, 'ModifyMyProfile']);
    Route::post('basic-information', [LakshmiKalyanamController::class, 'BasicInformationModify']);
    Route::post('success-story', [LakshmiKalyanamController::class, 'SuccessStory']);
    Route::post('abuse-form', [LakshmiKalyanamController::class, 'AbuseForm']);
    Route::post('faq', [LakshmiKalyanamController::class, 'faq']);
    Route::post('my-photo-upload', [LakshmiKalyanamController::class, 'AddPhoto']);
    Route::post('verification-document', [LakshmiKalyanamController::class, 'VerificationDocument']);
    Route::post('rasi-amsam', [LakshmiKalyanamController::class, 'EditHoroscope']);
    Route::post('/search-lakshmi',  [LakshmiKalyanamController::class, 'Lakshmisearch']);
    Route::post('/get-inbox',  [LakshmiKalyanamController::class, 'InboxMessage']);
    Route::post('get-received-messages',  [LakshmiKalyanamController::class, 'ReceivedMessages']);  
    Route::post('/get-send-inbox',  [LakshmiKalyanamController::class, 'SendInboxMessage']);
    Route::post('/inbox-messages',[LakshmiKalyanamController::class,'InboxMessages']);
    Route::post('/get-received-inbox',  [LakshmiKalyanamController::class, 'ReceivedInboxMessage']);
    Route::post('/get-intrest-sender',  [LakshmiKalyanamController::class, 'ExpressIntrestSender']);
    Route::post('/get-intrest-receiver',  [LakshmiKalyanamController::class, 'ExpressIntrestReceiver']);
    Route::post('/delete-message',  [LakshmiKalyanamController::class, 'DeleteMessage']);
    Route::post('add-short-list',  [LakshmiKalyanamController::class, 'AddShortList']);
    Route::post('/get-view-profile',  [LakshmiKalyanamController::class, 'GetViewProfile']);
    Route::post('/horoscope-upload-document',  [LakshmiKalyanamController::class, 'UpdateHoroscopeDocument']);
   
    Route::post('gnanakaithaa', [GnanakaithaaController::class, 'index']);
    
    Route::post('rmsm-donation', [RmDonationController::class, 'RmDonation']);
    Route::post('rmsm-delivery-details', [RmDonationController::class, 'RmDeliveryDetails']);
    Route::post('deposit-details', [RmDonationController::class, 'RmDeposit']);
    Route::post('get-deposit-detail', [RmDonationController::class, 'GetDepositDetail']);
    Route::post('rmsm-deposit-details', [RmDonationController::class, 'RmDepositDetails']);
    Route::post('rmsm-deposit-details-update', [RmDonationController::class, 'RmDepositUpdate']);
    Route::post('get-donation-categories', [RmDonationController::class, 'DonationCategories']);
    
    Route::post('entry-payment-details', [CCAvenueGateway::class, 'StoreThePaymentDetails']);
    Route::post('initiate-payment-gateway', [CCAvenueGateway::class, 'ccavenueInitiate']);
    Route::post('finalstep-payment-gateway', [CCAvenueGateway::class, 'ccavenueSuccess']);
    Route::post('ccavenue-status', [CCAvenueGateway::class, 'ccavenueStatus']);

    Route::post('/video-watching-record', [VideoController::class, 'storeVideoTime']);
    
});


