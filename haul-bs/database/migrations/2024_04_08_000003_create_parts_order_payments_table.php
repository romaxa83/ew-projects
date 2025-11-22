<?php

use App\Models\Orders;
use App\Models\Orders\Parts;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Parts\Payment::TABLE, function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->nullable()
                ->references('id')
                ->on(Parts\Order::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->decimal('amount');
            $table->timestamp('payment_at');
            $table->string('payment_method');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Parts\Payment::TABLE);
    }
};
