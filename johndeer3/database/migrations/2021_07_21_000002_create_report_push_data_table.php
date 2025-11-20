<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportPushDataTable extends Migration
{
    public function up(): void
    {
        Schema::create('reports_push_data', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('report_id');
            $table->foreign('report_id')
                ->references('id')
                ->on('reports')
                ->onDelete('cascade');

            $table->boolean('send_push')->default(false);
            $table->timestamp('planned_at')->nullable();
            $table->timestamp('prev_planned_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports_push_data');
    }
}
