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
            $table->decimal('unload_charge')->default(0);
            $table->json('deductions')->nullable()->comment('All deductions details and amount');
            $table->string('created_by', 100)->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
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
        Schema::dropIfExists('vehicle_unloads');
    }
}
