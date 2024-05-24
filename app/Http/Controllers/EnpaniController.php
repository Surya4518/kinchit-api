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
    Course_lesson
}
;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class EnpaniController extends Controller
{
    public function GetTheAudiosList(Request $request)
    {
        try {
            $categories = Course_lesson::where('post_type', 'LIKE', 'kinchit-en-pani')
                ->whereNull('deleted_at')
                ->orderBy('ID', 'asc')
                ->get();

            $audio_array = [];
            foreach ($categories as $category) {
                $audios = Course_lesson::whereRaw("JSON_CONTAINS(audio_parent->'$[*]', ?)", ['"' . $category->ID . '"'])
                    ->where('post_type', 'LIKE', 'kinchit-en-pani-audio')
                    ->orderBy('ID', 'asc')
                    ->whereNull('deleted_at')
                    ->get();
                $audio_array[] = [
                    'count' => $audios->count(),
                    'parent_id' => $category->ID,
                    'parent_title' => $category->post_title,
                    'audios' => $audios
                ];
            }
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
