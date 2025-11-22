<?php

use App\Models\Commercial\CommercialSettings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(CommercialSettings::TABLE,
            static function (Blueprint $table) {
                $table->string('quote_title')->nullable();
                $table->string('quote_address_line_1')->nullable();
                $table->string('quote_address_line_2')->nullable();
                $table->string('quote_phone')->nullable();
                $table->string('quote_email')->nullable();
                $table->string('quote_site')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(CommercialSettings::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('quote_title');
                $table->dropColumn('quote_address_line_1');
                $table->dropColumn('quote_address_line_2');
                $table->dropColumn('quote_phone');
                $table->dropColumn('quote_email');
                $table->dropColumn('quote_site');
            }
        );
    }
};
