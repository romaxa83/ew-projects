<?php

use App\Models\Division;
use App\Models\Employee;
use App\Models\Employee\Rate;
use App\Models\User\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Rate::TABLE, function (Blueprint $table) {
            $table->id('id');
            $table->integer('employee_id')
                ->references('id')
                ->on(Employee::TABLE)
                ->onDelete('cascade');
            $table->string('employee_name');
            $table->integer('role_id')
                ->references('id')
                ->on(Role::TABLE)
                ->onDelete('cascade');
            $table->string('role_name');
            $table->integer('division_id')
                ->references('id')
                ->on(Division::TABLE)
                ->onDelete('cascade');

            $table->string("season");

            $table->decimal("workday", 10, 2)->nullable();
            $table->decimal("holiday", 10, 2)->nullable();
            $table->decimal("peakday", 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Rate::TABLE);
    }
};
