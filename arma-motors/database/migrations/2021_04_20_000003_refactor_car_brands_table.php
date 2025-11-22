<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefactorCarBrandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('car_brands', function (Blueprint $table) {
            $table->string('uuid', 100)->nullable()->change();
            $table->tinyInteger('color')->default(\App\Models\Catalogs\Car\Brand::COLOR_NONE);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('car_brands', function (Blueprint $table) {
            $table->string('uuid', 50)->nullable()->change();
            $table->dropColumn('color');
        });
    }
}
