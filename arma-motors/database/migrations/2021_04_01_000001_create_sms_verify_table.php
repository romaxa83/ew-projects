<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsVerifyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_verify', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('phone');
            $table->string('sms_code');
            $table->string('sms_token')->nullable()->index()->unique();
            $table->dateTime('sms_token_expires')->nullable();
            $table->string('action_token')->nullable()->index()->unique();
            $table->dateTime('action_token_expires')->nullable();
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
        Schema::dropIfExists('sms_verify');
    }
}

