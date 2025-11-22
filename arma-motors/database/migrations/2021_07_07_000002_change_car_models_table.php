<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCarModelsTable extends Migration
{
    public function up(): void
    {
        Schema::table('car_models', function (Blueprint $table) {
            $table->string('uuid', 150)->nullable()->change();
            $table->string('name', 350)->change();
        });
    }

    public function down(): void
    {
        Schema::table('car_models', function (Blueprint $table) {
            $table->string('uuid', 100)->nullable()->change();
            $table->string('name', 50)->change();
        });
    }
}
