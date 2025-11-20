<?php

use App\Models\Employees\Employee;
use App\Models\Reports\Report;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Report::TABLE,
            static function (Blueprint $table) {
                $table->id();

                $table->unsignedInteger('employee_id')->nullable();
                $table->foreign('employee_id')
                    ->on(Employee::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->unsignedInteger('calls')->default(0);
                $table->unsignedInteger('answered_calls')->default(0);
                $table->unsignedInteger('dropped_calls')->default(0);
                $table->unsignedInteger('transfer_calls')->default(0);
                $table->unsignedInteger('wait')->default(0);
                $table->unsignedInteger('total_time')->default(0);

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Report::TABLE);
    }
};

