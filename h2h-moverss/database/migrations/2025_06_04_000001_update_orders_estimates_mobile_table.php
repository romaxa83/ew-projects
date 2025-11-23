<?php

use App\Models\Order\MobileEstimate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(MobileEstimate::TABLE, function (Blueprint $table) {
            $table->json("waiver_client_name")->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(MobileEstimate::TABLE, function (Blueprint $table) {
            $table->dropColumn("waiver_client_name");
        });
    }
};
