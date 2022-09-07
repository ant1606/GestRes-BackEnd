<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Settings extends Model
{
    use HasFactory;


    public static function getData($key, $subKey = null)
    {
        //$subKey => ['value' o 'id']
        // dd("Hola");
        if (!Cache::has('settings'))
            self::reload_data_settings_to_cache();

        $settings = Cache::get('settings');
        //Decodificando a un array asociativo por el true
        $settings = json_decode($settings, true);

        return array_key_exists($key, $settings) ?
            (is_null($subKey) ? $settings[$key] :  $settings[$key][$subKey])
            : null;
    }

    protected static function transform_data_settings_to_json(Collection $collection)
    {
        $res = $collection->mapWithKeys(function ($item, $key) {
            return
                [
                    $item['key'] => [
                        'id' => $item['id'],
                        'value' => $item['value']
                    ]
                ];
        });
        return $res->toJson();
    }

    protected static function reload_data_settings_to_cache()
    {
        Cache::forget('settings');

        $settingsJson = self::transform_data_settings_to_json(Settings::all());

        Cache::remember('settings', 60 * 10, function () use ($settingsJson) {
            return $settingsJson;
        });
    }
}
