<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarOrderStatusTranslationsTable extends Migration
{
    public function up()
    {
        Schema::create('car_order_status_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('lang')->index();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('model_id');

            $table->unique(['model_id', 'lang']);
            $table->foreign('model_id')
                ->references('id')
                ->on('car_order_statuses')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_order_status_translations');
    }
}
