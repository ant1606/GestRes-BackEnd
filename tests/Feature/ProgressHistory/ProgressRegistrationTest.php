<?php

  namespace Tests\Feature\ProgressHistory;

  use App\Enums\UnitMeasureProgressEnum;
  use App\Helpers\TimeHelper;
  use App\Models\Recourse;
  use App\Models\Settings;
  use App\Models\User;
  use Carbon\Carbon;
  use Illuminate\Foundation\Testing\RefreshDatabase;
  use Symfony\Component\HttpFoundation\Response;
  use Tests\TestCase;

  class ProgressRegistrationTest extends TestCase
  {
    use RefreshDatabase;
    use ProgressHistoryDataTrait;

    /** @test */
    public function a_progress_can_be_register()
    {
      $user = User::factory()->create();
      $recourse = Recourse::factory()->create(["user_id" => $user->id]);
      $recourse_measure_is_hour = Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name;
      $recourseTotal = Recourse::getTotalValueFromUnitMeasureProgress($recourse);
      $advanced = $recourse_measure_is_hour ? "00:10:00" : 4;
      $pending = $recourse_measure_is_hour ? TimeHelper::processHours($recourseTotal, $advanced) : $recourseTotal - $advanced;
      $dateProgress = Carbon::now()->toDateString();

      $progress = $this->progressHistoryValidData(
        $recourse_measure_is_hour,
        [
          'advanced' => $advanced,
          'date' => $dateProgress,
          'comment' => null,
        ]
      );

      $response = $this->actingAs($user)->postJson(route('progress.store', $recourse), $progress);

      $response->assertStatus(Response::HTTP_CREATED);
      $this->assertDatabaseCount('progress_histories', 2);
      $this->assertDatabaseHas('progress_histories', [
        'advanced' => $advanced,
        'pending' => $pending,
        'date' => $dateProgress,
        'comment' => null,
      ]);
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
      $response->assertJsonFragment(["avanzadoHasta"=>$advanced]);
    }

    /** @test */
    public function a_progress_can_not_be_register_with_a_invalid_date()
    {
      $user = User::factory()->create();
      $recourse = Recourse::factory()->create(["user_id" => $user->id]);
      $recourse_measure_is_hour = Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name;
      $recourseTotal = Recourse::getTotalValueFromUnitMeasureProgress($recourse);
      $advanced = $recourse_measure_is_hour ? "00:10:00" : 4;
      $pending = $recourse_measure_is_hour ? TimeHelper::processHours($recourseTotal, $advanced)  :$recourseTotal - $advanced;
      $dateProgress = Carbon::now()->subDays(15)->toDateString();

      $progress = $this->progressHistoryValidData(
        $recourse_measure_is_hour,
        [
          'advanced' => $advanced,
          'date' => $dateProgress,
          'comment' => null,
        ]
      );

      $response = $this->actingAs($user)->postJson(route('progress.store', $recourse), $progress);
      $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
      $this->assertDatabaseCount('progress_histories', 1);
      $response->assertJsonStructure([
        "status",
        "code",
        "error" => [
          "message",
          "details"
        ]
      ]);
      $response->assertJsonFragment(["message"=>"La fecha ingresada es menor al último registro existente."]);
    }
  }
