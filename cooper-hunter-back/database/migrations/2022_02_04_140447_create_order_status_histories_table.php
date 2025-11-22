<?php

use App\Enums\Orders\OrderStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'order_status_histories',
            static function (Blueprint $table)
            {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->enum(
                    'status',
                    OrderStatusEnum::getValues()
                );
                $table->numericMorphs('member');

                $table->timestamps();

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
        Schema::dropIfExists('order_status_histories');
    }
};
