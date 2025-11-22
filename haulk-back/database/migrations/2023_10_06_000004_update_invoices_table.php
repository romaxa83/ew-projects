<?php

use App\Models\Billing\Invoice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Invoice::TABLE_NAME, function (Blueprint $table) {
            $table->decimal('drivers_amount', 10, 2)->default(0);
        });

        DB::table(Invoice::TABLE_NAME)->update([
            'drivers_amount' => DB::raw('amount')
        ]);
    }

    public function down(): void
    {
        Schema::table(Invoice::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('drivers_amount');
        });
    }
};








