<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverLicensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('driver_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')
                ->references('id')->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->string('license_number', 16)->nullable();
            $table->date('issuing_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('issuing_country')->nullable();
            $table->foreignId('issuing_state_id')
                ->nullable()
                ->references('id')->on('states')
                ->onDelete('set null')
                ->onUpdate('cascade');
            $table->string('category', 10)->nullable();
            $table->string('category_name', 10)->nullable();
            $table->string('type', 10);
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
        Schema::dropIfExists('driver_licenses');
    }
}
