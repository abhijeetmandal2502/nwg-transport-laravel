<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePetrolPumpPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('petrol_pump_payments', function (Blueprint $table) {
            $table->id();
            $table->string('tr_id');
            $table->string('lr_no');
            $table->decimal('amount', 10, 2);
            $table->string('hsb_msd')->comment('particular number');
            $table->string('pump_id')->comment('petrol pump id');
            $table->string('method')->nullable()->comment('payment method');
            $table->string('txn_id')->nullable()->comment('payment id');
            $table->string('cheque_no')->nullable()->comment('if method cheque');
            $table->dateTime('create_at');
            $table->string('created_by');
            $table->timestamps();
            $table->softDeletes();
            // $table->foreign('pump_id')->references('pump_id')->on('petrol_pumps')->onUpdate('cascade');
            // $table->foreign('lr_no')->references('booking_id')->on('l_r_bookings')->onUpdate('cascade');
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
        Schema::dropIfExists('petrol_pump_payments');
    }
}
