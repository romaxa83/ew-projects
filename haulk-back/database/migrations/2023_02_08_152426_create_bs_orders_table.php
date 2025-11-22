<?php

use App\Models\BodyShop\Orders\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBsOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bs_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('truck_id')
                ->nullable()
                ->references('id')->on('trucks')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->foreignId('trailer_id')
                ->nullable()
                ->references('id')->on('trailers')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->float('discount')->nullable();
            $table->float('tax_inventory')->nullable();
            $table->float('tax_labor')->nullable();
            $table->dateTime('implementation_date');
            $table->foreignId('mechanic_id');
            $table->text('notes')->nullable();
            $table->string('status')->default(Order::STATUS_NEW);
            $table->string('payment_status')->default(Order::PAYMENT_STATUS_NOT_PAID);
            $table->string('order_number');
            $table->dateTime('due_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bs_orders');
    }
}
