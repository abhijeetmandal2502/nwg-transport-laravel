<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('emp_id')->unique();
            $table->string('name');
            $table->string('mobile')->unique();
            $table->string('email')->unique();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->dateTime('DOJ')->nullable()->comment('Date of joining');
            $table->date('DOB')->nullable()->comment('Date of Birth');
            $table->decimal('salary', 10, 2)->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('role_id')->nullable()->comment('comming from roles table');
            $table->enum('status', ['Active', 'Inactive', 'Hold', 'Blocked'])->default('Active')->comment('Account Status');
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
        Schema::dropIfExists('users');
    }
}
