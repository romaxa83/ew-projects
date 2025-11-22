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
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(Dealer::TABLE, function (Blueprint $table) {
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
        });
    }
};

