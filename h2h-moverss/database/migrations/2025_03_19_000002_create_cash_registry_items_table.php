<?php

use App\Models\CashRegistry\CashRegistry;
use App\Models\CashRegistry\CashRegistryItem;
use App\Models\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(CashRegistryItem::TABLE, function (Blueprint $table) {
            $table->id();

            $table->integer('cash_registry_id')
                ->references('id')
                ->on(CashRegistry::TABLE)
                ->onDelete('cascade');

            $table->integer('executor_id')
                ->nullable()
                ->references('id')
                ->on(Employee::TABLE)
                ->onDelete('cascade');

            $table->string("type", 40);
            $table->decimal("sum", 10, 2);
            $table->timestamp("insert_at");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(CashRegistryItem::TABLE);
    }
};
