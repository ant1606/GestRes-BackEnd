<?php

  namespace Tests\Feature\StatusHistory;

  use App\Enums\StatusRecourseEnum;
  use App\Models\Recourse;
  use App\Models\Settings;
  use App\Models\User;
  use Carbon\Carbon;
  use Illuminate\Foundation\Testing\RefreshDatabase;
  use Symfony\Component\HttpFoundation\Response;
  use Tests\TestCase;

  class StatusRegistrationTest extends TestCase
  {
    use RefreshDatabase;

    /** @test */
    public function a_status_can_be_register()
    {
      $user = User::factory()->create();
      $recourse = Recourse::factory()->create(["user_id" => $user]);

      $date = Carbon::now()->toDateString();
      $status = [
        'status_id' => Settings::getData(StatusRecourseEnum::STATUS_POREMPEZAR->name, "id"),
        'date' => $date,
        'comment' => 'Curso a punto de empezar'
      ];

      $response = $this->actingAs($user)->postJson(route('status.store', $recourse->id), $status);

      $response->assertStatus(Response::HTTP_CREATED);

      $this->assertDatabaseHas('status_histories', [
        'recourse_id' => $recourse->id,
        'status_id' => Settings::getData(StatusRecourseEnum::STATUS_POREMPEZAR->name, "id"),
        'date' => $date,
        'comment' => 'Curso a punto de empezar'
      ]);
      $this->assertDatabaseCount('status_histories', 2);
      $response->assertJsonStructure([
        "code",
        "status",
        "data" => [
          "identificador",
          "fecha",
          "comentario",
          "estadoId",
          "estadoNombre",
          "esUltimoRegistro"
        ]
      ]);
      $response->assertJsonFragment(["comentario" => $status["comment"]]);
    }

    /** @test */
    public function a_status_can_not_be_register_with_a_invalid_date()
    {
      $user = User::factory()->create();
      $recourse = Recourse::factory()->create(["user_id" => $user]);

      $date = Carbon::now()->subDays(15);
      $status = [
        'statusId' => Settings::getData(StatusRecourseEnum::STATUS_POREMPEZAR->name, "id"),
        'date' => $date,
        'comment' => 'Curso a punto de empezar'
      ];

      $response = $this->actingAs($user)->postJson(route('status.store', $recourse->id), $status);

      $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

      $this->assertDatabaseCount('status_histories', 1);
      $response->assertJsonStructure([
        "code",
        "status",
        "error" => [
          "message",
          "details",
        ]
      ]);
      $response->assertJsonFragment(["message"=>"La fecha ingresada no es correcta"]);
    }
  }
