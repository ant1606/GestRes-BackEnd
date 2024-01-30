<?php

  namespace Tests\Feature\Settings;

  use App\Enums\TypeSettingsEnum;
  use Illuminate\Foundation\Testing\RefreshDatabase;
  use Symfony\Component\HttpFoundation\Response;
  use Tests\TestCase;

  class GetSettingsValuesTest extends TestCase
  {
    use RefreshDatabase;

    /** @test */
    public function get_data_from_settings_when_sending_an_acceptable_value()
    {
      $typeName = array_rand([
        TypeSettingsEnum::SETTINGS_TYPE->name => 1,
        TypeSettingsEnum::SETTINGS_STATUS->name => 1,
        TypeSettingsEnum::SETTINGS_UNIT_MEASURE_PROGRESS->name => 1,
      ]);

      $response = $this->getJson(route('settings.show', $typeName));

//      dd($response->getContent());
      $response->assertStatus(Response::HTTP_OK);
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
      $value = "valorNoExistente";

      $response = $this->getJson(route('settings.show', $value));

      $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
      $response->assertJsonStructure([
        "status",
        "code",
        "error"=>[
          "message",
          "details"
        ]
      ]);
      $response->assertJsonFragment(["message"=>"Error al procesar la data"]);
    }
  }
