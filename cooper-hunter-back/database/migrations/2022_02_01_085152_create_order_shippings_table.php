<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'order_shippings',
            static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->string('first_name');
                $table->string('last_name');
                $table->string('phone', 24);
                $table->string('address_first_line');
                $table->string('address_second_line')
                    ->nullable();
                $table->string('city');
                $table->string('state', 32);
                $table->string('zip', 32);
                $table->string('trk_number')
                    ->nullable();
                $table->unsignedBigInteger('order_delivery_type_id')
                    ->nullable();
                $table->timestamps();

                $table->foreign('order_delivery_type_id')
                    ->on('order_delivery_types')
                    ->references('id')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();

                $table->foreign('order_id')
                    ->on('orders')
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('order_shippings');
    }
};
