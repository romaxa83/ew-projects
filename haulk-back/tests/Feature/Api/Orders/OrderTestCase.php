<?php

namespace Tests\Feature\Api\Orders;

use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

abstract class OrderTestCase extends TestCase
{
    use DatabaseTransactions;

    protected array $order_fields_create = [
        'pickup_full_name' => 'tester',
        'pickup_phones' => [
            [
                'number' => '4444555666',
                'notes' => 'some text',
            ],
        ],
        'payment_terms' => 'qwe',
        'vehicles' => [
            [
                'vin' => 'vin1',
                'make' => 'make1',
            ],
            [
                'vin' => 'vin2',
                'make' => 'make2',
            ],
        ],
        'expenses' => [
            [
                'type' => '1',
            ],
            [
                'type' => '1',
            ],
        ],
    ];

    protected array $order_fields_update = [
        'load_id' => 'qwe123',
        'status' => '10',
        'pickup_full_name' => 'contact',
        'pickup_phones' => [
            [
                'number' => '7777777777',
            ],
        ],
        'payment_terms' => 'terms',
        'vehicles' => [
            [
                'id' => 0,
                'vin' => 'vin11',
                'make' => 'make11',
            ],
            [
                'id' => 0,
                'vin' => 'vin22',
                'make' => 'make22',
            ],
        ],
        'expenses' => [
            [
                'id' => 0,
                'type' => '1',
            ],
            [
                'id' => 0,
                'type' => '1',
            ],
        ],
    ];

    protected function getRequiredFields(): array
    {
        return [
            'load_id' => 'qwe123',
            'inspection_type' => Order::INSPECTION_TYPE_HARD,
            'vehicles' => [
                [
                    'type_id' => 1,
                    'model' => 'Fusion',
                    'make' => 'Ford',
                ],
                [
                    'model' => 'Fusion',
                    'type_id' => 1,
                    'make' => 'Ford',
                ],
            ],
            /**
             * @see TimezoneController::TIMEZONES
             */
            'shipper_contact' => $this->getShipperContactData(),
            'delivery_contact' => $this->getDeliveryContactData(),
            'pickup_contact' => $this->getPickupContactData(),
            'expenses' => [
                [
                    'type_id' => 1,
                    'date' => '12/21/2025',
                    'price' => 10,
                ],
                [
                    'type_id' => 1,
                    'date' => '12/20/2025',
                    'price' => 10,
                ],
            ],
            'payment' => $this->getPaymentData(),
        ];
    }

    protected function getShipperContactData(): array
    {
        return [
            'full_name' => 'some American name 1',
            'timezone' => 'America/Adak',
            'type_id' => 1,
            'zip' => '123456',
            'state_id' => State::factory()->create()->id,
            'city' => 'Some-American-city',
            'address' => 'Some American Address',
            'phone' => '5555665555',
            'email' => $this->faker->email,
        ];
    }

    protected function getDeliveryContactData(): array
    {
        return [
            'full_name' => 'some American name 2',
            'timezone' => 'America/Adak',
            'type_id' => 1,
            'zip' => '123456',
            'state_id' => State::factory()->create()->id,
            'city' => 'Some-American-city',
            'address' => 'Some American Address',
            'phone' => '5555665555',
            'email' => $this->faker->email,
        ];
    }

    protected function getPickupContactData(): array
    {
        return [
            'full_name' => 'some American name 3',
            'timezone' => 'America/Adak',
            'type_id' => 1,
            'zip' => '123456',
            'state_id' => State::factory()->create()->id,
            'city' => 'Some-American-city',
            'address' => 'Some American Address',
            'phone' => '5555665555',
            'email' => $this->faker->email,
        ];
    }

    /**
     * @throws Exception
     */
    protected function getPaymentData(): array
    {
        $total = random_int(50, 10000);

        return [
            'total_carrier_amount' => $total,

            'customer_payment_amount' => $total,
            'customer_payment_method_id' => Payment::METHOD_USHIP,
            'customer_payment_location' => Order::LOCATION_PICKUP,
        ];
    }
}
