<?php

use App\Models\Orders\Dealer\PackingSlip;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(PackingSlip::TABLE,
            static function (Blueprint $table) {
                $table->json('files')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(PackingSlip::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('files');
            }
        );
    }
};
