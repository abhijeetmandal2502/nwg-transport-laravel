<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingDistancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setting_distances', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->comment('unique slug');
            $table->string('from_location')->comment('location slug');
            $table->string('to_location')->comment('location slug');
            $table->decimal('distance')->default(0)->comment('distance in KM');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('setting_distances');
    }
}
