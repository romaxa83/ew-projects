<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJdSizeParametersTable extends Migration
{
    public function up(): void
    {
        Schema::create('jd_size_parameters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('jd_id')->comment('Поле id из импорта');
            $table->boolean('status')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jd_size_parameters');
    }
}
