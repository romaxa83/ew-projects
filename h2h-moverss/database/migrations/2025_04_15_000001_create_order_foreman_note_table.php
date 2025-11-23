<?php

use App\Models\Employee;
use App\Models\Order;
use App\Models\Order\ForemanNote;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(ForemanNote::TABLE, function (Blueprint $table) {
            $table->id();

            $table->integer('order_id')
                ->references('id')
                ->on(Order::TABLE)
                ->onDelete('cascade');

            $table->integer('foreman_id')
                ->references('id')
                ->on(Employee::TABLE)
                ->onDelete('cascade');

            $table->text("text");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(ForemanNote::TABLE);
    }
};
