<?php

use App\Models\Orders\Dealer\PackingSlip;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\Orders\Dealer as DealerEnum;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(PackingSlip::TABLE,
            static function (Blueprint $table) {
                $table->string('status', 10)
                    ->default(DealerEnum\OrderStatus::DRAFT());
            }
        );
    }

    public function down(): void
    {
        Schema::table(PackingSlip::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('status');
            }
        );
    }
};
