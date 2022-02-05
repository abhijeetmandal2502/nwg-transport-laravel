<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SettingLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $city = $this->faker->city();
        $slug = strtr($city, ' ', '_');
        return [
            'slug' => $slug,
            'location' => $city,
        ];
    }
}
