<?php

use App\Enums\Commercial\Commissioning\QuestionStatus;
use App\Models\Commercial\Commissioning\Question;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Question::TABLE,
            static function (Blueprint $table) {
                $table->string('status', 10)
                    ->after('photo_type')
                    ->default(QuestionStatus::DRAFT)
                ;
            }
        );
    }

    public function down(): void
    {
        Schema::table(Question::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('status');
            }
        );
    }
};

