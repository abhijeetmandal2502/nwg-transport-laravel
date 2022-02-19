<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLRBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('l_r_bookings', function (Blueprint $table) {
            $table->id();
            $table->String('booking_id')->unique()->comment('custom id');
            $table->String('consignor_id')->comment('Sender id');
            $table->String('consignee_id')->comment('Receiver id');
            $table->String('other_id')->nullable()->comment('broker/vendor/third party id');
            $table->dateTime('indent_date', 0)->comment('unknown');
            $table->dateTime('reporting_date', 0)->comment('Loading date');
            $table->dateTime('booking_date', 0)->comment('LR Booking date');
            $table->string('from_location')->comment('pickup location');
            $table->string('to_location')->comment('destination location');
            $table->enum('active_status', ['active', 'closed'])->default('active')->comment('Booking Status');
            $table->String('other_status')->nullable()->comment('Other status if any');
            $table->String('driver_id')->nullable()->comment('Driver ID');
            $table->String('vehicle_id')->nullable()->comment('Vehicle ID');
            $table->decimal('amount')->nullable()->default(0)->comment('Booking Amount');
            $table->String('remark')->nullable()->comment('Remark if  any');
            $table->dateTime('closed_date', 0)->nullable()->comment('LR Closed date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('l_r_bookings');
    }
}
