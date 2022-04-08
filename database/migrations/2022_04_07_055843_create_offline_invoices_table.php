<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfflineInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offline_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('bill_no')->unique();
            $table->text('lr_no')->comment('lr number list');
            $table->decimal('total_weight', 10, 2)->default(0)->comment('all lr total weight');
            $table->decimal('system_amount', 10, 2)->default(0)->comment('sytem genrated amount');
            $table->decimal('process_amount', 10, 2)->default(0)->comment('sent bill to the vendor');
            $table->decimal('received_amount', 10, 2)->default(0)->comment('final received amount from the vendor');
            $table->decimal('tds_amount', 10, 2)->default(0)->comment('tds amount if any');
            $table->enum('status', ['processing', 'approved'])->default('processing');
            $table->string('narration')->nullable()->comment('remark or description if any');
            $table->dateTime('final_date')->nullable()->comment('final date when received');
            $table->string('created_by');
            $table->timestamps();
            $table->softDeletes();
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
        Schema::dropIfExists('offline_invoices');
    }
}
