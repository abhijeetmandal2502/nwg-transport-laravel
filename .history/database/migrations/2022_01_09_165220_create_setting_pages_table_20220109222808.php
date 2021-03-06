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
            $table->string('page_title')->comment('page name for menue');
            $table->string('page_url')->comment('url/route');
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
        Schema::dropIfExists('setting_pages');
    }
}
