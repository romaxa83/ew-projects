<?php

use App\Models\Commercial\Commissioning\Protocol;
use App\Models\Commercial\Commissioning\Question;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Question::TABLE,
            static function (Blueprint $table)
            {
                $table->increments('id');
                $table->string('answer_type', 20);
                $table->string('photo_type', 20);
                $table->integer('sort')->default(0);

                $table->unsignedInteger('protocol_id');
                $table->foreign('protocol_id')
                    ->on(Protocol::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Question::TABLE);
    }
};


