<?php

use App\Models\Localization\Language;
use App\Models\Localization\Translation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create(Translation::TABLE,
            function (Blueprint $table) {
                $table->id();
                $table->timestamps();

                $table->string('place');
                $table->string('key');
                $table->string('text')->nullable();

                $table->string('lang', 4);
                $table->foreign('lang')
                    ->references('slug')
                    ->on(Language::TABLE)
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->unique(['place', 'key', 'lang']);
            }
        );
    }

    public function down(): void
    {
//        Schema::table(
//            Translation::TABLE,
//            function (Blueprint $table) {
//                $table->dropUnique(['key', 'lang']);
//            }
//        );
        Schema::dropIfExists(Translation::TABLE);
    }
};
