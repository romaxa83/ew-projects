<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealershipTimeStepsTable extends Migration
{
    public function up(): void
    {
        Schema::create('dealership_time_steps', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('dealership_id');
            $table->foreign('dealership_id')
                ->references('id')
                ->on('dealerships')
                ->onDelete('cascade');

            $table->unsignedBigInteger('service_id');
            $table->foreign('service_id')
                ->references('id')
                ->on('services')
                ->onDelete('cascade');

            $table->integer('step');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dealership_time_steps');
    }
}
