<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailVerifyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_verify', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('entity_type', 350);
            $table->integer('entity_id');
            $table->string('email_token')->nullable()->index()->unique();
            $table->dateTime('email_token_expires')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_verify');
    }
}
