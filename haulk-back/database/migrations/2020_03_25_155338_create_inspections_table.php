<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInspectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inspections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->unsignedBigInteger('vehicle_id');

            $table->string('vin');
            $table->boolean('has_vin_inspection')->default(false);
            $table->boolean('has_exterior_inspection')->default(false);

            $table->boolean('condition_dark')->default(false);
            $table->boolean('condition_snow')->default(false);
            $table->boolean('condition_rain')->default(false);
            $table->boolean('condition_dirty_vehicle')->default(false);

            $table->string('odometer')->nullable();
            $table->string('notes')->nullable();

            $table->tinyInteger('num_keys')->default(0);
            $table->tinyInteger('num_remotes')->default(0);
            $table->tinyInteger('num_headrests')->default(0);

            $table->boolean('drivable')->default(false);
            $table->boolean('windscreen')->default(false);
            $table->boolean('glass_all_intact')->default(false);
            $table->boolean('title')->default(false);
            $table->boolean('cargo_cover')->default(false);
            $table->boolean('spare_tire')->default(false);
            $table->boolean('radio')->default(false);
            $table->boolean('manuals')->default(false);
            $table->boolean('navigation_disk')->default(false);
            $table->boolean('plugin_charger_cable')->default(false);
            $table->boolean('headphones')->default(false);

            $table->foreign('vehicle_id')
                ->references('id')->on('vehicles')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
        });
        Schema::dropIfExists('inspections');
    }
}
