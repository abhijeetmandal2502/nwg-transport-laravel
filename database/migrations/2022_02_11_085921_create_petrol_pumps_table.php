<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePetrolPumpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('petrol_pumps', function (Blueprint $table) {
            $table->id();
            $table->string('pump_id')->unique()->comment('system generated');
            $table->string('pump_name');
            $table->string('mobile')->unique()->comment('pump mobile');
            $table->string('alt_mobile')->nullable()->comment('second mobile no');
            $table->string('address')->comment('pump address');
            $table->string('city')->comment('pump city');
            $table->string('country')->comment('pump country');
            $table->string('state')->comment('pump state');
            $table->string('created_by')->comment('who add pump');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('pump active status');
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
        Schema::dropIfExists('petrol_pumps');
    }
}
