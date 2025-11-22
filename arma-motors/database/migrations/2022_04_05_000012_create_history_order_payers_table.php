<?php

use App\Models\History\Order;
use App\Models\History\OrderPayer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(OrderPayer::TABLE,
            static function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('row_id');
                $table->foreign('row_id')
                    ->references('id')
                    ->on(Order::TABLE)
                    ->onDelete('cascade');

                $table->string('name')->nullable();
                $table->string('date')->nullable();
                $table->string('number')->nullable();
                $table->string('contract')->nullable();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(OrderPayer::TABLE);
    }
};






