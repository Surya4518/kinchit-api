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
use DateTime;
use Illuminate\Support\Facades\Mail;
use View;
use Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use App\Common;
use App\Models\{
   Course_lesson,
    course_log,
    User
}
;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class KalakshepamController extends Controller
{
    public function GetTheCoursesList(Request $request)
    {
        try {
            $courses = Course_lesson::where('post_type', '=', 'namaste_course')
                ->whereNull('deleted_at')
                ->orderBy('ID', 'desc')
                ->get();

            $courses_array = $courses->map(function ($course) {
                return $course;
            });

            return response()->json([
                'status' => 200,
                'count' => $courses_array->count(),
                'data' => $courses_array,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }

    }
    
    public function GetTheOngoingCoursesList(Request $request)
    {
        try {
            $courses = Course_lesson::where('post_type', '=', 'namaste_course')
                ->where('course_status','=','ongoing')
                ->whereNull('deleted_at')
                ->orderBy('ID', 'desc')
                ->get();

            $courses_array = $courses->map(function ($course) {
                return $course;
            });

            return response()->json([
                'status' => 200,
                'count' => $courses_array->count(),
                'data' => $courses_array,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }

    }
    
    public function GetTheCompletedCoursesList(Request $request)
    {
        try {
            $courses = Course_lesson::where('post_type', '=', 'namaste_course')
                ->where('course_status','=','completed')
                ->whereNull('deleted_at')
                ->orderBy('ID', 'desc')
                ->get();

            $courses_array = $courses->map(function ($course) {
                return $course;
            });

            return response()->json([
                'status' => 200,
                'count' => $courses_array->count(),
                'data' => $courses_array,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }

    }

    public function GetTheCourseContent(Request $request)
    {
        try {
            $course = Course_lesson::where('post_type', '=', 'namaste_course')
                ->where('post_name', '=', $request->url)
                ->whereNull('deleted_at')
                ->orderBy('ID', 'desc')
                ->get();
            $lessons = Course_lesson::where('post_type', '=', 'namaste_lesson')
                ->where('course_id', 'LIKE', $course[0]->ID)
                ->whereNull('deleted_at')
                ->orderBy('ID', 'desc')
                ->get();
            $array = [
                'course' => $course,
                'lessons' => $lessons
            ];

            return response()->json([
                'status' => 200,
                'count' => $lessons->count(),
                'data' => $array,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }

    }

    public function GetTheLessonContent(Request $request)
    {
        try {
                dd($request->all());
            $lesson = Course_lesson::where('post_type', '=', 'namaste_lesson')
                ->where('post_name', '=', $request->url)
                ->whereNull('deleted_at')
                ->orderBy('ID', 'desc')
                ->get();
            $course = Course_lesson::where('post_type', '=', 'namaste_course')
                ->where('ID', '=', $lesson[0]->course_id)
                ->whereNull('deleted_at')
                ->orderBy('ID', 'desc')
                ->get();

            return response()->json([
                'status' => 200,
                'count' => $lesson->count(),
                'course' => $course[0]->post_title,
                'data' => $lesson,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }

    }
    
     public function SriPancharatra(Request $request)
    {
        try {
            $ses_user = Session::get('user');
            $validator = Validator::make($request->all(), [
                'student_name' => 'required|string|max:255',
                'father_name' => 'required|string|max:255',
                'acharyan' => 'required|string|max:255',
                'name_of_serving' => 'required|string|max:255',
                'full_address_serving' => 'required|string|max:255',
                'education_details' => 'required|string|max:255',
                'phone_number' => 'required|regex:/^(\+\d{1,3}[- ]?)?(\d{10})$/|max:15',
                'email' => 'required|email',
                'address_line1' => 'required|string|max:255',
                'address_line2' => 'required|string|max:255',
              
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
            }

            $user = User::where('ID', $ses_user[0]->ID)->first();

            $post = DB::table('wpu6_posts')->where('post_name', $request->url)->where('post_type', 'namaste_course')->first();
            $assigncourse = [
                'name_of_student' =>$request->student_name,
                'father_name' =>$request->father_name,
                'acharyan' =>$request->acharyan,
                'name_of_serving' =>$request->name_of_serving,
                'full_address_serving' =>$request->full_address_serving,
                'education_details' =>$request->education_details,
                'phone_number' =>$request->phone_number,
                'email' =>$request->email,
                'address_line1' =>$request->address_line1,
                'address_line2' =>$request->address_line2,
                'course_id' =>$post->ID,
                'user_id' =>$user->ID,
            ];
            Mail::to($request->email)->send(new MyMail(null, null, null, null, null, null, null, null,$assigncourse));
            $data = course_log::insert($assigncourse);

           
            $arr1 = [
                'course_id' => $post->ID,
                'user_id' =>$user->ID,
                'status' =>'pending',
            ];
            $data1 = DB::table('wpu6_namaste_student_courses')->insert($arr1);
            return response()->json(['status' => 200, 'message' => 'Created Successfully' , 'data'=> $data]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    public function GetTheCoursesForUser(Request $request)
    {
        try {
            $ses_user = Session::get('user');
            // $assigned_course = DB::table('wpu6_namaste_student_courses.')
            //     ->select('wpu6_namaste_student_courses.')
            //     ->where('user_id', $ses_user[0]->ID)
            //     ->whereNull('deleted_at')
            //     ->orderByDesc('id')
            //     ->get();
            $courses = DB::table('wpu6_namaste_student_courses')
                   ->select(
                       'wpu6_namaste_student_courses.*',
                       'wpu6_posts.ID as post_id',
                       'wpu6_posts.post_title as post_title',
                       'wpu6_posts.post_name as post_name',
                       'wpu6_posts.post_content as post_content',
                       'wpu6_posts.post_author as post_author',
                       'wpu6_posts.guid as guid',
                       'wpu6_posts.upanyasam_name as upanyasam_name',
                       'wpu6_posts.tutor_name as tutor_name'
                   )
                   ->leftJoin('wpu6_posts', 'wpu6_posts.ID', '=', 'wpu6_namaste_student_courses.course_id')
                   ->where('wpu6_namaste_student_courses.user_id', $ses_user[0]->ID)
                   ->orderBy('wpu6_namaste_student_courses.id', 'desc')
                   ->get();
                // dd($courses);
            $courses_array = $courses->map(function ($course) {
                return $course;
            });

            return response()->json([
                'status' => 200,
                'count' => $courses_array->count(),
                'data' => $courses_array,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }

    }
    
}
