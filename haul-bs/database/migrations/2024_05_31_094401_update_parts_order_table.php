<?php

use App\Enums\Orders\Parts\PaymentMethod;
use App\Models\Orders;
use App\Models\Orders\Parts;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Parts\Order::query()
            ->where('payment_method', 'card')
            ->update([
                'payment_method' => PaymentMethod::Online->value
            ]);
    }

    public function down(): void
    {
        Parts\Order::query()
            ->where('payment_method', PaymentMethod::Online->value)
            ->update([
                'payment_method' => 'card'
            ]);
    }
};

