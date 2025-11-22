<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupportRequestMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('support_request_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('support_request_id');
            $table->foreign('support_request_id')
                ->references('id')
                ->on('support_requests')
                ->onDelete('cascade');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_user_message')->default(false);
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
        Schema::dropIfExists('support_request_messages');
    }
}
