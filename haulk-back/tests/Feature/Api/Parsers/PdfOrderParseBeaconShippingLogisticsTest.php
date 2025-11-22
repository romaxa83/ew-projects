<?php

namespace Tests\Feature\Api\Parsers;

class PdfOrderParseBeaconShippingLogisticsTest extends PdfParserHelper
{
    private const FOLDER_NAME = 'BeaconShippingLogistics';

    protected function getFolderName(): string
    {
        return self::FOLDER_NAME . DIRECTORY_SEPARATOR;
    }

    public function test_it_parsed_1(): void
    {
        $this->createStates('MO', 'TN', 'RI')
            ->createTimeZones('64127', '37203', '02888')
            ->sendPdfFile('beacon_shipping_logistics_1')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => 'ORD-15724-P7K9T3',
                    'pickup_date' => '02/20/2020',
                    'delivery_date' => '02/21/2020',
                    'dispatch_instructions' => 'PLEASE TAKE A PICTURE AND TEXT TO 401-497-4094 AT PICK-UP. PLEASE TAKE A PICTURE AND TEXT TO 401-497-4094 AT DELIVERY.',
                    'vehicles' => [
                        [
                            'vin' => null,
                            'year' => '2018',
                            'make' => 'Mercedes-Benz',
                            'model' => 'E350',
                            'inop' => false,
                            'enclosed' => false
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => null,
                        'state' => 'MO',
                        'city' => 'Kansas City',
                        'address' => '1312 Monroe Ave',
                        'zip' => '64127',
                        'phones' => [
                            [
                                'number' => '16199409101'
                            ],
                        ],
                        'phone' => '19139070117',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => null,
                        'state' => 'TN',
                        'city' => 'Nashville',
                        'address' => '1600 Mcgavock St',
                        'zip' => '37203',
                        'phones' => [
                            [
                                'number' => '18657120568'
                            ],
                        ],
                        'phone' => '16199409101',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Beacon Shipping Logistics',
                        'state' => 'RI',
                        'city' => 'Warwick',
                        'address' => '25 Messenger Drive',
                        'zip' => '02888',
                        'phones' => [
                            [
                                'name' => null,
                                'notes' => 'Dispatch',
                                'extension' => '103',
                                'number' => '14012702993'
                            ],
                            [
                                'name' => null,
                                'notes' => 'Accounting',
                                'extension' => '301',
                                'number' => '14012702993'
                            ],
                        ],
                        'phone' => '14012702993',
                        'email' => 'jjerome@bslusa.com',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 500,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_2(): void
    {
        $this->createStates('PA', 'NJ', 'RI')
            ->createTimeZones('15090', '07936', '02888')
            ->sendPdfFile('beacon_shipping_logistics_2')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => 'ORD-16175-K7W6T4',
                    'pickup_date' => '03/03/2020',
                    'delivery_date' => '03/04/2020',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '7JRA22TK6LG054780',
                            'year' => '2020',
                            'make' => 'Volvo',
                            'model' => 'S60',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Bobby Rahal Volvo',
                        'state' => 'PA',
                        'city' => 'Wexford',
                        'address' => '15035 Perry Highway #1',
                        'zip' => '15090',
                        'phones' => [
                            [
                                'number' => '17249403506'
                            ],
                        ],
                        'phone' => '17249403400',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Prestige Volvo East Hanover',
                        'state' => 'NJ',
                        'city' => 'East Hanover',
                        'address' => '285 East, NJ-10',
                        'zip' => '07936',
                        'phones' => [
                            [
                                'number' => '19733307755'
                            ],
                        ],
                        'phone' => '19738842400',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Beacon Shipping Logistics',
                        'state' => 'RI',
                        'city' => 'Warwick',
                        'address' => '25 Messenger Drive',
                        'zip' => '02888',
                        'phones' => [
                            [
                                'name' => null,
                                'notes' => 'Dispatch',
                                'extension' => '103',
                                'number' => '14012702993'
                            ],
                            [
                                'name' => null,
                                'notes' => 'Accounting',
                                'extension' => '301',
                                'number' => '14012702993'
                            ],
                        ],
                        'phone' => '14012702993',
                        'email' => 'eric@bslusa.com',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 350,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_3(): void
    {
        $this->createStates('KS', 'AL', 'RI')
            ->createTimeZones('66203', '35216', '02888')
            ->sendPdfFile('beacon_shipping_logistics_3')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => 'ORD-16373-H5Y8H2',
                    'pickup_date' => '03/14/2020',
                    'delivery_date' => '03/16/2020',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => 'JTJJM7FX7H5157204',
                            'year' => '2017',
                            'make' => 'Lexus',
                            'model' => 'GX460',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Hendrick Lexus Kansas City',
                        'state' => 'KS',
                        'city' => 'Merriam',
                        'address' => '6935 West Frontage Rd',
                        'zip' => '66203',
                        'phones' => [
                            [
                                'number' => '18882635575'
                            ],
                        ],
                        'phone' => '18887644479',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Hendrick Chrysler Dodge Jeep RAM',
                        'state' => 'AL',
                        'city' => 'Hoover',
                        'address' => '1624 Montgomery Hwy',
                        'zip' => '35216',
                        'phones' => [
                            [
                                'number' => '12055696282'
                            ],
                        ],
                        'phone' => '12055458074',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Beacon Shipping Logistics',
                        'state' => 'RI',
                        'city' => 'Warwick',
                        'address' => '25 Messenger Drive',
                        'zip' => '02888',
                        'phones' => [
                            [
                                'name' => null,
                                'notes' => 'Dispatch',
                                'extension' => '103',
                                'number' => '14012702993'
                            ],
                            [
                                'name' => null,
                                'notes' => 'Accounting',
                                'extension' => '301',
                                'number' => '14012702993'
                            ],
                        ],
                        'phone' => '14012702993',
                        'email' => 'eric@bslusa.com',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 550,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_4(): void
    {
        $this->createStates('NC', 'OH', 'RI')
            ->createTimeZones('27511', '44136', '02888')
            ->sendPdfFile('beacon_shipping_logistics_4')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => 'ORD-15247-H7K7M7',
                    'pickup_date' => '01/22/2020',
                    'delivery_date' => '01/24/2020',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => 'ZASPAKAN8K7C69138',
                            'year' => '2019',
                            'make' => 'Alfa Romeo',
                            'model' => 'Stelvio',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Johnson Maserati of Cary',
                        'state' => 'NC',
                        'city' => 'Cary',
                        'address' => '5020 Old Raleigh Rd',
                        'zip' => '27511',
                        'phones' => [
                            [
                                'number' => '19194151599'
                            ],
                        ],
                        'phone' => '14437148300',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Alfa Romeo  & FIAT Of Strongsville',
                        'state' => 'OH',
                        'city' => 'Strongsville',
                        'address' => '11800 Pearl Rd',
                        'zip' => '44136',
                        'phones' => [],
                        'phone' => '14403342155',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Beacon Shipping Logistics',
                        'state' => 'RI',
                        'city' => 'Warwick',
                        'address' => '25 Messenger Drive',
                        'zip' => '02888',
                        'phones' => [
                            [
                                'name' => null,
                                'notes' => 'Dispatch',
                                'extension' => '103',
                                'number' => '14012702993'
                            ],
                            [
                                'name' => null,
                                'notes' => 'Accounting',
                                'extension' => '301',
                                'number' => '14012702993'
                            ],
                        ],
                        'phone' => '14012702993',
                        'email' => 'eric@bslusa.com',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 450,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_5(): void
    {
        $this->createStates('oh', 'NC', 'RI')
            ->createTimeZones('44319', '27713', '02888')
            ->sendPdfFile('beacon_shipping_logistics_5')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => 'ORD-15691-X1B6F1',
                    'pickup_date' => '02/13/2020',
                    'delivery_date' => '02/14/2020',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1GNSKCKC7GR144041',
                            'year' => '2016',
                            'make' => 'Chevrolet',
                            'model' => 'Tahoe',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Akron Auto Auction',
                        'state' => 'oh',
                        'city' => 'Akron',
                        'address' => '2471 LEY DR',
                        'zip' => '44319',
                        'phones' => [],
                        'phone' => '13304365808',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Hendrick GM Southpoint',
                        'state' => 'NC',
                        'city' => 'Durham',
                        'address' => '127 Kentington Dr',
                        'zip' => '27713',
                        'phones' => [],
                        'phone' => '19198884896',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Beacon Shipping Logistics',
                        'state' => 'RI',
                        'city' => 'Warwick',
                        'address' => '25 Messenger Drive',
                        'zip' => '02888',
                        'phones' => [
                            [
                                'name' => null,
                                'notes' => 'Dispatch',
                                'extension' => '103',
                                'number' => '14012702993'
                            ],
                            [
                                'name' => null,
                                'notes' => 'Accounting',
                                'extension' => '301',
                                'number' => '14012702993'
                            ],
                        ],
                        'phone' => '14012702993',
                        'email' => 'eric@bslusa.com',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 475,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_6(): void
    {
        $this->createStates('MN', 'GA', 'RI')
            ->createTimeZones('55109', '30341', '02888')
            ->sendPdfFile('beacon_shipping_logistics_6')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => 'ORD-15756-H6R5Z6',
                    'pickup_date' => '02/15/2020',
                    'delivery_date' => '02/17/2020',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => null,
                            'year' => '2019',
                            'make' => 'Volkswagen',
                            'model' => 'Beetle',
                            'inop' => false,
                            'enclosed' => false
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Schmelz Countryside Volkswagen',
                        'state' => 'MN',
                        'city' => 'Maplewood',
                        'address' => '1180 MN-36',
                        'zip' => '55109',
                        'phones' => [],
                        'phone' => '16514848441',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Jim Ellis Volkswagen Atlanta',
                        'state' => 'GA',
                        'city' => 'Atlanta',
                        'address' => '5901 Peachtree Ind Blvd',
                        'zip' => '30341',
                        'phones' => [
                            [
                                'number' => '17704586811'
                            ],
                        ],
                        'phone' => '17705970373',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Beacon Shipping Logistics',
                        'state' => 'RI',
                        'city' => 'Warwick',
                        'address' => '25 Messenger Drive',
                        'zip' => '02888',
                        'phones' => [
                            [
                                'name' => null,
                                'notes' => 'Dispatch',
                                'extension' => '103',
                                'number' => '14012702993'
                            ],
                            [
                                'name' => null,
                                'notes' => 'Accounting',
                                'extension' => '301',
                                'number' => '14012702993'
                            ],
                        ],
                        'phone' => '14012702993',
                        'email' => 'ryan@bslusa.com',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 650,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_7(): void
    {
        $this->createStates('NC', 'MN', 'RI')
            ->createTimeZones('28655', '56001', '02888')
            ->sendPdfFile('beacon_shipping_logistics_7')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => 'ORD-16356-Y8L8T0',
                    'pickup_date' => '03/14/2020',
                    'delivery_date' => '03/16/2020',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '3GTU2NEC1GG107916',
                            'year' => '2016',
                            'make' => 'GMC',
                            'model' => 'Sierra',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Everett Chevrolet Buick GMC of Morganton',
                        'state' => 'NC',
                        'city' => 'Morganton',
                        'address' => '145 Bush Dr',
                        'zip' => '28655',
                        'phones' => [],
                        'phone' => '18283279171',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'SNELL MOTORS',
                        'state' => 'MN',
                        'city' => 'MANKATO',
                        'address' => '1900 E MADISON AVE',
                        'zip' => '56001',
                        'phones' => [],
                        'phone' => '15073454626',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Beacon Shipping Logistics',
                        'state' => 'RI',
                        'city' => 'Warwick',
                        'address' => '25 Messenger Drive',
                        'zip' => '02888',
                        'phones' => [
                            [
                                'name' => null,
                                'notes' => 'Dispatch',
                                'extension' => '103',
                                'number' => '14012702993'
                            ],
                            [
                                'name' => null,
                                'notes' => 'Accounting',
                                'extension' => '301',
                                'number' => '14012702993'
                            ],
                        ],
                        'phone' => '14012702993',
                        'email' => 'chandley@bslusa.com',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 800,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_8(): void
    {
        $this->createStates('FL', 'AL', 'RI')
            ->createTimeZones('33463', '36606', '02888')
            ->sendPdfFile('beacon_shipping_logistics_8')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => 'ORD-16274-B9Z6J7',
                    'pickup_date' => '03/09/2020',
                    'delivery_date' => '03/10/2020',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1GC1KVEG8GF128690',
                            'year' => '2016',
                            'make' => 'Chevrolet',
                            'model' => 'Silverado 2500',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'AutoNation Chevrolet Greenacres',
                        'state' => 'FL',
                        'city' => 'Greenacres',
                        'address' => '5757 Lake Worth Rd',
                        'zip' => '33463',
                        'phones' => [
                            [
                                'number' => '15612918082'
                            ],
                        ],
                        'phone' => '15614912969',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'AutoNation Chrysler Dodge Jeep Ram  Mobile',
                        'state' => 'AL',
                        'city' => 'Mobile',
                        'address' => '3016 GOVERNMENT BLVD',
                        'zip' => '36606',
                        'phones' => [],
                        'phone' => '12512022253',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Beacon Shipping Logistics',
                        'state' => 'RI',
                        'city' => 'Warwick',
                        'address' => '25 Messenger Drive',
                        'zip' => '02888',
                        'phones' => [
                            [
                                'name' => null,
                                'notes' => 'Dispatch',
                                'extension' => '103',
                                'number' => '14012702993'
                            ],
                            [
                                'name' => null,
                                'notes' => 'Accounting',
                                'extension' => '301',
                                'number' => '14012702993'
                            ],
                        ],
                        'phone' => '14012702993',
                        'email' => 'chandley@bslusa.com',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 400,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_9(): void
    {
        $this->createStates('FL', 'AL', 'RI')
            ->createTimeZones('33157', '36606', '02888')
            ->sendPdfFile('beacon_shipping_logistics_9')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => 'ORD-16273-F0Q8B4',
                    'pickup_date' => '03/09/2020',
                    'delivery_date' => '03/10/2020',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1C4BJWDG2HL516015',
                            'year' => '2017',
                            'make' => 'Jeep',
                            'model' => 'Wrangler',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Dadeland Dodge Chrysler Jeep Ram',
                        'state' => 'FL',
                        'city' => 'Miami',
                        'address' => '16501 S Dixie Hwy',
                        'zip' => '33157',
                        'phones' => [],
                        'phone' => '13052789994',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'AutoNation Chrysler Dodge Jeep Ram  Mobile',
                        'state' => 'AL',
                        'city' => 'Mobile',
                        'address' => '3016 GOVERNMENT BLVD',
                        'zip' => '36606',
                        'phones' => [
                            [
                                'number' => '12512022253'
                            ],
                        ],
                        'phone' => '12513807902',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Beacon Shipping Logistics',
                        'state' => 'RI',
                        'city' => 'Warwick',
                        'address' => '25 Messenger Drive',
                        'zip' => '02888',
                        'phones' => [
                            [
                                'name' => null,
                                'notes' => 'Dispatch',
                                'extension' => '103',
                                'number' => '14012702993'
                            ],
                            [
                                'name' => null,
                                'notes' => 'Accounting',
                                'extension' => '301',
                                'number' => '14012702993'
                            ],
                        ],
                        'phone' => '14012702993',
                        'email' => 'chandley@bslusa.com',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 400,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_10(): void
    {
        $this->createStates('MI', 'NC', 'RI')
            ->createTimeZones('48126', '27713', '02888')
            ->sendPdfFile('beacon_shipping_logistics_10')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => 'ORD-16664-C4Z6R1',
                    'pickup_date' => '03/26/2020',
                    'delivery_date' => '03/27/2020',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1G4G45G32GF221148',
                            'year' => '2016',
                            'make' => 'Buick',
                            'model' => 'lacrosse',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'DETROIT DISTRIBUTION CENTER',
                        'state' => 'MI',
                        'city' => 'Dearborn',
                        'address' => '6301 Wyoming Ave',
                        'zip' => '48126',
                        'phones' => [],
                        'phone' => '13132093369',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Hendrick GM Southpoint',
                        'state' => 'NC',
                        'city' => 'Durham',
                        'address' => '127 Kentington Dr',
                        'zip' => '27713',
                        'phones' => [],
                        'phone' => '19198884896',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Beacon Shipping Logistics',
                        'state' => 'RI',
                        'city' => 'Warwick',
                        'address' => '25 Messenger Drive',
                        'zip' => '02888',
                        'phones' => [
                            [
                                'name' => null,
                                'notes' => 'Dispatch',
                                'extension' => '103',
                                'number' => '14012702993'
                            ],
                            [
                                'name' => null,
                                'notes' => 'Accounting',
                                'extension' => '301',
                                'number' => '14012702993'
                            ],
                        ],
                        'phone' => '14012702993',
                        'email' => 'eric@bslusa.com',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 500,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_11(): void
    {
        $this->createStates('OH', 'NC', 'RI')
            ->createTimeZones('44805', '27713', '02888')
            ->sendPdfFile('beacon_shipping_logistics_11')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => 'ORD-16552-W4T6D2',
                    'pickup_date' => '03/18/2020',
                    'delivery_date' => '03/19/2020',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => 'LRBFXASA1HD111466',
                            'year' => '2017',
                            'make' => 'Buick',
                            'model' => 'Envision',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Bill Harris Chrylser Dodge Jeep',
                        'state' => 'OH',
                        'city' => 'Ashland',
                        'address' => '2245 CLAREMONT AVE',
                        'zip' => '44805',
                        'phones' => [],
                        'phone' => '14192892000',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Hendrick GM Southpoint',
                        'state' => 'NC',
                        'city' => 'Durham',
                        'address' => '127 Kentington Dr',
                        'zip' => '27713',
                        'phones' => [
                            [
                                'number' => '19193494023'
                            ],
                        ],
                        'phone' => '19198884896',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Beacon Shipping Logistics',
                        'state' => 'RI',
                        'city' => 'Warwick',
                        'address' => '25 Messenger Drive',
                        'zip' => '02888',
                        'phones' => [
                            [
                                'name' => null,
                                'notes' => 'Dispatch',
                                'extension' => '103',
                                'number' => '14012702993'
                            ],
                            [
                                'name' => null,
                                'notes' => 'Accounting',
                                'extension' => '301',
                                'number' => '14012702993'
                            ],
                        ],
                        'phone' => '14012702993',
                        'email' => 'eric@bslusa.com',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 450,
                    ],
                ]
            ], true);
    }

}
