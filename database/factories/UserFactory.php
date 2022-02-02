<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $gender = $this->faker->randomElement(['male', 'female']);
        $roleId = $this->faker->randomElement(['marketing', 'account', 'supervisor', 'second_admin', 'admin']);
        return [
            'emp_id' => $this->faker->unique()->userName(),
            'name' => $this->faker->name($gender),
            'email' => $this->faker->unique()->safeEmail(),
            'mobile' => $this->faker->unique()->e164PhoneNumber(),
            'gender' => $gender,
            'DOJ' => $this->faker->date('Y-m-d'),
            'DOB' => $this->faker->date('Y-m-d'),
            'salary' => $this->faker->numberBetween(5000, 10000),
            'role_id' => $roleId,
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
