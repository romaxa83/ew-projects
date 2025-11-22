<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceDurationServiceRelationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_duration_service_relation', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id');
            $table->foreign('service_id')
                ->references('id')
                ->on('services')
                ->onDelete('cascade');
            $table->unsignedBigInteger('duration_id');
            $table->foreign('duration_id')
                ->references('id')
                ->on('service_durations')
                ->onDelete('cascade');

            $table->primary(['service_id', 'duration_id'], 'pk-sdsr_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_duration_service_relation');
    }
}
