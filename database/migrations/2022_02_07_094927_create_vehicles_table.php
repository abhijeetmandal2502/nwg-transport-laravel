<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_no')->unique();
            $table->string('type')->comment('vehicle category');
            $table->string('created_by')->comment('employee id ');
            $table->string('vehicle_details');
            $table->json('owner_details')->comment('owner all details');
            $table->json('driver_details')->nullable()->comment('driver all details');
            $table->string('state')->comment('vehicle state');
            $table->decimal('rating')->default(0)->comment('vehicle rating');
            $table->enum('active_status', ['active', 'inactive'])->default('active');
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
        Schema::dropIfExists('vehicles');
    }
}
