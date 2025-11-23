<?php

use App\Models\Employee;
use App\Models\Order\Payroll\Payroll;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Payroll::TABLE, function (Blueprint $table) {
            $table->integer('creator_id')
                ->nullable()
                ->references('id')
                ->on(Employee::TABLE)
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table(Payroll::TABLE, function (Blueprint $table) {
            $table->dropColumn('creator_id');
        });
    }
};
