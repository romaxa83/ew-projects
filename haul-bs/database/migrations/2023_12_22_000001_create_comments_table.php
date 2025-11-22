<?php

use App\Foundations\Modules\Comment\Models\Comment;
use App\Models\Users\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Comment::TABLE, function (Blueprint $table) {
            $table->id();
            $table->numericMorphs('model');

            $table->foreignId('author_id')
                ->references('id')
                ->on(User::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->text('text');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Comment::TABLE);
    }
};
