<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveSendPushToReportTable extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('planned_at');
            $table->dropColumn('send_push');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->timestamp('planned_at')->nullable();
            $table->boolean('send_push')->default(false);
        });
    }
}
