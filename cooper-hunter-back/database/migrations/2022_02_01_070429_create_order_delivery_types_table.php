<?php

use App\Models\Orders\Deliveries\OrderDeliveryType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'order_delivery_types',
            static function (Blueprint $table) {
                $table->id();
                $table->integer('sort')->default(OrderDeliveryType::DEFAULT_SORT);
                $table->boolean('active')->default(OrderDeliveryType::DEFAULT_ACTIVE);

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('order_delivery_types');
    }
};
