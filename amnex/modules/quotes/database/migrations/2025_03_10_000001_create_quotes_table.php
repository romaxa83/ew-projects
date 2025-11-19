<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Wezom\Quotes\Models\Quote;
use Wezom\Users\Models\User;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create(Quote::TABLE, function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')
                ->references('id')
                ->on(User::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('status', 50);
            $table->string('container_number', 255)
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Quote::TABLE);
    }
};
