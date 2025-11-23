<?php

use App\Models\Order\MobileEstimate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(MobileEstimate::TABLE, function (Blueprint $table) {
            $table->timestamp("inspection_origin_signed_at")->nullable();
            $table->timestamp("inspection_destination_signed_at")->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(MobileEstimate::TABLE, function (Blueprint $table) {
            $table->dropColumn("inspection_origin_signed_at");
            $table->dropColumn("inspection_destination_signed_at");
        });
    }
};
