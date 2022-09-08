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

    public static function getKeyfromId($id)
    {
        if (!Cache::has('settings'))
            self::reload_data_settings_to_cache();

        $settings = Cache::get('settings');
        //Decodificando a un array asociativo por el true
        $settings = json_decode($settings, true);

        $keySearch = null;

        foreach ($settings as $key => $keys) {
            foreach ($keys as $subKey => $value2) {
                if ($subKey !== "id")
                    continue;

                if ($value2 == $id)
                    continue;
                else {
                    continue 2;
                    //termina el loop foreach actual y continua con la logica del primer foreach (2 indica el nivel)
                    // https://stackoverflow.com/questions/32485581/php-nested-loop-break-inner-loops-and-continue-the-main-loop
                }
            }
            $keySearch = $key;
            break;
        }

        return $keySearch;
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
