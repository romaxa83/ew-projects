<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCarBrandsTable extends Migration
{
    public function up(): void
    {
        Schema::table('car_brands', function (Blueprint $table) {
            $table->string('uuid', 150)->nullable()->change();
            $table->string('name', 250)->change();
        });
    }

    public function down(): void
    {
        Schema::table('car_brands', function (Blueprint $table) {
            $table->string('uuid', 30)->nullable()->change();
            $table->string('name', 50)->change();
        });
    }
}
