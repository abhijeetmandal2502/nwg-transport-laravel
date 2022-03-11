<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingDriversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setting_drivers', function (Blueprint $table) {
            $table->id();
            $table->string('driver_id')->unique()->comment('system generated');
            $table->string('name')->comment('driver name');
            $table->string('mobile')->unique()->comment('unique mobile');
            $table->string('DL_no')->unique()->comment('unique DL_no');
            $table->date('DL_expire')->comment('Dl expire date');
            $table->string('address')->comment('driver address');
            $table->string('city')->comment('driver city');
            $table->string('country')->comment('driver country');
            $table->string('state')->comment('driver state');
            $table->string('alt_mobile')->nullable()->comment('second mobile no');
            $table->string('created_by')->comment('who add driver');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('driver active status');
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
        Schema::dropIfExists('setting_drivers');
    }
}
