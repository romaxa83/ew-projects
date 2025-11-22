<?php

use App\Models\Companies\Price;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Price::TABLE,
            static function (Blueprint $table) {
                $table->text('desc')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(Price::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('desc');
            }
        );
    }
};
