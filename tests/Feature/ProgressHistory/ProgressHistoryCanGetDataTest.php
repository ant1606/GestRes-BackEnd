<?php

namespace Tests\Feature\ProgressHistory;

use App\Models\ProgressHistory;
use App\Models\Recourse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ProgressHistoryCanGetDataTest extends TestCase
{
  use RefreshDatabase;

  /** @test **/
  public function get_all_progress_history_from_a_recourse_exists()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);
    $progressHistories = ProgressHistory::factory(4)->create([
      "recourse_id" => $recourse->id
    ]);

    $response = $this->actingAs($user)->getJson(route('progress.index', $recourse));

    $response->assertStatus(Response::HTTP_OK);
    // Tener en cuenta que puede variar segun la cantidad de resultados por pagina en ApiResponser
    $response->assertJsonCount(5, "data");
  }

  /** @test **/
  public function can_not_get_progress_history_from_a_recourse_that_not_exists()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);
    $progressHistories = ProgressHistory::factory(4)->create([
      "recourse_id" => $recourse->id
    ]);

    $response = $this->actingAs($user)->getJson(route('progress.index', 152));

    $response->assertStatus(Response::HTTP_NOT_FOUND);
    $response->assertJsonStructure([
      "error" => [
        "status", "detail"
      ]
    ]);
  }
}
