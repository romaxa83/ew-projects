<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeFieldPushDataTable extends Migration
{
    public function up(): void
    {
        Schema::table('reports_push_data', function (Blueprint $table) {
            $table->boolean('is_send_start_day')->default(false);
            $table->boolean('is_send_end_day')->default(false);
            $table->boolean('is_send_week')->default(false);
            $table->dropColumn('send_push');
        });
    }

    public function down(): void
    {
        Schema::table('reports_push_data', function (Blueprint $table) {
            $table->dropColumn('is_send_start_day');
            $table->dropColumn('is_send_end_day');
            $table->dropColumn('is_send_week');
            $table->boolean('send_push')->default(false);
        });
    }
}
