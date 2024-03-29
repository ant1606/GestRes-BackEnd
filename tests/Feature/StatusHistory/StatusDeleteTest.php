<?php

namespace Tests\Feature\StatusHistory;

use App\Models\Recourse;
use App\Models\StatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class StatusDeleteTest extends TestCase
{
  use RefreshDatabase;

  /** @test **/
  public function a_status_can_be_deleted_if_is_the_last_register()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);
    StatusHistory::factory()->create([
      "recourse_id" => $recourse->id,
      "comment" => "Status Test Second Delete"
    ]);
    $status2 = StatusHistory::factory()->create([
      "recourse_id" => $recourse->id,
      "comment" => "Status Test Last Delete"
    ]);

    $this->assertDatabaseCount("status_histories", 3);

    $response = $this->actingAs($user)->deleteJson(route('status.destroy',  $status2));

    $response->assertStatus(Response::HTTP_ACCEPTED);
    $this->assertDatabaseCount("status_histories", 2);
    $response->assertJsonStructure([
      "status",
      "code",
      "data" => [
        "identificador",
        "fecha",
        "comentario",
        "estadoId",
        "estadoNombre",
      ]
    ]);
    $response->assertJsonFragment(["comentario"=>$status2["comment"]]);
  }

  /** @test **/
  public function a_status_can_not_be_deleted_if_is_not_the_last_register()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);
    $status1 = StatusHistory::factory()->create([
      "recourse_id" => $recourse->id,
      "comment" => "Status Test Middle Register"
    ]);
     StatusHistory::factory()->create([
      "recourse_id" => $recourse->id,
      "comment" => "Status Test Last Register"
    ]);

    $this->assertDatabaseCount("status_histories", 3);

    $response = $this->actingAs($user)->deleteJson(route('status.destroy',  $status1));

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $this->assertDatabaseCount("status_histories", 3);
    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"
      ]
    ]);
    $response->assertJsonFragment(["message"=>"Acción prohibida, sólo puede eliminarse el último registro de estados del recurso"]);
  }


  /** @test **/
  public function a_status_can_not_be_deleted_if_is_generated_by_system()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);
    $statusGenerated = $recourse->status->first();

    $this->assertDatabaseCount("status_histories", 1);

    $response = $this->actingAs($user)->deleteJson(route('status.destroy',  $statusGenerated));

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $this->assertDatabaseCount("status_histories", 1);
    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"
      ]
    ]);
    $response->assertJsonFragment(["message"=>"Acción prohibida, No esta permitido eliminar el registro generado por el sistema"]);
  }
}
