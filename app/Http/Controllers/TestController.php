<?php


namespace App\Http\Controllers;

use App\News;
use Carbon\Carbon;
use Faker\Provider\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Intervention\Image\Filters\DemoFilter;


class TestController extends Controller
{
    public function image_resize(Request $request){
        $url = $request->input('file_url');

        $img = Image::make(file_get_contents($url));
        $ht = $img->height() < 400 ? $img->height(): 400;
        $img->heighten($ht, function ($constraint) {
            $constraint->upsize();
        });
        $path = 'image_urls/'.Uuid::uuid().'.jpg';
        Storage::disk('s3')->put($path, $img->stream('jpg', 50));
        return response()->json($path, 200, [], JSON_PRETTY_PRINT);
//        return response()->json('Test', 200, [], JSON_PRETTY_PRINT);
    }

    public function delete_s3_image(Request $request){
        Storage::disk('s3')->delete($request->input('path'));
    }
    public function timestamp(Request $request)
    {
        $data = News::where('published_time', '>=', Carbon::now()->subWeek(1))->get();
        return response()->json($data, 200, [], JSON_PRETTY_PRINT);
    }
}


