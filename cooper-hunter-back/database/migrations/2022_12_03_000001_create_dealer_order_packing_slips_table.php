<?php

use App\Models\Orders\Dealer\Order;
use App\Models\Orders\Dealer\PackingSlip;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(PackingSlip::TABLE,
            static function (Blueprint $table) {
                $table->id();
                $table->uuid('guid')->nullable()->unique();

                $table->unsignedBigInteger('order_id');
                $table->foreign('order_id')
                    ->on(Order::TABLE)
                    ->references('id')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->string('number')->nullable();
                $table->string('tracking_number')->nullable();
                $table->string('tracking_company')->nullable();

                $table->unsignedInteger('tax')->default(0);
                $table->unsignedInteger('shipping_price')->default(0);
                $table->unsignedInteger('total')->default(0);
                $table->unsignedInteger('total_discount')->default(0);
                $table->unsignedInteger('total_with_discount')->default(0);

                $table->string('invoice')->nullable();
                $table->timestamp('invoice_at')->nullable();
                $table->timestamp('shipped_at')->nullable();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(PackingSlip::TABLE);
    }
};
