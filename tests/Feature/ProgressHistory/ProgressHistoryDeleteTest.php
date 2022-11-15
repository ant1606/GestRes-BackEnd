<?php

namespace Tests\Feature\ProgressHistory;

use App\Models\ProgressHistory;
use App\Models\Recourse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ProgressHistoryDeleteTest extends TestCase
{
  use RefreshDatabase;

  /** @test **/
  public function a_progress_can_be_deleted_if_is_the_last_register()
  {
    $recourse = Recourse::factory()->create();
    $progress1 = ProgressHistory::factory()->create([
      "recourse_id" => $recourse->id,
      "comment" => "Progress Test Second Delete"
    ]);
    $progress2 = ProgressHistory::factory()->create([
      "recourse_id" => $recourse->id,
      "comment" => "Progress Test Last Delete"
    ]);

    $this->assertDatabaseCount("progress_histories", 3);

    $response = $this->deleteJson(route('progress.destroy',  $progress2));

    $response->assertStatus(Response::HTTP_ACCEPTED);
    $this->assertDatabaseCount("progress_histories", 2);
    $response->assertJsonStructure([
      "data" => [
        "identificador",
        "realizado",
        "pendiente",
        "fecha",
        "comentario"
      ]
    ]);
  }

  /** @test **/
  public function a_progress_can_not_be_deleted_if_is_not_the_last_register()
  {
    $this->withoutExceptionHandling();

    $recourse = Recourse::factory()->create();
    $progress1 = ProgressHistory::factory()->create([
      "recourse_id" => $recourse->id,
      "comment" => "Progress Test Middle Register"
    ]);
    $progress2 = ProgressHistory::factory()->create([
      "recourse_id" => $recourse->id,
      "comment" => "Progress Test Last Register"
    ]);

    $this->assertDatabaseCount("progress_histories", 3);

    $response = $this->deleteJson(route('progress.destroy',  $progress1));

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $this->assertDatabaseCount("progress_histories", 3);
    $response->assertJsonStructure([
      "error" => [
        [
          "status",
          "detail"
        ]
      ]
    ]);
  }

  /** @test **/
  public function a_progress_can_not_be_deleted_if_is_generated_by_system()
  {
    $this->withoutExceptionHandling();

    $recourse = Recourse::factory()->create();
    $progressGenerated = $recourse->progress->first();

    $this->assertDatabaseCount("progress_histories", 1);

    $response = $this->deleteJson(route('progress.destroy',  $progressGenerated));

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $this->assertDatabaseCount("progress_histories", 1);
    $response->assertJsonStructure([
      "error" => [
        [
          "status",
          "detail"
        ]
      ]
    ]);
  }
}
