<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClosedAtToServicesOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services_orders', function (Blueprint $table) {
            $table->dateTime('closed_at')->nullable();
            $table->string('mileage')->default(0);
            $table->dropColumn('additional');
            $table->dropColumn('vehicle');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('services_orders', function (Blueprint $table) {
            $table->dropColumn('closed_at');
            $table->dropColumn('mileage');
            $table->json('additional')->nullable();
            $table->json('vehicle')->nullable();
        });
    }
}
