<?php

namespace Database\Factories;

use App\Models\Recourse;
use App\Models\Settings;
use App\Models\StatusHistory;
use App\Enums\TypeRecourseEnum;
use App\Enums\StatusRecourseEnum;
use App\Models\ProgressHistory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recourse>
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
        $typeName = array_rand([
            TypeRecourseEnum::TYPE_LIBRO->name => 1,
            TypeRecourseEnum::TYPE_VIDEO->name => 1
        ]);

        $total_pages = $total_chapters = $total_videos = $total_hours = null;

        if ($typeName == TypeRecourseEnum::TYPE_LIBRO->name) {
            $total_pages = $this->faker->numberBetween(100, 600);
            $total_chapters = $this->faker->numberBetween(5, 30);
        }

        if ($typeName == TypeRecourseEnum::TYPE_VIDEO->name) {
            $total_videos = $this->faker->numberBetween(10, 150);
            $total_hours =
                $this->faker->numberBetween(7, 100) . ":" .
                str_pad($this->faker->numberBetween(0, 59), 2, '0', STR_PAD_LEFT) . ":" .
                str_pad($this->faker->numberBetween(0, 59), 2, '0', STR_PAD_LEFT);
        }

        // $this->faker->randomElement([
        //     Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
        //     Settings::getData(TypeRecourseEnum::TYPE_VIDEO->name, "id"),
        // ]
        return [
            'name' => $this->faker->words($this->faker->numberBetween(5, 15), true),
            'source' => $this->faker->url(),
            "author" => $this->faker->name(),
            "editorial" => $this->faker->company(),
            'type_id' =>  Settings::getData($typeName, "id"),
            "total_pages" => $total_pages,
            "total_chapters" => $total_chapters,
            "total_videos" => $total_videos,
            "total_hours" => $total_hours,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Recourse $recourse) {
            $dateRecord = Carbon::now()->toDateString();

            StatusHistory::factory()->create([
                'recourse_id' => $recourse->id,
                'status_id' => Settings::getData(StatusRecourseEnum::STATUS_REGISTRADO->name, "id"),
                'date' => $dateRecord,
                'comment' => "REGISTRO INICIAL GENERADO AUTOMATICAMENTE POR EL SISTEMA"
            ]);

            ProgressHistory::factory()->create([
                'recourse_id' => $recourse->id,
                'done' => 0,
                'pending' => $recourse->total_pages ? $recourse->total_pages : $recourse->total_videos,
                'date' => $dateRecord,
                'comment' => "REGISTRO INICIAL GENERADO AUTOMATICAMENTE POR EL SISTEMA"
            ]);
        });
    }
}
