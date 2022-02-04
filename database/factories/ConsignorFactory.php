<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ConsignorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'cons_id' => $this->faker->unique()->userName(),
            'name' => $this->faker->name(),
            'mobile' => $this->faker->unique()->e164PhoneNumber(),
            'alt_mobile' => $this->faker->unique()->e164PhoneNumber(),
            'gst_no' => $this->faker->iban(),
            'pan_no' => $this->faker->swiftBicNumber(),
            'aadhar_no' => $this->faker->isbn10(),
            'address1' => $this->faker->streetName(),
            'address2' => $this->faker->streetAddress(),
            'country' => $this->faker->country(),
            'state' => $this->faker->state(),
            'city' => $this->faker->city(),
            'pin_code' => $this->faker->postcode(),
            'cons_type' => $this->faker->randomElement(['consignor', 'consignee', 'other']),
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }
}
