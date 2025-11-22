<?php

use App\Models\Commercial\Commissioning\ProjectProtocolQuestion;
use App\Models\Locations\State;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(State::TABLE,
            static function (Blueprint $table) {
                $table->string('slug', 100)->after('short_name')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(State::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('slug');
            }
        );
    }
};




