<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupportRequestsViewersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('support_requests_viewers', function (Blueprint $table) {
            $table->bigInteger('support_request_id');
            $table->bigInteger('admin_id');
            $table->foreign('support_request_id')
                ->references('id')
                ->on('support_requests')
                ->onDelete('cascade');
            $table->foreign('admin_id')
                ->references('id')
                ->on('admins');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('support_requests_viewers');
    }
}
