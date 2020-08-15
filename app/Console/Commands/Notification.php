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
            $res = $this->send_notification($news_single['id'], $news_single['headline'], substr($news_single['summary'], 0, 100));
            $this->info($res);
        }


    }

    private function send_notification($news_id, $heading, $content){
        $client = new Client(['base_uri' => 'https://coronavirus-monitor.p.rapidapi.com/', 'timeout'  => 15.0, ]);
        try {

            $res = $client->request('POST', 'coronavirus/worldstat.php', [
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Authorization' => env('ONE_SIGNAL_API_KEY')
                ],
                'json' => json_encode([
                    'app_id' => env('ONE_SIGNAL_APP_ID'),
                    'included_segments'=> ["All"],
                    'data' => [ 'news_id' => $news_id],
                    'headings' => ['en' => $heading],
                    'contents' => ['en' => $content],
                    'android_accent_color' => 'FF3A89FF',
                    'huawei_accent_color' => 'FF3A89FF'

                ])

            ]);
            return $res->getStatusCode();

        } catch (ClientException $exception){
            $this->info($res->getStatusCode());
            $this->info($res->getBody()->getContents());
        }
    }


}
