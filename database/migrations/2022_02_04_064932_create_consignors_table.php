<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConsignorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consignors', function (Blueprint $table) {
            $table->id();
            $table->String('cons_id')->unique()->comment('vendor sub slug');
            $table->string('consignor')->comment('Main vendor slug');
            $table->String('name')->comment('vendor sub name');
            $table->String('mobile')->comment('vendor mobile');
            $table->String('alt_mobile')->nullable()->comment('vendor alt mobile');
            $table->String('gst_no')->nullable()->comment('vendor gst no');
            $table->String('pan_no')->nullable()->comment('vendor pan no');
            $table->String('location')->comment('vendor location');
            $table->String('address')->nullable()->comment('vendor address');
            $table->String('country')->comment('vendor country');
            $table->String('state')->comment('vendor state');
            $table->String('city')->comment('vendor city');
            $table->String('pin_code')->comment('vendor pin_code');
            $table->String('email')->nullable()->comment('vendor email');
            $table->enum('active_status', ['active', 'inactive', 'hold'])->default('active');
            $table->string('created_by');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('location')->references('slug')->on('setting_locations')->onUpdate('cascade');
            $table->foreign('consignor')->references('slug')->on('vendor_lists')->onUpdate('cascade');
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
        Schema::dropIfExists('consignors');
    }
}
