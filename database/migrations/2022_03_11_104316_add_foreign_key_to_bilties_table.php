<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyToBiltiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bilties', function (Blueprint $table) {
            $table->foreign('gst_no')->references('gst_no')->on('consignors')->onUpdate('cascade');
            $table->foreign('booking_id')->references('booking_id')->on('l_r_bookings')->onUpdate('cascade');
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
        Schema::table('bilties', function (Blueprint $table) {
            $table->dropForeign(['gst_no']);
            $table->dropForeign(['booking_id']);
            $table->dropForeign(['created_by']);
        });
    }
}
