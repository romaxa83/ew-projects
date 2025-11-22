<?php

use App\Models\History\Order;
use App\Models\History\OrderOwner;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(OrderOwner::TABLE,
            static function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('row_id');
                $table->foreign('row_id')
                    ->references('id')
                    ->on(Order::TABLE)
                    ->onDelete('cascade');

                $table->string('address')->nullable();
                $table->string('email')->nullable();
                $table->string('name')->nullable();
                $table->string('certificate')->nullable();
                $table->string('phone')->nullable();
                $table->string('etc')->nullable();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(OrderOwner::TABLE);
    }
};






