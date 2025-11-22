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
                $table->unsignedInteger('shipping_price')->default(0);
                $table->unsignedInteger('tax')->default(0);
                $table->unsignedInteger('discount_percent')->nullable();
                $table->unsignedInteger('discount_sum')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(CommercialQuote::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('shipping_price');
                $table->dropColumn('tax');
                $table->dropColumn('discount_percent');
                $table->dropColumn('discount_sum');
            }
        );
    }
};

