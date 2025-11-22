<?php

namespace Tests\Feature\Api\Parsers;

class PdfOrderParseAcvTransportationTest extends PdfParserHelper
{
    private const FOLDER_NAME = 'AcvTransportation';

    protected function getFolderName(): string
    {
        return self::FOLDER_NAME . DIRECTORY_SEPARATOR;
    }

    public function test_it_parsed_1(): void
    {
        $this->createMakes('RAM')
            ->createStates('PA', 'VA', 'NY')
            ->createTimeZones('15701', '22801', '14203')
            ->sendPdfFile('acv_transportation_1')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2828224',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/10/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '3C6TR5DT4GG145169',
                            'year' => '2016',
                            'make' => 'Ram',
                            'model' => '2500 SLT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Autosport Co',
                        'state' => 'PA',
                        'city' => 'Indiana',
                        'address' => '1115 Philadelphia Street',
                        'zip' => '15701',
                        'phones' => [],
                        'phone' => '17248403499',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Dick Myers, Inc',
                        'state' => 'VA',
                        'city' => 'Harrisonburg',
                        'address' => '1711, South Main Street',
                        'zip' => '22801',
                        'phones' => [],
                        'phone' => '15402142031',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 670
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_2(): void
    {
        $this->createMakes('DODGE')
            ->createStates('PA', 'OH', 'NY')
            ->createTimeZones('17901', '44256', '14203')
            ->sendPdfFile('acv_transportation_2')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2835983',
                    'pickup_date' => '05/11/2021',
                    'delivery_date' => '05/12/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1D7HU18D14S705894',
                            'year' => '2004',
                            'make' => 'Dodge',
                            'model' => 'Ram 1500 SLT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'POTTSVILLE FORD',
                        'state' => 'PA',
                        'city' => 'Pottsville',
                        'address' => '1456 ROUTE 61 SOUTH',
                        'zip' => '17901',
                        'phones' => null,
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Southern Select Auto Sales',
                        'state' => 'OH',
                        'city' => 'Medina',
                        'address' => '920 Medina road',
                        'zip' => '44256',
                        'phones' => [],
                        'phone' => '13302391113',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 580
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_3(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('ME', 'IL', 'NY')
            ->createTimeZones('04046', '60565', '14203')
            ->sendPdfFile('acv_transportation_3')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2836093',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/10/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1GCVKREC3EZ349390',
                            'year' => '2014',
                            'make' => 'Chevrolet',
                            'model' => 'Silverado 1500 LT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Weirs Motor Inc',
                        'state' => 'ME',
                        'city' => 'Kennebunkport',
                        'address' => '1513 Portland Road',
                        'zip' => '04046',
                        'phones' => [],
                        'phone' => '12075901186',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Naper Motors Inc',
                        'state' => 'IL',
                        'city' => 'Naperville',
                        'address' => '1090 East 75th St',
                        'zip' => '60565',
                        'phones' => [],
                        'phone' => '16308573756',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1340
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_4(): void
    {
        $this->createMakes('HONDA')
            ->createStates('WV', 'TN', 'NY')
            ->createTimeZones('26003', '37040', '14203')
            ->sendPdfFile('acv_transportation_4')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2837066',
                    'pickup_date' => '05/06/2021',
                    'delivery_date' => '05/08/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '5FNYF6H51HB040783',
                            'year' => '2017',
                            'make' => 'Honda',
                            'model' => 'Pilot EX-L',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Wheeling Automotive Group',
                        'state' => 'WV',
                        'city' => 'Wheeling',
                        'address' => '1 National Road',
                        'zip' => '26003',
                        'phones' => [],
                        'phone' => '13042427313',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Wyatt Johnson Automotive Group Inc',
                        'state' => 'TN',
                        'city' => 'Clarksville',
                        'address' => '2425 Wilma Rudolph Boulevard',
                        'zip' => '37040',
                        'phones' => [],
                        'phone' => '19316484300',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 820
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_5(): void
    {
        $this->createMakes('FORD')
            ->createStates('OH', 'SD', 'NY')
            ->createTimeZones('45434', '57104', '14203')
            ->sendPdfFile('acv_transportation_5')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2838557',
                    'pickup_date' => '05/05/2021',
                    'delivery_date' => '05/07/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1FT7W2BT2HEE69489',
                            'year' => '2017',
                            'make' => 'Ford',
                            'model' => 'F250SD XLT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'GERMAIN FORD OF BEAVERCREEK',
                        'state' => 'OH',
                        'city' => 'Dayton',
                        'address' => '2356 HELLER DR',
                        'zip' => '45434',
                        'phones' => null,
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Larson Auto World Inc',
                        'state' => 'SD',
                        'city' => 'Sioux Falls',
                        'address' => '2700 W 7th St',
                        'zip' => '57104',
                        'phones' => [],
                        'phone' => '16053341820',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1050
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_6(): void
    {
        $this->createMakes('FORD')
            ->createStates('SD', 'OH', 'NY')
            ->createTimeZones('57580', '44601', '14203')
            ->sendPdfFile('acv_transportation_6')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2842789',
                    'pickup_date' => '05/11/2021',
                    'delivery_date' => '05/14/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1FT8W3B61GEA91524',
                            'year' => '2016',
                            'make' => 'Ford',
                            'model' => 'F350SD Lariat',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Frontier Motors Automotive Group, Inc',
                        'state' => 'SD',
                        'city' => 'Winner',
                        'address' => '31406 US HWY 18',
                        'zip' => '57580',
                        'phones' => [],
                        'phone' => '16058421880',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Alliance Auto Group LLC',
                        'state' => 'OH',
                        'city' => 'Alliance',
                        'address' => '2010 W State St',
                        'zip' => '44601',
                        'phones' => null,
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Kyle Sippel',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1990
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_7(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('SC', 'CO', 'NY')
            ->createTimeZones('29505', '80110', '14203')
            ->sendPdfFile('acv_transportation_7')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2843770',
                    'pickup_date' => '05/15/2021',
                    'delivery_date' => '05/19/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '000003C154N124579',
                            'year' => '1963',
                            'make' => 'Chevrolet',
                            'model' => 'C10',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Let\'s Go Auto LLC',
                        'state' => 'SC',
                        'city' => 'Florence',
                        'address' => '1920 Pamplico Hwy',
                        'zip' => '29505',
                        'phones' => [],
                        'phone' => '17169498258',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Momentum Auto, LLC',
                        'state' => 'CO',
                        'city' => 'Englewood',
                        'address' => '4695 South Broadway',
                        'zip' => '80110',
                        'phones' => null,
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 2160
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_8(): void
    {
        $this->createMakes('FORD')
            ->createStates('MI', 'OH', 'NY')
            ->createTimeZones('49120', '45638', '14203')
            ->sendPdfFile('acv_transportation_8')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2845039',
                    'pickup_date' => '05/11/2021',
                    'delivery_date' => '05/12/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1FAHP3M28CL385215',
                            'year' => '2012',
                            'make' => 'Ford',
                            'model' => 'Focus SEL',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'TIM TYLER MOTORS INC',
                        'state' => 'MI',
                        'city' => 'Niles',
                        'address' => '1810 S 11TH ST (M51)',
                        'zip' => '49120',
                        'phones' => [],
                        'phone' => '12696831710',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Best value Truck Rental LLC',
                        'state' => 'OH',
                        'city' => 'Ironton',
                        'address' => '704 south 3rd street',
                        'zip' => '45638',
                        'phones' => [],
                        'phone' => '17405329991',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Kyle Sippel',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 590
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_9(): void
    {
        $this->createMakes('JEEP')
            ->createStates('TX', 'AR', 'NY')
            ->createTimeZones('75243', '72704', '14203')
            ->sendPdfFile('acv_transportation_9')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2848063',
                    'pickup_date' => '05/12/2021',
                    'delivery_date' => '05/14/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1C4RJEAG8DC549933',
                            'year' => '2013',
                            'make' => 'Jeep',
                            'model' => 'Grand Cherokee Laredo',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Jaguar Land Rover Dallas / Snell Motor Co',
                        'state' => 'TX',
                        'city' => 'Dallas',
                        'address' => '11400 N Central Expressway',
                        'zip' => '75243',
                        'phones' => [],
                        'phone' => '12146914294',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'CAR-MART of Fayetteville',
                        'state' => 'AR',
                        'city' => 'Fayetteville',
                        'address' => '2724 W Martin Luther King Blvd',
                        'zip' => '72704',
                        'phones' => [],
                        'phone' => '14795712277',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 356.16
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_10(): void
    {
        $this->createMakes('CHRYSLER')
            ->createStates('CT', 'KS', 'NY')
            ->createTimeZones('06475', '66614', '14203')
            ->sendPdfFile('acv_transportation_10')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2849625',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/14/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '2C4RC1DGXLR148307',
                            'year' => '2020',
                            'make' => 'Chrysler',
                            'model' => 'Voyager LXi',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Saybrook Ford Inc',
                        'state' => 'CT',
                        'city' => 'Old Saybrook',
                        'address' => '1 Ford Drive',
                        'zip' => '06475',
                        'phones' => [],
                        'phone' => '18602275945',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Ralph Motors LLC DBA Lewis Toyota of Topeka',
                        'state' => 'KS',
                        'city' => 'Topeka',
                        'address' => '2951 SW Fairlawn Rd',
                        'zip' => '66614',
                        'phones' => null,
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1700
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_11(): void
    {
        $this->createMakes('CHRYSLER')
            ->createStates('CT', 'KS', 'NY')
            ->createTimeZones('06475', '66614', '14203')
            ->sendPdfFile('acv_transportation_11')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2849625',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/14/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '2C4RC1DGXLR148307',
                            'year' => '2020',
                            'make' => 'Chrysler',
                            'model' => 'Voyager LXi',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Saybrook Ford Inc',
                        'state' => 'CT',
                        'city' => 'Old Saybrook',
                        'address' => '1 Ford Drive',
                        'zip' => '06475',
                        'phones' => [],
                        'phone' => '18602275945',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Ralph Motors LLC DBA Lewis Toyota of Topeka',
                        'state' => 'KS',
                        'city' => 'Topeka',
                        'address' => '2951 SW Fairlawn Rd',
                        'zip' => '66614',
                        'phones' => null,
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1700
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_12(): void
    {
        $this->createMakes('LEXUS')
            ->createStates('GA', 'CO', 'NY')
            ->createTimeZones('31909', '80033', '14203')
            ->sendPdfFile('acv_transportation_12')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2849696',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/10/2021',
                    'dispatch_instructions' => 'Pickup only from Mon-Fri 8am-6pm. No exceptions.',
                    'vehicles' => [
                        [
                            'vin' => 'JTJHT00W374026591',
                            'year' => '2007',
                            'make' => 'Lexus',
                            'model' => 'LX 470',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Kia Autosport of Columbus',
                        'state' => 'GA',
                        'city' => 'Columbus',
                        'address' => '7041 Whittelesy Blvd',
                        'zip' => '31909',
                        'phones' => [],
                        'phone' => '17066042605',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Elevated Motors LLC',
                        'state' => 'CO',
                        'city' => 'Wheat Ridge',
                        'address' => '9160 w 44th ave',
                        'zip' => '80033',
                        'phones' => [],
                        'phone' => '17205328833',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1085
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_13(): void
    {
        $this->createMakes('BUICK')
            ->createStates('MS', 'AR', 'NY')
            ->createTimeZones('38901', '72704', '14203')
            ->sendPdfFile('acv_transportation_13')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2850304',
                    'pickup_date' => '05/11/2021',
                    'delivery_date' => '05/13/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1G4HP52K144110611',
                            'year' => '2004',
                            'make' => 'Buick',
                            'model' => 'LeSabre Custom',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'kirk Toyota',
                        'state' => 'MS',
                        'city' => 'Grenada',
                        'address' => '237 sw frontage rd',
                        'zip' => '38901',
                        'phones' => [],
                        'phone' => '16622261181',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'CAR-MART of Fayetteville',
                        'state' => 'AR',
                        'city' => 'Fayetteville',
                        'address' => '2724 W Martin Luther King Blvd',
                        'zip' => '72704',
                        'phones' => [],
                        'phone' => '14795712277',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 440.16
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_14(): void
    {
        $this->createMakes('TOYOTA')
            ->createStates('PA', 'MD', 'NY')
            ->createTimeZones('16148', '21401', '14203')
            ->sendPdfFile('acv_transportation_14')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2850849',
                    'pickup_date' => '05/07/2021',
                    'delivery_date' => '05/08/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '5TFCZ5AN4JX141060',
                            'year' => '2018',
                            'make' => 'Toyota',
                            'model' => 'Tacoma TRD Sport',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Taylor of Hermitage II',
                        'state' => 'PA',
                        'city' => 'Hermitage',
                        'address' => '2757 E State St',
                        'zip' => '16148',
                        'phones' => [],
                        'phone' => '14405960275',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Koons Toyota of Annapolis Inc',
                        'state' => 'MD',
                        'city' => 'Annapolis',
                        'address' => '1019 West St',
                        'zip' => '21401',
                        'phones' => [],
                        'phone' => '14102686480',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 470
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_15(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('TX', 'AR', 'NY')
            ->createTimeZones('75402', '72704', '14203')
            ->sendPdfFile('acv_transportation_15')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2851905',
                    'pickup_date' => '05/12/2021',
                    'delivery_date' => '05/14/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1GNKRJKD9DJ239784',
                            'year' => '2013',
                            'make' => 'Chevrolet',
                            'model' => 'Traverse LT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Britain Chevrolet Inc',
                        'state' => 'TX',
                        'city' => 'Greenville',
                        'address' => '4495 Interstate 30 FR',
                        'zip' => '75402',
                        'phones' => [],
                        'phone' => '19037805450',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'CAR-MART of Fayetteville',
                        'state' => 'AR',
                        'city' => 'Fayetteville',
                        'address' => '2724 W Martin Luther King Blvd',
                        'zip' => '72704',
                        'phones' => [],
                        'phone' => '14795712277',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 356.16
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_16(): void
    {
        $this->createMakes('RAM')
            ->createStates('VA', 'OH', 'NY')
            ->createTimeZones('22701', '44119', '14203')
            ->sendPdfFile('acv_transportation_16')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2852277',
                    'pickup_date' => '05/07/2021',
                    'delivery_date' => '05/08/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '3C63R3KL6FG586114',
                            'year' => '2015',
                            'make' => 'Ram',
                            'model' => '3500 Laramie Longhorn',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'W.L.S. Motors, Inc',
                        'state' => 'VA',
                        'city' => 'Culpeper',
                        'address' => '11030 James Monroe Highway',
                        'zip' => '22701',
                        'phones' => [],
                        'phone' => '15408780399',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'All Out Diesels, LLC',
                        'state' => 'OH',
                        'city' => 'Cleveland',
                        'address' => '18222 lanken ave',
                        'zip' => '44119',
                        'phones' => [],
                        'phone' => '14408151794',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => null,
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 590
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_17(): void
    {
        $this->createMakes('DODGE')
            ->createStates('OK', 'TX', 'NY')
            ->createTimeZones('73114', '79701', '14203')
            ->sendPdfFile('acv_transportation_17')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2852364',
                    'pickup_date' => '05/11/2021',
                    'delivery_date' => '05/15/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1D3HB18T79S751297',
                            'year' => '2009',
                            'make' => 'Dodge',
                            'model' => 'Ram 1500 Laramie',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'G1 West - Bob Howard Dodge, Inc',
                        'state' => 'OK',
                        'city' => 'Oklahoma City',
                        'address' => '13250 Boradway Ext',
                        'zip' => '73114',
                        'phones' => [],
                        'phone' => '14052450859',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'LATINO AUTO SALES INC',
                        'state' => 'TX',
                        'city' => 'Midland',
                        'address' => '1610 GARDEN CITY HWY',
                        'zip' => '79701',
                        'phones' => [],
                        'phone' => '14326869488',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 760
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_18(): void
    {
        $this->createMakes('GMC')
            ->createStates('NJ', 'NC', 'NY')
            ->createTimeZones('07027', '27215', '14203')
            ->sendPdfFile('acv_transportation_18')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2853391',
                    'pickup_date' => '05/07/2021',
                    'delivery_date' => '05/08/2021',
                    'dispatch_instructions' => 'Monday - Saturday 9- 5pm. Please call main office 908-789-0555 to arrange pick up.',
                    'vehicles' => [
                        [
                            'vin' => '1GKKRRKD3EJ156221',
                            'year' => '2014',
                            'make' => 'GMC',
                            'model' => 'Acadia SLT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Marano and Sons Auto Sale',
                        'state' => 'NJ',
                        'city' => 'Garwood',
                        'address' => '150 South Ave',
                        'zip' => '07027',
                        'phones' => [],
                        'phone' => '19082960445',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'RUSH AUTO SALES',
                        'state' => 'NC',
                        'city' => 'Burlington',
                        'address' => '2318 MAPLE AVE',
                        'zip' => '27215',
                        'phones' => [],
                        'phone' => '13363438562',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Dan Montgomery',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 700
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_19(): void
    {
        $this->createMakes('JEEP')
            ->createStates('OH', 'IL', 'NY')
            ->createTimeZones('44408', '61350', '14203')
            ->sendPdfFile('acv_transportation_19')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2853750',
                    'pickup_date' => '05/07/2021',
                    'delivery_date' => '05/08/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1J4AA5D14AL208118',
                            'year' => '2010',
                            'make' => 'Jeep',
                            'model' => 'Wrangler Sahara',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Columbiana Chrysler Jeep Dodge',
                        'state' => 'OH',
                        'city' => 'Columbiana',
                        'address' => '100 commerce circle',
                        'zip' => '44408',
                        'phones' => [],
                        'phone' => '13304824415',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Kenn Motors. IBT # 2991-0730',
                        'state' => 'IL',
                        'city' => 'Ottawa',
                        'address' => '1516 COLUMBUS ST',
                        'zip' => '61350',
                        'phones' => [],
                        'phone' => '18154346264',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 600
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_20(): void
    {
        $this->createMakes('DODGE')
            ->createStates('OH', 'MO', 'NY')
            ->createTimeZones('44319', '64012', '14203')
            ->sendPdfFile('acv_transportation_20')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2854097',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/12/2021',
                    'dispatch_instructions' => 'Pickup Monday - Friday 8a-5p only.',
                    'vehicles' => [
                        [
                            'vin' => '1D7RV1GT1AS222041',
                            'year' => '2010',
                            'make' => 'Dodge',
                            'model' => 'Ram 1500 SLT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'VanDevere Inc',
                        'state' => 'OH',
                        'city' => 'Akron',
                        'address' => '1490 Vernon Odom Blvd',
                        'zip' => '44319',
                        'phones' => [],
                        'phone' => '13308673010',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'C and S Sales',
                        'state' => 'MO',
                        'city' => 'Belton',
                        'address' => '305 E North St',
                        'zip' => '64012',
                        'phones' => [],
                        'phone' => '19139158132',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1140
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_21(): void
    {
        $this->createMakes('LEXUS')
            ->createStates('MI', 'AR', 'NY')
            ->createTimeZones('48036', '72712', '14203')
            ->sendPdfFile('acv_transportation_21')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2854736',
                    'pickup_date' => '05/12/2021',
                    'delivery_date' => '05/13/2021',
                    'dispatch_instructions' => 'The drop off location is gated, Make sure to call 810-650-1449 before delivery.',
                    'vehicles' => [
                        [
                            'vin' => 'JTJBARBZ4H2122802',
                            'year' => '2017',
                            'make' => 'Lexus',
                            'model' => 'NX 200t Base',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Jepson Car Co. LLC',
                        'state' => 'MI',
                        'city' => 'Clinton Township',
                        'address' => '1313 Gratiot',
                        'zip' => '48036',
                        'phones' => [],
                        'phone' => '18106501449',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Crain Kia of Bentonville',
                        'state' => 'AR',
                        'city' => 'Bentonville',
                        'address' => '2901 Moberly Lane',
                        'zip' => '72712',
                        'phones' => [],
                        'phone' => '14796017565',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => null,
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 980
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_22(): void
    {
        $this->createMakes('MERCEDES-BENZ')
            ->createStates('TN', 'IL', 'NY')
            ->createTimeZones('38128', '60445', '14203')
            ->sendPdfFile('acv_transportation_22')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2856758',
                    'pickup_date' => '05/07/2021',
                    'delivery_date' => '05/08/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => 'WDDKJ7DB2CF153352',
                            'year' => '2012',
                            'make' => 'Mercedes-Benz',
                            'model' => 'E Class E550',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'City Auto Sales, LLC (Memphis)',
                        'state' => 'TN',
                        'city' => 'Memphis',
                        'address' => '4932 Elmore Rd',
                        'zip' => '38128',
                        'phones' => [],
                        'phone' => '16157134900',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Adam Auto Group Inc',
                        'state' => 'IL',
                        'city' => 'Crestwood',
                        'address' => '13901 Cicero Ave',
                        'zip' => '60445',
                        'phones' => [],
                        'phone' => '17089727914',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Tamara',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 545.16
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_23(): void
    {
        $this->createMakes('DODGE')
            ->createStates('RI', 'IN', 'NY')
            ->createTimeZones('02919', '47421', '14203')
            ->sendPdfFile('acv_transportation_23')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2857850',
                    'pickup_date' => '05/13/2021',
                    'delivery_date' => '05/16/2021',
                    'dispatch_instructions' => 'Call ahead to make an appointment and confirm details. John 401-524-2928 Walter 954-662-3739 Pick up location is 1674 Hartford Ave, Johnston, RI Pick up hours are 9 am-5 pm Monday-Friday. Behind Enterprise is a storage lot. Please park and enter the service center with the release and ask John for the keys. MUST HAVE RELEASE TO PICK UP VEHICLE.',
                    'vehicles' => [
                        [
                            'vin' => '3D7MX38C77G760869',
                            'year' => '2007',
                            'make' => 'Dodge',
                            'model' => 'Ram 3500 Laramie',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Tasca Automotive Group, Route 6 Two, Inc',
                        'state' => 'RI',
                        'city' => 'Johnston',
                        'address' => '1660 - 1670 Hartford Ave',
                        'zip' => '02919',
                        'phones' => [],
                        'phone' => '19546623739',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'B & B Cars Inc',
                        'state' => 'IN',
                        'city' => 'Bedford',
                        'address' => '2025 16th Street',
                        'zip' => '47421',
                        'phones' => [],
                        'phone' => '18122753800',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1550
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_24(): void
    {
        $this->createMakes('GMC')
            ->createStates('MA', 'OH', 'NY')
            ->createTimeZones('02540', '43616', '14203')
            ->sendPdfFile('acv_transportation_24')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2857984',
                    'pickup_date' => '05/07/2021',
                    'delivery_date' => '05/10/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1GKKVRKD8GJ150526',
                            'year' => '2016',
                            'make' => 'GMC',
                            'model' => 'Acadia SLT1',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'O’Hara Motors',
                        'state' => 'MA',
                        'city' => 'Falmouth',
                        'address' => '50 spring bars road',
                        'zip' => '02540',
                        'phones' => [],
                        'phone' => '15084579300',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'J. Duran, Inc',
                        'state' => 'OH',
                        'city' => 'Oregon',
                        'address' => '2251 Woodville Road',
                        'zip' => '43616',
                        'phones' => [],
                        'phone' => '14192906542',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 940
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_25(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('MI', 'MN', 'NY')
            ->createTimeZones('48234', '55320', '14203')
            ->sendPdfFile('acv_transportation_25')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2859347',
                    'pickup_date' => '05/07/2021',
                    'delivery_date' => '05/10/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '3GCPKSE78DG212883',
                            'year' => '2013',
                            'make' => 'Chevrolet',
                            'model' => 'Silverado 1500 LT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'legacy motors',
                        'state' => 'MI',
                        'city' => 'Detroit',
                        'address' => '6000 E 8 MILE RD',
                        'zip' => '48234',
                        'phones' => null,
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'JP Motors Inc',
                        'state' => 'MN',
                        'city' => 'Clearwater',
                        'address' => '8198 179TH ST NE',
                        'zip' => '55320',
                        'phones' => [],
                        'phone' => '13205586659',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Mac Backer',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 940
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_26(): void
    {
        $this->createMakes('FORD')
            ->createStates('OH', 'GA', 'NY')
            ->createTimeZones('45011', '30126', '14203')
            ->sendPdfFile('acv_transportation_26')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2861961',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/10/2021',
                    'dispatch_instructions' => 'If you need help, call Spencer at 678-910- 7258 CALL AHEAD FOR EVERYTHING.',
                    'vehicles' => [
                        [
                            'vin' => '1FTSW21P95ED01097',
                            'year' => '2005',
                            'make' => 'Ford',
                            'model' => 'F250SD Lariat',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Rose Automotive Group',
                        'state' => 'OH',
                        'city' => 'Hamilton',
                        'address' => '110 North Erie Hwy',
                        'zip' => '45011',
                        'phones' => [],
                        'phone' => '15138637878',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Cars for All Inc',
                        'state' => 'GA',
                        'city' => 'Mableton',
                        'address' => '1218 old powder springs rd',
                        'zip' => '30126',
                        'phones' => [],
                        'phone' => '14048608571',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 710
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_27(): void
    {
        $this->createMakes('FORD')
            ->createStates('AR', 'TX', 'NY')
            ->createTimeZones('72704', '77077', '14203')
            ->sendPdfFile('acv_transportation_27')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2862753',
                    'pickup_date' => '05/11/2021',
                    'delivery_date' => '05/12/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1FMCU9D75AKB95432',
                            'year' => '2010',
                            'make' => 'Ford',
                            'model' => 'Escape XLT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Crain Volkswagen of Fayetteville',
                        'state' => 'AR',
                        'city' => 'Fayetteville',
                        'address' => '2011 West Foxglove Drive',
                        'zip' => '72704',
                        'phones' => [],
                        'phone' => '14796955904',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Colton Goodwin',
                        'state' => 'TX',
                        'city' => 'Houston',
                        'address' => '11777 South Lake Drive',
                        'zip' => '77077',
                        'phones' => [],
                        'phone' => '11234567890',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Dan Montgomery',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 700
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_28(): void
    {
        $this->createMakes('JEEP')
            ->createStates('MI', 'AR', 'NY')
            ->createTimeZones('48091', '72712', '14203')
            ->sendPdfFile('acv_transportation_28')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2863443',
                    'pickup_date' => '05/12/2021',
                    'delivery_date' => '05/13/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1C4RJFAG0JC113319',
                            'year' => '2018',
                            'make' => 'Jeep',
                            'model' => 'Grand Cherokee Laredo',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Ryan Auto Sales Inc',
                        'state' => 'MI',
                        'city' => 'Warren',
                        'address' => '4203 East 8 Mile Road',
                        'zip' => '48091',
                        'phones' => [],
                        'phone' => '15864275332',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Crain Kia of Bentonville',
                        'state' => 'AR',
                        'city' => 'Bentonville',
                        'address' => '2901 Moberly Lane',
                        'zip' => '72712',
                        'phones' => [],
                        'phone' => '14796017565',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1000
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_29(): void
    {
        $this->createMakes('JEEP')
            ->createStates('WV', 'AL', 'NY')
            ->createTimeZones('25053', '35758', '14203')
            ->sendPdfFile('acv_transportation_29')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2864115',
                    'pickup_date' => '05/13/2021',
                    'delivery_date' => '05/14/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1J4PN5GK4BW509071',
                            'year' => '2011',
                            'make' => 'Jeep',
                            'model' => 'Liberty Limited',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Stephens Auto Center',
                        'state' => 'WV',
                        'city' => 'Danville',
                        'address' => '104 STEPHENS DR',
                        'zip' => '25053',
                        'phones' => [],
                        'phone' => '13044692901',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Rowe Motors LLC',
                        'state' => 'AL',
                        'city' => 'Madison',
                        'address' => '193 Production Ave',
                        'zip' => '35758',
                        'phones' => [],
                        'phone' => '12564174448',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1140
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_30(): void
    {
        $this->createMakes('FORD')
            ->createStates('MD', 'TX', 'NY')
            ->createTimeZones('21704', '78653', '14203')
            ->sendPdfFile('acv_transportation_30')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2864388',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/10/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1FTEW1E89HKE25256',
                            'year' => '2017',
                            'make' => 'Ford',
                            'model' => 'F150 XL',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Hi Lo Auto Sales of Maryland, Inc',
                        'state' => 'MD',
                        'city' => 'Frederick',
                        'address' => '5806 Urbana Pike',
                        'zip' => '21704',
                        'phones' => [],
                        'phone' => '12405200010',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Riata Ford',
                        'state' => 'TX',
                        'city' => 'Manor',
                        'address' => '10507 Highway 290 East',
                        'zip' => '78653',
                        'phones' => [],
                        'phone' => '15127990232',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1760
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_31(): void
    {
        $this->createMakes('CHRYSLER')
            ->createStates('MI', 'LA', 'NY')
            ->createTimeZones('48141', '70072', '14203')
            ->sendPdfFile('acv_transportation_31')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2864959',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/10/2021',
                    'dispatch_instructions' => 'For pickup please call Sam 3139141000.',
                    'vehicles' => [
                        [
                            'vin' => '1C3CCBBB3CN119669',
                            'year' => '2012',
                            'make' => 'Chrysler',
                            'model' => '200 Touring',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Metro Auto Broker LLC',
                        'state' => 'MI',
                        'city' => 'Inkster',
                        'address' => '29030 Michigan ave',
                        'zip' => '48141',
                        'phones' => [],
                        'phone' => '13138877777',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'New Orleans Style Auto',
                        'state' => 'LA',
                        'city' => 'Marrero',
                        'address' => '6223 West Bank Expressway',
                        'zip' => '70072',
                        'phones' => [],
                        'phone' => '15043775226',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1025
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_32(): void
    {
        $this->createMakes('JEEP')
            ->createStates('AL', 'LA', 'NY')
            ->createTimeZones('36606', '71241', '14203')
            ->sendPdfFile('acv_transportation_32')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2865873',
                    'pickup_date' => '05/11/2021',
                    'delivery_date' => '05/12/2021',
                    'dispatch_instructions' => '****Must call 318-368-3066 before delivery****.',
                    'vehicles' => [
                        [
                            'vin' => '1C4NJDEB2FD183424',
                            'year' => '2015',
                            'make' => 'Jeep',
                            'model' => 'Compass Latitude',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'McCrea Auto Sales LLC',
                        'state' => 'AL',
                        'city' => 'Mobile',
                        'address' => '2018 Halls Mill Rd',
                        'zip' => '36606',
                        'phones' => [],
                        'phone' => '12513829823',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Lakeview Auto Sales LLC',
                        'state' => 'LA',
                        'city' => 'Famerville',
                        'address' => '1044 South Main Street',
                        'zip' => '71241',
                        'phones' => [],
                        'phone' => '13183683066',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 490
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_33(): void
    {
        $this->createMakes('DODGE')
            ->createStates('PA', 'NJ', 'NY')
            ->createTimeZones('16441', '08037', '14203')
            ->sendPdfFile('acv_transportation_33')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2866217',
                    'pickup_date' => '05/11/2021',
                    'delivery_date' => '05/13/2021',
                    'dispatch_instructions' => 'aftermarket suspension and tires please ask for pictures.',
                    'vehicles' => [
                        [
                            'vin' => '3D7KS29C77G715476',
                            'year' => '2007',
                            'make' => 'Dodge',
                            'model' => 'Ram 2500 SLT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Humes Chrysler Jeep Dodge Ram, Inc',
                        'state' => 'PA',
                        'city' => 'Waterford',
                        'address' => '1010 Route 19',
                        'zip' => '16441',
                        'phones' => [],
                        'phone' => '18147961776',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Starrz Auto, LLC',
                        'state' => 'NJ',
                        'city' => 'Hammonton',
                        'address' => '520 Whitehorse Pike',
                        'zip' => '08037',
                        'phones' => [],
                        'phone' => '16097045709',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 840
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_34(): void
    {
        $this->createMakes('NISSAN')
            ->createStates('OH', 'WI', 'NY')
            ->createTimeZones('44089', '53227', '14203')
            ->sendPdfFile('acv_transportation_34')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2866904',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/11/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1N6AD0FV8GN723133',
                            'year' => '2016',
                            'make' => 'Nissan',
                            'model' => 'Frontier SV',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Patrick O\'Brien Jr. Chevrolet IV, Inc',
                        'state' => 'OH',
                        'city' => 'Vermilion',
                        'address' => '2315 State Road',
                        'zip' => '44089',
                        'phones' => [],
                        'phone' => '14409673144',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Selig Leasing Co. Inc',
                        'state' => 'WI',
                        'city' => 'Milwaukee',
                        'address' => '2510 s.108th',
                        'zip' => '53227',
                        'phones' => [],
                        'phone' => '14143272100',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 670
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_35(): void
    {
        $this->createMakes('FORD')
            ->createStates('VA', 'OH', 'NY')
            ->createTimeZones('22801', '44203', '14203')
            ->sendPdfFile('acv_transportation_35')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2867272',
                    'pickup_date' => '05/07/2021',
                    'delivery_date' => '05/08/2021',
                    'dispatch_instructions' => 'Pickup during normal business hours. Please call ahead.',
                    'vehicles' => [
                        [
                            'vin' => '1FT7W2BT7DEA47177',
                            'year' => '2013',
                            'make' => 'Ford',
                            'model' => 'F250SD Lariat',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Harrisonburg Auto Outlet, Inc',
                        'state' => 'VA',
                        'city' => 'Harrisonburg',
                        'address' => '3040 South Main Street',
                        'zip' => '22801',
                        'phones' => [],
                        'phone' => '15406070964',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Ah ride in pride',
                        'state' => 'OH',
                        'city' => 'Barberton',
                        'address' => '22 w wolf ave',
                        'zip' => '44203',
                        'phones' => [],
                        'phone' => '13303888036',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 520
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_36(): void
    {
        $this->createMakes('INFINITI')
            ->createStates('MA', 'IN', 'NY')
            ->createTimeZones('01089', '46241', '14203')
            ->sendPdfFile('acv_transportation_36')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2867648',
                    'pickup_date' => '05/15/2021',
                    'delivery_date' => '05/17/2021',
                    'dispatch_instructions' => 'Attn Transporter: if dropping off vehicle at night, leave keys in mailbox & park in lot.',
                    'vehicles' => [
                        [
                            'vin' => 'JN8AZ2NE2F9085216',
                            'year' => '2015',
                            'make' => 'INFINITI',
                            'model' => 'QX80',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Union Street Auto Sales',
                        'state' => 'MA',
                        'city' => 'West Springfield',
                        'address' => '697 Union Street',
                        'zip' => '01089',
                        'phones' => [],
                        'phone' => '14132857619',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Cyclone One LLC',
                        'state' => 'IN',
                        'city' => 'Indianapolis',
                        'address' => '3636 W Washington St',
                        'zip' => '46241',
                        'phones' => [],
                        'phone' => '31763612',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Adam LoBello',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1240
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_37(): void
    {
        $this->createMakes('FORD')
            ->createStates('SD', 'KY', 'NY')
            ->createTimeZones('57107', '41094', '14203')
            ->sendPdfFile('acv_transportation_37')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2868277',
                    'pickup_date' => '05/07/2021',
                    'delivery_date' => '05/08/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1FM5K8DH1GGA95924',
                            'year' => '2016',
                            'make' => 'Ford',
                            'model' => 'Explorer XLT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Autoland Inc',
                        'state' => 'SD',
                        'city' => 'Sioux Falls',
                        'address' => '2500 North Maple Lane',
                        'zip' => '57107',
                        'phones' => [],
                        'phone' => '16053393366',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Right Time Automotive LLC',
                        'state' => 'KY',
                        'city' => 'Walton',
                        'address' => '10975 dixie hwy',
                        'zip' => '41094',
                        'phones' => [],
                        'phone' => '18595341014',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 850
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_38(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('NE', 'TX', 'NY')
            ->createTimeZones('68008', '78634', '14203')
            ->sendPdfFile('acv_transportation_38')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2868431',
                    'pickup_date' => '05/10/2021',
                    'delivery_date' => '05/12/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1GC3K0C89EF113507',
                            'year' => '2014',
                            'make' => 'Chevrolet',
                            'model' => 'Silverado 3500 LT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Sid Dillon Chevrolet Blair, Inc',
                        'state' => 'NE',
                        'city' => 'Blair',
                        'address' => '2261 S HWY 30',
                        'zip' => '68008',
                        'phones' => [],
                        'phone' => '14024264121',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Platinum Auto Group LLC',
                        'state' => 'TX',
                        'city' => 'Hutto',
                        'address' => '740 County Rd. 138',
                        'zip' => '78634',
                        'phones' => [],
                        'phone' => '15129899398',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1090
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_39(): void
    {
        $this->createMakes('FORD')
            ->createStates('PA', 'MO', 'NY')
            ->createTimeZones('15210', '63143', '14203')
            ->sendPdfFile('acv_transportation_39')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2869692',
                    'pickup_date' => '05/12/2021',
                    'delivery_date' => '05/15/2021',
                    'dispatch_instructions' => 'PRIMARY PICKUP LOCATION. Call Anthony Coll @ (412) 805-9332 prior to pickup to confirm.',
                    'vehicles' => [
                        [
                            'vin' => '1FMEU74E79UA30333',
                            'year' => '2009',
                            'make' => 'Ford',
                            'model' => 'Explorer Eddie Bauer',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Premier Automotive Group',
                        'state' => 'PA',
                        'city' => 'Pittsburgh',
                        'address' => '2135 Brownsville Road',
                        'zip' => '15210',
                        'phones' => [],
                        'phone' => '14126682122',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Vogue Motor Co. Inc',
                        'state' => 'MO',
                        'city' => 'Saint Louis',
                        'address' => '7125 Manchester Ave',
                        'zip' => '63143',
                        'phones' => [],
                        'phone' => '13147762122',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 890
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_40(): void
    {
        $this->createMakes('FORD')
            ->createStates('RI', 'MS', 'NY')
            ->createTimeZones('02818', '38930', '14203')
            ->sendPdfFile('acv_transportation_40')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2870336',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/18/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1FTFW1EV6AFB55590',
                            'year' => '2010',
                            'make' => 'Ford',
                            'model' => 'F150 Platinum',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'International Sports Car Co Inc',
                        'state' => 'RI',
                        'city' => 'East Greenwich',
                        'address' => '4657 Post Rd',
                        'zip' => '02818',
                        'phones' => [],
                        'phone' => '14018855355',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Wiltshire Auto Sales LLC',
                        'state' => 'MS',
                        'city' => 'Greenwood',
                        'address' => '921 Hwy 82 West',
                        'zip' => '38930',
                        'phones' => [],
                        'phone' => '16624550050',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'John Todd',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1840
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_41(): void
    {
        $this->createMakes('MERCEDES-BENZ')
            ->createStates('PA', 'OH', 'NY')
            ->createTimeZones('16801', '44102', '14203')
            ->sendPdfFile('acv_transportation_41')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2871070',
                    'pickup_date' => '05/11/2021',
                    'delivery_date' => '05/12/2021',
                    'dispatch_instructions' => 'During Regular Business Hours Monday- Friday.',
                    'vehicles' => [
                        [
                            'vin' => '4JGBB86E78A360761',
                            'year' => '2008',
                            'make' => 'Mercedes-Benz',
                            'model' => 'M Class ML350',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Ciocca Enterprises Inc',
                        'state' => 'PA',
                        'city' => 'State College',
                        'address' => '127 Leisure Lane',
                        'zip' => '16801',
                        'phones' => null,
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Jonnies llc',
                        'state' => 'OH',
                        'city' => 'Cleveland',
                        'address' => '6409 clark ave',
                        'zip' => '44102',
                        'phones' => [],
                        'phone' => '12167018526',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => null,
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 440
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_42(): void
    {
        $this->createMakes('FORD')
            ->createStates('MI', 'MO', 'NY')
            ->createTimeZones('48161', '63143', '14203')
            ->sendPdfFile('acv_transportation_42')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2871501',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/15/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1FTZR15E67PA84527',
                            'year' => '2007',
                            'make' => 'Ford',
                            'model' => 'Ranger',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Groulx Buick GMC',
                        'state' => 'MI',
                        'city' => 'Monroe',
                        'address' => '15435 Dixie Highway',
                        'zip' => '48161',
                        'phones' => [],
                        'phone' => '17342413704',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Vogue Motor Co. Inc',
                        'state' => 'MO',
                        'city' => 'Saint Louis',
                        'address' => '7125 Manchester Ave',
                        'zip' => '63143',
                        'phones' => [],
                        'phone' => '13147762122',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Dan Montgomery',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 600
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_43(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('PA', 'OH', 'NY')
            ->createTimeZones('18504', '44460', '14203')
            ->sendPdfFile('acv_transportation_43')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2871610',
                    'pickup_date' => '05/10/2021',
                    'delivery_date' => '05/11/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1G1PC5SH1C7171706',
                            'year' => '2012',
                            'make' => 'Chevrolet',
                            'model' => 'Cruze LS',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'First Solutions Auto Sales, LLC',
                        'state' => 'PA',
                        'city' => 'Scranton',
                        'address' => '535 North Keyser Avenue, Suite B',
                        'zip' => '18504',
                        'phones' => [],
                        'phone' => '14844471841',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Z-MAC MOTORS LLC',
                        'state' => 'OH',
                        'city' => 'Salem',
                        'address' => '920 W STATE STREET',
                        'zip' => '44460',
                        'phones' => null,
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 510
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_44(): void
    {
        $this->createMakes('FORD')
            ->createStates('IL', 'TX', 'NY')
            ->createTimeZones('61561', '78217', '14203')
            ->sendPdfFile('acv_transportation_44')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2871771',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/10/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1FT7W2BT9LEE54450',
                            'year' => '2020',
                            'make' => 'Ford',
                            'model' => 'F250SD Lariat',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'ROANOKE FORD',
                        'state' => 'IL',
                        'city' => 'Roanoke',
                        'address' => '217 W HUSSEMAN',
                        'zip' => '61561',
                        'phones' => [],
                        'phone' => '13099232141',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'American Auto Brokers',
                        'state' => 'TX',
                        'city' => 'San Antonio',
                        'address' => '10750 Iota Drive',
                        'zip' => '78217',
                        'phones' => [],
                        'phone' => '12109467800',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1240
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_45(): void
    {
        $this->createMakes('TOYOTA')
            ->createStates('MS', 'MO', 'NY')
            ->createTimeZones('38901', '65807', '14203')
            ->sendPdfFile('acv_transportation_45')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2872772',
                    'pickup_date' => '05/11/2021',
                    'delivery_date' => '05/13/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '5TDJKRFH1FS147451',
                            'year' => '2015',
                            'make' => 'Toyota',
                            'model' => 'Highlander XLE',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'kirk Toyota',
                        'state' => 'MS',
                        'city' => 'Grenada',
                        'address' => '237 sw frontage rd',
                        'zip' => '38901',
                        'phones' => [],
                        'phone' => '16622261181',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'John Youngblood Motors',
                        'state' => 'MO',
                        'city' => 'Springfield',
                        'address' => '3525 S Campbell Ave',
                        'zip' => '65807',
                        'phones' => [],
                        'phone' => '14175519111',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 540
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_46(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('LA', 'IN', 'NY')
            ->createTimeZones('71111', '46241', '14203')
            ->sendPdfFile('acv_transportation_46')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2872899',
                    'pickup_date' => '05/10/2021',
                    'delivery_date' => '05/11/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '3GCEK23389G149251',
                            'year' => '2009',
                            'make' => 'Chevrolet',
                            'model' => 'Silverado 1500 LT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Morgan Buick GMC Bossier City, Inc',
                        'state' => 'LA',
                        'city' => 'Bossier City',
                        'address' => '2295 Autoplex Drive',
                        'zip' => '71111',
                        'phones' => [],
                        'phone' => '13185177170',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Heartland Auto Sales',
                        'state' => 'IN',
                        'city' => 'Indianapolis',
                        'address' => '5630 West Washington Street',
                        'zip' => '46241',
                        'phones' => [],
                        'phone' => '13172198960',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 800
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_47(): void
    {
        $this->createMakes('SUBARU')
            ->createStates('MD', 'NC', 'NY')
            ->createTimeZones('20601', '28269', '14203')
            ->sendPdfFile('acv_transportation_47')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2872905',
                    'pickup_date' => '05/07/2021',
                    'delivery_date' => '05/08/2021',
                    'dispatch_instructions' => 'Vehicles MUST be picked up within 7 days of auction. 9-8 M-F. 9-7 Saturday. See John or Jay in Used Cars.',
                    'vehicles' => [
                        [
                            'vin' => '4S4BP62C487347294',
                            'year' => '2008',
                            'make' => 'Subaru',
                            'model' => 'Outback Limited',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'WALDORF HONDA',
                        'state' => 'MD',
                        'city' => 'Waldorf',
                        'address' => '2450 Crain Highway',
                        'zip' => '20601',
                        'phones' => [],
                        'phone' => '13018438700',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Duckworth Automotive',
                        'state' => 'NC',
                        'city' => 'Charlotte',
                        'address' => '4428 Statesville Rd',
                        'zip' => '28269',
                        'phones' => [],
                        'phone' => '17047289225',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 480
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_48(): void
    {
        $this->createMakes('SATURN')
            ->createStates('TX', 'AR', 'NY')
            ->createTimeZones('77065', '72756', '14203')
            ->sendPdfFile('acv_transportation_48')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2873918',
                    'pickup_date' => '05/11/2021',
                    'delivery_date' => '05/12/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1G8ZV57777F134742',
                            'year' => '2007',
                            'make' => 'Saturn',
                            'model' => 'Aura XR',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Joe Myers Toyota',
                        'state' => 'TX',
                        'city' => 'Houston',
                        'address' => '19010 NW FRWY',
                        'zip' => '77065',
                        'phones' => [],
                        'phone' => '12812514040',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Car Solutions 4 U LLC',
                        'state' => 'AR',
                        'city' => 'Rogers',
                        'address' => '1325 W Walnut',
                        'zip' => '72756',
                        'phones' => [],
                        'phone' => '14176297576',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Dan Montgomery',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 608.16
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_49(): void
    {
        $this->createMakes('TOYOTA')
            ->createStates('NJ', 'KS', 'NY')
            ->createTimeZones('07435', '66614', '14203')
            ->sendPdfFile('acv_transportation_49')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2875190',
                    'pickup_date' => '05/11/2021',
                    'delivery_date' => '05/13/2021',
                    'dispatch_instructions' => 'Call Frank 2 hours ahead of Delivery - (862) 266-6907.',
                    'vehicles' => [
                        [
                            'vin' => '4T1BZ1HK2KU026908',
                            'year' => '2019',
                            'make' => 'Toyota',
                            'model' => 'Camry XLE',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Quality Autos, LLC',
                        'state' => 'NJ',
                        'city' => 'Newfoundland',
                        'address' => '2925 Route 23, Sales Room D2',
                        'zip' => '07435',
                        'phones' => [],
                        'phone' => '18622666907',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Ralph Motors LLC DBA Lewis Toyota of Topeka',
                        'state' => 'KS',
                        'city' => 'Topeka',
                        'address' => '2951 SW Fairlawn Rd',
                        'zip' => '66614',
                        'phones' => null,
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1140
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_50(): void
    {
        $this->createMakes('MAZDA')
            ->createStates('MA', 'MI', 'NY')
            ->createTimeZones('02301', '48423', '14203')
            ->sendPdfFile('acv_transportation_50')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2876675',
                    'pickup_date' => '05/07/2021',
                    'delivery_date' => '05/11/2021',
                    'dispatch_instructions' => 'Please call an hr before delivery.810-394- 2722. Do not search business address. Deliver vehicles to address listed.',
                    'vehicles' => [
                        [
                            'vin' => 'JM3KE4CY7F0463868',
                            'year' => '2015',
                            'make' => 'Mazda',
                            'model' => 'CX-5 Touring',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Liberty Acquisition Corp',
                        'state' => 'MA',
                        'city' => 'Brockton',
                        'address' => '122 Liberty St',
                        'zip' => '02301',
                        'phones' => [],
                        'phone' => '15085879040',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Nealis Advertising, LLC',
                        'state' => 'MI',
                        'city' => 'Davison',
                        'address' => '1022 Sturbridge Ln',
                        'zip' => '48423',
                        'phones' => [],
                        'phone' => '18104458800',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 818.16
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_51(): void
    {
        $this->createMakes('JEEP')
            ->createStates('NY', 'KS', 'NY')
            ->createTimeZones('10523', '66614', '14203')
            ->sendPdfFile('acv_transportation_51')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2877552',
                    'pickup_date' => '05/11/2021',
                    'delivery_date' => '05/17/2021',
                    'dispatch_instructions' => 'Drivers MUST wear a mask, as per NY State Regulations Pickup Hours Monday - Friday from 8am to 4pm. Please call 24 hours in advance so the vehicle can be pulled out for Transporters. Call (914) -220-0588 and ask Mike Willard.',
                    'vehicles' => [
                        [
                            'vin' => '3C4NJDBB9KT712117',
                            'year' => '2019',
                            'make' => 'Jeep',
                            'model' => 'Compass Latitude',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Pepe Motors Corp',
                        'state' => 'NY',
                        'city' => 'Elmsford',
                        'address' => '2269 Saw Mill River Road',
                        'zip' => '10523',
                        'phones' => [],
                        'phone' => '11111111111',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Ralph Motors LLC DBA Lewis Toyota of Topeka',
                        'state' => 'KS',
                        'city' => 'Topeka',
                        'address' => '2951 SW Fairlawn Rd',
                        'zip' => '66614',
                        'phones' => null,
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1160
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_52(): void
    {
        $this->createMakes('TOYOTA')
            ->createStates('PA', 'MD', 'NY')
            ->createTimeZones('16509', '20601', '14203')
            ->sendPdfFile('acv_transportation_52')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2877618',
                    'pickup_date' => '05/07/2021',
                    'delivery_date' => '05/08/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '4T1BK36B86U077676',
                            'year' => '2006',
                            'make' => 'Toyota',
                            'model' => 'Avalon Limited',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Gary Miller Chrysler Jeep Inc',
                        'state' => 'PA',
                        'city' => 'Erie',
                        'address' => '5746 Peach St',
                        'zip' => '16509',
                        'phones' => [],
                        'phone' => '18142178767',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'True Concept Auto Outlet, LLC',
                        'state' => 'MD',
                        'city' => 'Waldorf',
                        'address' => '11750 Oak Manor Drive',
                        'zip' => '20601',
                        'phones' => [],
                        'phone' => '13019325690',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 419.16
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_53(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('PA', 'OH', 'NY')
            ->createTimeZones('18509', '44319', '14203')
            ->sendPdfFile('acv_transportation_53')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2877736',
                    'pickup_date' => '05/10/2021',
                    'delivery_date' => '05/11/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1GNSKCKC5FR128693',
                            'year' => '2015',
                            'make' => 'Chevrolet',
                            'model' => 'Tahoe LTZ',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Tom Hesser Chevrolet, Inc',
                        'state' => 'PA',
                        'city' => 'Scranton',
                        'address' => '1001 North Washington Ave',
                        'zip' => '18509',
                        'phones' => [],
                        'phone' => '15703431221',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'VanDevere Inc',
                        'state' => 'OH',
                        'city' => 'Akron',
                        'address' => '1490 Vernon Odom Blvd',
                        'zip' => '44319',
                        'phones' => [],
                        'phone' => '13308673010',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 470
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_54(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('TX', 'OK', 'NY')
            ->createTimeZones('78751', '74110', '14203')
            ->sendPdfFile('acv_transportation_54')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2877914',
                    'pickup_date' => '05/10/2021',
                    'delivery_date' => '05/11/2021',
                    'dispatch_instructions' => 'TRANSPORTERS: PLEASE CALL JENN BEFORE PICKING UP TO CONFIRM THE LOCATION OF THE VEHICLE. 254-730-3010 IF NO ANSWER TEXT VEHICLE INFO WITH ESTIMATED PICK UP TIME. PICK UP HOURS 9-5 MON-FRI.',
                    'vehicles' => [
                        [
                            'vin' => '1G1ZD5EB7AF221959',
                            'year' => '2010',
                            'make' => 'Chevrolet',
                            'model' => 'Malibu LT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Leif Johnson Ford',
                        'state' => 'TX',
                        'city' => 'Austin',
                        'address' => '501 East Koenig Lane',
                        'zip' => '78751',
                        'phones' => [],
                        'phone' => '15128011807',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Hiz n Herz',
                        'state' => 'OK',
                        'city' => 'Tulsa',
                        'address' => '2619 E Apache St',
                        'zip' => '74110',
                        'phones' => [],
                        'phone' => '19188306301',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 509.15
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_55(): void
    {
        $this->createMakes('GMC')
            ->createStates('IL', 'TX', 'NY')
            ->createTimeZones('62702', '78217', '14203')
            ->sendPdfFile('acv_transportation_55')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2878015',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/10/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1GTP9EEL4LZ252225',
                            'year' => '2020',
                            'make' => 'GMC',
                            'model' => 'Sierra 1500 AT4',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Landmark Ford Trucks Inc',
                        'state' => 'IL',
                        'city' => 'Springfield',
                        'address' => '3401 E Clear Ave',
                        'zip' => '62702',
                        'phones' => [],
                        'phone' => '12174336937',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'American Auto Brokers',
                        'state' => 'TX',
                        'city' => 'San Antonio',
                        'address' => '10750 Iota Drive',
                        'zip' => '78217',
                        'phones' => [],
                        'phone' => '12109467800',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1050
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_56(): void
    {
        $this->createMakes('JEEP')
            ->createStates('PA', 'MI', 'NY')
            ->createTimeZones('17368', '48036', '14203')
            ->sendPdfFile('acv_transportation_56')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2878369',
                    'pickup_date' => '05/11/2021',
                    'delivery_date' => '05/14/2021',
                    'dispatch_instructions' => 'PICK UP ONLY MONDAY thru FRIDAY 9am to 5pm Call John, to negotiate 717-368-1343.',
                    'vehicles' => [
                        [
                            'vin' => '1C4RJFCT4EC146355',
                            'year' => '2014',
                            'make' => 'Jeep',
                            'model' => 'Grand Cherokee Overland',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Susquehanna Chrysler Dodge Jeep RAM',
                        'state' => 'PA',
                        'city' => 'Wrightsville',
                        'address' => '950 Hellam St',
                        'zip' => '17368',
                        'phones' => [],
                        'phone' => '17173681343',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Vinson Motors',
                        'state' => 'MI',
                        'city' => 'Clinton Township',
                        'address' => '44450 North Gratiot Ave',
                        'zip' => '48036',
                        'phones' => [],
                        'phone' => '15867832110',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 620
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_57(): void
    {
        $this->createMakes('BUICK')
            ->createStates('KY', 'MI', 'NY')
            ->createTimeZones('41701', '48066', '14203')
            ->sendPdfFile('acv_transportation_57')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2878479',
                    'pickup_date' => '05/13/2021',
                    'delivery_date' => '05/14/2021',
                    'dispatch_instructions' => 'ALL VEHICLES MUST BE DELIVERED TO THIS ADDRESS.',
                    'vehicles' => [
                        [
                            'vin' => '5GAKVBKD0GJ144255',
                            'year' => '2016',
                            'make' => 'Buick',
                            'model' => 'Enclave Leather',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Tim Short Chrysler',
                        'state' => 'KY',
                        'city' => 'Hazard',
                        'address' => '270 Fitz Gilbert Road',
                        'zip' => '41701',
                        'phones' => [],
                        'phone' => '16064380339',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'A & B Motors',
                        'state' => 'MI',
                        'city' => 'Roseville',
                        'address' => '29999 Groesbeck Highway',
                        'zip' => '48066',
                        'phones' => [],
                        'phone' => '15867099626',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 600
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_58(): void
    {
        $this->createMakes('FORD')
            ->createStates('TX', 'WV', 'NY')
            ->createTimeZones('78222', '25560', '14203')
            ->sendPdfFile('acv_transportation_58')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2879097',
                    'pickup_date' => '05/12/2021',
                    'delivery_date' => '05/14/2021',
                    'dispatch_instructions' => '***All Buyers Must wait 24 Hours prior to picking up their Vehicle*** Vehicles can be picked up Monday through Friday from 9:00 a.m. until 5:00 p.m from: Abel’s Towing 3131 SE Loop 410 San Antonio, Texas 78222 (210) 727-0136 No Saturday pick up available.',
                    'vehicles' => [
                        [
                            'vin' => '1FT7W2BT1FEA39322',
                            'year' => '2015',
                            'make' => 'Ford',
                            'model' => 'F250SD XL',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Fairway Ford',
                        'state' => 'TX',
                        'city' => 'San Antonio',
                        'address' => '3131 SE Loop 410',
                        'zip' => '78222',
                        'phones' => [],
                        'phone' => '12103806094',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Suntrust Pre-Owned Auto, LLC',
                        'state' => 'WV',
                        'city' => 'Saint Albans',
                        'address' => '2754 Winfield Road',
                        'zip' => '25560',
                        'phones' => null,
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1180
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_59(): void
    {
        $this->createMakes('FORD')
            ->createStates('AL', 'TX', 'NY')
            ->createTimeZones('35401', '78218', '14203')
            ->sendPdfFile('acv_transportation_59')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2880139',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/15/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1FTFW1EF3BFA17886',
                            'year' => '2011',
                            'make' => 'Ford',
                            'model' => 'F150 Lariat',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Wyatt Brothers Wholesale LLC',
                        'state' => 'AL',
                        'city' => 'Tuscaloosa',
                        'address' => '3010 skyland blvd e',
                        'zip' => '35401',
                        'phones' => [],
                        'phone' => '12059236343',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Five Star Auto Group',
                        'state' => 'TX',
                        'city' => 'San Antonio',
                        'address' => '1776 Austin highway',
                        'zip' => '78218',
                        'phones' => [],
                        'phone' => '12104618474',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Adam LoBello',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1030
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_60(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('GA', 'PA', 'NY')
            ->createTimeZones('30549', '16428', '14203')
            ->sendPdfFile('acv_transportation_60')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2880699',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/15/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1GCHK23D76F160079',
                            'year' => '2006',
                            'make' => 'Chevrolet',
                            'model' => 'Silverado 2500 LT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Platinum Motorsports, LLC',
                        'state' => 'GA',
                        'city' => 'Jefferson',
                        'address' => '841 Lee Street',
                        'zip' => '30549',
                        'phones' => [],
                        'phone' => '16786296975',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'NFI Empire',
                        'state' => 'PA',
                        'city' => 'North East',
                        'address' => '10120 West Main Street',
                        'zip' => '16428',
                        'phones' => [],
                        'phone' => '18147464213',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Sara Payne',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 850
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_61(): void
    {
        $this->createMakes('BUICK')
            ->createStates('VA', 'CO', 'NY')
            ->createTimeZones('24501', '80923', '14203')
            ->sendPdfFile('acv_transportation_61')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2881437',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/17/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '5GAKVCKD6HJ354527',
                            'year' => '2017',
                            'make' => 'Buick',
                            'model' => 'Enclave Premium',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Clarion Automotive Inc',
                        'state' => 'VA',
                        'city' => 'Lynchburg',
                        'address' => '2625 Lakeside Dr',
                        'zip' => '24501',
                        'phones' => [],
                        'phone' => '14344557061',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Bob Penkhus Mazda at Powers',
                        'state' => 'CO',
                        'city' => 'Colorado Springs',
                        'address' => '7455 Test Drive',
                        'zip' => '80923',
                        'phones' => [],
                        'phone' => '17197859666',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Adam LoBello',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1670
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_62(): void
    {
        $this->createMakes('KIA')
            ->createStates('MA', 'IN', 'NY')
            ->createTimeZones('01905', '46514', '14203')
            ->sendPdfFile('acv_transportation_62')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2882192',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/10/2021',
                    'dispatch_instructions' => 'Pick up vehicles from Pride Chevrolet.',
                    'vehicles' => [
                        [
                            'vin' => '5XYKTCA65EG497407',
                            'year' => '2014',
                            'make' => 'Kia',
                            'model' => 'Sorento LX',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Pride Auto Group Kia',
                        'state' => 'MA',
                        'city' => 'Lynn',
                        'address' => '715 Lynnway',
                        'zip' => '01905',
                        'phones' => [],
                        'phone' => '17814695250',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Howard\'s Auto Sales, LLC',
                        'state' => 'IN',
                        'city' => 'Elkhart',
                        'address' => '1705 West Bristol Street',
                        'zip' => '46514',
                        'phones' => [],
                        'phone' => '15743332220',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 818.16
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_63(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('DE', 'MI', 'NY')
            ->createTimeZones('19720', '48867', '14203')
            ->sendPdfFile('acv_transportation_63')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2882200',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/15/2021',
                    'dispatch_instructions' => 'When drivers come up to the gate let them know delivery is for "Whip Flip". Drop-offs/Pickups ONLY Mon-Fri 8am-4pm. YOU WILL BE TURNED AWAY IF YOU COME OUTSIDE THESE HOURS.',
                    'vehicles' => [
                        [
                            'vin' => '3GNEK13TX3G263664',
                            'year' => '2003',
                            'make' => 'Chevrolet',
                            'model' => 'Avalanche 1500',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'WhipFlip Inc',
                        'state' => 'DE',
                        'city' => 'New Castle',
                        'address' => '170 Pigeon Point Rd',
                        'zip' => '19720',
                        'phones' => [],
                        'phone' => '13022873665',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Michigan Auto Plaza, LLC',
                        'state' => 'MI',
                        'city' => 'Owosso',
                        'address' => '1205 West Main Street, Bldg. 1205',
                        'zip' => '48867',
                        'phones' => [],
                        'phone' => '18009368151',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 910
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_64(): void
    {
        $this->createMakes('JEEP')
            ->createStates('PA', 'MI', 'NY')
            ->createTimeZones('16001', '49441', '14203')
            ->sendPdfFile('acv_transportation_64')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2882526',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/16/2021',
                    'dispatch_instructions' => 'Open M-F 7-3:30.',
                    'vehicles' => [
                        [
                            'vin' => '1C4PJMAS3HW570845',
                            'year' => '2017',
                            'make' => 'Jeep',
                            'model' => 'Cherokee Sport',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Fuller Auto Sales',
                        'state' => 'PA',
                        'city' => 'Butler',
                        'address' => '125 1/2 Pillow St',
                        'zip' => '16001',
                        'phones' => [],
                        'phone' => '17248225550',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => '2221 Henry Street LLC',
                        'state' => 'MI',
                        'city' => 'Muskegon',
                        'address' => '2221 Henry st',
                        'zip' => '49441',
                        'phones' => [],
                        'phone' => '12317552116',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Adam LoBello',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 670
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_65(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('CT', 'IN', 'NY')
            ->createTimeZones('06051', '46113', '14203')
            ->sendPdfFile('acv_transportation_65')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2882738',
                    'pickup_date' => '05/15/2021',
                    'delivery_date' => '05/17/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '3GCEK23M09G140665',
                            'year' => '2009',
                            'make' => 'Chevrolet',
                            'model' => 'Silverado 1500 LT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Gallagher Buick GMC Inc',
                        'state' => 'CT',
                        'city' => 'New Britain',
                        'address' => '325 columbus blvd',
                        'zip' => '06051',
                        'phones' => [],
                        'phone' => '18606822545',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Mr. Care Auto LLC',
                        'state' => 'IN',
                        'city' => 'Camby',
                        'address' => '13999 N. State Road 67',
                        'zip' => '46113',
                        'phones' => [],
                        'phone' => '13178343172',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Adam LoBello',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1180
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_66(): void
    {
        $this->createMakes('DODGE')
            ->createStates('GA', 'PA', 'NY')
            ->createTimeZones('30014', '16428', '14203')
            ->sendPdfFile('acv_transportation_66')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2884057',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/15/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1B7MF3652SS139915',
                            'year' => '1995',
                            'make' => 'Dodge',
                            'model' => 'Ram 3500 LT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Trust Capital Automotive Inc',
                        'state' => 'GA',
                        'city' => 'Covington',
                        'address' => '10111 US-278',
                        'zip' => '30014',
                        'phones' => [],
                        'phone' => '14704441199',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'NFI Empire',
                        'state' => 'PA',
                        'city' => 'North East',
                        'address' => '10120 West Main Street',
                        'zip' => '16428',
                        'phones' => [],
                        'phone' => '18147464213',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Sara Payne',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 900
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_67(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('NV', 'CA', 'NY')
            ->createTimeZones('89101', '94583', '14203')
            ->sendPdfFile('acv_transportation_67')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2885192',
                    'pickup_date' => '05/11/2021',
                    'delivery_date' => '05/11/2021',
                    'dispatch_instructions' => 'WAIT 48HRS FOR CAR TO BE MOVED TO HOLDING LOT DO NOT CONTACT DEALERSHIP Monday - Saturday 8a - 6p Call Rich 24 hours before pickup 928-300-5538.',
                    'vehicles' => [
                        [
                            'vin' => '1GC1KTEY1KF216681',
                            'year' => '2019',
                            'make' => 'Chevrolet',
                            'model' => 'Silverado 2500 LTZ',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Fairway Chevrolet',
                        'state' => 'NV',
                        'city' => 'Las Vegas',
                        'address' => '1066 S Main St',
                        'zip' => '89101',
                        'phones' => null,
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'RHINO MOTORS',
                        'state' => 'CA',
                        'city' => 'San Ramon',
                        'address' => '2092 Omega Rd, Ste E1',
                        'zip' => '94583',
                        'phones' => [],
                        'phone' => '15102054142',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 620
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_68(): void
    {
        $this->createMakes('FORD')
            ->createStates('KY', 'MI', 'NY')
            ->createTimeZones('41222', '49441', '14203')
            ->sendPdfFile('acv_transportation_68')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2885432',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/15/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1FM5K8GT7GGC84187',
                            'year' => '2016',
                            'make' => 'Ford',
                            'model' => 'Explorer Sport',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Auto Brokers of Paintsville',
                        'state' => 'KY',
                        'city' => 'Hagerhill',
                        'address' => '10 McCarty Branch Rd',
                        'zip' => '41222',
                        'phones' => [],
                        'phone' => '16066384110',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Betten Friendly Motor Co',
                        'state' => 'MI',
                        'city' => 'Muskegon',
                        'address' => '3146 Henry St',
                        'zip' => '49441',
                        'phones' => [],
                        'phone' => '16162986997',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 730
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_69(): void
    {
        $this->createMakes('FORD')
            ->createStates('GA', 'PA', 'NY')
            ->createTimeZones('30549', '18964', '14203')
            ->sendPdfFile('acv_transportation_69')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2885593',
                    'pickup_date' => '05/10/2021',
                    'delivery_date' => '05/15/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1FT7W2BT2HED03022',
                            'year' => '2017',
                            'make' => 'Ford',
                            'model' => 'F250SD XLT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Platinum Motorsports, LLC',
                        'state' => 'GA',
                        'city' => 'Jefferson',
                        'address' => '841 Lee Street',
                        'zip' => '30549',
                        'phones' => [],
                        'phone' => '16786296975',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Ciocca Ford of Souderton',
                        'state' => 'PA',
                        'city' => 'Souderton',
                        'address' => '3470 Bethlehem Pike',
                        'zip' => '18964',
                        'phones' => [],
                        'phone' => '19082689727',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 800
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_70(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('TX', 'LA', 'NY')
            ->createTimeZones('77074', '70001', '14203')
            ->sendPdfFile('acv_transportation_70')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2887539',
                    'pickup_date' => '05/12/2021',
                    'delivery_date' => '05/13/2021',
                    'dispatch_instructions' => 'Please contact Sherilynn at (713) 437-7315 or Maurilio at (832) 287-7666 to make an appointment for pick up. Pick up hours are Monday - Friday 9a-6p. No exceptions. Pick ups cannot be made without an appointment.',
                    'vehicles' => [
                        [
                            'vin' => '1G1JB5SH0H4134955',
                            'year' => '2017',
                            'make' => 'Chevrolet',
                            'model' => 'Sonic LS',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Platinum Investors of Texas, LLC',
                        'state' => 'TX',
                        'city' => 'Houston',
                        'address' => '9811 Southwest Freeway',
                        'zip' => '77074',
                        'phones' => [],
                        'phone' => '18325269409',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Car City Autoplex',
                        'state' => 'LA',
                        'city' => 'Metairie',
                        'address' => '4604 W Napoleon Ave',
                        'zip' => '70001',
                        'phones' => [],
                        'phone' => '15043040094',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 400
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_71(): void
    {
        $this->createMakes('FORD')
            ->createStates('KY', 'PA', 'NY')
            ->createTimeZones('40509', '15401', '14203')
            ->sendPdfFile('acv_transportation_71')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2887624',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/18/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1FT7X2BT4BEB72860',
                            'year' => '2011',
                            'make' => 'Ford',
                            'model' => 'F250SD XLT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Digital Auto LLC',
                        'state' => 'KY',
                        'city' => 'Lexington',
                        'address' => '2143 WILDERNESS CT',
                        'zip' => '40509',
                        'phones' => [],
                        'phone' => '18593689937',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Alpha Xtreme AutoPlex LLC',
                        'state' => 'PA',
                        'city' => 'Uniontown',
                        'address' => '783 McClellandtown Rd',
                        'zip' => '15401',
                        'phones' => [],
                        'phone' => '17245504900',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 600
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_72(): void
    {
        $this->createMakes('JEEP')
            ->createStates('RI', 'IN', 'NY')
            ->createTimeZones('02919', '47421', '14203')
            ->sendPdfFile('acv_transportation_72')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2888117',
                    'pickup_date' => '05/13/2021',
                    'delivery_date' => '05/16/2021',
                    'dispatch_instructions' => 'Call ahead to make an appointment and confirm details. John 401-524-2928 Walter 954-662-3739 Pick up location is 1674 Hartford Ave, Johnston, RI Pick up hours are 9 am-5 pm Monday-Friday. Behind Enterprise is a storage lot. Please park and enter the service center with the release and ask John for the keys. MUST HAVE RELEASE TO PICK UP VEHICLE.',
                    'vehicles' => [
                        [
                            'vin' => '1C4BJWEG2EL322983',
                            'year' => '2014',
                            'make' => 'Jeep',
                            'model' => 'Wrangler Unlimited Sahara',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Tasca Automotive Group, Route 6 Two, Inc',
                        'state' => 'RI',
                        'city' => 'Johnston',
                        'address' => '1660 - 1670 Hartford Ave',
                        'zip' => '02919',
                        'phones' => [],
                        'phone' => '19546623739',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'B & B Cars Inc',
                        'state' => 'IN',
                        'city' => 'Bedford',
                        'address' => '2025 16th Street',
                        'zip' => '47421',
                        'phones' => [],
                        'phone' => '18122753800',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 900
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_73(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('DE', 'MO', 'NY')
            ->createTimeZones('19808', '63077', '14203')
            ->sendPdfFile('acv_transportation_73')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2888477',
                    'pickup_date' => '05/15/2021',
                    'delivery_date' => '05/19/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '2GCEK19T611270844',
                            'year' => '2001',
                            'make' => 'Chevrolet',
                            'model' => 'Silverado 1500 LS',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Future Ford Sales',
                        'state' => 'DE',
                        'city' => 'Wilmington',
                        'address' => '4001 Kirkwood Hwy',
                        'zip' => '19808',
                        'phones' => [],
                        'phone' => '13023193951',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Jim\'s Auto Sales',
                        'state' => 'MO',
                        'city' => 'Saint Clair',
                        'address' => '50 North Main Street',
                        'zip' => '63077',
                        'phones' => [],
                        'phone' => '16366291809',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1240
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_74(): void
    {
        $this->createMakes('CADILLAC')
            ->createStates('MI', 'IL', 'NY')
            ->createTimeZones('48357', '62702', '14203')
            ->sendPdfFile('acv_transportation_74')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2888524',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/15/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1G6AX5SX1J0134043',
                            'year' => '2018',
                            'make' => 'Cadillac',
                            'model' => 'CTS Luxury',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Lafontaine Cadillac Buick GMC, Inc',
                        'state' => 'MI',
                        'city' => 'Highland',
                        'address' => '4000 West Highland Road',
                        'zip' => '48357',
                        'phones' => [],
                        'phone' => '12487141045',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Landmark Ford Trucks Inc',
                        'state' => 'IL',
                        'city' => 'Springfield',
                        'address' => '3401 E Clear Ave',
                        'zip' => '62702',
                        'phones' => [],
                        'phone' => '12174336937',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Dan Montgomery',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 550
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_75(): void
    {
        $this->createMakes('FORD')
            ->createStates('OH', 'TX', 'NY')
            ->createTimeZones('43112', '78217', '14203')
            ->sendPdfFile('acv_transportation_75')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2889158',
                    'pickup_date' => '05/15/2021',
                    'delivery_date' => '05/20/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1FT8W3BTXKEC96418',
                            'year' => '2019',
                            'make' => 'Ford',
                            'model' => 'F350SD King Ranch',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Lancaster CGMC, LLC',
                        'state' => 'OH',
                        'city' => 'Carroll',
                        'address' => '3733 Claypool Street Northwest',
                        'zip' => '43112',
                        'phones' => [],
                        'phone' => '17406549590',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'American Auto Brokers',
                        'state' => 'TX',
                        'city' => 'San Antonio',
                        'address' => '10750 Iota Drive',
                        'zip' => '78217',
                        'phones' => [],
                        'phone' => '12109467800',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1590
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_76(): void
    {
        $this->createMakes('NISSAN')
            ->createStates('PA', 'IN', 'NY')
            ->createTimeZones('17345', '46240', '14203')
            ->sendPdfFile('acv_transportation_76')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2889347',
                    'pickup_date' => '05/13/2021',
                    'delivery_date' => '05/14/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1N6AD06W15C441594',
                            'year' => '2005',
                            'make' => 'Nissan',
                            'model' => 'Frontier SE',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Thornton Chevrolet Inc',
                        'state' => 'PA',
                        'city' => 'Manchester',
                        'address' => '160 Glen Dr',
                        'zip' => '17345',
                        'phones' => [],
                        'phone' => '17172668800',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'a&z group',
                        'state' => 'IN',
                        'city' => 'Indianapolis',
                        'address' => '7270 n keystone ave',
                        'zip' => '46240',
                        'phones' => [],
                        'phone' => '13176521325',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Dan Montgomery',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 780
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_77(): void
    {
        $this->createMakes('HONDA')
            ->createStates('TX', 'OK', 'NY')
            ->createTimeZones('77840', '74112', '14203')
            ->sendPdfFile('acv_transportation_77')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2889650',
                    'pickup_date' => '05/10/2021',
                    'delivery_date' => '05/14/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '2HGFG12857H577290',
                            'year' => '2007',
                            'make' => 'Honda',
                            'model' => 'Civic EX',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Allen Honda',
                        'state' => 'TX',
                        'city' => 'College Station',
                        'address' => '2450 earl rudder freeway south',
                        'zip' => '77840',
                        'phones' => [],
                        'phone' => '19796962424',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Azteka Motor',
                        'state' => 'OK',
                        'city' => 'Tulsa',
                        'address' => '1920 South Memorial Drive',
                        'zip' => '74112',
                        'phones' => [],
                        'phone' => '19188285656',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 461.16
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_78(): void
    {
        $this->createMakes('FORD')
            ->createStates('MO', 'MO', 'NY')
            ->createTimeZones('64801', '63841', '14203')
            ->sendPdfFile('acv_transportation_78')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2890092',
                    'pickup_date' => '05/12/2021',
                    'delivery_date' => '05/13/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '2FMDK3GC8BBB04262',
                            'year' => '2011',
                            'make' => 'Ford',
                            'model' => 'Edge SE',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'JD Byrider of Joplin',
                        'state' => 'MO',
                        'city' => 'Joplin',
                        'address' => '3215 East 20th Street',
                        'zip' => '64801',
                        'phones' => [],
                        'phone' => '15737211438',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'BURKE AUTO AND ATV LLC',
                        'state' => 'MO',
                        'city' => 'Dexter',
                        'address' => '404 S State Highway 25 # US',
                        'zip' => '63841',
                        'phones' => [],
                        'phone' => '15733448865',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Joe K',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 450
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_79(): void
    {
        $this->createMakes('FORD')
            ->createStates('TX', 'MS', 'NY')
            ->createTimeZones('78217', '39402', '14203')
            ->sendPdfFile('acv_transportation_79')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2891823',
                    'pickup_date' => '05/12/2021',
                    'delivery_date' => '05/13/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1FT7W2BT7FEC51867',
                            'year' => '2015',
                            'make' => 'Ford',
                            'model' => 'F250SD XLT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'American Auto Brokers',
                        'state' => 'TX',
                        'city' => 'San Antonio',
                        'address' => '10750 Iota Drive',
                        'zip' => '78217',
                        'phones' => [],
                        'phone' => '12109467800',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'DANIELL MOTORS',
                        'state' => 'MS',
                        'city' => 'Hattiesburg',
                        'address' => '1500 BROADWAY DR',
                        'zip' => '39402',
                        'phones' => [],
                        'phone' => '16012090024',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 680
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_80(): void
    {
        $this->createMakes('FORD')
            ->createStates('TX', 'LA', 'NY')
            ->createTimeZones('77074', '70072', '14203')
            ->sendPdfFile('acv_transportation_80')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2892155',
                    'pickup_date' => '05/12/2021',
                    'delivery_date' => '05/13/2021',
                    'dispatch_instructions' => 'Call 713-272-1700 with any questions.',
                    'vehicles' => [
                        [
                            'vin' => '1FAHP3FN8AW146329',
                            'year' => '2010',
                            'make' => 'Ford',
                            'model' => 'Focus SE',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Archer VW',
                        'state' => 'TX',
                        'city' => 'Houston',
                        'address' => '10400 Southwest Freeway',
                        'zip' => '77074',
                        'phones' => null,
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'New Orleans Style Auto',
                        'state' => 'LA',
                        'city' => 'Marrero',
                        'address' => '6223 West Bank Expressway',
                        'zip' => '70072',
                        'phones' => [],
                        'phone' => '15043775226',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 400
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_81(): void
    {
        $this->createMakes('FORD')
            ->createStates('AL', 'VA', 'NY')
            ->createTimeZones('35405', '20176', '14203')
            ->sendPdfFile('acv_transportation_81')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2892292',
                    'pickup_date' => '05/12/2021',
                    'delivery_date' => '05/16/2021',
                    'dispatch_instructions' => 'no pick up after 7 no pick up on Sundays.',
                    'vehicles' => [
                        [
                            'vin' => '1FTHF36F4VEC30648',
                            'year' => '1997',
                            'make' => 'Ford',
                            'model' => 'F350 XLT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Carlock of Tuscaloosa',
                        'state' => 'AL',
                        'city' => 'Tuscaloosa',
                        'address' => '550 Skyland Blvd East',
                        'zip' => '35405',
                        'phones' => [],
                        'phone' => '12057904599',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Jerrys Chevrolet Inc',
                        'state' => 'VA',
                        'city' => 'Leesburg',
                        'address' => '18 Fort Evans Road NE',
                        'zip' => '20176',
                        'phones' => [],
                        'phone' => '12404018100',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 900
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_82(): void
    {
        $this->createMakes('AUDI')
            ->createStates('OK', 'IL', 'NY')
            ->createTimeZones('74601', '60559', '14203')
            ->sendPdfFile('acv_transportation_82')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2892514',
                    'pickup_date' => '05/12/2021',
                    'delivery_date' => '05/13/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => 'WA1VXAF78MD010604',
                            'year' => '2021',
                            'make' => 'Audi',
                            'model' => 'Q7 Prestige',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Stuteville Ford Lincoln Of Ponca City',
                        'state' => 'OK',
                        'city' => 'Ponca City',
                        'address' => '2415 N 14th St',
                        'zip' => '74601',
                        'phones' => [],
                        'phone' => '13166448159',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Ultimo Motors East ibt 4149-2056',
                        'state' => 'IL',
                        'city' => 'Westmont',
                        'address' => '150 W Ogden ave',
                        'zip' => '60559',
                        'phones' => [],
                        'phone' => '16306646620',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 770
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_83(): void
    {
        $this->createMakes('HONDA')
            ->createStates('PA', 'OH', 'NY')
            ->createTimeZones('17972', '44142', '14203')
            ->sendPdfFile('acv_transportation_83')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2892521',
                    'pickup_date' => '05/11/2021',
                    'delivery_date' => '05/12/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '2HGFG11828H508253',
                            'year' => '2008',
                            'make' => 'Honda',
                            'model' => 'Civic EX',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Skook Auto Sales',
                        'state' => 'PA',
                        'city' => 'Schuylkill Haven',
                        'address' => '312 Centre Ave',
                        'zip' => '17972',
                        'phones' => [],
                        'phone' => '15705935278',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Metro Cleveland Auto Sales LLC',
                        'state' => 'OH',
                        'city' => 'Brookpark',
                        'address' => '13405 Brookpark Rd',
                        'zip' => '44142',
                        'phones' => [],
                        'phone' => '12164409000',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 410
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_84(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('AL', 'TX', 'NY')
            ->createTimeZones('36264', '78009', '14203')
            ->sendPdfFile('acv_transportation_84')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2892549',
                    'pickup_date' => '05/15/2021',
                    'delivery_date' => '05/17/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => 'KL79MMS23MB018274',
                            'year' => '2021',
                            'make' => 'Chevrolet',
                            'model' => 'Trailblazer LS',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Buster Miles Ford Mercury Inc',
                        'state' => 'AL',
                        'city' => 'Heflin',
                        'address' => '1884 almon St',
                        'zip' => '36264',
                        'phones' => [],
                        'phone' => '19044008674',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'North Park Castroville Motors, LLC',
                        'state' => 'TX',
                        'city' => 'Castroville',
                        'address' => '1955 US Highway 90 E',
                        'zip' => '78009',
                        'phones' => [],
                        'phone' => '8309319200217',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 980
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_85(): void
    {
        $this->createMakes('HONDA')
            ->createStates('TX', 'LA', 'NY')
            ->createTimeZones('77074', '70819', '14203')
            ->sendPdfFile('acv_transportation_85')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2892911',
                    'pickup_date' => '05/12/2021',
                    'delivery_date' => '05/13/2021',
                    'dispatch_instructions' => 'Call 713-272-1700 with any questions.',
                    'vehicles' => [
                        [
                            'vin' => 'JHLRE38789C004095',
                            'year' => '2009',
                            'make' => 'Honda',
                            'model' => 'CR-V EX-L',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Archer VW',
                        'state' => 'TX',
                        'city' => 'Houston',
                        'address' => '10400 Southwest Freeway',
                        'zip' => '77074',
                        'phones' => null,
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Baton Rouge Auto Sales',
                        'state' => 'LA',
                        'city' => 'Baton Rouge',
                        'address' => '14880 Florida Blvd',
                        'zip' => '70819',
                        'phones' => [],
                        'phone' => '12252732100',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 400
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_86(): void
    {
        $this->createMakes('FORD')
            ->createStates('CA', 'CA', 'NY')
            ->createTimeZones('95616', '90605', '14203')
            ->sendPdfFile('acv_transportation_86')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2893197',
                    'pickup_date' => '05/15/2021',
                    'delivery_date' => '05/17/2021',
                    'dispatch_instructions' => 'deliver vehicles.',
                    'vehicles' => [
                        [
                            'vin' => '1FT7W3BT8BEA79378',
                            'year' => '2011',
                            'make' => 'Ford',
                            'model' => 'F350SD XL',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Brocco Motors LLC',
                        'state' => 'CA',
                        'city' => 'Davis',
                        'address' => '1700 olive dr. Suite # c',
                        'zip' => '95616',
                        'phones' => [],
                        'phone' => '19162869224',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Ez budget auto sales',
                        'state' => 'CA',
                        'city' => 'Whittier',
                        'address' => '9636 Barkerville ave',
                        'zip' => '90605',
                        'phones' => [],
                        'phone' => '15623512839',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Yolanda Davis',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 750
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_87(): void
    {
        $this->createMakes('DODGE')
            ->createStates('MO', 'NE', 'NY')
            ->createTimeZones('63122', '68037', '14203')
            ->sendPdfFile('acv_transportation_87')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2894454',
                    'pickup_date' => '05/12/2021',
                    'delivery_date' => '05/15/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '2C4RDGCG3HR631338',
                            'year' => '2017',
                            'make' => 'Dodge',
                            'model' => 'Grand Caravan SXT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Glendale Chrysler Jeep Dodge Ram',
                        'state' => 'MO',
                        'city' => 'Saint Louis',
                        'address' => '10070 Manchester Road',
                        'zip' => '63122',
                        'phones' => [],
                        'phone' => '13149655100',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'COPPLE CHEVROLET-GMC, INC',
                        'state' => 'NE',
                        'city' => 'Louisville',
                        'address' => '306 MAIN STREET',
                        'zip' => '68037',
                        'phones' => [],
                        'phone' => '14027202359',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 515
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_88(): void
    {
        $this->createMakes('KIA')
            ->createStates('NJ', 'OH', 'NY')
            ->createTimeZones('08330', '44319', '14203')
            ->sendPdfFile('acv_transportation_88')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2895246',
                    'pickup_date' => '05/12/2021',
                    'delivery_date' => '05/14/2021',
                    'dispatch_instructions' => 'Pick up between 9 am and 3;30pm Monday through Friday. See Michelle in service for keys.',
                    'vehicles' => [
                        [
                            'vin' => '5XXGM4A72FG414202',
                            'year' => '2015',
                            'make' => 'Kia',
                            'model' => 'Optima LX',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Richardson Enterprises, Inc',
                        'state' => 'NJ',
                        'city' => 'Mays Landing',
                        'address' => '4236 Black Horse Pike',
                        'zip' => '08330',
                        'phones' => [],
                        'phone' => '16094021283',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'VanDevere Inc',
                        'state' => 'OH',
                        'city' => 'Akron',
                        'address' => '1490 Vernon Odom Blvd',
                        'zip' => '44319',
                        'phones' => [],
                        'phone' => '13308673010',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 570
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_89(): void
    {
        $this->createMakes('FIAT')
            ->createStates('RI', 'MS', 'NY')
            ->createTimeZones('02919', '39232', '14203')
            ->sendPdfFile('acv_transportation_89')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2898577',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/18/2021',
                    'dispatch_instructions' => 'Call ahead to make an appointment and confirm details. John 401-524-2928 Walter 954-662-3739 Pick up location is 1674 Hartford Ave, Johnston, RI Pick up hours are 9 am-5 pm Monday-Friday. Behind Enterprise is a storage lot. Please park and enter the service center with the release and ask John for the keys. MUST HAVE RELEASE TO PICK UP VEHICLE.',
                    'vehicles' => [
                        [
                            'vin' => 'JC1NFAEK5H0105078',
                            'year' => '2017',
                            'make' => 'Fiat',
                            'model' => '124 Spider Lusso',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Tasca Automotive Group, Route 6 Two, Inc',
                        'state' => 'RI',
                        'city' => 'Johnston',
                        'address' => '1660 - 1670 Hartford Ave',
                        'zip' => '02919',
                        'phones' => [],
                        'phone' => '14015242928',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Flowood Mac Haik CDJR',
                        'state' => 'MS',
                        'city' => 'Flowood',
                        'address' => '4000 Lakeland Dr',
                        'zip' => '39232',
                        'phones' => [],
                        'phone' => '16015637046',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'John Todd',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1200
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_90(): void
    {
        $this->createMakes('RAM')
            ->createStates('CA', 'CA', 'NY')
            ->createTimeZones('92530', '95824', '14203')
            ->sendPdfFile('acv_transportation_90')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2900860',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/15/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '3C6UR5CL6GG247398',
                            'year' => '2016',
                            'make' => 'Ram',
                            'model' => '2500 Tradesman',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Anderson Chevrolet',
                        'state' => 'CA',
                        'city' => 'Lake Elsinore',
                        'address' => '31201 auto center drive',
                        'zip' => '92530',
                        'phones' => null,
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => '916 Auto Mart',
                        'state' => 'CA',
                        'city' => 'Sacramento',
                        'address' => '5877 Power Inn Road',
                        'zip' => '95824',
                        'phones' => [],
                        'phone' => '19167559695',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => null,
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 545
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_91(): void
    {
        $this->createMakes('BMW')
            ->createStates('KY', 'WI', 'NY')
            ->createTimeZones('40391', '53215', '14203')
            ->sendPdfFile('acv_transportation_91')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2901106',
                    'pickup_date' => '05/12/2021',
                    'delivery_date' => '05/15/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => 'WBAEK73445B325105',
                            'year' => '2005',
                            'make' => 'BMW',
                            'model' => '6-Series 645Ci',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Town & Country Auto Sales',
                        'state' => 'KY',
                        'city' => 'Winchester',
                        'address' => '1010 Bypass Rd',
                        'zip' => '40391',
                        'phones' => [],
                        'phone' => '18593854615',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Arandas Auto Body & Sales',
                        'state' => 'WI',
                        'city' => 'Milwaukee',
                        'address' => '2725 W Hayes Ave',
                        'zip' => '53215',
                        'phones' => [],
                        'phone' => '14143847772',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Dan Montgomery',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 503.16
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_92(): void
    {
        $this->createMakes('GMC')
            ->createStates('KY', 'IN', 'NY')
            ->createTimeZones('41222', '46767', '14203')
            ->sendPdfFile('acv_transportation_92')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2901111',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/15/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1GTR9BED8LZ243348',
                            'year' => '2020',
                            'make' => 'GMC',
                            'model' => 'Sierra 1500 SLE',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Car City Automotive',
                        'state' => 'KY',
                        'city' => 'Hagerhill',
                        'address' => '10 McCarty Branch Rd',
                        'zip' => '41222',
                        'phones' => [],
                        'phone' => '16066384110',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Burnworth-Zollars Ford Chevy',
                        'state' => 'IN',
                        'city' => 'Ligonier',
                        'address' => '309 US-6 West',
                        'zip' => '46767',
                        'phones' => [],
                        'phone' => '12608947176',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 500
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_93(): void
    {
        $this->createMakes('JEEP')
            ->createStates('PA', 'MI', 'NY')
            ->createTimeZones('15236', '49441', '14203')
            ->sendPdfFile('acv_transportation_93')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2902012',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/16/2021',
                    'dispatch_instructions' => 'EIN: 841670451.',
                    'vehicles' => [
                        [
                            'vin' => '1C4RJFBM9FC787240',
                            'year' => '2015',
                            'make' => 'Jeep',
                            'model' => 'Grand Cherokee Limited',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Three Rivers Chrysler Jeep Dodge LLC',
                        'state' => 'PA',
                        'city' => 'Pittsburgh',
                        'address' => '2633 West Liberty Avenue',
                        'zip' => '15236',
                        'phones' => [],
                        'phone' => '14123431200',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Betten Friendly Motor Co',
                        'state' => 'MI',
                        'city' => 'Muskegon',
                        'address' => '3146 Henry St',
                        'zip' => '49441',
                        'phones' => [],
                        'phone' => '12317334401',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Adam LoBello',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 500
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_94(): void
    {
        $this->createMakes('RAM')
            ->createStates('VA', 'CO', 'NY')
            ->createTimeZones('20186', '80219', '14203')
            ->sendPdfFile('acv_transportation_94')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2904604',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/17/2021',
                    'dispatch_instructions' => 'Must pickup within 7 days from purchase. Pick up hours between Monday - Friday 9-5pm. No after hours pickup. See TEDDY for KEYS 540-827-9560.',
                    'vehicles' => [
                        [
                            'vin' => '3C6UR5NL5JG366872',
                            'year' => '2018',
                            'make' => 'Ram',
                            'model' => '2500 Laramie',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Country Chevrolet Inc',
                        'state' => 'VA',
                        'city' => 'Warrenton',
                        'address' => '5457 E Lee Highway',
                        'zip' => '20186',
                        'phones' => [],
                        'phone' => '15404284050',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Truck Kings',
                        'state' => 'CO',
                        'city' => 'Denver',
                        'address' => '444 S Federal Blvd',
                        'zip' => '80219',
                        'phones' => [],
                        'phone' => '13039968822',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Adam LoBello',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1400
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_95(): void
    {
        $this->createMakes('NISSAN')
            ->createStates('MD', 'TN', 'NY')
            ->createTimeZones('21401', '37686', '14203')
            ->sendPdfFile('acv_transportation_95')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2908662',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/15/2021',
                    'dispatch_instructions' => 'Please see Katie Green or Bill Chopra for pick up. Pick up Monday-Friday 10 am-7 pm only.',
                    'vehicles' => [
                        [
                            'vin' => '1N6AA0EC2AN322676',
                            'year' => '2010',
                            'make' => 'Nissan',
                            'model' => 'Titan PRO-4X',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Chopra, LLC',
                        'state' => 'MD',
                        'city' => 'Annapolis',
                        'address' => '2000 West St',
                        'zip' => '21401',
                        'phones' => [],
                        'phone' => '14108970067',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Legg Motor Company',
                        'state' => 'TN',
                        'city' => 'Piney Flats',
                        'address' => '5477 Hwy 11 E',
                        'zip' => '37686',
                        'phones' => [],
                        'phone' => '14233910477',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Dan Montgomery',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 503.16
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_96(): void
    {
        $this->createMakes('FORD')
            ->createStates('WV', 'MI', 'NY')
            ->createTimeZones('25801', '49417', '14203')
            ->sendPdfFile('acv_transportation_96')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2909653',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/15/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1FMCU9J95HUA92628',
                            'year' => '2017',
                            'make' => 'Ford',
                            'model' => 'Escape Titanium',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Sheets Automotive',
                        'state' => 'WV',
                        'city' => 'Beckley',
                        'address' => '250 Auto Plaza Drive',
                        'zip' => '25801',
                        'phones' => [],
                        'phone' => '13042524555',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Preferred Automotive Group',
                        'state' => 'MI',
                        'city' => 'Grand Haven',
                        'address' => '1401 South Beacon Blvd',
                        'zip' => '49417',
                        'phones' => [],
                        'phone' => '16168420600',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 608.16
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_97(): void
    {
        $this->createMakes('KIA')
            ->createStates('MD', 'TN', 'NY')
            ->createTimeZones('21207', '37601', '14203')
            ->sendPdfFile('acv_transportation_97')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2909981',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/15/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => 'KNDMB5C11G6165009',
                            'year' => '2016',
                            'make' => 'Kia',
                            'model' => 'Sedona LX',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Antwerpen Security Nissan',
                        'state' => 'MD',
                        'city' => 'Gwynn Oak',
                        'address' => '1701 Woodlawn Drive PO Box 31790',
                        'zip' => '21207',
                        'phones' => [],
                        'phone' => '14102984400',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'GREYSTOKE CHEVY CAD INC',
                        'state' => 'TN',
                        'city' => 'Johnson City',
                        'address' => '3608 BRISTOL HWY',
                        'zip' => '37601',
                        'phones' => [],
                        'phone' => '14232624200',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Dan Montgomery',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 482.16
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_98(): void
    {
        $this->createMakes('CHRYSLER')
            ->createStates('VA', 'CO', 'NY')
            ->createTimeZones('22801', '80905', '14203')
            ->sendPdfFile('acv_transportation_98')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2910866',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/17/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '2C3CCAKG4EH216574',
                            'year' => '2014',
                            'make' => 'Chrysler',
                            'model' => '300 C',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Dick Myers, Inc',
                        'state' => 'VA',
                        'city' => 'Harrisonburg',
                        'address' => '1711, South Main Street',
                        'zip' => '22801',
                        'phones' => [],
                        'phone' => '15402141151',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Penkhus Motor Company',
                        'state' => 'CO',
                        'city' => 'Colorado Springs',
                        'address' => '1101 Motor City Drive',
                        'zip' => '80905',
                        'phones' => [],
                        'phone' => '17194734100',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Adam LoBello',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 1200
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_99(): void
    {
        $this->createMakes('FORD')
            ->createStates('OK', 'OH', 'NY')
            ->createTimeZones('74012', '44203', '14203')
            ->sendPdfFile('acv_transportation_99')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2912507',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/15/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1FT7W2BT8FEC62201',
                            'year' => '2015',
                            'make' => 'Ford',
                            'model' => 'F250SD Lariat',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Regional Hyundai, LLC',
                        'state' => 'OK',
                        'city' => 'Broken Arrow',
                        'address' => '2380 West Kenosha Street',
                        'zip' => '74012',
                        'phones' => [],
                        'phone' => '19183616859',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Ah ride in pride',
                        'state' => 'OH',
                        'city' => 'Barberton',
                        'address' => '22 w wolf ave',
                        'zip' => '44203',
                        'phones' => [],
                        'phone' => '13303888036',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Adam LoBello',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 900
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_100(): void
    {
        $this->createMakes('CHRYSLER')
            ->createStates('MI', 'MO', 'NY')
            ->createTimeZones('48390', '64055', '14203')
            ->sendPdfFile('acv_transportation_100')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2913481',
                    'pickup_date' => '05/15/2021',
                    'delivery_date' => '05/18/2021',
                    'dispatch_instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1C3EL75R44N340889',
                            'year' => '2004',
                            'make' => 'Chrysler',
                            'model' => 'Sebring GTC',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Lafontaine Subaru',
                        'state' => 'MI',
                        'city' => 'Walled Lake',
                        'address' => '3055 East West Maple Road, Suite A',
                        'zip' => '48390',
                        'phones' => [],
                        'phone' => '12486604553',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Arrowhead Auto Mart, LLC',
                        'state' => 'MO',
                        'city' => 'Independence',
                        'address' => '9630 e us hwy 40',
                        'zip' => '64055',
                        'phones' => [],
                        'phone' => '18165039479',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 725
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_101(): void
    {
        $this->createMakes('HYUNDAI')
            ->createStates('NY', 'IL', 'NY')
            ->createTimeZones('14048', '60073', '14203')
            ->sendPdfFile('acv_transportation_101')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '2914295',
                    'pickup_date' => '05/15/2021',
                    'delivery_date' => '05/17/2021',
                    'dispatch_instructions' => 'take long driveway to the right of DW’s garage all the way to the back. Call Mike at 716-397-8843 at least one hour before pickup to ensure pickup is available.',
                    'vehicles' => [
                        [
                            'vin' => '5XYZH4AG2BG001277',
                            'year' => '2011',
                            'make' => 'Hyundai',
                            'model' => 'Santa Fe SE',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Ken\'s Sales',
                        'state' => 'NY',
                        'city' => 'Dunkirk',
                        'address' => '3534 New Road',
                        'zip' => '14048',
                        'phones' => [],
                        'phone' => '17168636152',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Cedar Lake Auto Exchange Ltd',
                        'state' => 'IL',
                        'city' => 'Round Lake',
                        'address' => '712 West Rollins Rd',
                        'zip' => '60073',
                        'phones' => [],
                        'phone' => '12243562358',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'Russ Q',
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 600
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_102(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('NY', 'VA', 'NY')
            ->createTimeZones('14425', '23451', '14203')
            ->sendPdfFile('acv_transportation_102')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '4183746',
                    'pickup_date' => '01/22/2022',
                    'delivery_date' => '01/25/2022',
                    'dispatch_instructions' => 'DO NOT CONTACT SELLER FOR PICKUP. SOLD UNITS MOVED TO ADDRESS BELOW WITHIN 24HRS OF SALE Hours: 9-5 Monday - Friday Call or Text 2 Hours in advance - Matt Farrell 716-380-9910 Do not go to dealership.',
                    'vehicles' => [
                        [
                            'vin' => '1G1ZD5E15BF209740',
                            'year' => '2011',
                            'make' => 'Chevrolet',
                            'model' => 'Malibu LT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'Boss Towing',
                        'state' => 'NY',
                        'city' => 'Farmington',
                        'address' => '5911 Loomis Rd',
                        'zip' => '14425',
                        'phones' => [],
                        'phone' => '17168806459',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'Autopro Virginia, LLC',
                        'state' => 'VA',
                        'city' => 'Virginia Beach',
                        'address' => '533 Virginia Beach Blvd',
                        'zip' => '23451',
                        'phones' => [],
                        'phone' => '17579378296',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => null,
                        'state' => 'NY',
                        'city' => 'Buffalo',
                        'address' => '640 Ellicott St Suite 321',
                        'zip' => '14203',
                        'phones' => [],
                        'phone' => '18005534070',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                    'payment' => [
                        'total_carrier_amount' => 573.18
                    ],
                ]
            ], true);
    }

}
