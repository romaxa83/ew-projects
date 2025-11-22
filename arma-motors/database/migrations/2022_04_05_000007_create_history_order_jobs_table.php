<?php

use App\Models\History\Order;
use App\Models\History\OrderJob;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(OrderJob::TABLE,
            static function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('row_id');
                $table->foreign('row_id')
                    ->references('id')
                    ->on(Order::TABLE)
                    ->onDelete('cascade');

                $table->string('name')->nullable();
                $table->float('amount_including_vat')->nullable();
                $table->float('amount_without_vat')->nullable();
                $table->float('coefficient')->nullable();
                $table->float('price')->nullable();
                $table->float('price_with_vat')->nullable();
                $table->float('price_without_vat')->nullable();
                $table->string('ref')->nullable();
                $table->float('rate')->nullable();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(OrderJob::TABLE);
    }
};



