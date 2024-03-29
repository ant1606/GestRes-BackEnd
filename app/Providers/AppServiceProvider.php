<?php

namespace App\Providers;

use App\Models\Settings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
// use Illuminate\Http\Resources\Json\JsonResource;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
      if ($this->app->environment('local')) {
        $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
        $this->app->register(TelescopeServiceProvider::class);
      }
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

            Settings::reload_data_settings_to_cache();
        }

        // JsonResource::withoutWrapping();
    }
}
