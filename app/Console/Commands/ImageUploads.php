<?php
namespace App\Console\Commands;
use App\Employee;
use App\Http\Controllers\DateTimeController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanScheduleController;
use App\Loan;
use App\LoanProduct;
use App\LoanSchedule;
use App\Member;
use App\News;
use App\SavingRspm;
use App\Shamity;
use App\TransferHistory;
use App\User;
use Carbon\Carbon;
use Faker\Provider\Uuid;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImageUploads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'image:uploads';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Image Uploading Task to S3';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $latestNewsModels = News::where('published_time', '>=', Carbon::now()->subDays(4))
            ->where('image_url', '<>', '')
            ->where('s3_image_url', '=', null)
            ->orderByDesc('id')
            ->get();
        $oldNewsModels = News::where('published_time', '<', Carbon::now()->subDays(5))
            ->where('s3_image_url', '<>', null)
            ->get();
        foreach ($oldNewsModels as $news){
            $this->deleteImage($news->s3_image_url);
            $news->update([
                's3_image_url' => null
            ]);
        }
        foreach ($latestNewsModels as $news){
            $url = $this->storeImage($news->image_url);
            if(!$url) continue;
            $news->update([
                's3_image_url' => $url
            ]);
        }

    }

    private function storeImage($external_url){
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $external_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
            $output = curl_exec($ch);
            curl_close($ch);

            $img = Image::make(base64_encode($output));
            $ht = $img->height() < 400 ? $img->height(): 400;
            $img->heighten($ht, function ($constraint) {
                $constraint->upsize();
            });
            $path = 'image_urls/'.Uuid::uuid().'.jpg';
            Storage::disk('s3')->put($path, $img->stream('jpg', 50));
            return $path;
        }
        catch (\Exception $e){
            return false;
        }

    }
    private function deleteImage($s3_path){
        Storage::disk('s3')->delete($s3_path);
    }
}
