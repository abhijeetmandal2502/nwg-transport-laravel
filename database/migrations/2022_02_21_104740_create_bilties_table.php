<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBiltiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bilties', function (Blueprint $table) {
            $table->id();
            $table->string('invoice')->comment('Unique invoice number with consignor');
            $table->string('booking_id')->comment('lr booking number');
            $table->string('shipment_no')->comment('shipment number');
            $table->integer('packages')->default(0)->comment('No of packages');
            $table->string('invoice_no')->comment('Invoice number');
            $table->dateTime('date')->comment('genrated date');
            $table->text('description')->comment('description of package');
            $table->string('gst_no')->nullable()->comment('shipment number');
            $table->decimal('weight', 10, 2)->default(0)->comment('weight in kg/tan/other');
            $table->string('unit', 50)->nullable()->comment('shipment number');
            $table->decimal('goods_value', 10, 2)->default(0)->comment('Package value in amount');
            $table->string('created_by')->nullable()->comment('who generated');
            $table->timestamps();
            $table->softDeletes();
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
        Schema::dropIfExists('bilties');
    }
}
