<?php

namespace Database\Seeders;

use App\Enums\StatusRecourseEnum;
use App\Enums\TypeRecourseEnum;
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
            DB::table('settings')->insert(['key' => $item->name, 'value' => $item->value],);
        });

        collect(StatusRecourseEnum::cases())->each(function ($item, $key) {
            DB::table('settings')->insert(['key' => $item->name, 'value' => $item->value],);
        });

        // DB::table('settings')->insert([
        //     ['key' => TypeRecourseEnum::TYPE_LIBRO->name, 'value' => TypeRecourseEnum::TYPE_LIBRO->value],
        //     ['key' => TypeRecourseEnum::TYPE_VIDEO->name, 'value' => TypeRecourseEnum::TYPE_VIDEO->value],
        //     ['key' => 'STATUS_REGISTRADO', 'value' => 'REGISTRADO'],
        //     ['key' => 'STATUS_POREMPEZAR', 'value' => 'POR EMPEZAR'],
        //     ['key' => 'STATUS_ENPROCESO', 'value' => 'EN PROCESO'],
        //     ['key' => 'STATUS_CULMINADO', 'value' => 'CULMINADO'],
        //     ['key' => 'STATUS_DESCARTADO', 'value' => 'DESCARTADO'],
        //     ['key' => 'STATUS_DESFASADO', 'value' => 'DESFASADO'],
        // ]);
    }
}
