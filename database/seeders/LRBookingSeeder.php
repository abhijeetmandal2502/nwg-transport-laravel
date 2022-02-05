<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LRBookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\LRBooking::factory(10)->create();
    }
}
