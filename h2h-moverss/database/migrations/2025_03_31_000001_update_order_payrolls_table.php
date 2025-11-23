<?php

use App\Models\Order\Payroll\Payroll;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Payroll::TABLE, function (Blueprint $table) {
            $table->decimal('cash_on_hand', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Payroll::TABLE, function (Blueprint $table) {
            $table->dropColumn('cash_on_hand');
        });
    }
};
