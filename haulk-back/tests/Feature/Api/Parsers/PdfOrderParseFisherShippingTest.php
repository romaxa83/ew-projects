<?php

namespace Tests\Feature\Api\Parsers;

use Illuminate\Http\Response;

class PdfOrderParseFisherShippingTest extends PdfParserHelper
{
    private const FOLDER_NAME = 'FisherShipping';

    protected function getFolderName(): string
    {
        return self::FOLDER_NAME . DIRECTORY_SEPARATOR;
    }

    public function test_it_parsed_1(): void
    {
        $this->createStates('MO', 'IN', 'MA')
            ->createTimeZones('64081', '46996', '01501')
            ->sendPdfFile('fisher_shipping_1')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '242338',
                    'pickup_date' => '02/17/2020',
                    'delivery_date' => '02/18/2020',
                    'dispatch_instructions' => '*VANS MUST BE UNLOADED BEFORE 2:30pm OR DELIVERY LOCATION WILL NOT CHECK THEM IN*',
                    'vehicles' => [
                        [
                            'vin' => null,
                            'year' => '2019',
                            'make' => 'Dodge',
                            'model' => 'Grand Caravan',
                            'color' => null,
                            'inop' => false,
                            'enclosed' => false
                        ],
                        [
                            'vin' => null,
                            'year' => '2019',
                            'make' => 'Dodge',
                            'model' => 'Grand Caravan',
                            'color' => null,
                            'inop' => false,
                            'enclosed' => false
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Jay Hatfield Mobility',
                        'state' => 'MO',
                        'city' => 'Lee\'s Summit',
                        'address' => '1115 SW Oldham Parkway',
                        'zip' => '64081',
                        'phones' => [],
                        'phone' => '18166005124',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Braun Corporation',
                        'state' => 'IN',
                        'city' => 'Winamac',
                        'address' => '631 West 11th Street',
                        'zip' => '46996',
                        'phones' => [],
                        'phone' => '18009467513',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Fisher Shipping',
                        'state' => 'MA',
                        'city' => 'Auburn',
                        'address' => '19 Midstate Drive, Suite 120',
                        'zip' => '01501',
                        'phones' => [],
                        'phone' => '15087922427',
                        'email' => 'jduque@fishershipping.com',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 700,
                        'broker_fee_amount' => null,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_2(): void
    {
        $this->createStates('IL', 'NC', 'MA')
            ->createTimeZones('60448', '28704', '01501')
            ->sendPdfFile('fisher_shipping_2')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '242368',
                    'pickup_date' => '02/17/2020',
                    'delivery_date' => '02/19/2020',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => null,
                            'year' => '2019',
                            'make' => 'Ford',
                            'model' => 'F150 Super Crew Cab',
                            'color' => '6.5 foot bed',
                            'inop' => false,
                            'enclosed' => false
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Ozinga',
                        'state' => 'IL',
                        'city' => 'Mokena',
                        'address' => '19001 Old Lagrange Road #300',
                        'zip' => '60448',
                        'phones' => [
                            [
                                'notes' => null,
                                'extension' => null,
                                'number' => '17083264200'
                            ],
                        ],
                        'phone' => '13127583934',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Alltech Eco',
                        'state' => 'NC',
                        'city' => 'Arden',
                        'address' => '101 Fair Oaks Road',
                        'zip' => '28704',
                        'phones' => [],
                        'phone' => '18286548300',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Fisher Shipping',
                        'state' => 'MA',
                        'city' => 'Auburn',
                        'address' => '19 Midstate Drive, Suite 120',
                        'zip' => '01501',
                        'phones' => [],
                        'phone' => '15087922427',
                        'email' => 'jduque@fishershipping.com',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 450,
                        'broker_fee_amount' => null,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_3(): void
    {
        $this->sendPdfFile('fisher_shipping_3')
            ->assertStatus(Response::HTTP_NOT_ACCEPTABLE)
            ->assertJson([
                'errors' => [
                    [
                        'title' => __('validation.custom.parser.file_error'),
                        'status' => Response::HTTP_NOT_ACCEPTABLE
                    ]
                ]
            ]);
    }

    public function test_it_parsed_4(): void
    {
        $this->createStates('WI', 'IL', 'MA')
            ->createTimeZones('54401', '62044', '01501')
            ->sendPdfFile('fisher_shipping_4')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '242405',
                    'pickup_date' => '02/18/2020',
                    'delivery_date' => '02/19/2020',
                    'dispatch_instructions' => '***MUST BE PICKED UP WITH ORDER 242404*** ***Length 190" / Height 72"***',
                    'vehicles' => [
                        [
                            'vin' => null,
                            'year' => '2019',
                            'make' => 'Ford',
                            'model' => 'Transit Connect XL',
                            'color' => null,
                            'inop' => false,
                            'enclosed' => false
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Kocourek Ford Lincoln',
                        'state' => 'WI',
                        'city' => 'Wausau',
                        'address' => '2727 N. 20th Avenue',
                        'zip' => '54401',
                        'phones' => [
                            [
                                'number' => '17153934804',
                                'extension' => null,
                                'notes' => null
                            ]
                        ],
                        'phone' => '18663376745',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Morrow Brothers Ford Inc',
                        'state' => 'IL',
                        'city' => 'Greenfield',
                        'address' => '1242 Main Street',
                        'zip' => '62044',
                        'phones' => [],
                        'phone' => '12173683037',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Fisher Shipping',
                        'state' => 'MA',
                        'city' => 'Auburn',
                        'address' => '19 Midstate Drive, Suite 120',
                        'zip' => '01501',
                        'phones' => [],
                        'phone' => '15087922427',
                        'email' => 'jduque@fishershipping.com',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 500,
                        'broker_fee_amount' => null,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_5(): void
    {
        $this->createStates('IN', 'PA', '')
            ->createTimeZones('46545', '19425', '')
            ->sendPdfFile('fisher_shipping_5')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '243327',
                    'pickup_date' => '03/20/2020',
                    'delivery_date' => '03/21/2020',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => null,
                            'year' => '2020',
                            'make' => 'Lexus',
                            'model' => 'RX450',
                            'color' => null,
                            'inop' => false,
                            'enclosed' => false
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Lexus of Mishawaka',
                        'state' => 'IN',
                        'city' => 'Mishawaka',
                        'address' => '4325 Grape Rd',
                        'zip' => '46545',
                        'phones' => [
                            [
                                'notes' => null,
                                'extension' => null,
                                'number' => '15745208882'
                            ],
                        ],
                        'phone' => '15742437700',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Lexus of Chester Springs',
                        'state' => 'PA',
                        'city' => 'Chester Springs',
                        'address' => '400 Pottstown Pike',
                        'zip' => '19425',
                        'phones' => [
                            [
                                'notes' => null,
                                'extension' => null,
                                'number' => '16108047047'
                            ],
                        ],
                        'phone' => '16103218000',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Fisher Shipping',
                        'state' => null,
                        'city' => null,
                        'address' => null,
                        'zip' => null,
                        'phones' => null,
                        'email' => null
                    ],
                    'payment' => null,
                ]
            ], true);
    }

    public function test_it_parsed_6(): void
    {
        $this->sendPdfFile('fisher_shipping_6')
            ->assertStatus(Response::HTTP_NOT_ACCEPTABLE)
            ->assertJson([
                'errors' => [
                    [
                        'title' => __('validation.custom.parser.file_error'),
                        'status' => Response::HTTP_NOT_ACCEPTABLE
                    ]
                ]
            ]);
    }

    public function test_it_parsed_7(): void
    {
        $this->createStates('IL', 'TX', 'MA')
            ->createTimeZones('60443', '75070', '01501')
            ->sendPdfFile('fisher_shipping_7')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '243355',
                    'pickup_date' => '03/18/2020',
                    'delivery_date' => '03/20/2020',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => null,
                            'year' => '2019',
                            'make' => 'Hyundai',
                            'model' => 'Sonata',
                            'color' => null,
                            'inop' => false,
                            'enclosed' => false
                        ],
                        [
                            'vin' => null,
                            'year' => '2019',
                            'make' => 'Hyundai',
                            'model' => 'Sonata',
                            'color' => null,
                            'inop' => false,
                            'enclosed' => false
                        ],
                        [
                            'vin' => null,
                            'year' => '2019',
                            'make' => 'Hyundai',
                            'model' => 'Sonata',
                            'color' => null,
                            'inop' => false,
                            'enclosed' => false
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Manheim Chicago',
                        'state' => 'IL',
                        'city' => 'Matteson',
                        'address' => '20401 Cox Avenue',
                        'zip' => '60443',
                        'phones' => [],
                        'phone' => '18158064222',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Huffines Hyundai McKinney',
                        'state' => 'TX',
                        'city' => 'McKinney',
                        'address' => '1301 N Central Expressway',
                        'zip' => '75070',
                        'phones' => [
                            [
                                'notes' => 'Larry Direct',
                                'extension' => null,
                                'number' => '14695254332'
                            ],
                        ],
                        'phone' => '14695254500',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Fisher Shipping',
                        'state' => 'MA',
                        'city' => 'Suite 120 Auburn',
                        'address' => '19 Midstate Drive',
                        'zip' => '01501',
                        'phones' => [],
                        'phone' => '15087922427',
                        'email' => null,
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1425,
                        'broker_fee_amount' => 225,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_8(): void
    {
        $this->createStates('MN', 'WI', '')
            ->createTimeZones('55904', '53713', '')
            ->sendPdfFile('fisher_shipping_8')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '243432',
                    'pickup_date' => '03/23/2020',
                    'delivery_date' => '03/24/2020',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => null,
                            'year' => '2020',
                            'make' => 'Mercedes-Benz',
                            'model' => 'E 350',
                            'color' => null,
                            'inop' => false,
                            'enclosed' => false
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Mercedes-Benz of Rochester',
                        'state' => 'MN',
                        'city' => 'Rochester',
                        'address' => '4447 Canal Place SE',
                        'zip' => '55904',
                        'phones' => [
                            [
                                'notes' => 'Brian cell',
                                'extension' => null,
                                'number' => '15073131169'
                            ],
                        ],
                        'phone' => '15074243001',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Zimbrick European',
                        'state' => 'WI',
                        'city' => 'Madison',
                        'address' => '2300 Rimrock Rd',
                        'zip' => '53713',
                        'phones' => [
                            [
                                'notes' => 'Patrick cell',
                                'extension' => null,
                                'number' => '16085752517'
                            ],
                        ],
                        'phone' => '16082915873',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Fisher Shipping',
                        'state' => null,
                        'city' => null,
                        'address' => null,
                        'zip' => null,
                        'phones' => null,
                        'email' => null
                    ],
                    'payment' => null,
                ]
            ], true);
    }

