<?php

namespace Tests\Feature\Recourse;

use App\Enums\TypeRecourseEnum;
use App\Models\Recourse;
use App\Models\Settings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RecourseCanGetDataTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function can_get_list_of_recourses()
  {
    $user = User::factory()->create();
    Recourse::factory(5)->create(["user_id" => $user->id]);

    $response = $this->actingAs($user)->get(route('recourses.index'));

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJsonCount(5, "data");
    $response->assertJsonStructure([
      "data" => [
        [
          "identificador",
          "nombre",
          "ruta",
          "autor",
          "editorial",
          "tipoId",
          "totalPaginas",
          "totalCapitulos",
          "totalVideos",
          "totalHoras",
        ]
      ]
    ]);
  }

  /** @test */
  public function can_search_by_name()
  {
    $user = User::factory()->create();
    Recourse::factory(5)->create(["user_id" => $user->id]);
    Recourse::factory()->create(["name" => "Este es mi recurso", "user_id" => $user->id]);
    $filterSearchNombre = "recurso";

    $this->assertDatabaseCount("recourses", 6);

    $response = $this->actingAs($user)->getJson(route('recourses.index', ["searchNombre" => $filterSearchNombre]));

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJsonCount(1, "data");
    $response->assertJsonStructure([
      "data" => [
        [
          "identificador",
          "nombre",
          "ruta",
          "autor",
          "editorial",
          "tipoId",
          "totalPaginas",
          "totalCapitulos",
          "totalVideos",
          "totalHoras",
        ]
      ]
    ]);
    $response->assertJsonFragment(['nombre' => "Este es mi recurso"]);
  }

  /** @test */
  public function can_search_by_type_of_recourse()
  {
    // $this->withoutExceptionHandling();
    $user = User::factory()->create();
    Recourse::factory(4)->create(["type_id" => Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"), "user_id" => $user->id]);
    Recourse::factory(2)->create(["type_id" => Settings::getData(TypeRecourseEnum::TYPE_VIDEO->name, "id"), "user_id" => $user->id]);
    $filterSearchTipo = Settings::getData(TypeRecourseEnum::TYPE_VIDEO->name, "id");

    $response = $this->actingAs($user)->getJson(route('recourses.index', ["searchTipo" => $filterSearchTipo]));

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJsonCount(2, "data");
    $response->assertJsonStructure([
      "data" => [
        [
          "identificador",
          "nombre",
          "ruta",
          "autor",
          "editorial",
          "tipoId",
          "totalPaginas",
          "totalCapitulos",
          "totalVideos",
          "totalHoras",
        ]
      ]
    ]);
    $response->assertJsonFragment(['tipoId' => $filterSearchTipo]);
  }

  //TODO Realizar el filtrado de recursos por Estado de Recurso
  //TODO Realizar el filtrado de recursos por Etiquetas
}
