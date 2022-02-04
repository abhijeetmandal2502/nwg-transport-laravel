<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ConsignorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Consignor::factory(10)->create();
    }
}
