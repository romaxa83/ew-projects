<?php

use App\Models\Employee;
use App\Models\Order\Payroll\Item;
use App\Models\Order\Payroll\Payroll;
use App\Models\User\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Item::TABLE, function (Blueprint $table) {
            $table->id();

            $table->integer('payroll_id')
                ->references('id')
                ->on(Payroll::TABLE)
                ->onDelete('cascade');
            $table->integer('employee_id')
                ->references('id')
                ->on(Employee::TABLE)
                ->onDelete('cascade');
            $table->integer('role_id')
                ->references('id')
                ->on(Role::TABLE)
                ->onDelete('cascade');

            $table->decimal("hourly_rate", 10, 2)->default(0);
            $table->decimal("hours", 10, 2)->default(0);
            $table->decimal("extras", 10, 2)->default(0);
            $table->boolean("is_cc_due");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Item::TABLE);
    }
};
