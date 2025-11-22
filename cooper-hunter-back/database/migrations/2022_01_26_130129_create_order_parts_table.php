<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'order_parts',
            static function (Blueprint $table) {
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('order_category_id');
                $table->unsignedInteger('quantity')->default(1);
                $table->string('description', 500)->nullable();

                $table->primary(['order_id', 'order_category_id'], 'order_part_id');

                $table->foreign('order_id')
                    ->on('orders')
                    ->references('id')
                    ->cascadeOnDelete();

                $table->foreign('order_category_id')
                    ->on('order_categories')
                    ->references('id')
                    ->cascadeOnDelete();

            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('order_parts');
    }
};
