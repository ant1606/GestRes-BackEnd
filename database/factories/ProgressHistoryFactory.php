<?php

namespace Database\Factories;

use Carbon\Carbon;
use App\Models\Recourse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProgressHistory>
 */
class ProgressHistoryFactory extends Factory
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
      'done' => $this->faker->numberBetween(10, 20),
      'advanced' => $this->faker->numberBetween(10, 20),
      'pending' => $this->faker->numberBetween(21, 40),
      'date' => Carbon::now()->toDateString(),
      'comment' => "",
    ];
  }
}
