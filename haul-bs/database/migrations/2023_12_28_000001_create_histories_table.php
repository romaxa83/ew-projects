<?php

use App\Foundations\Modules\History\Models\History;
use App\Models\Users\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(History::TABLE, function (Blueprint $table) {
            $table->id();
            $table->string('type', 15);
            $table->numericMorphs('model');

            $table->foreignId('user_id')->nullable()
                ->references('id')
                ->on(User::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');


            $table->string('user_role', 30)->nullable();
            $table->string('msg');
            $table->json('msg_attr')->nullable();
            $table->timestamp('performed_at');
            $table->string('performed_timezone', 30)->nullable();
            $table->json('details')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(History::TABLE);
    }
};
