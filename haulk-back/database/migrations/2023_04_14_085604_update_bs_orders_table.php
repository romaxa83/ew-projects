<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\BodyShop\Orders\Order;

class UpdateBsOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bs_orders', function (Blueprint $table) {
            $table->boolean('is_billed')
                ->default(false)
                ->index();
            $table->dateTime('billed_at')->nullable();
            $table->boolean('is_paid')
                ->default(false)
                ->index();
            $table->dateTime('paid_at')->nullable();
            $table->dropColumn('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bs_orders', function (Blueprint $table) {
            $table->dropColumn('is_billed');
            $table->dropColumn('billed_at');
            $table->dropColumn('is_paid');
            $table->dropColumn('paid_at');
            $table->string('payment_status')->default(Order::PAYMENT_STATUS_NOT_PAID);
        });
    }
}
