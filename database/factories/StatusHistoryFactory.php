<?php

namespace Database\Factories;

use Carbon\Carbon;
use App\Models\Recourse;
use App\Models\Settings;
use App\Enums\StatusRecourseEnum;
use Illuminate\Database\Eloquent\Factories\Factory;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StatusHistory>
 */
class StatusHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'recourse_id' => function () {
                return Recourse::factory()->create();
            },
            'status_id' => $this->faker->randomElements([
                Settings::getData(StatusRecourseEnum::STATUS_POREMPEZAR->name, "id"),
                Settings::getData(StatusRecourseEnum::STATUS_ENPROCESO->name, "id"),
                Settings::getData(StatusRecourseEnum::STATUS_CULMINADO->name, "id"),
                Settings::getData(StatusRecourseEnum::STATUS_DESCARTADO->name, "id"),
                Settings::getData(StatusRecourseEnum::STATUS_DESFASADO->name, "id"),
            ]),
            'date' => Carbon::now(),
            'comment' => ''
        ];
    }
}
