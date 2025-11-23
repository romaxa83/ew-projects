<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Imports\Models\Import;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Import::TABLE,
            static function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('user_id')->nullable();
                $table->foreign('user_id')
                    ->on('users')
                    ->references('id')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');

                $table->string('type');
                $table->string('status')->default(Import::STATUS_NEW);
                $table->longText('message')->nullable();
                $table->longText('error_data')->nullable();
                $table->string('file')->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Import::TABLE);
    }
};
