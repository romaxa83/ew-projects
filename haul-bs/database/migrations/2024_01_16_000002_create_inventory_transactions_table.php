<?php

use App\Models\Inventories\Inventory;
use App\Models\Inventories\Transaction;
use App\Models\Orders\BS;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Transaction::TABLE, function (Blueprint $table) {
            $table->id();

            $table->foreignId('inventory_id')
                ->nullable()
                ->index()
                ->references('id')
                ->on(Inventory::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignId('order_id')
                ->nullable()
                ->index()
                ->references('id')
                ->on(BS\Order::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('operation_type')->index();
            $table->string('invoice_number')->nullable();
            $table->decimal('price');
            $table->float('quantity');
            $table->string('describe')->nullable();
            $table->date('transaction_date')->index();
            $table->boolean('is_reserve')->default(false);
            $table->decimal('discount')->nullable();
            $table->decimal('tax')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('company_name')->nullable();
            $table->string('payment_method')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Transaction::TABLE);
    }
};
