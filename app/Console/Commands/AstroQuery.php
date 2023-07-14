<?php

namespace App\Console\Commands;

use App\Models\Astro;
use App\Models\Astros;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AstroQuery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'astro-query {id : 星座索引ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->argument('id');
        $date = Carbon::now()->format('Ymd');

        $astro = Astros::query()->where('index', $id)->where('date', $date)->first();

        $this->line('日期: ' . $astro->date);
        $this->line('星座: ' . $astro->constellation);

        $this->line('综合运势: ' . $astro->overall_desc);
        $this->line('爱情运势: ' . $astro->romance_desc);
        $this->line('事业运势: ' . $astro->workjob_desc);
        $this->line('财富运势: ' . $astro->wealth_desc);
        $this->line('健康运势: ' . $astro->health_desc);

        $this->line('速配星座: ' . $astro->lucky_astro);
        $this->line('提防星座: ' . $astro->alert_astro);
        $this->line('幸运颜色: ' . $astro->lucky_color);
        $this->line('幸运数字: ' . $astro->lucky_number);

        $this->info('search success !');
    }
}
