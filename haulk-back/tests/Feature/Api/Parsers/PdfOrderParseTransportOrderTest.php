<?php

namespace Tests\Feature\Api\Parsers;

use App\Models\Orders\Payment;

class PdfOrderParseTransportOrderTest extends PdfParserHelper
{
    private const FOLDER_NAME = 'TransportOrder';

    protected function getFolderName(): string
    {
        return self::FOLDER_NAME . DIRECTORY_SEPARATOR;
    }

    public function test_it_parsed_1(): void
    {
        $this->createMakes('HONDA')
            ->createStates('OH', 'NJ', 'AZ')
            ->createTimeZones('43123', '08505', '85040')
            ->sendPdfFile('transport_order_1')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => 'L20886252',
                    'pickup_date' => '01/28/2022',
                    'delivery_date' => '01/31/2022',
                    'dispatch_instructions' => '* SHIPPER REQUIRES THIS MUST PICK UP BY 1/28/2022 AND MUST DROP OFF BY 1/31/2022 * Check in with dealer # 5282912 MP MOTORS INC;***1D MOBILE APP/EBOL IS REQUIRED*** Driver must call 24 prior & 1 hour out; NO DRY RUNS WILL BE PAID',
                    'vehicles' => [
                        [
                            'vin' => '1HGCV1F31KA157248',
                            'year' => '2019',
                            'make' => 'Honda',
                            'model' => 'Accord Sport',
                            'inop' => false,
                            'enclosed' => false
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Manheim Ohio',
                        'state' => 'OH',
                        'city' => 'Grove City',
                        'address' => '3905 Jackson Pike',
                        'zip' => '43123',
                        'phones' => [],
                        'phone' => '16148712771',
                        'fax' => null,
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Manheim New Jersey',
                        'state' => 'NJ',
                        'city' => 'Bordentown',
                        'address' => 'PO Box 188',
                        'zip' => '08505',
                        'phones' => [],
                        'fax' => null,
                        'phone' => '16092983400',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Ready Logistics',
                        'state' => 'AZ',
                        'city' => 'Phoenix',
                        'address' => '4615 E Elwood St STE 400',
                        'zip' => '85040',
                        'phones' => [],
                        'fax' => '14805583216',
                        'phone' => '14805583200',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 444,
                        'terms' => null,
                        'customer_payment_amount' => null,
                        'customer_payment_method_id' => null,
                        'customer_payment_method' => [
                            'id' => null,
                            'title' => null,
                        ],
                        'customer_payment_location' => null,
                        'broker_payment_amount' => 444,
                        'broker_payment_method_id' => Payment::METHOD_QUICKPAY,
                        'broker_payment_method' => [
                            'id' => Payment::METHOD_QUICKPAY,
                            'title' => Payment::BROKER_METHODS[Payment::METHOD_QUICKPAY]
                        ],
                        'broker_payment_days' => null,
                        'broker_payment_begins' => null,
                        'broker_fee_amount' => null,
                        'broker_fee_method_id' => null,
                        'broker_fee_method' => [
                            'id' => null,
                            'title' => null
                        ],
                        'broker_fee_days' => null,
                        'broker_fee_begins' => null,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_2(): void
    {
        $this->createMakes('HYUNDAI')
            ->createStates('TX', 'OK', 'AZ')
            ->createTimeZones('78212', '74010', '85040')
            ->sendPdfFile('transport_order_2')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => 'L20804870',
                    'pickup_date' => '01/19/2022',
                    'delivery_date' => '01/20/2022',
                    'dispatch_instructions' => '* SHIPPER REQUIRES THIS MUST PICK UP BY 1/15/2022 AND MUST DROP OFF BY 1/17/2022 * Must call prior to pick and drop off. Driver must call 24 hours in advance to confirm pick & drop hrs. No dry run paid. PU Dealership, PU Hours M-Sat 9am-7pm, verified addr, no lot restrictions, RDK',
                    'vehicles' => [
                        [
                            'vin' => 'KM8J23A48HU401975',
                            'year' => '2017',
                            'make' => 'Hyundai',
                            'model' => 'Tucson SE',
                            'inop' => false,
                            'enclosed' => false
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'SPOTLESS AUTO LLC',
                        'state' => 'TX',
                        'city' => 'San Antonio',
                        'address' => '5415 San Pedro Ave',
                        'zip' => '78212',
                        'phones' => [],
                        'phone' => '12102402194',
                        'fax' => null,
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MAINER FORD OF BRISTOW',
                        'state' => 'OK',
                        'city' => 'Bristow',
                        'address' => '512 N Main St',
                        'zip' => '74010',
                        'phones' => [],
                        'fax' => null,
                        'phone' => '19183673373',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Ready Logistics',
                        'state' => 'AZ',
                        'city' => 'Phoenix',
                        'address' => '4615 E Elwood St STE 400',
                        'zip' => '85040',
                        'phones' => [],
                        'fax' => '14805583216',
                        'phone' => '14805583200',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 435,
                        'terms' => null,
                        'customer_payment_amount' => null,
                        'customer_payment_method_id' => null,
                        'customer_payment_method' => [
                            'id' => null,
                            'title' => null,
                        ],
                        'customer_payment_location' => null,
                        'broker_payment_amount' => 435,
                        'broker_payment_method_id' => Payment::METHOD_QUICKPAY,
                        'broker_payment_method' => [
                            'id' => Payment::METHOD_QUICKPAY,
                            'title' => Payment::BROKER_METHODS[Payment::METHOD_QUICKPAY]
                        ],
                        'broker_payment_days' => null,
                        'broker_payment_begins' => null,
                        'broker_fee_amount' => null,
                        'broker_fee_method_id' => null,
                        'broker_fee_method' => [
                            'id' => null,
                            'title' => null
                        ],
                        'broker_fee_days' => null,
                        'broker_fee_begins' => null,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_3(): void
    {
        $this->createMakes('FORD')
            ->createStates('TX', 'OK', 'AZ')
            ->createTimeZones('78216', '74010', '85040')
            ->sendPdfFile('transport_order_3')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => 'L20804871',
                    'pickup_date' => '01/19/2022',
                    'delivery_date' => '01/20/2022',
                    'dispatch_instructions' => '* SHIPPER REQUIRES THIS MUST PICK UP BY 1/15/2022 AND MUST DROP OFF BY 1/18/2022 * ;Must call prior to pick and drop off. Driver must call 24 hours in advance to confirm pick & drop hrs. No dry run paid.',
                    'vehicles' => [
                        [
                            'vin' => '1FMSK7FH9LGB57123',
                            'year' => '2020',
                            'make' => 'Ford',
                            'model' => 'Explorer Limited',
                            'inop' => false,
                            'enclosed' => false
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'AVIS BUDGET GROUP-SAN ANTONIO',
                        'state' => 'TX',
                        'city' => 'San Antonio',
                        'address' => '430 Sandau Rd Ste 1',
                        'zip' => '78216',
                        'phones' => [],
                        'phone' => '12102483301',
                        'fax' => null,
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MAINER FORD OF BRISTOW',
                        'state' => 'OK',
                        'city' => 'Bristow',
                        'address' => '512 N Main St',
                        'zip' => '74010',
                        'phones' => [],
                        'fax' => null,
                        'phone' => '19183673373',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Ready Logistics',
                        'state' => 'AZ',
                        'city' => 'Phoenix',
                        'address' => '4615 E Elwood St STE 400',
                        'zip' => '85040',
                        'phones' => [],
                        'fax' => '14805583216',
                        'phone' => '14805583200',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 435,
                        'terms' => null,
                        'customer_payment_amount' => null,
                        'customer_payment_method_id' => null,
                        'customer_payment_method' => [
                            'id' => null,
                            'title' => null,
                        ],
                        'customer_payment_location' => null,
                        'broker_payment_amount' => 435,
                        'broker_payment_method_id' => Payment::METHOD_QUICKPAY,
                        'broker_payment_method' => [
                            'id' => Payment::METHOD_QUICKPAY,
                            'title' => Payment::BROKER_METHODS[Payment::METHOD_QUICKPAY]
                        ],
                        'broker_payment_days' => null,
                        'broker_payment_begins' => null,
                        'broker_fee_amount' => null,
                        'broker_fee_method_id' => null,
                        'broker_fee_method' => [
                            'id' => null,
                            'title' => null
                        ],
                        'broker_fee_days' => null,
                        'broker_fee_begins' => null,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_4(): void
    {
        $this->createMakes('FORD')
            ->createStates('KY', 'OK', 'AZ')
            ->createTimeZones('42240', '74010', '85040')
            ->sendPdfFile('transport_order_4')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => 'L20807442',
                    'pickup_date' => '01/18/2022',
                    'delivery_date' => '01/21/2022',
                    'dispatch_instructions' => '* SHIPPER REQUIRES THIS MUST PICK UP BY 1/15/2022 AND MUST DROP OFF BY 1/19/2022 * Must call prior to pick and drop off. Driver must call 24 hours in advance to confirm pick & drop hrs. No dry run paid.',
                    'vehicles' => [
                        [
                            'vin' => '1FTEW1E55JKC20562',
                            'year' => '2018',
                            'make' => 'Ford',
                            'model' => 'F-150 King Ranch',
                            'inop' => false,
                            'enclosed' => false
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Evan Ledford',
                        'state' => 'KY',
                        'city' => 'Hopkinsville',
                        'address' => '4395 Fort Campbell Blvd',
                        'zip' => '42240',
                        'phones' => [],
                        'phone' => '19312418632',
                        'fax' => null,
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MAINER FORD OF BRISTOW',
                        'state' => 'OK',
                        'city' => 'Bristow',
                        'address' => '512 N Main St',
                        'zip' => '74010',
                        'phones' => [],
                        'fax' => null,
                        'phone' => '19183673373',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Ready Logistics',
                        'state' => 'AZ',
                        'city' => 'Phoenix',
                        'address' => '4615 E Elwood St STE 400',
                        'zip' => '85040',
                        'phones' => [],
                        'fax' => '14805583216',
                        'phone' => '14805583200',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 680,
                        'terms' => null,
                        'customer_payment_amount' => null,
                        'customer_payment_method_id' => null,
                        'customer_payment_method' => [
                            'id' => null,
                            'title' => null,
                        ],
                        'customer_payment_location' => null,
                        'broker_payment_amount' => 680,
                        'broker_payment_method_id' => Payment::METHOD_QUICKPAY,
                        'broker_payment_method' => [
                            'id' => Payment::METHOD_QUICKPAY,
                            'title' => Payment::BROKER_METHODS[Payment::METHOD_QUICKPAY]
                        ],
                        'broker_payment_days' => null,
                        'broker_payment_begins' => null,
                        'broker_fee_amount' => null,
                        'broker_fee_method_id' => null,
                        'broker_fee_method' => [
                            'id' => null,
                            'title' => null
                        ],
                        'broker_fee_days' => null,
                        'broker_fee_begins' => null,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_5(): void
    {
        $this->createMakes('DODGE')
            ->createStates('NC', 'VA', 'AZ')
            ->createTimeZones('28625', '22601', '85040')
            ->sendPdfFile('transport_order_5')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => 'L20825646',
                    'pickup_date' => '01/22/2022',
                    'delivery_date' => '01/23/2022',
                    'dispatch_instructions' => '* SHIPPER REQUIRES THIS MUST PICK UP BY 1/20/2022 AND MUST DROP OFF BY 1/24/2022 * :Must call prior to pick and drop off. Driver must call 24 hours in advance to confirm pick & drop hrs. No dry run paid.',
                    'vehicles' => [
                        [
                            'vin' => '2C4RDGBG8JR179474',
                            'year' => '2018',
                            'make' => 'Dodge',
                            'model' => 'Grand Caravan SE',
                            'inop' => false,
                            'enclosed' => false
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Manheim Statesville',
                        'state' => 'NC',
                        'city' => 'Statesville',
                        'address' => '145 Auction Ln',
                        'zip' => '28625',
                        'phones' => [],
                        'phone' => '17048761111',
                        'fax' => null,
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MALLOY FORD',
                        'state' => 'VA',
                        'city' => 'Winchester',
                        'address' => '1911 Valley Ave',
                        'zip' => '22601',
                        'phones' => [],
                        'fax' => null,
                        'phone' => '15712456401',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Ready Logistics',
                        'state' => 'AZ',
                        'city' => 'Phoenix',
                        'address' => '4615 E Elwood St STE 400',
                        'zip' => '85040',
                        'phones' => [],
                        'fax' => '14805583216',
                        'phone' => '14805583200',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 320,
                        'terms' => null,
                        'customer_payment_amount' => null,
                        'customer_payment_method_id' => null,
                        'customer_payment_method' => [
                            'id' => null,
                            'title' => null,
                        ],
                        'customer_payment_location' => null,
                        'broker_payment_amount' => 320,
                        'broker_payment_method_id' => Payment::METHOD_QUICKPAY,
                        'broker_payment_method' => [
                            'id' => Payment::METHOD_QUICKPAY,
                            'title' => Payment::BROKER_METHODS[Payment::METHOD_QUICKPAY]
                        ],
                        'broker_payment_days' => null,
                        'broker_payment_begins' => null,
                        'broker_fee_amount' => null,
                        'broker_fee_method_id' => null,
                        'broker_fee_method' => [
                            'id' => null,
                            'title' => null
                        ],
                        'broker_fee_days' => null,
                        'broker_fee_begins' => null,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_6(): void
    {
        $this->createMakes('JEEP')
            ->createStates('NC', 'PA', 'AZ')
            ->createTimeZones('28625', '17402', '85040')
            ->sendPdfFile('transport_order_6')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => 'L20840250',
                    'pickup_date' => '01/22/2022',
                    'delivery_date' => '01/23/2022',
                    'dispatch_instructions' => '* SHIPPER REQUIRES THIS MUST PICK UP BY 1/21/2022 AND MUST DROP OFF BY 1/25/2022 * ;Must call prior to pick and drop off. Driver must call 24 hours in advance to confirm pick & drop hrs. No dry run paid. ;***DEALER MUST SIGN FOR THE UNIT. NO EXCEPTIONS. PLEASE CALL AHEAD***Must call prior to pick & drop off. Driver must call 24 hours in advance to confirm pick & drop hrs. No Dry Run Paid.',
                    'vehicles' => [
                        [
                            'vin' => '1C4BJWDG7DL631834',
                            'year' => '2013',
                            'make' => 'Jeep',
                            'model' => 'Wrangler Unlimited Freedom Edition',
                            'inop' => false,
                            'enclosed' => false
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Maneim Statesville',
                        'state' => 'NC',
                        'city' => 'Statesville',
                        'address' => '145 Auction Ln',
                        'zip' => '28625',
                        'phones' => [],
                        'phone' => '17048761111',
                        'fax' => null,
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'YORK VOLKSWAGEN',
                        'state' => 'PA',
                        'city' => 'York',
                        'address' => '3475 E Market St # 3514',
                        'zip' => '17402',
                        'phones' => [],
                        'fax' => null,
                        'phone' => '17178552502',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Ready Logistics',
                        'state' => 'AZ',
                        'city' => 'Phoenix',
                        'address' => '4615 E Elwood St STE 400',
                        'zip' => '85040',
                        'phones' => [],
                        'fax' => '14805583216',
                        'phone' => '14805583200',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 331,
                        'terms' => null,
                        'customer_payment_amount' => null,
                        'customer_payment_method_id' => null,
                        'customer_payment_method' => [
                            'id' => null,
                            'title' => null,
                        ],
                        'customer_payment_location' => null,
                        'broker_payment_amount' => 331,
                        'broker_payment_method_id' => Payment::METHOD_QUICKPAY,
                        'broker_payment_method' => [
                            'id' => Payment::METHOD_QUICKPAY,
                            'title' => Payment::BROKER_METHODS[Payment::METHOD_QUICKPAY]
                        ],
                        'broker_payment_days' => null,
                        'broker_payment_begins' => null,
                        'broker_fee_amount' => null,
                        'broker_fee_method_id' => null,
                        'broker_fee_method' => [
                            'id' => null,
                            'title' => null
                        ],
                        'broker_fee_days' => null,
                        'broker_fee_begins' => null,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_7(): void
    {
        $this->createMakes('JEEP')
            ->createStates('FL', 'NC', 'AZ')
            ->createTimeZones('33312', '28001', '85040')
            ->sendPdfFile('transport_order_7')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => 'L20853864',
                    'pickup_date' => '01/25/2022',
                    'delivery_date' => '01/27/2022',
                    'dispatch_instructions' => '* SHIPPER REQUIRES THIS MUST PICK UP BY 1/24/2022 AND MUST DROP OFF BY 1/26/2022 * Must call prior to pick and drop off. Driver must call 24 hours in advance to confirm pick & drop hrs. No dry run paid.',
                    'vehicles' => [
                        [
                            'vin' => '3C4NJDDB8LT159314',
                            'year' => '2020',
                            'make' => 'Jeep',
                            'model' => 'Compass Trailhawk',
                            'inop' => false,
                            'enclosed' => false
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Avis',
                        'state' => 'FL',
                        'city' => 'Fort Lauderdale',
                        'address' => '2371 SW 36th St',
                        'zip' => '33312',
                        'phones' => [],
                        'phone' => '19544448639',
                        'fax' => null,
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'JOE MAUS CHRYSLER DODGE JEEP RAM',
                        'state' => 'NC',
                        'city' => 'Albemarle',
                        'address' => '200 Highway 740 Byp E',
                        'zip' => '28001',
                        'phones' => [],
                        'fax' => null,
                        'phone' => '13212547878',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Ready Logistics',
                        'state' => 'AZ',
                        'city' => 'Phoenix',
                        'address' => '4615 E Elwood St STE 400',
                        'zip' => '85040',
                        'phones' => [],
                        'fax' => '14805583216',
                        'phone' => '14805583200',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 410,
                        'terms' => null,
                        'customer_payment_amount' => null,
                        'customer_payment_method_id' => null,
                        'customer_payment_method' => [
                            'id' => null,
                            'title' => null,
                        ],
                        'customer_payment_location' => null,
                        'broker_payment_amount' => 410,
                        'broker_payment_method_id' => Payment::METHOD_QUICKPAY,
                        'broker_payment_method' => [
                            'id' => Payment::METHOD_QUICKPAY,
                            'title' => Payment::BROKER_METHODS[Payment::METHOD_QUICKPAY]
                        ],
                        'broker_payment_days' => null,
                        'broker_payment_begins' => null,
                        'broker_fee_amount' => null,
                        'broker_fee_method_id' => null,
                        'broker_fee_method' => [
                            'id' => null,
                            'title' => null
                        ],
                        'broker_fee_days' => null,
                        'broker_fee_begins' => null,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_8(): void
    {
        $this->createMakes('HYUNDAI')
            ->createStates('TX', 'OK', 'AZ')
            ->createTimeZones('78219', '73139', '85040')
            ->sendPdfFile('transport_order_8')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => 'L20854284',
                    'pickup_date' => '01/25/2022',
                    'delivery_date' => '01/28/2022',
                    'dispatch_instructions' => '* SHIPPER REQUIRES THIS MUST PICK UP BY 1/24/2022 AND MUST DROP OFF BY 1/26/2022 * Must call prior to pick & drop off. Driver must call 24 hours in advance to confirm pick & drop hrs. No Dry Run Paid. Must call prior to pick & drop off. Driver must call 24 hours in advance to confirm pick & drop hrs. No Dry Run Paid. Must call prior to pick & drop off. Driver must call 24 hours in advance to confirm pick & drop hrs. No Dry Run Paid.',
                    'vehicles' => [
                        [
                            'vin' => 'KM8J33A46KU925601',
                            'year' => '2019',
                            'make' => 'Hyundai',
                            'model' => 'Tucson SE',
                            'inop' => false,
                            'enclosed' => false
                        ],
                        [
                            'vin' => 'KMHTG6AF1KU017511',
                            'year' => '2019',
                            'make' => 'Hyundai',
                            'model' => 'Veloster 2.0L',
                            'inop' => false,
                            'enclosed' => false
                        ],
                        [
                            'vin' => '5NMS23AD2LH155871',
                            'year' => '2020',
                            'make' => 'Hyundai',
                            'model' => 'Santa Fe SE',
                            'inop' => false,
                            'enclosed' => false
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM SAN ANTONIO',
                        'state' => 'TX',
                        'city' => 'San Antonio',
                        'address' => '2042 Ackerman Rd San Antonio T',
                        'zip' => '78219',
                        'phones' => [],
                        'phone' => '12106614200',
                        'fax' => null,
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'BOB HOWARD HYUNDAI',
                        'state' => 'OK',
                        'city' => 'Oklahoma City',
                        'address' => '613 W I 240 Service Rd',
                        'zip' => '73139',
                        'phones' => [],
                        'fax' => null,
                        'phone' => '14057538792',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Ready Logistics',
                        'state' => 'AZ',
                        'city' => 'Phoenix',
                        'address' => '4615 E Elwood St STE 400',
                        'zip' => '85040',
                        'phones' => [],
                        'fax' => '14805583216',
                        'phone' => '14805583200',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1020,
                        'terms' => null,
                        'customer_payment_amount' => null,
                        'customer_payment_method_id' => null,
                        'customer_payment_method' => [
                            'id' => null,
                            'title' => null,
                        ],
                        'customer_payment_location' => null,
                        'broker_payment_amount' => 1020,
                        'broker_payment_method_id' => Payment::METHOD_QUICKPAY,
                        'broker_payment_method' => [
                            'id' => Payment::METHOD_QUICKPAY,
                            'title' => Payment::BROKER_METHODS[Payment::METHOD_QUICKPAY]
                        ],
                        'broker_payment_days' => null,
                        'broker_payment_begins' => null,
                        'broker_fee_amount' => null,
                        'broker_fee_method_id' => null,
                        'broker_fee_method' => [
                            'id' => null,
                            'title' => null
                        ],
                        'broker_fee_days' => null,
                        'broker_fee_begins' => null,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_9(): void
    {
        $this->createMakes('DODGE')
            ->createStates('TX', 'OK', 'AZ')
            ->createTimeZones('77047', '74129', '85040')
            ->sendPdfFile('transport_order_9')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => 'L20875779',
                    'pickup_date' => '01/27/2022',
                    'delivery_date' => '01/30/2022',
                    'dispatch_instructions' => '* SHIPPER REQUIRES THIS MUST PICK UP BY 1/27/2022 AND MUST DROP OFF BY 1/28/2022 * Must call prior to pick and drop off. Driver must call 24 hours in advance to confirm pick & drop hrs. No dry run paid.unit is ready rdk must see office for gatepass or call ahead on friday to have printed for sat pu M-th9-5 330-6 fri sat 10-3 no fees trucks may need to park on side',
                    'vehicles' => [
                        [
                            'vin' => '3C4PDCBG8JT522720',
                            'year' => '2018',
                            'make' => 'Dodge',
                            'model' => 'Journey SXT',
                            'inop' => false,
                            'enclosed' => false
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'America\'s Auto Auction Houston',
                        'state' => 'TX',
                        'city' => 'Houston',
                        'address' => '1826 Almeda Genoa Rd',
                        'zip' => '77047',
                        'phones' => [],
                        'phone' => '17134160379',
                        'fax' => null,
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'JIM NORTON T-TOWN CHEVROLET',
                        'state' => 'OK',
                        'city' => 'Tulsa',
                        'address' => '8130 E Skelly Dr',
                        'zip' => '74129',
                        'phones' => [],
                        'fax' => null,
                        'phone' => '19182710430',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Ready Logistics',
                        'state' => 'AZ',
                        'city' => 'Phoenix',
                        'address' => '4615 E Elwood St STE 400',
                        'zip' => '85040',
                        'phones' => [],
                        'fax' => '14805583216',
                        'phone' => '14805583200',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 310,
                        'terms' => null,
                        'customer_payment_amount' => null,
                        'customer_payment_method_id' => null,
                        'customer_payment_method' => [
                            'id' => null,
                            'title' => null,
                        ],
                        'customer_payment_location' => null,
                        'broker_payment_amount' => 310,
                        'broker_payment_method_id' => Payment::METHOD_QUICKPAY,
                        'broker_payment_method' => [
                            'id' => Payment::METHOD_QUICKPAY,
                            'title' => Payment::BROKER_METHODS[Payment::METHOD_QUICKPAY]
                        ],
                        'broker_payment_days' => null,
                        'broker_payment_begins' => null,
                        'broker_fee_amount' => null,
                        'broker_fee_method_id' => null,
                        'broker_fee_method' => [
                            'id' => null,
                            'title' => null
                        ],
                        'broker_fee_days' => null,
                        'broker_fee_begins' => null,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_10(): void
    {
        $this->createMakes('TOYOTA')
            ->createStates('OH', 'VA', 'AZ')
            ->createTimeZones('44134', '22601', '85040')
            ->sendPdfFile('transport_order_10')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => 'L20877208',
                    'pickup_date' => '01/27/2022',
                    'delivery_date' => '01/29/2022',
                    'dispatch_instructions' => '* SHIPPER REQUIRES THIS MUST PICK UP BY 1/27/2022 AND MUST DROP OFF BY 1/31/2022 * Must call prior to pick and drop off. Driver must call 24 hours in advance to confirm pick & drop hrs. No dry run paid.',
                    'vehicles' => [
                        [
                            'vin' => '5TFDY5F17LX880309',
                            'year' => '2020',
                            'make' => 'Toyota',
                            'model' => 'Tundra SR5',
                            'inop' => false,
                            'enclosed' => false
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Manheim Cleveland',
                        'state' => 'OH',
                        'city' => 'Cleveland',
                        'address' => '4720 Brookpark Rd',
                        'zip' => '44134',
                        'phones' => [],
                        'phone' => '12165391701',
                        'fax' => null,
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MALLOY TOYOTA',
                        'state' => 'VA',
                        'city' => 'Winchester',
                        'address' => '400 Weems Ln',
                        'zip' => '22601',
                        'phones' => [],
                        'fax' => null,
                        'phone' => '15406365524',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Ready Logistics',
                        'state' => 'AZ',
                        'city' => 'Phoenix',
                        'address' => '4615 E Elwood St STE 400',
                        'zip' => '85040',
                        'phones' => [],
                        'fax' => '14805583216',
                        'phone' => '14805583200',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 410,
                        'terms' => null,
                        'customer_payment_amount' => null,
                        'customer_payment_method_id' => null,
                        'customer_payment_method' => [
                            'id' => null,
                            'title' => null,
                        ],
                        'customer_payment_location' => null,
                        'broker_payment_amount' => 410,
                        'broker_payment_method_id' => Payment::METHOD_QUICKPAY,
                        'broker_payment_method' => [
                            'id' => Payment::METHOD_QUICKPAY,
                            'title' => Payment::BROKER_METHODS[Payment::METHOD_QUICKPAY]
                        ],
                        'broker_payment_days' => null,
                        'broker_payment_begins' => null,
                        'broker_fee_amount' => null,
                        'broker_fee_method_id' => null,
                        'broker_fee_method' => [
                            'id' => null,
                            'title' => null
                        ],
                        'broker_fee_days' => null,
                        'broker_fee_begins' => null,
                    ],
                ]
            ], true);
    }

}
