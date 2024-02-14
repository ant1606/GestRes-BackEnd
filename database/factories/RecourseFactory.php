<?php

  namespace Database\Factories;

  use App\Models\Recourse;
  use App\Models\Settings;
  use App\Models\StatusHistory;
  use App\Enums\TypeRecourseEnum;
  use App\Enums\StatusRecourseEnum;
  use App\Enums\UnitMeasureProgressEnum;
  use App\Models\ProgressHistory;
  use App\Models\User;
  use Carbon\Carbon;
  use Illuminate\Database\Eloquent\Factories\Factory;

  /**
   * @extends Factory<Recourse>
   */
  class RecourseFactory extends Factory
  {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
      // solo devuelve el key, asi que se genera un valor aleatorio
//    $typeName = array_rand([
//      TypeRecourseEnum::TYPE_LIBRO->name => 1,
//      TypeRecourseEnum::TYPE_VIDEO->name => 1
//    ]);
//
//    $total_pages = $total_chapters = $total_videos = $total_hours = $unit_measure_progress_name = null;
//
//    if ($typeName === TypeRecourseEnum::TYPE_LIBRO->name) {
//      $total_pages = $this->faker->numberBetween(100, 600);
//      $total_chapters = $this->faker->numberBetween(5, 30);
//      $unit_measure_progress_name = array_rand([UnitMeasureProgressEnum::UNIT_CHAPTERS->name => 1, UnitMeasureProgressEnum::UNIT_PAGES->name => 1]);
//    }else if ($typeName === TypeRecourseEnum::TYPE_VIDEO->name) {
//      $total_videos = $this->faker->numberBetween(10, 150);
//      $total_hours =
//        $this->faker->numberBetween(7, 100) . ":" .
//        str_pad($this->faker->numberBetween(0, 59), 2, '0', STR_PAD_LEFT) . ":" .
//        str_pad($this->faker->numberBetween(0, 59), 2, '0', STR_PAD_LEFT);
//      $unit_measure_progress_name = array_rand([UnitMeasureProgressEnum::UNIT_VIDEOS->name => 1, UnitMeasureProgressEnum::UNIT_HOURS->name => 1]);
//    }
//    $unit_measure_progress_id = Settings::getData($unit_measure_progress_name, "id");

//    'type_id' =>  Settings::getData($typeName, "id"),
//      'unit_measure_progress_id' => $unit_measure_progress_id,
//      "total_pages" => $total_pages,
//      "total_chapters" => $total_chapters,
//      "total_videos" => $total_videos,
//      "total_hours" => $total_hours,
//      "user_id" => User::factory()->create()->id
      $type_id = Settings::getData(TypeRecourseEnum::TYPE_VIDEO->name, 'id');
      $unit_measure_progress_id = Settings::getData(UnitMeasureProgressEnum::UNIT_HOURS->name, 'id');
      return [
        'name' => $this->faker->words($this->faker->numberBetween(5, 15), true),
        'source' => $this->faker->url(),
        "author" => $this->faker->name(),
        "editorial" => $this->faker->company(),
        'type_id' => $type_id,
        'unit_measure_progress_id' => $unit_measure_progress_id,
        'total_pages' => $this->faker->numberBetween(100, 600),
        'total_chapters' => $this->faker->numberBetween(5, 30),
        'total_videos' => $this->faker->numberBetween(10, 150),
        'total_hours' => $this->faker->time('H:i:s', 'now'), // Generar tiempo aleatorio
        "user_id" => User::factory()->create()->id
      ];
    }

    public function configure()
    {
      return $this->afterMaking(function (Recourse $recourse) {
        $typeName = Settings::getKeyfromId($recourse->type_id);
        $total_pages = $total_chapters = $total_videos = $total_hours = $unit_measure_progress_name = null;

        if ($typeName === TypeRecourseEnum::TYPE_LIBRO->name) {
          $total_pages = $this->faker->numberBetween(100, 600);
          $total_chapters = $this->faker->numberBetween(5, 30);
          $unit_measure_progress_name = array_rand([UnitMeasureProgressEnum::UNIT_CHAPTERS->name => 1, UnitMeasureProgressEnum::UNIT_PAGES->name => 1]);
        } else if ($typeName === TypeRecourseEnum::TYPE_VIDEO->name) {
          $total_videos = $this->faker->numberBetween(10, 150);
          $total_hours =
            $this->faker->numberBetween(7, 100) . ":" .
            str_pad($this->faker->numberBetween(0, 59), 2, '0', STR_PAD_LEFT) . ":" .
            str_pad($this->faker->numberBetween(0, 59), 2, '0', STR_PAD_LEFT);
          $unit_measure_progress_name = array_rand([UnitMeasureProgressEnum::UNIT_VIDEOS->name => 1, UnitMeasureProgressEnum::UNIT_HOURS->name => 1]);
        }
        $unit_measure_progress_id = Settings::getData($unit_measure_progress_name, "id");

        $recourse->unit_measure_progress_id = $unit_measure_progress_id;
        $recourse->total_pages = $total_pages;
        $recourse->total_chapters = $total_chapters;
        $recourse->total_videos = $total_videos;
        $recourse->total_hours = $total_hours;

      })->afterCreating(function (Recourse $recourse) {
        $dateRecord = Carbon::now()->toDateString();

        StatusHistory::factory()->create([
          'recourse_id' => $recourse->id,
          'status_id' => Settings::getData(StatusRecourseEnum::STATUS_REGISTRADO->name, "id"),
          'date' => $dateRecord,
          'comment' => "REGISTRO INICIAL GENERADO AUTOMATICAMENTE POR EL SISTEMA"
        ]);
//      dd(Recourse::getTotalValueFromUnitMeasureProgress($recourse));
        ProgressHistory::factory()->create([
          'recourse_id' => $recourse->id,
          "done" => Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name ? "00:00:00" : "0",
          "advanced" => Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name ? "00:00:00" : "0",
          "pending" => Recourse::getTotalValueFromUnitMeasureProgress($recourse),
          'date' => $dateRecord,
          'comment' => "REGISTRO INICIAL GENERADO AUTOMATICAMENTE POR EL SISTEMA"
        ]);
      });
    }
  }
