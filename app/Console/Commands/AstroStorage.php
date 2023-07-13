<?php

namespace App\Console\Commands;

use App\Models\Astros;
use Carbon\Carbon;
use GuzzleHttp\Pool;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\DomCrawler\Crawler;

class AstroStorage extends Command
{


    protected $urls = [
        'https://www.1212.com/luck/aries/20230713.html',
        'https://www.1212.com/luck/taurus/20230713.html',
        'https://www.1212.com/luck/gemini/20230713.html',
        'https://www.1212.com/luck/cancer/20230713.html',
        'https://www.1212.com/luck/leo/20230713.html',
        'https://www.1212.com/luck/virgo/20230713.html',
        'https://www.1212.com/luck/libra/20230713.html',
        'https://www.1212.com/luck/scorpio/20230713.html',
        'https://www.1212.com/luck/sagittarius/20230713.html',
        'https://www.1212.com/luck/capricorn/20230713.html',
        'https://www.1212.com/luck/aquarius/20230713.html',
        'https://www.1212.com/luck/pisces/20230713.html',
    ];

    protected $astros = [
        '白羊座', '金牛座', '双子座', '巨蟹座', '狮子座', '处女座', '天秤座', '天蝎座', '射手座', '摩羯座', '水瓶座', '双鱼座'
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'astro-storage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '从 www.1212.com 采集今日星座运势并入库';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 绕过证书
        $client = new Client(['verify' => false]);

        // 数据采集数组
        $contents = [];

        // 默认当日
        $date = Carbon::now()->format('Ymd');

        // 处理链接
        $urls = array_map(function ($url) use ($date){
            return str_replace('20230713', $date, $url);
        }, $this->urls);

        // 每个链接对应一个进程
        $bar = $this->output->createProgressBar(count($this->urls));

        $bar->start();

        $requests = function ($urls) {
            foreach ($urls as $url) {
                yield new Request('GET', $url, [
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36',
                    'Accept-Encoding' => 'gzip, deflate, br'
                ]);
            }
        };

        $pool = new Pool($client, $requests($urls), [
            'concurrency' => 4, // 这个数值表示最大并发请求的数量
            'fulfilled' => function ($response, $index) use (&$contents, $bar, $date) {
                // 请求成功后的回调函数
                $html = (string) $response->getBody();
                $crawler = new Crawler($html);

                // 星座名称
                $constellation = $crawler->filter('.ysbox .xzxzmenu p em')->each(function (Crawler $node) {
                    return $node->text();
                });

                // 运势集合
                $luckyDescArr = $crawler->filter('.infrobox .infro-list .jzbox')->each(function (Crawler $node) {
                    return $node->text();
                });

                // 其他集合
                $extraDescArr = $crawler->filter('.ysbox-list2 .ptit')->each(function (Crawler $node) {
                    return $node->text();
                });

                // 获取星座索引id
                $index = array_search($constellation[0], $this->astros);

                // 采集信息
                $contents[$index]['index'] = $index;
                $contents[$index]['date'] = $date;
                $contents[$index]['constellation'] = $constellation[0];

                $contents[$index]['overall_desc'] = $luckyDescArr[0];
                $contents[$index]['romance_desc'] = $luckyDescArr[1];
                $contents[$index]['workjob_desc'] = $luckyDescArr[2];
                $contents[$index]['wealth_desc']  = $luckyDescArr[3];
                $contents[$index]['health_desc']  = $luckyDescArr[4];

                $contents[$index]['lucky_astro']  = $extraDescArr[0];
                $contents[$index]['alert_astro']  = $extraDescArr[1];
                $contents[$index]['lucky_color']  = $extraDescArr[2];
                $contents[$index]['lucky_number'] = $extraDescArr[3];

                $bar->advance();
            },
            'rejected' => function ($reason, $index) use ($date) {
                $this->newLine();
                $this->error($date . '星座运势采集失败，具体信息请查看日志！');
                logger()->error($date . '星座运势采集失败: index => ' . $index . ', reason => ' . print_r($reason, true));
                return false;
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();

        // 进行数据入库
        Db::beginTransaction();
        try {
            // 查询是否已存在今日数据
            $check = Astros::query()->where('date', $date)->first();

            if (!empty($check)) {
                Astros::query()->where('date', $date)->delete();
            }

            foreach ($contents as $content) {
                Astros::query()->create($content);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->newLine();
            $this->error($date . '星座运势入库失败！error message: ' . $e->getMessage());
            logger()->error($date . '星座运势入库失败！error message: ' . $e->getMessage());
            return false;
        }
        $bar->finish();
        $this->newLine();
        $this->info($date . '星座运势采集入库完成！');
        return true;
    }
}
