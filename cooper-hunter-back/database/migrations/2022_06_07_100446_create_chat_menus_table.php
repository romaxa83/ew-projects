<?php

use App\Enums\Chat\ChatMenuActionEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'chat_menus',
            static function (Blueprint $table)
            {
                $table->id();

                $table->enum('action', ChatMenuActionEnum::getValues());
                $table
                    ->string('redirect_to')
                    ->nullable();

                $table
                    ->foreignId('parent_id')
                    ->nullable()
                    ->constrained('chat_menus')
                    ->references('id')
                    ->cascadeOnDelete();

                $table
                    ->boolean('active')
                    ->default(true);

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_menus');
    }
};
