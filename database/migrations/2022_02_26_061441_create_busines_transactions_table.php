<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinesTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('busines_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('tr_id')->unique()->comment('Transaction id');
            $table->string('lr_no')->nullable()->comment('lr booking no');
            $table->string('action_type')->comment('transaction for');
            $table->json('description');
            $table->decimal('amount', 10, 2);
            $table->enum('trans_type', ['credit', 'debit'])->nullable();
            $table->dateTime('created_at');
            $table->string('created_by');
            $table->softDeletes();
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
        Schema::dropIfExists('busines_transactions');
    }
}
