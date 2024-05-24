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
    DharmaSandheha,
    User
}
;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class DharmaSandhehaController extends Controller
{
   public function GetThreadsList(Request $request)
    {
        try {
            $threads = DharmaSandheha::with([
                'user' => function ($query) {
                    $query->select(['ID', 'parent', 'user_email', 'display_name', 'user_type']); // Include only the columns you need
                },
                'userdetails' => function ($query) {
                    $query->select(['user_image','user_id']); // Include only the columns you need
                }
            ])
                ->where('post_type', '=', $request->type)
                ->whereNull('deleted_at')
                ->orderBy('ID', 'desc')
                ->get();
            $thread_array = $threads->map(function ($thread) {
                return $thread;
            });
            return response()->json([
                'status' => 200,
                'data' => $thread_array,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function GetRepliesList(Request $request)
    {
        try {
            $thread = DharmaSandheha::where('post_type', 'LIKE', 'thread')
                ->where('post_name', '=', $request->url)
                ->whereNull('deleted_at')
                ->orderBy('ID', 'DESC')
                ->get();
                // dd($thread);
                if($thread->count() > 0){
            $replies = DharmaSandheha::with([
                'user' => function ($query) {
                    $query->select(['ID', 'parent', 'user_email', 'display_name', 'user_type']); // Include only the columns you need
                },
                 'userdetails' => function ($query) {
                    $query->select(['user_image','user_id']); // Include only the columns you need
                }
            ])
                ->where('post_type', '=', $request->type)
                ->where('post_parent', '=', $thread[0]->ID)
                ->whereNull('deleted_at')
                ->orderBy('ID', 'desc')
                ->get();
            $reply_array = $replies->map(function ($reply) {
                return $reply;
            });
            return response()->json([
                'status' => 200,
                'data' => $reply_array,
                'thread' => $thread,
            ]);
                }else{
                    return response()->json([
                'status' => 400,
                'message' => 'Thread not found'
            ]);
                }
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
     public function Reply(Request $request){
        try {
            $ses_user = Session::get('user');
            $user = DB::table('wpu6_users')->where('ID', $ses_user[0]->ID)->first();
    
            $thread = DharmaSandheha::where('post_type', 'LIKE', 'thread')
                ->where('post_name', '=', $request->url)
                ->whereNull('deleted_at')
                ->orderBy('ID', 'DESC')
                ->first(); // Use first() instead of get() to get a single result
    
            if ($thread) { // Check if $thread is not null
    
                $arr = [
                    'post_author' =>  $user->ID,
                    'post_date' => date("Y-m-d H:i:s"),
                    'post_date_gmt' => date("Y-m-d H:i:s", strtotime($thread->post_date_gmt)),
                    'post_content' => $request->postcontent,
                    'post_title' => $thread->post_title,
                    'post_excerpt' => $thread->post_excerpt,
                    'post_status' => $thread->post_status,
                    'comment_status' => $thread->comment_status,
                    'ping_status' => $thread->ping_status,
                    'post_password' => $thread->post_password,
                    'post_name' => $request->url,
                    'to_ping' => $thread->to_ping,
                    'pinged' => $thread->pinged,
                    'post_modified' => $thread->post_modified,
                    'post_modified_gmt' => $thread->post_modified_gmt,
                    'post_content_filtered' => $thread->post_content_filtered,
                    'post_parent' => $thread->ID,
                    'guid' => $thread->guid,
                    'menu_order' => $thread->menu_order,
                    'post_type' => $request->type,
                    'post_mime_type' => $thread->post_mime_type,
                    'comment_count' => $thread->comment_count,
                ];
    
                 DB::table('wphy_posts')->insert($arr);
    
                return response()->json([
                    'status' => 200,
                    'data' =>  $thread,
                    'replies' =>  $arr,
                    'message' => 'Successfully.',
                ]);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'No thread found.',
                ]);
            }
    
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
