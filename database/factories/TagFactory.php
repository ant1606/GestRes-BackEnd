<?php

namespace Database\Factories;

use App\Enums\TagStyleEnum;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
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
      'style' => $this->randomTagStyle(),
    ];
  }

  private function randomTagStyle()
  {
    return array_rand([
      TagStyleEnum::TAG_STYLE_BLUE->value => 1,
      TagStyleEnum::TAG_STYLE_EMERALD->value => 1,
      TagStyleEnum::TAG_STYLE_GREEN->value => 1,
      TagStyleEnum::TAG_STYLE_INDIGO->value => 1,
      TagStyleEnum::TAG_STYLE_LIME->value => 1,
      TagStyleEnum::TAG_STYLE_ORANGE->value => 1,
      TagStyleEnum::TAG_STYLE_PINK->value => 1,
      TagStyleEnum::TAG_STYLE_PURPLE->value => 1,
      TagStyleEnum::TAG_STYLE_RED->value => 1,
      TagStyleEnum::TAG_STYLE_ROSE->value => 1,
      TagStyleEnum::TAG_STYLE_SKY->value => 1,
      TagStyleEnum::TAG_STYLE_TEAL->value => 1,
      TagStyleEnum::TAG_STYLE_YELLOW->value => 1,
      TagStyleEnum::TAG_STYLE_GRAY->value => 1
    ]);
  }
}
