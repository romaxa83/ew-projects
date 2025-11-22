<?php

declare(strict_types=1);

use ElasticAdapter\Indices\Mapping;
use ElasticAdapter\Indices\Settings;
use ElasticMigrations\Facades\Index;
use ElasticMigrations\MigrationInterface;

final class CreateOrdersIndex implements MigrationInterface
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        Index::create('orders', function (Mapping $mapping, Settings $settings) {
            $mapping->integer('id');
            $mapping->integer('carrier_id');
            $mapping->integer('broker_id');
            $mapping->integer("status");
            $mapping->keyword("calculated_status");
            $mapping->integer("calculated_status_weight");
            $mapping->keyword('mobile_tab');
            $mapping->boolean('need_review');
            $mapping->boolean('has_review');
            $mapping->boolean('deleted');
            $mapping->integer('driver_id');
            $mapping->integer('dispatcher_id');
            $mapping->integer('owner_id');
            $mapping->keyword('make');
            $mapping->keyword('model');
            $mapping->keyword('year');
            $mapping->keyword('vin');
            $mapping->keyword('load_id');
            $mapping->keyword('pickup_full_name');
            $mapping->keyword('delivery_full_name');
            $mapping->keyword('shipper_full_name');
            $mapping->integer('tag');
            $mapping->keyword('broker_invoice');
            $mapping->keyword('customer_invoice');
            $mapping->long('calculated_date_first');
            $mapping->long('calculated_date_second');
            $mapping->long('last_payment_stage');
            $mapping->long('last_payment_stage_id');
            $mapping->keyword('reference_number');
            $mapping->keyword('broker_reference_number');
            $mapping->boolean('is_broker_paid');
            $mapping->boolean('is_customer_paid');
            $mapping->boolean('is_broker_fee_paid');
            $mapping->boolean('is_billed');
            $mapping->float('broker_amount_forecast');
            $mapping->float('customer_amount_forecast');
            $mapping->float('broker_fee_amount_forecast');
            $mapping->float('total_carrier_amount');
            $mapping->date('pickup_planned_date');
            $mapping->date('delivery_planned_date');
            $mapping->date('customer_payment_planned_date');
            $mapping->date('broker_payment_planned_date');
            $mapping->date('broker_fee_payment_planned_date');
            $mapping->integer('broker_payment_method_id');
            $mapping->integer('customer_payment_method_id');
            $mapping->integer('broker_fee_payment_method_id');
            $mapping->date('pickup_date_actual');
            $mapping->date('delivery_date_actual');
            $mapping->date('broker_invoice_send_date');
            $mapping->date('customer_invoice_send_date');
            $mapping->date('paid_at');
            $mapping->date('broker_fee_paid_at');
            $mapping->date('created_at');

            $settings->index([
                'max_result_window' => 100000000,
            ]);
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Index::dropIfExists('orders');
    }
}
