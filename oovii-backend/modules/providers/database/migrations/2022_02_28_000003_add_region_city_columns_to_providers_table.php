<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRegionCityColumnsToProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(
            'providers',
            function (Blueprint $table) {
                $table->integer('region_code');
                $table->integer('city_code');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(
            'providers',
            function (Blueprint $table) {
                $table->dropColumn('region_code');
                $table->dropColumn('city_code');
            }
        );
    }
}
