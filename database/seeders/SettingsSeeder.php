<?php

namespace Database\Seeders;

use App\Enums\APILimitRateEnum;
use App\Enums\APINameEnum;
use App\Enums\StatusRecourseEnum;
use App\Enums\StatusRecourseStyleEnum;
use App\Enums\TypeRecourseEnum;
use App\Enums\TypeSettingsEnum;
use App\Enums\UnitMeasureProgressEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


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
      if(!DB::table('settings')
        ->where('type', TypeSettingsEnum::SETTINGS_TYPE->name)
        ->where('key', $item->name)
        ->exists()){
        DB::table('settings')->insert([
          'type' => TypeSettingsEnum::SETTINGS_TYPE->name,
          'key' => $item->name,
          'value' => $item->value,
          'value2' => null,
        ]);
      }
    });

    collect(UnitMeasureProgressEnum::cases())->each(function ($item, $key) {
      if(!DB::table('settings')
        ->where('type', TypeSettingsEnum::SETTINGS_UNIT_MEASURE_PROGRESS->name)
        ->where('key', $item->name)
        ->exists()) {
        DB::table('settings')->insert([
          'type' => TypeSettingsEnum::SETTINGS_UNIT_MEASURE_PROGRESS->name,
          'key' => $item->name,
          'value' => $item->value,
          'value2' => null,
        ]);
      }
    });

    collect(StatusRecourseEnum::cases())->each(function ($item, $key) {
      if(!DB::table('settings')
        ->where('type', TypeSettingsEnum::SETTINGS_STATUS->name)
        ->where('key', $item->name)
        ->exists()) {
        DB::table('settings')->insert([
          'type' => TypeSettingsEnum::SETTINGS_STATUS->name,
          'key' => $item->name,
          'value' => $item->value,
          'value2' => StatusRecourseStyleEnum::fromName($item->name)
        ]);
      }
    });

    //Insertando API_LIMIT para limitar las consultas a API_YOUTUBE
    collect(APINameEnum::cases())->each(function ($item, $key) {
      if(!DB::table('settings')
        ->where('type', TypeSettingsEnum::SETTINGS_API->name)
        ->where('key', $item->name)
        ->exists()) {
        DB::table('settings')->insert([
          'type' => TypeSettingsEnum::SETTINGS_API->name,
          'key' => $item->name,
          'value' => APILimitRateEnum::fromName($item->name),
          'value2' => '0'
        ]);
      }
    });
  }
}
