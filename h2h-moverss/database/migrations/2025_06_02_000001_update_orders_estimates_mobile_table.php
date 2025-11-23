<?php

use App\Models\Order\MobileEstimate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(MobileEstimate::TABLE, function (Blueprint $table) {
            $table->timestamp("waiver_failure_to_protect_property_signed_at")->nullable();
            $table->timestamp("waiver_oversized_object_handling_signed_at")->nullable();
            $table->timestamp("waiver_custom_reason_signed_at")->nullable();
            $table->mediumText("waiver_custom_reason")->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(MobileEstimate::TABLE, function (Blueprint $table) {
            $table->dropColumn("waiver_failure_to_protect_property_signed_at");
            $table->dropColumn("waiver_oversized_object_handling_signed_at");
            $table->dropColumn("waiver_custom_reason_signed_at");
            $table->dropColumn("waiver_custom_reason");
        });
    }
};
