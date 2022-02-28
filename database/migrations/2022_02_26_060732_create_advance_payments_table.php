<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvancePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advance_payments', function (Blueprint $table) {
            $table->id();
            $table->string('tr_id');
            $table->string('lr_no');
            $table->decimal('amount', 10, 2);
            $table->string('narration')->comment('remark');
            $table->string('method')->comment('payment method');
            $table->string('txn_id')->nullable()->comment('payment id');
            $table->string('cheque_no')->nullable()->comment('if method cheque');
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
        Schema::dropIfExists('advance_payments');
    }
}
