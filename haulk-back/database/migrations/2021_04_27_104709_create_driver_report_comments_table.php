<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverReportCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('driver_report_comments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('carrier_id');
            $table->unsignedBigInteger('driver_report_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('role_id');
            $table->text('comment');

            $table->index('carrier_id');

            $table->foreign('driver_report_id')
                ->references('id')->on('driver_reports')
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
        Schema::dropIfExists('driver_report_comments');
    }
}
