<?php

use App\Models\Commercial\CommercialQuote;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(CommercialQuote::TABLE,
            static function (Blueprint $table) {
                $table->string('shipping_address')->after('email')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(CommercialQuote::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('shipping_address');
            }
        );
    }
};
