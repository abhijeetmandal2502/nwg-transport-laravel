<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingDistancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setting_distances', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->comment('unique slug');
            $table->string('consignor')->comment('vendor slug');
            $table->string('from_location')->comment('location slug');
            $table->string('to_location')->comment('location slug');
            $table->decimal('distance', 10, 2)->default(0)->comment('distance in KM');
            $table->decimal('own_per_kg_rate', 10, 2)->default(0)->comment('amount/kg for own');
            $table->decimal('vendor_per_kg_rate', 10, 2)->default(0)->comment('amount/kg for vendor');
            $table->string('created_by', 100)->comment('who created');
            $table->timestamps();
            $table->softDeletes();
            // $table->foreign('from_location')->references('slug')->on('setting_locations')->onUpdate('cascade');
            // $table->foreign('to_location')->references('slug')->on('setting_locations')->onUpdate('cascade');
            // $table->foreign('consignor')->references('slug')->on('vendor_lists')->onUpdate('cascade');
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
        Schema::dropIfExists('setting_distances');
    }
}
