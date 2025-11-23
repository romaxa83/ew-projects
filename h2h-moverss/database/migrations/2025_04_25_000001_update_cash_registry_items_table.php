<?php

use App\Models\CashRegistry\CashRegistryItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(CashRegistryItem::TABLE, function (Blueprint $table) {
            $table->decimal("balance", 10, 2)->default(0);
            $table->timestamp("insert_date")->nullable();
            $table->dropColumn("insert_at");
        });
    }

    public function down(): void
    {
        Schema::table(CashRegistryItem::TABLE, function (Blueprint $table) {
            $table->dropColumn('balance');
            $table->dropColumn('insert_date');
            $table->timestamp("insert_at");
        });
    }
};
