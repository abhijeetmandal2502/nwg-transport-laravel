<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\SettingLocation::factory(10)->create();
    }
}
