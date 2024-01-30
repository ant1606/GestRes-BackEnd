<?php

namespace Tests\Feature\StatusHistory;

use App\Models\StatusHistory;
use App\Models\Recourse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class StatusHistoryCanGetDataTest extends TestCase
{
  use RefreshDatabase;

  /** @test **/
  public function get_all_status_history_from_a_recourse_exists()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);
    $statusHistories = StatusHistory::factory(4)->create([
      "recourse_id" => $recourse->id
    ]);

    $response = $this->actingAs($user)->getJson(route('status.index', $recourse));

//    dd($response->getContent());
    $response->assertStatus(Response::HTTP_OK);
    // Tener en cuenta que puede variar segun la cantidad de resultados por pagina en ApiResponser
    $response->assertJsonCount(5, "data");
    $response->assertJsonStructure([
      "status",
      "code",
      "data"=>[
        [
          "identificador",
          "fecha",
          "comentario",
          "estadoId",
          "estadoNombre",
          "esUltimoRegistro"
        ]
      ],
      "links",
      "meta"
    ]);
  }

  /** @test **/
  public function can_not_get_status_history_from_a_recourse_that_not_exists()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);
    StatusHistory::factory(4)->create([
      "recourse_id" => $recourse->id
    ]);

    $response = $this->actingAs($user)->getJson(route('status.index', 152));

    $response->assertStatus(Response::HTTP_NOT_FOUND);
    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"
      ]
    ]);
    $response->assertJsonFragment(["message"=>"No se encontró el recurso"]);
  }
}
