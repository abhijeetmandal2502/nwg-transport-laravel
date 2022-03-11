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
            $table->enum('ownership', ['third-party', 'owned'])->comment('ownership type');
            $table->string('created_by')->comment('employee id ');
            $table->string('vehicle_details');
            $table->json('owner_details')->nullable()->comment('owner all details');
            $table->string('driver_id')->nullable()->comment('driver id from driver tbl');
            $table->string('state')->comment('vehicle state');
            $table->decimal('rating')->default(0)->comment('vehicle rating');
            $table->enum('active_status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
            // $table->foreign('driver_id')->references('driver_id')->on('setting_drivers')->onUpdate('cascade');
            // $table->foreign('created_by')->references('emp_id')->on('users')->onUpdate('cascade');
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
