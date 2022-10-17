<?php

namespace Tests\Feature\Settings;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class GetSettingsValuesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function get_data_from_settings_when_sending_an_acceptable_value()
    {
        $this->withoutExceptionHandling();

        //TODO Colocar como enum los tipos de valor a enviar ["type", "status"] para obtener los datos de settigns
        $value = ["type"];

        $response = $this->getJson(route('settings.show'), $value);

        $response->assertStatus(Response::HTTP_OK);

        // dd($response);
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "value",
                ]
            ]
        ]);
    }

    /** @test */
    public function get_error_from_settings_when_sending_an_unacceptable_value()
    {
        $this->withoutExceptionHandling();

        //TODO Colocar como enum los tipos de valor a enviar ["type", "status"] para obtener los datos de settigns
        $value = "valorNoExistente";

        $response = $this->getJson(route('settings.show', $value));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        // dd($response);
        $response->assertJsonStructure([
            "error"
        ]);
    }
}
