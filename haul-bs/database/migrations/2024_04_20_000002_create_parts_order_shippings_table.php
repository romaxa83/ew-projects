<?php

use App\Models\Orders;
use App\Models\Orders\Parts;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Parts\Shipping::TABLE, function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->references('id')
                ->on(Parts\Order::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->decimal('cost')->default(0);
            $table->string('method');
            $table->string('terms')->nullable();
            $table->string('track_number')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Parts\Shipping::TABLE);
    }
};

