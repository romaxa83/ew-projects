<?php

use App\Models\Order;
use App\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create(Order\InventoryActivity::TABLE, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('order_id')
                ->references('id')
                ->on(Order::TABLE)
                ->onDelete('cascade');
            $table->integer('client_id')
                ->nullable()
                ->references('id')
                ->on(\App\Models\Client::TABLE)
                ->onDelete('cascade');
            $table->integer('user_id')
                ->nullable()
                ->references('id')
                ->on(User::TABLE)
                ->onDelete('cascade');

            $table->string("action");
            $table->boolean("is_client_action");
            $table->json("miscs");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Order\InventoryActivity::TABLE);
    }
};
