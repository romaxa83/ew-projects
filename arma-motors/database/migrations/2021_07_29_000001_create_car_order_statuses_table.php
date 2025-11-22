<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarOrderStatusesTable extends Migration
{
    public function up(): void
    {
        Schema::create('car_order_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('active')->default(true);
            $table->integer('sort')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_order_statuses');
    }
}

