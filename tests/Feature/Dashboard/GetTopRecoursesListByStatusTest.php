<?php

  namespace Tests\Feature\Dashboard;

  use App\Enums\StatusRecourseEnum;
  use App\Models\Recourse;
  use App\Models\Settings;
  use App\Models\User;
  use Carbon\Carbon;
  use Illuminate\Foundation\Testing\RefreshDatabase;
  use Symfony\Component\HttpFoundation\Response;
  use Tests\TestCase;

  class GetTopRecoursesListByStatusTest extends TestCase
  {
    use RefreshDatabase;

    /** @test */
    public function can_get_top_5_recourses_with_status_porEmpezar()
    {
      $user = User::factory()->create();
      $recourses = Recourse::factory(4)->create(["user_id" => $user->id]);
      Recourse::factory(6)->create(["user_id" => $user->id]);
      $date = Carbon::now()->toDateString();
      //Sólo 4 recursos tendrán el estado porEmpezar
      $status = [
        'status_id' => Settings::getData(StatusRecourseEnum::STATUS_POREMPEZAR->name, "id"),
        'date' => $date,
        'comment' => 'Curso a punto de empezar'
      ];
      foreach ($recourses as $recourse) {
        $this->actingAs($user)->post(route('status.store', $recourse->id), $status);
      }

      $response = $this->actingAs($user)->get(route('dashboard.getTop5Recourses', ['porEmpezar' => true]));

      $response->assertStatus(Response::HTTP_OK);
      $response->assertJsonCount(4, "data");
      $response->assertJsonStructure([
        "status",
        "code",
        "data" => [
          [
            "id",
            "name",
          ]
        ]
      ]);
    }

    /** @test */
    public function can_get_top_5_recourses_with_status_enProceso()
    {
      $user = User::factory()->create();

      $recourses = Recourse::factory(2)->create(["user_id" => $user->id]);
      Recourse::factory(8)->create(["user_id" => $user->id]);
      $date = Carbon::now()->toDateString();
      $status = [
        'status_id' => Settings::getData(StatusRecourseEnum::STATUS_POREMPEZAR->name, "id"),
        'date' => $date,
        'comment' => 'Curso a punto de empezar'
      ];
      foreach ($recourses as $recourse) {
        $this->actingAs($user)->post(route('status.store', $recourse->id), $status);
      }
      //Sólo 2 recursos tendrán el estado enProceso
      $date = Carbon::now()->toDateString();
      $status = [
        'status_id' => Settings::getData(StatusRecourseEnum::STATUS_ENPROCESO->name, "id"),
        'date' => $date,
        'comment' => 'Cursos en proceso'
      ];
      foreach ($recourses as $recourse) {
        $this->actingAs($user)->post(route('status.store', $recourse->id), $status);
      }

      $response = $this->actingAs($user)->get(route('dashboard.getTop5Recourses', ['porEmpezar' => false]));

      $response->assertStatus(Response::HTTP_OK);
      $response->assertJsonCount(2, "data");
      $response->assertJsonStructure([
        "status",
        "code",
        "data" => [
          [
            "id",
            "name",
          ]
        ]
      ]);
    }

    /** @test */
    public function get_empty_data_when_not_match_result_found()
    {
      $user = User::factory()->create();
      Recourse::factory(2)->create(["user_id" => $user->id]);

      $response = $this->actingAs($user)->get(route('dashboard.getTop5Recourses', ['porEmpezar' => false]));

      $response->assertStatus(Response::HTTP_OK);
      $response->assertJsonCount(0, "data");
      $response->assertJsonStructure([
        "status",
        "code",
        "data"
      ]);
    }
  }
