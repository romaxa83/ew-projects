<?php

use App\Models\Order\Estimate\Local;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Local::TABLE, function (Blueprint $table) {
            $table->decimal("hours_min", 5, 2)->change();
            $table->decimal("hours_max", 5, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table(Local::TABLE, function (Blueprint $table) {
            $table->decimal("hours_min", 5, 1)->change();
            $table->decimal("hours_max", 5, 1)->change();
        });
    }
};
