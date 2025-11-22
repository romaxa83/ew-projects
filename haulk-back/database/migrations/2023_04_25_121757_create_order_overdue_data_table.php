<?php

use App\Models\Orders\OrderOverdueData;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {

    public function up(): void
    {
        Schema::create('order_overdue_data', function (Blueprint $table) {
            $table
                ->foreignId('id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table
                ->primary('id');
            $table
                ->unsignedFloat('customer_amount_forecast')
                ->nullable();
            $table
                ->unsignedFloat('broker_amount_forecast')
                ->nullable();
            $table
                ->unsignedFloat('broker_fee_amount_forecast')
                ->nullable();
            $table
                ->unsignedFloat('customer_amount_actual')
                ->nullable();
            $table
                ->unsignedFloat('broker_amount_actual')
                ->nullable();
            $table
                ->unsignedFloat('broker_fee_amount_actual')
                ->nullable();
            $table
                ->unsignedInteger('pickup_planned_date')
                ->nullable();
            $table
                ->string('pickup_timezone')
                ->nullable();
            $table
                ->unsignedInteger('delivery_planned_date')
                ->nullable();
            $table
                ->string('delivery_timezone')
                ->nullable();
            $table
                ->unsignedInteger('customer_payment_planned_date')
                ->nullable();
            $table
                ->unsignedInteger('broker_payment_planned_date')
                ->nullable();
            $table
                ->unsignedInteger('broker_fee_payment_planned_date')
                ->nullable();
            $table
                ->unsignedInteger('customer_paid_at')
                ->nullable();
            $table
                ->unsignedInteger('broker_paid_at')
                ->nullable();
            $table
                ->unsignedInteger('broker_fee_paid_at')
                ->nullable();
            $table
                ->unsignedInteger('paid_at')
                ->nullable();
            $table
                ->boolean('is_picked_up')
                ->nullable();
            $table
                ->boolean('is_delivered')
                ->nullable();
            $table
                ->boolean('is_broker_paid')
                ->nullable();
            $table
                ->boolean('is_broker_fee_paid')
                ->nullable();
            $table
                ->boolean('is_customer_paid')
                ->nullable();
            $table
                ->boolean('is_paid')
                ->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_overdue_data');
    }
};
