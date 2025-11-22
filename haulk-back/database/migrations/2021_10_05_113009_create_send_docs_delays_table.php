<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSendDocsDelaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('send_docs_delays', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('order_id');
            $table->string('inspection_type');
            $table->json('request_data');

            $table->unique(
                [
                    'order_id',
                    'inspection_type',
                ]
            );

            $table->foreign('order_id')
                ->references('id')->on('orders')
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
        Schema::table('send_docs_delays', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropUnique(
                [
                    'order_id',
                    'inspection_type',
                ]
            );
        });

        Schema::dropIfExists('send_docs_delays');
    }
}
