<?php

use App\Models\Tags\Tag;
use App\Models\Tags\Taggable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Taggable::TABLE, function (Blueprint $table) {

            $table->foreignId('tag_id')
                ->references('id')
                ->on(Tag::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unsignedBigInteger('taggable_id')->index();
            $table->string('taggable_type')->index();

            $table->unique(['tag_id', 'taggable_id', 'taggable_type']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Taggable::TABLE);
    }
};
