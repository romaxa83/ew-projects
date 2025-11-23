<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventAfterCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_after_calls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('project_id')->nullable();
            $table->string('call_id', 50)->nullable();
            $table->string('type', 10)->nullable();
            $table->string('scheme_name', 50)->nullable();
            $table->string('status', 50)->nullable();
            $table->string('destination', 20)->nullable();
            $table->string('number_e164', 20)->nullable();
            $table->string('caller_number', 20)->nullable();
            $table->string('employee', 100)->nullable();
            $table->string('employee_estension', 10)->nullable();
            $table->string('employee_id', 20)->nullable();
            $table->boolean('recording_presence')->nullable();
            $table->string('recording', 255)->nullable();
            $table->string('recording_wav', 255)->nullable();
            $table->unsignedInteger('duration_call')->nullable();
            $table->unsignedInteger('duration_conversation')->nullable();
            $table->unsignedInteger('duration_waiting')->nullable();
            $table->dateTime('call_date')->nullable();
            $table->unsignedBigInteger('call_timestamp')->nullable();
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
        Schema::dropIfExists('event_after_calls');
    }
}
