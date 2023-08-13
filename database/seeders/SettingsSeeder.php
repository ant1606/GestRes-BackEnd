<?php

namespace Database\Seeders;

use App\Enums\StatusRecourseEnum;
use App\Enums\StatusRecourseStyleEnum;
use App\Enums\TypeRecourseEnum;
use App\Enums\TypeSettingsEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SettingsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {

    collect(TypeRecourseEnum::cases())->each(function ($item, $key) {
      DB::table('settings')->insert([
        'type' => TypeSettingsEnum::SETTINGS_TYPE->name,
        'key' => $item->name,
        'value' => $item->value,
        'value2' => null,
      ]);
    });

    collect(StatusRecourseEnum::cases())->each(function ($item, $key) {
      DB::table('settings')->insert([
        'type' => TypeSettingsEnum::SETTINGS_STATUS->name,
        'key' => $item->name,
        'value' => $item->value,
        'value2' => StatusRecourseStyleEnum::fromName($item->name)
      ]);
    });
  }
}
