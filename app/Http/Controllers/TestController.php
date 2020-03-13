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

//        $filename = Uuid::uuid().'.jpg';
//        $tempfile = tempname(sys_get_temp_dir(), $filename);
//        copy($url, $tempfile);

        // create curl resource
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        $output = curl_exec($ch);
        curl_close($ch);

        $img = Image::make(base64_encode($output));
        $ht = $img->height() < 400 ? $img->height(): 400;
        $img->heighten($ht, function ($constraint) {
            $constraint->upsize();
        });
        $path = 'test/'. Uuid::uuid().'.jpg';
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

    public function get_s3_summary(){
        $latestNewsModels = News::where('published_time', '>=', Carbon::now()->subDay(4))
            ->where('image_url', '<>', '')
            ->where('s3_image_url', '=', null)
            ->get();
        $oldNewsModels = News::where('published_time', '<', Carbon::now()->subWeek(5))
            ->where('s3_image_url', '<>', null)
            ->get();
        return response()->json($latestNewsModels->count().' '.$oldNewsModels->count(), 200, [], JSON_PRETTY_PRINT);

    }
}


