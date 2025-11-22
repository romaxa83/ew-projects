<?php


use App\Models\Forms\Draft;
use App\Models\Users\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Draft::TABLE, function (Blueprint $table) {
            $table->id();

            $table->json('body');

            $table->string('path');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->on(User::TABLE)
                ->references('id')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unique(['user_id', 'path']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Draft::TABLE);
    }
};

