<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFcmNotificationTemplateTranslationsTable extends Migration
{
    public function up(): void
    {
        Schema::create('notification_template_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('lang');
            $table->string('title');
            $table->string('text', 2000);

            $table->bigInteger('model_id')->unsigned();
            $table->foreign('model_id')
                ->references('id')
                ->on('notification_templates')
                ->onDelete('cascade');

            $table->unique(['model_id', 'lang']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_template_translations');
    }
}
