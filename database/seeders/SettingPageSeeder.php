<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\SettingPage::factory(30)->create();
    }
}
