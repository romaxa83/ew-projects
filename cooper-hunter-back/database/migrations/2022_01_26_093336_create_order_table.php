<?php

use App\Enums\Orders\OrderDeliveryTypeEnum;
use App\Enums\Orders\OrderStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'orders',
            static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('technician_id');
                $table->enum('status', OrderStatusEnum::getValues())
                    ->default(OrderStatusEnum::CREATED);

                $table->unsignedBigInteger('product_id')
                    ->nullable();

                $table->string('serial_number')
                    ->nullable();

                $table->string('first_name');
                $table->string('last_name');
                $table->string('phone', 24);
                $table->string('address_first_line');
                $table->string('address_second_line');
                $table->string('city');
                $table->string('state', 32);
                $table->string('zip', 32);

                $table->enum('delivery_type', OrderDeliveryTypeEnum::getValues())
                    ->default(OrderDeliveryTypeEnum::GROUND);

                $table->softDeletes();
                $table->timestamps();

                $table->foreign('technician_id')
                    ->on('technicians')
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->foreign('product_id')
                    ->on('catalog_products')
                    ->references('id')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();

                $table->foreign('serial_number')
                    ->on('product_serial_numbers')
                    ->references('serial_number')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
