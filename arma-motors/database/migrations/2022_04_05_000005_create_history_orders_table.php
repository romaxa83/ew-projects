<?php

use App\Models\History\CarItem;
use App\Models\History\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Order::TABLE,
            static function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('row_id');
                $table->foreign('row_id')
                    ->references('id')
                    ->on(CarItem::TABLE)
                    ->onDelete('cascade');
                $table->unsignedBigInteger('sys_id')->nullable();
                $table->foreign('sys_id')
                    ->references('id')
                    ->on(\App\Models\Order\Order::TABLE_NAME)
                    ->onDelete('cascade');

                $table->string('aa_id')->nullable();
                $table->string('amount_in_words')->nullable();
                $table->float('amount_including_vat')->nullable();
                $table->float('amount_without_vat')->nullable();
                $table->float('amount_vat')->nullable();
                $table->string('body_number')->nullable();
                $table->timestamp('closing_date')->nullable();
                $table->string('current_account')->nullable();
                $table->string('date')->nullable();
                $table->timestamp('date_of_sale')->nullable();
                $table->string('dealer')->nullable();
                $table->string('disassembled_parts')->nullable();
                $table->float('discount')->nullable();
                $table->float('discount_jobs')->nullable();
                $table->float('discount_parts')->nullable();
                $table->float('jobs_amount_including_vat')->nullable();
                $table->float('jobs_amount_vat')->nullable();
                $table->float('jobs_amount_without_vat')->nullable();
                $table->string('model')->nullable();
                $table->string('number')->nullable();
                $table->float('parts_amount_including_vat')->nullable();
                $table->float('parts_amount_vat')->nullable();
                $table->float('parts_amount_without_vat')->nullable();
                $table->string('producer')->nullable();
                $table->string('recommendations')->nullable();
                $table->string('repair_type')->nullable();
                $table->string('state_number')->nullable();
                $table->float('mileage')->nullable();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Order::TABLE);
    }
};
