<?php

use App\Models\Locations\Country;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Country::TABLE,
            static function (Blueprint $table) {
                $table->string('country_code', 10)->unique()->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(Country::TABLE, function (Blueprint $table) {
            $table->dropColumn(['country_code']);
        });
    }
};


