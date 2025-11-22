<?php

use App\Enums\Orders\BS\OrderStatus;
use App\Models\Orders\BS;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(BS\Order::TABLE, function (Blueprint $table) {

            $table->id();

            $table->numericMorphs('vehicle');

            $table->string('order_number');

            $table->string('status')->default(OrderStatus::New->value);
            $table->string('status_before_deleting')->nullable();
            $table->timestamp('status_changed_at')->nullable();

            $table->dateTime('implementation_date');
            $table->foreignId('mechanic_id');
            $table->text('notes')->nullable();
            $table->dateTime('due_date');

            $table->boolean('is_billed')
                ->default(false)
                ->index();
            $table->dateTime('billed_at')->nullable();
            $table->boolean('is_paid')
                ->default(false)
                ->index();
            $table->dateTime('paid_at')->nullable();

            $table->float('discount')->nullable();
            $table->float('tax_inventory')->nullable();
            $table->float('tax_labor')->nullable();
            $table->decimal('total_amount')->nullable();
            $table->decimal('paid_amount')->nullable();
            $table->decimal('debt_amount')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(BS\Order::TABLE);
    }
};
