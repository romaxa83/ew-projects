<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Core\Models\Administrator;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Collection::TABLE,
            static function (Blueprint $table) {
                $table->id();
                $table->boolean('published')->default(true);

                $table->unsignedBigInteger('creator_id');
                $table->foreign('creator_id')
                    ->references('id')
                    ->on(Administrator::TABLE)
                    ->onDelete('cascade');

                $table->unsignedBigInteger('moderator_id')->nullable();
                $table->foreign('moderator_id')
                    ->references('id')
                    ->on(Administrator::TABLE)
                    ->onDelete('cascade');

                $table->timestamp('start_at')->nullable();
                $table->timestamp('end_at')->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Collection::TABLE);
    }
};
