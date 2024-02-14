<?php

  namespace Tests\Feature\Recourse;

  use App\Enums\UnitMeasureProgressEnum;
  use App\Models\Settings;
  use App\Enums\TypeRecourseEnum;
  use App\Models\User;

  trait RecourseDataTrait
  {

    protected function recourseValidData($overrides = []): array
    {
      if (key_exists("type_id", $overrides)) {
        $typeName = Settings::getKeyfromId($overrides["type_id"]);
      } else {
        // solo devuelve el key, asi que se genera un valor aleatorio
        $typeName = array_rand([
          TypeRecourseEnum::TYPE_LIBRO->name => 1,
          TypeRecourseEnum::TYPE_VIDEO->name => 1
        ]);
      }

      $total_pages = $total_chapters = $total_videos = $total_hours = $unit_measure_progress_name = null;

      if ($typeName == TypeRecourseEnum::TYPE_LIBRO->name) {
        $total_pages = 150;
        $total_chapters = 20;
        $unit_measure_progress_name = array_rand([UnitMeasureProgressEnum::UNIT_CHAPTERS->name => 1, UnitMeasureProgressEnum::UNIT_PAGES->name => 1]);
      }

      if ($typeName == TypeRecourseEnum::TYPE_VIDEO->name) {
        $total_videos = 50;
        $total_hours = "15:20:13";
        $unit_measure_progress_name = array_rand([UnitMeasureProgressEnum::UNIT_VIDEOS->name => 1, UnitMeasureProgressEnum::UNIT_HOURS->name => 1]);
      }
      $unit_measure_progress_id = Settings::getData($unit_measure_progress_name, "id");
      return array_merge([
        "name" => 'Nombre de mi recurso',
        "source" => 'D://micarpeta/misvideos/micurso',
        "author" => "Pepe LUna",
        "editorial" => "Mi editorial de Ejemplo",
        "type_id" => Settings::getData($typeName, "id"),
        'unit_measure_progress_id' => $unit_measure_progress_id,
        "total_pages" => $total_pages,
        "total_chapters" => $total_chapters,
        "total_videos" => $total_videos,
        "total_hours" => $total_hours,
        "user_id" => User::factory()->create()->id
      ], $overrides);
    }

  }
