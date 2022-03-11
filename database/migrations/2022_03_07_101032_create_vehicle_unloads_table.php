<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleUnloadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_unloads', function (Blueprint $table) {
            $table->id();
            $table->string('lr_no');
            $table->date('arrive_date');
            $table->date('unload_date');
            $table->decimal('total_goods', 10, 2)->default(0)->comment('total loaded goods in vehicle');
            $table->decimal('receive_goods', 10, 2)->default(0)->comment('total received goods to vendor');
            $table->decimal('order_weight', 10, 2)->default(0)->comment('total bilty weight');
            $table->decimal('per_kg_rate', 10, 2)->default(0)->comment('if owner vehicle');
            $table->decimal('unload_charge', 10, 2)->default(0);
            $table->json('deductions')->nullable()->comment('All deductions details and amount');
            $table->decimal('total_amount', 10, 2)->default(0)->comment('total booking amount');
            $table->decimal('advance_amount', 10, 2)->default(0)->comment('total advance payment');
            $table->decimal('petrol_amount', 10, 2)->default(0)->comment('total petrol payment');
            $table->decimal('total_deduction', 10, 2)->default(0)->comment('total other deductions amount');
            $table->decimal('final_amount', 10, 2)->default(0)->comment('final payable amount');
            $table->decimal('paid_amount', 10, 2)->default(0)->comment('actual paid amount');
            $table->string('created_by', 100)->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();
            $table->softDeletes();
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
        Schema::dropIfExists('vehicle_unloads');
    }
}
