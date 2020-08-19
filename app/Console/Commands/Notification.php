<?php
namespace App\Console\Commands;


use App\News;
use App\User;
use Carbon\Carbon;
use Faker\Provider\Uuid;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class Notification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:create';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notification Create Auto';

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
        $news = News::orderBy('published_time', 'desc')->take(5)->get();
        $this->info('Starting to send');
        foreach ($news as $news_single) {
            $response = $this->send_notification($news_single['id'], $news_single['headline'], substr($news_single['summary'], 0, 100));
            $return["allresponses"] = $response;
            $return = json_encode($return);

            $data = json_decode($response, true);
            print_r($data);
            $id = $data['id'];
            print_r($id);

            print("\n\nJSON received:\n");
            print($return);
            print("\n");
        }
//        $res = $this->send_notification(268416, 'বঙ্গবন্ধুকে নিয়ে প্রথম ভার্চুয়াল প্রদর্শনী শুরু', 'জাতীয় শোক দিবস পালন এবং জাতির পিতা বঙ্গবন্ধু শেখ মুজিবুর রহমানের জন্মশতবার্ষিকী উদযাপন উপলক্ষে শুক্রবার বঙ্গবন্ধুকে নিয়ে প্রথমবারের মতো ভার্চুয়াল আর্ট, ফটোগ্রাফি ও মাল্টিমিডিয়া প্রদর্শনী ‘ব্রেভ হার্ট’ শুরু হয়েছে। দেশের ৪২ প্রখ্যাত শিল্পীর কাজ নিয়ে গ্যালারি কসমস ');
//        $this->info($res);

    }

    private function send_notification($news_id, $heading, $content){

        $fields = array(
            'app_id' => '70759d02-334a-4146-970a-f838033b8ab6',
            'included_segments' => array(
                'All'
            ),
            'data' => array(
                "news_id" => $news_id
            ),
            'headings' => array(
                "en" => $heading
            ),
            'contents' => array(
                "en" => $content
            ),
            'android_accent_color' => 'FF3A89FF',
            'huawei_accent_color' => 'FF3A89FF'
        );

        $fields = json_encode($fields);
        print("\nJSON sent:\n");
        print($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic YzFkMDgwNjUtMTFmOS00MTgwLWFhMjItMTk4MGE4MjJmNGQx'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;





//        $client = new Client(['base_uri' => 'https://onesignal.com/', 'timeout'  => 15.0, ]);
//        try {
//
//            $res = $client->request('POST', 'api/v1/notifications', [
//                'headers' => [
//                    'Content-Type' => 'application/json; charset=utf-8',
//                    'Authorization' => env('ONE_SIGNAL_API_KEY')
//                ],
//                'body' => json_encode([
//                    'app_id' => "70759d02-334a-4146-970a-f838033b8ab6",
//                    'included_segments'=> ["All"],
//                    'data' => [ 'news_id' => $news_id],
//                    'headings' => ['en' => $heading],
//                    'contents' => ['en' => $content],
//                    'android_accent_color' => 'FF3A89FF',
//                    'huawei_accent_color' => 'FF3A89FF'
//
//                ])
//
//            ]);
//            return $res->getStatusCode();
//
//        } catch (ClientException $exception){
//            $res = $exception->getResponse();
//            $this->info($res->getStatusCode());
//            $this->info($res->getBody()->getContents());
//        }
    }




}
