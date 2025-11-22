<?php

use App\Models\Orders\BS\Order;
use App\Models\Orders\BS\Payment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Payment::TABLE, function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->references('id')
                ->on(Order::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->decimal('amount');
            $table->dateTime('payment_date');
            $table->string('payment_method');
            $table->text('notes')->nullable();
            $table->string('reference_number')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Payment::TABLE);
    }
};
