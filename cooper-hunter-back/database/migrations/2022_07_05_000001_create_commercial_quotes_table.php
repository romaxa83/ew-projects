<?php

use App\Enums\Commercial\CommercialQuoteStatusEnum;
use App\Models\Commercial\CommercialQuote;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(CommercialQuote::TABLE,
            static function (Blueprint $table) {
                $table->increments('id');

                $table->foreignId('commercial_project_id')
                    ->constrained()
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->string('email');
                $table->string('status', 10)
                    ->default(CommercialQuoteStatusEnum::PENDING);

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(CommercialQuote::TABLE);
    }
};
