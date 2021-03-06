<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SettingPageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'page_slug' => $this->faker->unique()->userName(),
            'parent_title' => $this->faker->randomElement(['home', 'booking', 'loading', 'receive', 'getpass', 'delivery', 'accounting', 'reports', 'settings']),
        ];
    }
}
