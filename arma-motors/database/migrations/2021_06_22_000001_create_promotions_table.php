<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->string('type', 10)->index();
            $table->string('link')->nullable();

            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')
                ->references('id')
                ->on('dealership_departments')
                ->onDelete('cascade');

            $table->timestamp('start_at');
            $table->timestamp('finish_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
}
