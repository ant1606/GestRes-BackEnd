<?php

namespace Tests\Feature\ProgressHistory;

use App\Models\ProgressHistory;
use App\Models\Recourse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ProgressHistoryDeleteTest extends TestCase
{
  use RefreshDatabase;

  /** @test **/
  public function a_progress_can_be_deleted_only_if_is_the_last_register()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);
    ProgressHistory::factory()->create([
      "recourse_id" => $recourse->id,
      "comment" => "Progress Test Second Delete"
    ]);
    $progress2 = ProgressHistory::factory()->create([
      "recourse_id" => $recourse->id,
      "comment" => "Progress Test Last Delete"
    ]);

    $this->assertDatabaseCount("progress_histories", 3);

    $response = $this->actingAs($user)->deleteJson(route('progress.destroy',  $progress2));

    $response->assertStatus(Response::HTTP_ACCEPTED);
    $this->assertDatabaseCount("progress_histories", 2);
    $response->assertJsonStructure([
      "status",
      "code",
      "data" => [
          "identificador",
          "avanzadoHasta",
          "realizado",
          "pendiente",
          "fecha",
          "comentario",
          "esUltimoRegistro",
          "total"
      ]
    ]);
  }

  /** @test **/
  public function a_progress_can_not_be_deleted_if_is_not_the_last_register()
  {
    // $this->withoutExceptionHandling();
    $user = User::factory()->create();

    $recourse = Recourse::factory()->create(["user_id" => $user->id]);
    $progress1 = ProgressHistory::factory()->create([
      "recourse_id" => $recourse->id,
      "comment" => "Progress Test Middle Register"
    ]);
    ProgressHistory::factory()->create([
      "recourse_id" => $recourse->id,
      "comment" => "Progress Test Last Register"
    ]);

    $this->assertDatabaseCount("progress_histories", 3);

    $response = $this->actingAs($user)->deleteJson(route('progress.destroy',  $progress1));

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $this->assertDatabaseCount("progress_histories", 3);
    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"
      ]
    ]);
    $response->assertJsonFragment(["message"=>"No se puede eliminar el registro, sólo puede eliminarse el ultimo registro del recurso"]);
  }

  /** @test **/
  public function a_progress_can_not_be_deleted_if_is_generated_by_system()
  {
    // $this->withoutExceptionHandling();
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);
    $progressGenerated = $recourse->progress->first();

    $this->assertDatabaseCount("progress_histories", 1);

    $response = $this->actingAs($user)->deleteJson(route('progress.destroy',  $progressGenerated));

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $this->assertDatabaseCount("progress_histories", 1);
    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"
      ]
    ]);
    $response->assertJsonFragment(["message"=>"No se puede eliminar el registro generado por el sistema"]);
  }
}
