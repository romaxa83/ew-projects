<?php

use App\Models\Employee;
use App\Models\Order;
use App\Models\Order\Payroll\Payroll;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Payroll::TABLE, function (Blueprint $table) {
            $table->id();
            $table->integer('order_id')
                ->references('id')
                ->on(Order::TABLE)
                ->onDelete('cascade');
            $table->integer('processed_employee_id')
                ->nullable()
                ->references('id')
                ->on(Employee::TABLE)
                ->onDelete('cascade');

            $table->json('paid_form_bol');
            $table->decimal("hours", 10, 2)->default(0);
            $table->boolean('is_processed');
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Payroll::TABLE);
    }
};
