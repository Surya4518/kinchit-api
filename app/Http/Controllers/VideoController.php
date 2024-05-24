<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class VideoController extends Controller
{
    public function storeVideoTime(Request $request)
    {
        $ses_user = Session::get('user');
        $videoRecord = DB::table('videowatchingrecord')->where('user_id', $ses_user[0]->ID)
            ->where('video_name', $request->title)
            ->first();
    
        if (!$videoRecord) {
            // Insert a new record
            if ($request->starsec <= $request->totalsec) {
            $videoRecordId = DB::table('videowatchingrecord')->insertGetId([
                'user_id' => $ses_user[0]->ID,
                'video_name' => $request->title,
                'start_sec' => $request->starsec,
                'total_sec' => $request->totalsec,
            ]);
            $videoRecord1 = DB::table('videowatchingrecord')->where('user_id', $ses_user[0]->ID)
            ->where('video_name', $request->title)
            ->first();
            return response()->json(['data' => $videoRecord1, 'message' => 'Data stored successfully']);
        }else {
            return response()->json(['message' => 'The Video Sec\'s Over Loaded']);
        }
        } else {
            // Update the existing record
            if ($request->starsec <= $videoRecord->total_sec) {
                DB::table('videowatchingrecord')
                    ->where('user_id', $ses_user[0]->ID)
                    ->where('video_name', $request->title)
                    ->update([
                        'start_sec' => $request->starsec,
                        'total_sec' => $request->totalsec,
                    ]);
                    $videoRecord2 = DB::table('videowatchingrecord')->where('user_id', $ses_user[0]->ID)
                    ->where('video_name', $request->title)
                    ->first();
                return response()->json(['data' => $videoRecord2, 'message' => 'Data updated successfully']);
            } else {
                return response()->json(['message' => 'The Video Sec\'s Over Loaded']);
            }
        }
    }
    
}
