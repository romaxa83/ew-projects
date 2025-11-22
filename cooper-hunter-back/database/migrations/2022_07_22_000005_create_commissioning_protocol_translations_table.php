<?php

use App\Models\Commercial\Commissioning\Protocol;
use App\Models\Commercial\Commissioning\ProtocolTranslation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(ProtocolTranslation::TABLE,
            static function (Blueprint $table)
            {
                $table->id();
                $table->string('title');
                $table->text('desc');

                $table->unsignedInteger('row_id');
                $table->foreign('row_id')
                    ->on(Protocol::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('language');
                $table->foreign('language')
                    ->on('languages')
                    ->references('slug')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(ProtocolTranslation::TABLE);
    }
};

