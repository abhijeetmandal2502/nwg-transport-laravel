<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyToPetrolPumpPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('petrol_pump_payments', function (Blueprint $table) {
            $table->foreign('pump_id')->references('pump_id')->on('petrol_pumps')->onUpdate('cascade');
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
        Schema::table('petrol_pump_payments', function (Blueprint $table) {
            $table->dropForeign(['pump_id']);
            $table->dropForeign(['lr_no']);
            $table->dropForeign(['created_by']);
        });
    }
}
