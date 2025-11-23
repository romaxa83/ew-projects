<?php

use App\Models\Order\StatusGroup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(StatusGroup::TABLE, function (Blueprint $table) {
            $table->tinyInteger('in_funel_report')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table(StatusGroup::TABLE, function (Blueprint $table) {
            $table->dropColumn('in_funel_report');
        });
    }
};