    public function test_it_parsed_9(): void
    {
        $this->createStates('WI', 'NY', '')
            ->createTimeZones('53221', '12308', '')
            ->sendPdfFile('fisher_shipping_9')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '243447',
                    'pickup_date' => '03/25/2020',
                    'delivery_date' => '03/27/2020',
                    'dispatch_instructions' => 'H-6\'1" L-15\'5"**DRIVER MUST CALL DAY BEFORE DELIVERY TO SCHEDULE. NO DELIVERY ALLOWED AFTER 8 00PM**',
                    'vehicles' => [
                        [
                            'vin' => null,
                            'year' => '2020',
                            'make' => 'Nissan',
                            'model' => 'Altima',
                            'color' => null,
                            'inop' => false,
                            'enclosed' => false
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Rosen Nissan',
                        'state' => 'WI',
                        'city' => 'Milwaukee',
                        'address' => '5839 South 27th Street',
                        'zip' => '53221',
                        'phones' => [
                            [
                                'notes' => 'Jeff Cell',
                                'extension' => null,
                                'number' => '12622105906'
                            ],
                            [
                                'notes' => 'Justin preferre',
                                'extension' => null,
                                'number' => '12629897907'
                            ],
                        ],
                        'phone' => '14142829300',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Private Customer',
                        'state' => 'NY',
                        'city' => 'Schenectady',
                        'address' => '34 Columbia Street',
                        'zip' => '12308',
                        'phones' => [
                            [
                                'notes' => 'Andrew personal cell',
                                'extension' => null,
                                'number' => '19293163919'
                            ],
                        ],
                        'phone' => '12142405979',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Fisher Shipping',
                        'state' => null,
                        'city' => null,
                        'address' => null,
                        'zip' => null,
                        'phones' => null,
                        'email' => null
                    ],
                    'payment' => null,
                ]
            ], true);
    }

    public function test_it_parsed_10(): void
    {
        $this->createStates('WI', 'NY', '')
            ->createTimeZones('53221', '12065', '')
            ->sendPdfFile('fisher_shipping_10')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '243460',
                    'pickup_date' => '03/26/2020',
                    'delivery_date' => '03/27/2020',
                    'dispatch_instructions' => 'H-6\'1" L-15\'5"**DRIVER MUST CALL DAY BEFORE DELIVERY TO SCHEDULE. NO DELIVERY ALLOWED AFTER 8 00PM**',
                    'vehicles' => [
                        [
                            'vin' => null,
                            'year' => '2020',
                            'make' => 'Nissan',
                            'model' => 'NV200',
                            'color' => null,
                            'inop' => false,
                            'enclosed' => false
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Rosen Nissan',
                        'state' => 'WI',
                        'city' => 'Milwaukee',
                        'address' => '5839 South 27th Street',
                        'zip' => '53221',
                        'phones' => [
                            [
                                'notes' => 'Jeff Cell',
                                'extension' => null,
                                'number' => '12622105906'
                            ],
                            [
                                'notes' => 'Justin preferre',
                                'extension' => null,
                                'number' => '12629897907'
                            ],
                        ],
                        'phone' => '14142829300',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Private Customer',
                        'state' => 'NY',
                        'city' => 'Clifton Park',
                        'address' => '113 Old Coach Road',
                        'zip' => '12065',
                        'phones' => [],
                        'phone' => '14694384839',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Fisher Shipping',
                        'state' => null,
                        'city' => null,
                        'address' => null,
                        'zip' => null,
                        'phones' => null,
                        'email' => null
                    ],
                    'payment' => null,
                ]
            ], true);
    }

    public function test_it_parsed_11(): void
    {
        $this->createStates('GA', 'IL', '')
            ->createTimeZones('30331', '61550', '')
            ->sendPdfFile('fisher_shipping_11')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '243466',
                    'pickup_date' => '',
                    'delivery_date' => '03/27/2020',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => null,
                            'year' => '2019',
                            'make' => 'Chevrolet',
                            'model' => 'Colorado Crew Cab',
                            'color' => null,
                            'inop' => false,
                            'enclosed' => false
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM GEORGIA',
                        'state' => 'GA',
                        'city' => 'ATLANTA',
                        'address' => '7205 CAMPBELLTON ROAD',
                        'zip' => '30331',
                        'phones' => [],
                        'phone' => '14043495555',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Bob Grimm Chevrolet',
                        'state' => 'IL',
                        'city' => 'Morton',
                        'address' => '2271 S Main Street - MUST CALL DAY BEFORE TO SCHEDULE',
                        'zip' => '61550',
                        'phones' => [
                            [
                                'notes' => 'Rick cell',
                                'extension' => null,
                                'number' => '13092652977'
                            ],
                        ],
                        'phone' => '13092632241',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Fisher Shipping',
                        'state' => null,
                        'city' => null,
                        'address' => null,
                        'zip' => null,
                        'phones' => null,
                        'email' => null
                    ],
                    'payment' => null,
                ]
            ], true);
    }
}
