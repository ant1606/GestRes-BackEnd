<?php

  namespace Tests\Feature\ProgressHistory;

  use App\Enums\UnitMeasureProgressEnum;
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
      // $this->withoutExceptionHandling();
      $user = User::factory()->create();
      $recourse = Recourse::factory()->create(["user_id" => $user->id]);
      $recourse_measure_is_hour = Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name;
      $recourseTotal = $this->getValueFromUnitMeasureProgress($recourse);
      $advanced = $recourse_measure_is_hour ? "00:10:00" : 4;
      $pending = $recourse_measure_is_hour ? $this->processHours($recourseTotal, $advanced)  :$recourseTotal - $advanced;
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
      //        $this->withoutExceptionHandling();
      $user = User::factory()->create();
      $recourse = Recourse::factory()->create(["user_id" => $user->id]);
      $recourse_measure_is_hour = Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name;
      $recourseTotal = $this->getValueFromUnitMeasureProgress($recourse);
      $advanced = $recourse_measure_is_hour ? "00:10:00" : 4;
      $pending = $recourse_measure_is_hour ? $this->processHours($recourseTotal, $advanced)  :$recourseTotal - $advanced;
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

    //TODO EXtraer esta logica
    private function getValueFromUnitMeasureProgress(Recourse $recourse)
    {
      switch (Settings::getKeyfromId($recourse['unit_measure_progress_id'])) {
        case UnitMeasureProgressEnum::UNIT_CHAPTERS->name:
          return $recourse->total_chapters;
        case UnitMeasureProgressEnum::UNIT_PAGES->name:
          return $recourse->total_pages;
        case UnitMeasureProgressEnum::UNIT_HOURS->name:
          return $recourse->total_hours;
        case UnitMeasureProgressEnum::UNIT_VIDEOS->name:
          return $recourse->total_videos;
      }
    }

    //TODO ExtraerLogica
    private function processHours($hora1, $hora2, $isSubstract = true)
    {
      list($horas1, $minutos1, $segundos1) = explode(':', $hora1);
      list($horas2, $minutos2, $segundos2) = explode(':', $hora2);

      $totalSegundos1 = $horas1 * 3600 + $minutos1 * 60 + $segundos1;
      $totalSegundos2 = $horas2 * 3600 + $minutos2 * 60 + $segundos2;

      $totalSegundos = $isSubstract ? $totalSegundos1 - $totalSegundos2 : $totalSegundos1 + $totalSegundos2;

      $nuevasHoras = floor($totalSegundos / 3600);
      $nuevosMinutos = floor(($totalSegundos % 3600) / 60);
      $nuevosSegundos = $totalSegundos % 60;

      return sprintf('%02d:%02d:%02d', abs($nuevasHoras), abs($nuevosMinutos), abs($nuevosSegundos));
    }
  }
