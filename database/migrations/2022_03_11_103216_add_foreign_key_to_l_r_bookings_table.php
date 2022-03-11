<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyToLRBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('l_r_bookings', function (Blueprint $table) {
            $table->foreign('consignor_id')->references('cons_id')->on('consignors')->onUpdate('cascade');
            $table->foreign('consignee_id')->references('cons_id')->on('consignors')->onUpdate('cascade');
            $table->foreign('from_location')->references('slug')->on('setting_locations')->onUpdate('cascade');
            $table->foreign('to_location')->references('slug')->on('setting_locations')->onUpdate('cascade');
            $table->foreign('driver_id')->references('driver_id')->on('setting_drivers')->onUpdate('cascade');
            $table->foreign('vehicle_id')->references('vehicle_no')->on('vehicles')->onUpdate('cascade');
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
        Schema::table('l_r_bookings', function (Blueprint $table) {
            $table->dropForeign(['consignor_id']);
            $table->dropForeign(['consignee_id']);
            $table->dropForeign(['from_location']);
            $table->dropForeign(['to_location']);
            $table->dropForeign(['driver_id']);
            $table->dropForeign(['vehicle_id']);
            $table->dropForeign(['created_by']);
        });
    }
}
