<?php

use App\Models\Calls\History;
use App\Models\Employees\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(History::TABLE,
            function (Blueprint $table) {
                $table->unsignedInteger('from_employee_id')
                    ->after('employee_id')->nullable();
                $table->foreign('from_employee_id')
                    ->on(Employee::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            });
    }

    public function down(): void
    {
        Schema::table(History::TABLE,
            function (Blueprint $table) {
                $table->dropForeign('call_histories_from_employee_id_foreign');
                $table->dropColumn('from_employee_id');
            });
    }
};
