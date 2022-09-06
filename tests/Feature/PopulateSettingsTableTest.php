<?php

namespace Tests\Feature;

use App\Enums\StatusResourceEnum;
use Tests\TestCase;
use App\Enums\TypeResourceEnum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;


class PopulateSettingsTableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function match_initial_data_for_system()
    {
        $this->withoutExceptionHandling();
        //Check if the initial data has registered in the table settings throught execute seeders
        $this->assertDatabaseHas('settings', ['key' => TypeResourceEnum::TYPE_LIBRO->name, 'value' => TypeResourceEnum::TYPE_LIBRO->value]);
        $this->assertDatabaseHas('settings', ['key' => TypeResourceEnum::TYPE_VIDEO->name, 'value' => TypeResourceEnum::TYPE_VIDEO->value]);
        $this->assertDatabaseHas('settings', ['key' => StatusResourceEnum::STATUS_REGISTRADO->name, 'value' => StatusResourceEnum::STATUS_REGISTRADO->value]);
        $this->assertDatabaseHas('settings', ['key' => StatusResourceEnum::STATUS_POREMPEZAR->name, 'value' => StatusResourceEnum::STATUS_POREMPEZAR->value]);
        $this->assertDatabaseHas('settings', ['key' => StatusResourceEnum::STATUS_ENPROCESO->name, 'value' => StatusResourceEnum::STATUS_ENPROCESO->value]);
        $this->assertDatabaseHas('settings', ['key' => StatusResourceEnum::STATUS_CULMINADO->name, 'value' => StatusResourceEnum::STATUS_CULMINADO->value]);
        $this->assertDatabaseHas('settings', ['key' => StatusResourceEnum::STATUS_DESCARTADO->name, 'value' => StatusResourceEnum::STATUS_DESCARTADO->value],);
        $this->assertDatabaseHas('settings', ['key' => StatusResourceEnum::STATUS_DESFASADO->name, 'value' => StatusResourceEnum::STATUS_DESFASADO->value]);
    }
}
