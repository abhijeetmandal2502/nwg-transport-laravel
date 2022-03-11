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
            $table->date('doj')->nullable()->comment('Date of joining');
            $table->date('dob')->nullable()->comment('Date of Birth');
            $table->decimal('salary', 10, 2)->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('view_pass', 100)->comment('for reading password');
            $table->rememberToken();
            $table->string('role_id')->comment('comming from roles table');
            $table->enum('status', ['Active', 'Inactive', 'Hold', 'Blocked'])->default('Active')->comment('Account Status');
            $table->string('created_by', 100)->nullable();
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
        Schema::dropIfExists('users');
    }
}
