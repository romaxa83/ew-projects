<?php

use App\Models\Calls\Queue;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Queue::TABLE,
            static function (Blueprint $table) {
                $table->id();

                $table->unsignedInteger('employee_id')->nullable();
                $table->foreign('employee_id')
                    ->on(Employee::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->unsignedInteger('department_id')->nullable();
                $table->foreign('department_id')
                    ->on(Department::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('status');
                $table->string('caller_num')->nullable();
                $table->string('caller_name')->nullable();
                $table->integer('position');
                $table->integer('wait');
                $table->string('serial_number')->nullable();
                $table->string('case_id')->nullable();
                $table->string('comment')->nullable();
                $table->string('uniqueid')->unique();
                $table->string('channel');
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Queue::TABLE);
    }
};
