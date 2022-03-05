<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_lists', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->comment('vendor slug');
            $table->string('name')->comment('vendor name');
            $table->string('created_by')->comment('who created');
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
        Schema::dropIfExists('vendor_lists');
    }
}