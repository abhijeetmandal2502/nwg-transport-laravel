<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyToConsignorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consignors', function (Blueprint $table) {
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
        Schema::table('consignors', function (Blueprint $table) {
            $table->dropForeign(['location']);
            $table->dropForeign(['consignor']);
            $table->dropForeign(['created_by']);
        });
    }
}
