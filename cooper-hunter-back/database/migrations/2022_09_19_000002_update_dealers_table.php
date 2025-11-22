<?php

use App\Models\Dealers\Dealer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Dealer::TABLE,
            static function (Blueprint $table) {
                $table->boolean('is_main_company')->default(false);
            }
        );
    }

    public function down(): void
    {
        Schema::table(Dealer::TABLE, function (Blueprint $table) {
            $table->dropColumn(['is_main_company']);
        });
    }
};
