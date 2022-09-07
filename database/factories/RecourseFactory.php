<?php

namespace Database\Factories;

use App\Enums\TypeRecourseEnum;
use App\Models\Settings;
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
        return [
            'name' => $this->faker->words($this->faker->numberBetween(5, 15), true),
            'source' => $this->faker->url(),
            'type_id' => $this->faker->randomElement([
                Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
                Settings::getData(TypeRecourseEnum::TYPE_VIDEO->name, "id"),
            ]),
        ];
    }
}
