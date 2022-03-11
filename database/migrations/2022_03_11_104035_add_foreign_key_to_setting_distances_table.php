<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyToSettingDistancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('setting_distances', function (Blueprint $table) {
            $table->foreign('from_location')->references('slug')->on('setting_locations')->onUpdate('cascade');
            $table->foreign('to_location')->references('slug')->on('setting_locations')->onUpdate('cascade');
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
        Schema::table('setting_distances', function (Blueprint $table) {
            $table->dropForeign(['from_location']);
            $table->dropForeign(['to_location']);
            $table->dropForeign(['consignor']);
            $table->dropForeign(['created_by']);
        });
    }
}
