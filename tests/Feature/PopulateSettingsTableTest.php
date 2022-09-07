<?php

namespace Tests\Feature;

use App\Enums\StatusRecourseEnum;
use App\Enums\TypeRecourseEnum;
use App\Models\Settings;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PopulateSettingsTableTest extends TestCase
{
    use RefreshDatabase;

    // TODO
    /**
     * Contemplar 2 casos adicionales para los test
     * 1- Testear cuando se actualiza un valor en la base de datos (El value de un tipo) y se actualice en la cache
     *      Obtengo los datos de cache
     *      Obtengo el valor inicial de un item que vamos a actualizar
     *      Actualizamos el valor del item
     *      Eliminamos y Actualizamos los datos en cache
     *      Verificamos que el valor actualizado (en cache) sea distinto al valor inicial obtenido del item
     *      Verificamos que el valor actualizado (en cache) sea igual al valor que se indico para actualizar
     * 2-
     */
    /** @test */
    public function match_initial_data_for_system()
    {
        $this->withoutExceptionHandling();
        //Check if the initial data has registered in the table settings throught execute seeders
        $this->assertDatabaseHas('settings', ['key' => TypeRecourseEnum::TYPE_LIBRO->name, 'value' => TypeRecourseEnum::TYPE_LIBRO->value]);
        $this->assertDatabaseHas('settings', ['key' => TypeRecourseEnum::TYPE_VIDEO->name, 'value' => TypeRecourseEnum::TYPE_VIDEO->value]);
        $this->assertDatabaseHas('settings', ['key' => StatusRecourseEnum::STATUS_REGISTRADO->name, 'value' => StatusRecourseEnum::STATUS_REGISTRADO->value]);
        $this->assertDatabaseHas('settings', ['key' => StatusRecourseEnum::STATUS_POREMPEZAR->name, 'value' => StatusRecourseEnum::STATUS_POREMPEZAR->value]);
        $this->assertDatabaseHas('settings', ['key' => StatusRecourseEnum::STATUS_ENPROCESO->name, 'value' => StatusRecourseEnum::STATUS_ENPROCESO->value]);
        $this->assertDatabaseHas('settings', ['key' => StatusRecourseEnum::STATUS_CULMINADO->name, 'value' => StatusRecourseEnum::STATUS_CULMINADO->value]);
        $this->assertDatabaseHas('settings', ['key' => StatusRecourseEnum::STATUS_DESCARTADO->name, 'value' => StatusRecourseEnum::STATUS_DESCARTADO->value],);
        $this->assertDatabaseHas('settings', ['key' => StatusRecourseEnum::STATUS_DESFASADO->name, 'value' => StatusRecourseEnum::STATUS_DESFASADO->value]);
    }

    /** @test */
    public function search_setting_value_in_cache()
    {
        $this->withoutExceptionHandling();

        // Cache::shouldReceive('remember')
        //     ->once()
        //     ->with('settings', 10, \Closure::class)
        //     ->andReturn(Settings::transform_data_settings_to_json(Settings::all()));

        Settings::reload_data_settings_to_cache();

        $this->assertJson(Cache::get('settings'));

        collect(TypeRecourseEnum::cases())->each(function ($item, $key) {
            // dd(Settings::getData($item->name, "id"));
            $this->assertEquals($item->value, Settings::getData($item->name, "value"));
        });

        collect(StatusRecourseEnum::cases())->each(function ($item, $key) {
            $this->assertEquals($item->value, Settings::getData($item->name, "value"));
        });
    }
}
