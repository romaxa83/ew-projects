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
                $table->timestamp('closed_at')->nullable();
                $table->boolean('send_detail_data')->default(true);
                $table->tinyInteger('count_email_sending')->default(0);
            }
        );
    }

    public function down(): void
    {
        Schema::table(CommercialQuote::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('closed_at');
                $table->dropColumn('send_detail_data');
                $table->dropColumn('count_email_sending');
            }
        );
    }
};

