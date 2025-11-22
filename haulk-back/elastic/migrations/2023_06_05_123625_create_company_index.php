<?php

declare(strict_types=1);

use ElasticAdapter\Indices\Mapping;
use ElasticAdapter\Indices\Settings;
use ElasticMigrations\Facades\Index;
use ElasticMigrations\MigrationInterface;

final class CreateCompanyIndex implements MigrationInterface
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        Index::create('companies', function (Mapping $mapping, Settings $settings) {
            $mapping->keyword('id');
            $mapping->integer('carrier_id');
            $mapping->integer('broker_id');
            $mapping->keyword('company_name');
            $mapping->long('last_payment_stage_id');
            $mapping->long('last_payment_stage');
            $mapping->long('order_count');
            $mapping->long('total_due_count');
            $mapping->float('total_due');
            $mapping->keyword('reference_number');
            $mapping->keyword('invoice');
            $mapping->boolean('is_paid');
            $mapping->long('payment_method_id');
            $mapping->date('invoice_send_date');
            $mapping->nested(
                'broker_amount',
                [
                    'properties' => [
                        'forecast' => [
                            'type' => 'float'
                        ],
                        'planned_date' => [
                            'type' => 'date'
                        ]
                    ]
                ]
            );

            $settings->index([
                'max_result_window' => 100000000
            ]);
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Index::dropIfExists('companies');
    }
}
