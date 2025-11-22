<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminsTable extends Migration
{

    public function up()
    {
        Schema::create(
            'admins',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('full_name')->nullable();
                $table->string('email')->unique();
                $table->string('phone')->nullable();
                $table->boolean('status')->default(true);
                $table->string('password')->nullable();
                $table->timestamps();
                $table->softDeletes();
            }
        );
    }

    public function down()
    {
        Schema::dropIfExists('admins');
    }
}
