<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'order_payments',
            static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->unsignedInteger('order_price')
                    ->nullable();
                $table->unsignedInteger('order_price_with_discount')
                    ->nullable();
                $table->unsignedInteger('shipping_cost')
                    ->nullable();
                $table->unsignedInteger('tax')
                    ->nullable();
                $table->unsignedInteger('discount')
                    ->nullable();
                $table->unsignedBigInteger('paid_at')
                    ->nullable();
                $table->timestamps();

                $table->foreign('order_id')
                    ->on('orders')
                    ->references('id')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payments');
    }
};
