<?php

use App\Enums\Solutions\SolutionIndoorEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'solutions',
            static function (Blueprint $table) {
                $table->enum(
                    'indoor_type',
                    SolutionIndoorEnum::getValues()
                )
                    ->nullable()
                    ->change();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'solutions',
            static function (Blueprint $table) {
            }
        );
    }
};
