<?php

namespace App\Console\Commands;

use App\Enums\APINameEnum;
use App\Models\Settings;
use Illuminate\Console\Command;

class ResetQueryAccumulatorAPIYouTube extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apiyoutube:accumulator-reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset accumulator of quota for query API YouTube V3';

    /**
     * Execute the console command.
     */
    public function handle()
    {
      Settings::query()->where('key', APINameEnum::API_YOUTUBE->name)->update(['value2' => '0']);
    }
}
