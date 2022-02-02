<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $roleId = $this->faker->unique()->randomElement(['marketing', 'account', 'supervisor', 'second_admin', 'admin']);
        return [
            'role_id' => $roleId,
            'role_name' => Str::ucfirst($roleId)
        ];
    }
}
