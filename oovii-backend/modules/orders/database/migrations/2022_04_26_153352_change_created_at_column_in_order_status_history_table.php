<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCreatedAtColumnInOrderStatusHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('order_status_history', function (Blueprint $table) {
            $table->dropTimestamps();
        });

        Schema::table('order_status_history', function (Blueprint $table) {
            $table->timestamps(3);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('order_status_history', function (Blueprint $table) {
            $table->dropTimestamps();
        });

        Schema::table('order_status_history', function (Blueprint $table) {
            $table->timestamps();
        });
    }
}
