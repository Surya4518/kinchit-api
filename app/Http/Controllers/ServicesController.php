<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use DB;
use Exception;
use Session;
use Storage;
use Mail;
use View;
use Response;
use Carbon\{Carbon};
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use App\Common;
use App\Models\{
    User
};
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class ServicesController extends Controller
{
    public function GetServicesList(Request $request)
    {
        try {
            $data = DB::table('services_articles')->where('status', '=', '0')->whereNull('deleted_at')->get();
            return response()->json(['status' => 200, 'data' => $data]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function GetServiceContentList(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'service_id' => 'required|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 401, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
            }
            $article = DB::table('services_articles')->where('page_slug', '=', $request->service_id)->where('status', '=', '0')->whereNull('deleted_at')->get();
            $select = DB::table('services_article_contents')->where('status', '=', '0')->where('service_id', '=', $article[0]->id)->whereNull('deleted_at')->get();
            $data = [];
            for ($i = 0; $i < $select->count(); $i++) {
                $select1 = DB::table('services_article_content_images')->where('service_content_id', '=', $select[$i]->id)->whereNull('deleted_at')->get();
                $images = [];
                foreach ($select1 as $key => $value) {
                    $images[] = $value->image;
                }
                $data[] = [
                    'title' => $select[$i]->title,
                    'description' => $select[$i]->description,
                    'images' => $images,
                ];
            }
            return response()->json(['status' => 200, 'data' => $data, 'service_title' => $article[0]->service_title]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }

}
