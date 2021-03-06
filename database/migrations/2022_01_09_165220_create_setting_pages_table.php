<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setting_pages', function (Blueprint $table) {
            $table->id();
            $table->string('page_slug')->unique()->comment('page slug withount space');
            $table->string('page_title')->comment('page title');
            $table->string('parent_title')->comment('main parent of this page');
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
        Schema::dropIfExists('setting_pages');
    }
}
