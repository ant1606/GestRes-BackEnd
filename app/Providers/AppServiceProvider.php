<?php

namespace App\Providers;

use App\Models\Settings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //Se verifica la existencia de la tabla por fallo al realizar testing que no encuentra la tabla
        if (Schema::hasTable('settings') && !Cache::has('settings')) {
            // dd("hola");
            Settings::reload_data_settings_to_cache();
        }
    }
}
