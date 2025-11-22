<?php

use App\Models\Customers\Customer;
use App\Models\Orders;
use App\Models\Orders\Parts;
use App\Models\Users\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Parts\Order::TABLE, function (Blueprint $table) {
            $table->id();

            $table->string('order_number');

            $table->foreignId('customer_id')
                ->nullable()
                ->references('id')
                ->on(Customer::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignId('sales_manager_id')
                ->nullable()
                ->references('id')
                ->on(User::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('status', 20)->default(Parts\Order::DEFAULT_STATUS);
            $table->string('status_before_deleting')->nullable();
            $table->timestamp('status_changed_at')->nullable();

            $table->string('shipping_method', 30)->nullable();
            $table->json('delivery_address')->nullable();
            $table->json('billing_address')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->decimal('paid_amount', 10, 2)->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Parts\Order::TABLE);
    }
};
