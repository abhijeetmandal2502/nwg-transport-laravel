<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyToVehicleUnloadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicle_unloads', function (Blueprint $table) {
            $table->foreign('lr_no')->references('booking_id')->on('l_r_bookings')->onUpdate('cascade');
            $table->foreign('created_by')->references('emp_id')->on('users')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicle_unloads', function (Blueprint $table) {
            $table->dropForeign(['lr_no']);
            $table->dropForeign(['created_by']);
        });
    }
}
