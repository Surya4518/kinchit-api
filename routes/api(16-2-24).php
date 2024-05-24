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
    ContactusController
};

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
    dd('test');
});

Route::post('usercreate', [IndexController::class, 'UserRegister']);
Route::post('user-login', [IndexController::class, 'UserLogin'])->name('user-login.index');
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

Route::post('get-services-list', [ServicesController::class, 'GetServicesList'])->name('get-services-list.services');
Route::post('get-services-contents-list', [ServicesController::class, 'GetServiceContentList'])->name('get-services-contents-list.services');

Route::post('get-tutorial-audios', [TutorialsController::class, 'GetTheAudiosList'])->name('get-tutorial-audios.tutorial');
Route::post('get-tutorial-videos', [TutorialsController::class, 'GetTheVideosList'])->name('get-tutorial-videos.tutorial');

Route::post('faq', [LakshmiKalyanamController::class, 'faq']);
     
Route::middleware(['user.check'])->group(function () {
    Route::post('update-password', [IndexController::class, 'UpdateNewPassword'])->name('update-password.index');
    Route::post('update-the-profile', [IndexController::class, 'UpdateTheProfile'])->name('update-the-profile.index');
    Route::post('my-profile', [IndexController::class, 'MyProfile']);
    Route::post('logout', [IndexController::class, 'logout']);


    Route::post('get-enpani-audios', [EnpaniController::class, 'GetTheAudiosList'])->name('get-enpani-audios.enpani');

    Route::post('get-threads', [DharmaSandhehaController::class, 'GetThreadsList'])->name('get-threads.dharma');
    Route::post('get-replies', [DharmaSandhehaController::class, 'GetRepliesList'])->name('get-replies.dharma');

    Route::post('get-active-members', [VolunteerController::class, 'GetActiveMembers'])->name('get-active-members.volunteer');
    Route::post('get-inactive-members', [VolunteerController::class, 'GetInActiveMembers'])->name('get-inactive-members.volunteer');
    Route::post('become-volunteer', [VolunteerController::class, 'RequestToBeAVolunteer'])->name('become-volunteer.volunteer');
    Route::post('add-member', [VolunteerController::class, 'AddMember']);

    Route::post('get-courses', [KalakshepamController::class, 'GetTheCoursesList'])->name('get-courses.kalakshepam');
    Route::post('get-course-data', [KalakshepamController::class, 'GetTheCourseContent'])->name('get-course-data.kalakshepam');
    Route::post('get-lesson-data', [KalakshepamController::class, 'GetTheLessonContent'])->name('get-lesson-data.kalakshepam');

    Route::post('get-upanyasam-audios', [RmOnlineUpanyasamController::class, 'GetTheUpanyasamAudioList'])->name('get-upanyasam-audios.kalakshepam');
    Route::post('get-upanyasam-videos', [RmOnlineUpanyasamController::class, 'GetTheUpanyasamVideoList'])->name('get-upanyasam-videos.kalakshepam');

    Route::post('get-volunteer-profile', [MemberController::class, 'GetVolunteerProfile'])->name('get-volunteer-profile.member');
    Route::post('get-volunteers', [MemberController::class, 'GetVolunteersList'])->name('get-volunteers.member');
    Route::post('change-the-volunteer', [MemberController::class, 'RequestChangeTheVolunteer'])->name('change-the-volunteer.member');
    

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
   

    Route::post('gnanakaithaa', [GnanakaithaaController::class, 'index']);
    
    
});


