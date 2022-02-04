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
            $table->String('cons_id')->unique()->comment('consign(or/ee) id');
            $table->String('name')->comment('consign(or/ee) name');
            $table->String('mobile')->comment('consign(or/ee) mobile');
            $table->String('alt_mobile')->nullable()->comment('consign(or/ee) alt mobile');
            $table->String('gst_no')->nullable()->comment('consign(or/ee) gst no');
            $table->String('pan_no')->nullable()->comment('consign(or/ee) pan no');
            $table->String('aadhar_no')->nullable()->comment('consign(or/ee) aadhar no');
            $table->String('address1')->nullable()->comment('consign(or/ee) address1');
            $table->String('address2')->nullable()->comment('consign(or/ee) address2');
            $table->String('country')->comment('consign(or/ee) country');
            $table->String('state')->comment('consign(or/ee) state');
            $table->String('city')->comment('consign(or/ee) city');
            $table->String('pin_code')->comment('consign(or/ee) pin_code');
            $table->String('email')->nullable()->comment('consign(or/ee) email');
            $table->enum('active_status', ['active', 'inactive', 'hold'])->default('active');
            $table->enum('cons_type', ['consignor', 'consignee', 'other'])->nullable()->comment('consignor/consignee)');
            $table->timestamps();
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
