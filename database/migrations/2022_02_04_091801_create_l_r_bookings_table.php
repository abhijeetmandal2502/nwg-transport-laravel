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
            $table->dateTime('indent_date', 0)->comment('unknown');
            $table->dateTime('reporting_date', 0)->comment('Loading date');
            $table->dateTime('booking_date', 0)->comment('LR Booking date');
            $table->string('from_location')->comment('pickup location');
            $table->string('to_location')->comment('destination location');
            $table->enum('status', ['fresh', 'vehicle-assigned', 'cancel', 'closed', 'hold', 'loading', 'unload'])->default('fresh')->comment('Booking Status');
            $table->String('driver_id')->nullable()->comment('driver information');
            $table->String('vehicle_id')->nullable()->comment('vehicle information');
            $table->decimal('amount')->nullable()->default(0)->comment('vehicle booking amount');
            $table->enum('is_advance_done', ['yes', 'no'])->default('no')->comment('advance payment status');
            $table->string('created_by', 100)->nullable()->comment('who created');
            $table->String('remark')->nullable()->comment('Remark if  any');
            $table->dateTime('closed_date', 0)->nullable()->comment('LR Closed date');
            $table->timestamps();
            $table->softDeletes();
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
