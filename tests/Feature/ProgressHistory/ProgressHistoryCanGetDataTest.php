<?php

  namespace Tests\Feature\ProgressHistory;

  use App\Models\ProgressHistory;
  use App\Models\Recourse;
  use App\Models\User;
  use Illuminate\Foundation\Testing\RefreshDatabase;
  use Symfony\Component\HttpFoundation\Response;
  use Tests\TestCase;

  class ProgressHistoryCanGetDataTest extends TestCase
  {
    use RefreshDatabase;

    /** @test * */
    public function get_all_progress_history_from_a_recourse_exists()
    {
      $user = User::factory()->create();
      $recourse = Recourse::factory()->create(["user_id" => $user->id]);
      ProgressHistory::factory(4)->create([
        "recourse_id" => $recourse->id
      ]);

      $response = $this->actingAs($user)->getJson(route('progress.index', $recourse));

      $response->assertStatus(Response::HTTP_OK);
      // Tener en cuenta que puede variar segun la cantidad de resultados por página en ApiResponser
      // Son 5, porque 1 se crea al momento de crear un recurso, y los otros 4 fueron generados por el ProgressHistory::factory
      $response->assertJsonCount(5, "data");
      $response->assertJsonStructure([
        "status",
        "code",
        "data" => [
          [
            "identificador",
            "avanzadoHasta",
            "realizado",
            "pendiente",
            "fecha",
            "comentario",
            "esUltimoRegistro",
            "total"
          ]
        ],
        "meta",
        "links"
      ]);
    }

    /** @test * */
    public function can_not_get_progress_history_from_a_recourse_that_not_exists()
    {
      $user = User::factory()->create();
      $recourse = Recourse::factory()->create(["user_id" => $user->id]);
      ProgressHistory::factory(4)->create([
        "recourse_id" => $recourse->id
      ]);

      $response = $this->actingAs($user)->getJson(route('progress.index', 152));

      $response->assertStatus(Response::HTTP_NOT_FOUND);
      $response->assertJsonStructure([
        "status",
        "code",
        "error" => [
          "message",
          "details"
        ]
      ]);
      $response->assertJsonFragment(["message"=> "No se encontró el recurso"]);
    }
  }
