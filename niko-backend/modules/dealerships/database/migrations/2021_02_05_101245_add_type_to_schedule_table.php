<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dealership_schedules', function (Blueprint $table) {
            $table->tinyInteger('type')->default(\WezomCms\Dealerships\Models\Schedule::TYPE_SALON);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dealership_schedules', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
