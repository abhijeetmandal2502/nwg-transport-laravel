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
            $table->string('lr_no')->comment('lr booking no');
            $table->string('action_type')->comment('transaction for');
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->enum('trans_type', ['credit', 'debit'])->nullable();
            $table->dateTime('created_at');
            $table->string('created_by');

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
