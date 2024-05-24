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
    Course_lesson,
    Tutorial_categories
}
;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class TutorialsController extends Controller
{
    public function GetTheAudiosList(Request $request)
    {
        try {
            $tutorial_categories = Tutorial_categories::with([
                'audios' => function ($query) {
                    $query->select(['ID', 'post_title', 'audio_category_id', 'post_content', 'post_name', 'post_parent', 'post_type', 'guid']); // Include only the columns you need
                }
            ])
                ->where('category_type', '=', 'audio')
                ->whereNull('deleted_at')
                ->orderBy('id', 'desc')
                ->get();

            $audio_array = $tutorial_categories->map(function ($category) {
                return [
                    'category_id' => $category->id,
                    'category_name' => $category->category_name,
                    'audios' => $category->audios
                ];
            });

            return response()->json([
                'status' => 200,
                'data' => $audio_array,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function GetTheVideosList(Request $request)
    {
        try {
            $tutorial_categories = Tutorial_categories::with([
                'videos' => function ($query) {
                    $query->select(['ID', 'post_title', 'audio_category_id', 'post_content', 'post_name', 'post_parent', 'post_type', 'guid']); // Include only the columns you need
                }
            ])
                ->where('category_type', '=', 'video')
                ->whereNull('deleted_at')
                ->orderBy('id', 'desc')
                ->get();

            $audio_array = $tutorial_categories->map(function ($category) {
                return [
                    'category_id' => $category->id,
                    'category_name' => $category->category_name,
                    'videos' => $category->videos
                ];
            });

            return response()->json([
                'status' => 200,
                'data' => $audio_array,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }

}
