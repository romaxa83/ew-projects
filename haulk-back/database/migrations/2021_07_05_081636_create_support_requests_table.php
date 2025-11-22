<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupportRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('support_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')
                ->nullable();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->unsignedBigInteger('carrier_id')
                ->nullable();
            $table->foreign('carrier_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->unsignedBigInteger('admin_id')
                ->nullable();
            $table->foreign('admin_id')
                ->references('id')
                ->on('admins');
            $table->string('user_name');
            $table->string('user_email');
            $table->string('user_phone');
            $table->string('subject');
            $table->smallInteger('status')
                ->default(\App\Models\Saas\Support\SupportRequest::STATUS_NEW);
            $table->smallInteger('label');
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
        Schema::dropIfExists('support_requests');
    }
}
