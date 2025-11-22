<?php

namespace Tests\Feature\Api\Parsers;

class PdfOrderParseWholesaleExpressTest extends PdfParserHelper
{
    private const FOLDER_NAME = 'WholesaleExpress';

    protected function getFolderName(): string
    {
        return self::FOLDER_NAME . DIRECTORY_SEPARATOR;
    }

    public function test_it_parsed_1(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('MD', 'NC', 'TN')
            ->createTimeZones('21017', '28786', '37122')
            ->sendPdfFile('wholesale_express_1')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '183291',
                    'pickup_date' => '02/26/2020',
                    'delivery_date' => '02/27/2020',
                    'instructions' => 'Must call in advance to confirm availability prior to arrival at location. CALL IN ADVANCE; release attached',
                    'vehicles' => [
                        [
                            'vin' => '1GNKVGKD2GJ193069',
                            'year' => '2016',
                            'make' => 'CHEVROLET',
                            'model' => 'TRAVERSE AWD 4DR LT W/1LT',
                            'color' => null,
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null
                        ]
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Bel Air Aa',
                        'address' => '4805 PHILADELPHIA RD',
                        'city' => 'Belcamp',
                        'state' => 'MD',
                        'zip' => '21017',
                        'phones' => [],
                        'phone' => '14433452054',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Waynesville Chrysler Dodge Jeep Ram',
                        'address' => '280 HYATT CREEK RD',
                        'city' => 'Waynesville',
                        'state' => 'NC',
                        'zip' => '28786',
                        'phones' => null,
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'address' => '8037 EASTGATE BLVD',
                        'city' => 'MOUNT JULIET',
                        'state' => 'TN',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'fax' => '16155232896',
                        'email' => 'getpaid@wholesaleinc.com',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone
                    ]
                ]
            ], true);
    }

    public function test_it_parsed_2(): void
    {
        $this->createMakes('NISSAN')
            ->createStates('TN', 'NC', 'TN')
            ->createTimeZones('37122', '28115', '37122')
            ->sendPdfFile('wholesale_express_2')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '183196',
                    'pickup_date' => '02/25/2020',
                    'delivery_date' => '02/26/2020',
                    'instructions' => 'Must call in advance to confirm availability prior to arrival at location.',
                    'vehicles' => [
                        [
                            'vin' => '1N4BL4CV3LC126697',
                            'year' => '2020',
                            'make' => 'NISSAN',
                            'model' => 'ALTIMA 2.5 SR SEDAN',
                            'color' => null,
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null
                        ]
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Manheim Nashville',
                        'address' => '8400 EASTGATE BLVD',
                        'city' => 'Mount Juliet',
                        'state' => 'TN',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16157733800',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Pit Box Auto Sales LLC',
                        'address' => '169 E PLAZA DR',
                        'city' => 'Mooresville',
                        'state' => 'NC',
                        'zip' => '28115',
                        'phones' => [],
                        'phone' => '17043608207',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'address' => '8037 EASTGATE BLVD',
                        'city' => 'MOUNT JULIET',
                        'state' => 'TN',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'fax' => '16155232896',
                        'email' => 'getpaid@wholesaleinc.com',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone
                    ]
                ]
            ], true);
    }

    public function test_it_parsed_3(): void
    {
        $this->createMakes('NISSAN')
            ->createStates('MN', 'TN', 'TN')
            ->createTimeZones('55313', '37122', '37122')
            ->sendPdfFile('wholesale_express_3')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '183115',
                    'pickup_date' => '02/27/2020',
                    'delivery_date' => '02/29/2020',
                    'instructions' => 'Must call in advance to confirm availability prior to arrival at location. DROP HOURS 8-5 DAILY. AFTER HOURS DROP IS ACCEPTABLE. LEAVE KEYS IN VEHICLE. BOL **MUST** BE STAMPED BY SECURITY. CONTACT DEE WITH ISSUES 615-613-7458',
                    'vehicles' => [
                        [
                            'vin' => '1C6RR7KT3GS187592',
                            'year' => '2016',
                            'make' => 'RAM',
                            'model' => '1500 4WD CREW CAB 140.5"',
                            'color' => 'black',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null
                        ]
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Ryan Motors',
                        'address' => '1000 HIGHWAY 55 E',
                        'city' => 'Buffalo',
                        'state' => 'MN',
                        'zip' => '55313',
                        'phones' => [],
                        'phone' => '17636822424',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Wholesale Inc Eastgate',
                        'address' => '8037 EASTGATE BLVD',
                        'city' => 'Mount Juliet',
                        'state' => 'TN',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16156137458',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'address' => '8037 EASTGATE BLVD',
                        'city' => 'MOUNT JULIET',
                        'state' => 'TN',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'fax' => '16155232896',
                        'email' => 'getpaid@wholesaleinc.com',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone
                    ]
                ]
            ], true);
    }

    public function test_it_parsed_4(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('MI', 'TX', 'TN')
            ->createTimeZones('48423', '75236', '37122')
            ->sendPdfFile('wholesale_express_4')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '182850',
                    'pickup_date' => '02/25/2020',
                    'delivery_date' => '02/27/2020',
                    'instructions' => 'Must call in advance to confirm availability prior to arrival at location. DROP INSTRUCTIONS: Write on the window Dealer #5258127 Wholesale inc.',
                    'vehicles' => [
                        [
                            'vin' => '1GNKRJKD9HJ170570',
                            'year' => '2017',
                            'make' => 'CHEVROLET',
                            'model' => 'TRAVERSE FWD 4DR PREMIER',
                            'color' => 'gold',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null
                        ]
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Hank Graff Chevrolet Davison',
                        'address' => '800 N STATE RD',
                        'city' => 'Davison',
                        'state' => 'MI',
                        'zip' => '48423',
                        'phones' => [],
                        'phone' => '18106534111',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Manheim Dallas',
                        'address' => '2435 S WALTON WALKER BLVD',
                        'city' => 'Dallas',
                        'state' => 'TX',
                        'zip' => '75236',
                        'phones' => null,
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'address' => '8037 EASTGATE BLVD',
                        'city' => 'MOUNT JULIET',
                        'state' => 'TN',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'fax' => '16155232896',
                        'email' => 'getpaid@wholesaleinc.com',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone
                    ]
                ]
            ], true);
    }

    public function test_it_parsed_5(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('MI', 'TN', 'TN')
            ->createTimeZones('48911', '37122', '37122')
            ->sendPdfFile('wholesale_express_5')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '182634',
                    'pickup_date' => '02/25/2020',
                    'delivery_date' => '02/26/2020',
                    'instructions' => 'Must call in advance to confirm availability prior to arrival at location. DROP HOURS 8-5 DAILY. AFTER HOURS DROP IS ACCEPTABLE. LEAVE KEYS IN VEHICLE. BOL **MUST** BE STAMPED BY SECURITY. CONTACT DEE WITH ISSUES 615-613-7458',
                    'vehicles' => [
                        [
                            'vin' => '1C6RR7KG5HS669856',
                            'year' => '2017',
                            'make' => 'RAM',
                            'model' => '1500 4X4 CREW CAB 5\'7" BOX',
                            'color' => 'blue',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null
                        ]
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Bill Snethkamp Lansing Dodge',
                        'address' => '6131 S PENNSYLVANIA AVE',
                        'city' => 'Lansing',
                        'state' => 'MI',
                        'zip' => '48911',
                        'phones' => [],
                        'phone' => '15172206343',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Wholesale Inc Eastgate',
                        'address' => '8037 EASTGATE BLVD',
                        'city' => 'Mount Juliet',
                        'state' => 'TN',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16156137458',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'address' => '8037 EASTGATE BLVD',
                        'city' => 'MOUNT JULIET',
                        'state' => 'TN',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'fax' => '16155232896',
                        'email' => 'getpaid@wholesaleinc.com',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone
                    ]
                ]
            ], true);
    }

    public function test_it_parsed_6(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('OH', 'LA', 'TN')
            ->createTimeZones('43123', '70461', '37122')
            ->sendPdfFile('wholesale_express_6')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '182127',
                    'pickup_date' => '02/26/2020',
                    'delivery_date' => '03/02/2020',
                    'instructions' => 'Must call in advance to confirm availability prior to arrival at location. CALL IN ADVANCE; Call ahead to confirm gatepass is issued. MUST PICK UP DURING BUSINESS HOURS.',
                    'vehicles' => [
                        [
                            'vin' => '1GC1KWEY6JF235999',
                            'year' => '2018',
                            'make' => 'CHEVROLET',
                            'model' => 'SILVERADO 2500HD 4WD CREW CAB LTZ',
                            'color' => null,
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null
                        ]
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Manheim Ohio',
                        'address' => '3905 JACKSON PIKE',
                        'city' => 'Grove City',
                        'state' => 'OH',
                        'zip' => '43123',
                        'phones' => [],
                        'phone' => '16148712771',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Matt Bowers Chevy',
                        'address' => '316 E HOWZE BEACH RD',
                        'city' => 'Slidell',
                        'state' => 'LA',
                        'zip' => '70461',
                        'phones' => [],
                        'phone' => '15049141478',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'address' => '8037 EASTGATE BLVD',
                        'city' => 'MOUNT JULIET',
                        'state' => 'TN',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'fax' => '16155232896',
                        'email' => 'getpaid@wholesaleinc.com',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone
                    ]
                ]
            ], true);
    }

    public function test_it_parsed_7(): void
    {
        $this->createMakes('INFINITI')
            ->createStates('TX', 'OK', 'TN')
            ->createTimeZones('77074', '74133', '37122')
            ->sendPdfFile('wholesale_express_7')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '182918',
                    'pickup_date' => '02/26/2020',
                    'delivery_date' => '02/27/2020',
                    'instructions' => 'Must call in advance to confirm availability prior to arrival at location. MUST CALL PICKUP LOCATION BEFORE ARRIVAL; RELEASE ATTACHED; CALL IN ADVANCE',
                    'vehicles' => [
                        [
                            'vin' => 'JN8AZ2NE0G9124791',
                            'year' => '2016',
                            'make' => 'INFINITI',
                            'model' => 'QX80 4WD 4DR',
                            'color' => null,
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null
                        ]
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Southwest Infiniti',
                        'address' => '10495 SOUTHWEST FWY',
                        'city' => 'Houston',
                        'state' => 'TX',
                        'zip' => '77074',
                        'phones' => [],
                        'phone' => '12817450405',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Jackie Cooper Infiniti',
                        'address' => '8825 S MEMORIAL DR',
                        'city' => 'Tulsa',
                        'state' => 'OK',
                        'zip' => '74133',
                        'phones' => [],
                        'phone' => '19182894184',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'address' => '8037 EASTGATE BLVD',
                        'city' => 'MOUNT JULIET',
                        'state' => 'TN',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'fax' => '16155232896',
                        'email' => 'getpaid@wholesaleinc.com',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone
                    ]
                ]
            ], true);
    }

    public function test_it_parsed_8(): void
    {
        $this->createMakes('HYUNDAI')
            ->createStates('TN', 'OH', 'TN')
            ->createTimeZones('37122', '44312', '37122')
            ->sendPdfFile('wholesale_express_8')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '182968',
                    'pickup_date' => '02/25/2020',
                    'delivery_date' => '02/26/2020',
                    'instructions' => 'Must call in advance to confirm availability prior to arrival at location. gatepass attached',
                    'vehicles' => [
                        [
                            'vin' => 'KM8SNDHF9GU148401',
                            'year' => '2016',
                            'make' => 'HYUNDAI',
                            'model' => 'SANTA FE AWD 4DR',
                            'color' => null,
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null
                        ]
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Manheim Nashville',
                        'address' => '8400 EASTGATE BLVD',
                        'city' => 'Mount Juliet',
                        'state' => 'TN',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16157733800',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Hyundai Of Green',
                        'address' => '3360 S ARLINGTON RD',
                        'city' => 'Akron',
                        'state' => 'OH',
                        'zip' => '44312',
                        'phones' => [],
                        'phone' => '18443851366',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'address' => '8037 EASTGATE BLVD',
                        'city' => 'MOUNT JULIET',
                        'state' => 'TN',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'fax' => '16155232896',
                        'email' => 'getpaid@wholesaleinc.com',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone
                    ]
                ]
            ], true);
    }

    public function test_it_parsed_9(): void
    {
        $this->createMakes('NISSAN', 'FORD')
            ->createStates('NV', 'MO', 'TN')
            ->createTimeZones('89156', '64116', '37122')
            ->sendPdfFile('wholesale_express_9')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '182901',
                    'pickup_date' => '02/25/2020',
                    'delivery_date' => '02/27/2020',
                    'instructions' => 'Must call in advance to confirm availability prior to arrival at location. CALL IN ADVANCE; GP at guard shack',
                    'vehicles' => [
                        [
                            'vin' => '1N4AZ0CP0FC312447',
                            'year' => '2015',
                            'make' => 'NISSAN',
                            'model' => 'LEAF 4DR HB SV',
                            'color' => null,
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null
                        ],
                        [
                            'vin' => '1FMSU43P14EC64362',
                            'year' => '2004',
                            'make' => 'FORD',
                            'model' => 'EXCURSION 137" WB 6.0L LIMITED 4WD',
                            'color' => null,
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null
                        ]
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Manheim Nevada',
                        'address' => '6600 AUCTION LN',
                        'city' => 'Las Vegas',
                        'state' => 'NV',
                        'zip' => '89156',
                        'phones' => [],
                        'phone' => '17027301400',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Arc Auto LLC',
                        'address' => '95 DESIGN DR',
                        'city' => 'N Kansas City',
                        'state' => 'MO',
                        'zip' => '64116',
                        'phones' => [],
                        'phone' => '18165919150',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'address' => '8037 EASTGATE BLVD',
                        'city' => 'MOUNT JULIET',
                        'state' => 'TN',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'fax' => '16155232896',
                        'email' => 'getpaid@wholesaleinc.com',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone
                    ]
                ]
            ], true);
    }

    public function test_it_parsed_10(): void
    {
        $this->createMakes('BUICK')
            ->createStates('MI', 'IA', 'TN')
            ->createTimeZones('48506', '51031', '37122')
            ->sendPdfFile('wholesale_express_10')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '182199',
                    'pickup_date' => '02/22/2020',
                    'delivery_date' => '02/24/2020',
                    'instructions' => 'Must call in advance to confirm availability prior to arrival at location.',
                    'vehicles' => [
                        [
                            'vin' => '5GAKVBKD3HJ207446',
                            'year' => '2017',
                            'make' => 'BUICK',
                            'model' => 'ENCLAVE AWD 4DR LEATHER',
                            'color' => null,
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null
                        ],
                        [
                            'vin' => '5GAKVBKD9HJ307325',
                            'year' => '2017',
                            'make' => 'BUICK',
                            'model' => 'ENCLAVE AWD 4DR LEATHER',
                            'color' => null,
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null
                        ]
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Adesa Flint',
                        'address' => '3711 WESTERN RD',
                        'city' => 'Flint',
                        'state' => 'MI',
                        'zip' => '48506',
                        'phones' => [],
                        'phone' => '18107362700',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Total Motors Of Le Mars',
                        'address' => '801 HAWKEYE AVE SW',
                        'city' => 'Le Mars',
                        'state' => 'IA',
                        'zip' => '51031',
                        'phones' => [],
                        'phone' => '17125464115',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'address' => '8037 EASTGATE BLVD',
                        'city' => 'MOUNT JULIET',
                        'state' => 'TN',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'fax' => '16155232896',
                        'email' => 'getpaid@wholesaleinc.com',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone
                    ]
                ]
            ], true);
    }

}
