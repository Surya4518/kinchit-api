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
    Rm_online_upanyasam,
    Tutorial_categories
}
;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class RmOnlineUpanyasamController extends Controller
{
    public function GetTheUpanyasamAudioList(Request $request)
    {
        try {
            $tutorial_categories = Tutorial_categories::with([
                'upanyasamaudios' => function ($query) {
                    $query->select(['id', 'post_author', 'category_id', 'post_title', 'post_slug', 'post_content', 'download_url', 'post_type', 'structure_type', 'parent_id']); // Include only the columns you need
                }
            ])
                ->where('category_type', '=', 'rm-online-upanyasam-audio')
                ->whereNull('deleted_at')
                ->orderBy('id', 'asc')
                ->get();
            $audio_array = $tutorial_categories->map(function ($category) {
                return [
                    'category_id' => $category->id,
                    'category_name' => $category->category_name,
                    'category_slug' => $category->category_slug,
                    'audios' => $category->upanyasamaudios
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

    public function GetTheUpanyasamVideoList(Request $request)
    {
        try {
            $tutorial_categories = Tutorial_categories::with([
                'upanyasamvideos' => function ($query) {
                    $query->select(['id', 'post_author', 'category_id', 'post_title', 'post_slug', 'post_content', 'download_url', 'post_type', 'structure_type', 'parent_id']); // Include only the columns you need
                }
            ])
                ->where('category_type', '=', 'rm-online-upanyasam-video')
                ->whereNull('deleted_at')
                ->orderBy('id', 'asc')
                ->get();
            $video_array = $tutorial_categories->map(function ($category) {
                return [
                    'category_id' => $category->id,
                    'category_name' => $category->category_name,
                    'category_slug' => $category->category_slug,
                    'videos' => $category->upanyasamvideos
                ];
            });

            return response()->json([
                'status' => 200,
                'data' => $video_array,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
