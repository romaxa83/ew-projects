<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoyaltiesTable extends Migration
{
    public function up(): void
    {
        Schema::create('loyalties', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('brand_id');
            $table->foreign('brand_id')
                ->references('id')
                ->on('car_brands')
                ->onDelete('cascade');

            $table->boolean('active')->default(true);
            $table->string('type', 20);
            $table->string('age', 20)->nullable();
            $table->integer('discount');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalties');
    }
}
