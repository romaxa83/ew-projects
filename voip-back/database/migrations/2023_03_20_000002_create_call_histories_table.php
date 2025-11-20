<?php

use App\Models\Calls\History;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(History::TABLE,
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
                $table->string('from')->nullable();
                $table->string('dialed')->nullable();
                $table->integer('duration');
                $table->integer('billsec');
                $table->string('serial_numbers')->nullable();
                $table->string('case_id')->nullable();
                $table->string('comment')->nullable();
                $table->string('lastapp');
                $table->string('uniqueid');
                $table->timestamp('call_date');
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(History::TABLE);
    }
};
