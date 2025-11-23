<?php

use App\Models\Employee;
use App\Models\Order\MobileEstimate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(MobileEstimate::TABLE, function (Blueprint $table) {
            $table->unsignedInteger('bol_signed_employee_id')->nullable();
            $table->foreign('bol_signed_employee_id')
                ->references('id')
                ->on(Employee::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unsignedInteger('estimate_signed_employee_id')->nullable();
            $table->foreign('estimate_signed_employee_id')
                ->references('id')
                ->on(Employee::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table(MobileEstimate::TABLE, function (Blueprint $table) {
            $table->dropForeign([
                'bol_signed_employee_id',
            ]);
            $table->dropForeign([
                'estimate_signed_employee_id',
            ]);
            $table->dropColumn([
                'bol_signed_employee_id',
                'estimate_signed_employee_id'
            ]);
        });
    }
};
