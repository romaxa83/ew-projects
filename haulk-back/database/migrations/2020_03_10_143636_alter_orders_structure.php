<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterOrdersStructure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('has_pickup_inspection')->default(false);
            $table->boolean('has_pickup_signature')->default(false);
            $table->boolean('has_delivery_inspection')->default(false);
            $table->boolean('has_delivery_signature')->default(false);
            $table->boolean('is_billed')->default(false);
            $table->boolean('is_paid')->default(false);
            $table->boolean('is_archived')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('has_pickup_inspection');
            $table->dropColumn('has_pickup_signature');
            $table->dropColumn('has_delivery_inspection');
            $table->dropColumn('has_delivery_signature');
            $table->dropColumn('is_billed');
            $table->dropColumn('is_paid');
            $table->dropColumn('is_archived');
        });
    }
}
