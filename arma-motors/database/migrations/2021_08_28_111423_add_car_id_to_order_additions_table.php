<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCarIdToOrderAdditionsTable extends Migration
{
    public function up(): void
    {
        Schema::table('order_additions', function (Blueprint $table) {
            $table->timestamp('on_date')->nullable();
            $table->string('comment', 1000)->nullable();
            $table->integer('mileage')->nullable();

            $table->unsignedBigInteger('car_id')->nullable()->after('order_id');
            $table->foreign('car_id')
                ->references('id')
                ->on('user_cars');

            $table->unsignedBigInteger('dealership_id')->nullable()->after('duration_id');
            $table->foreign('dealership_id')
                ->references('id')
                ->on('dealerships');
        });
    }

    public function down(): void
    {
        Schema::table('order_additions', function (Blueprint $table) {
            $table->dropForeign('order_additions_car_id_foreign');
            $table->dropColumn('car_id');

            $table->dropForeign('order_additions_dealership_id_foreign');
            $table->dropColumn('dealership_id');

            $table->dropColumn('on_date');
            $table->dropColumn('comment');
            $table->dropColumn('mileage');
        });
    }
}
