<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForFrontToCarOrderStatusesTable extends Migration
{
    public function up(): void
    {
        Schema::table('car_order_statuses', function (Blueprint $table) {
            $table->boolean('for_front')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('car_order_statuses', function (Blueprint $table) {
            $table->dropColumn('for_front');
        });
    }
}
