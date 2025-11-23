<?php

use App\Models\CashRegistry\CashRegistry;
use App\Models\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(CashRegistry::TABLE, function (Blueprint $table) {
            $table->id();

            $table->integer('employee_id')
                ->references('id')
                ->on(Employee::TABLE)
                ->onDelete('cascade');

            $table->boolean("active")->default(true);
            $table->decimal("cash_on_hand", 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(CashRegistry::TABLE);
    }
};
