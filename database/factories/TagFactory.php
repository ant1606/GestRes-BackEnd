<?php

namespace Database\Factories;

use App\Helpers\TagHelper;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition()
  {
    return [
      'name' => $this->faker->words(2, true),
      'style' => TagHelper::getRandomTagStyle()
    ];
  }
}
