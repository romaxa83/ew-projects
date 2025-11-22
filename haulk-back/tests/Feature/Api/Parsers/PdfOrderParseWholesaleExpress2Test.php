<?php

namespace Tests\Feature\Api\Parsers;


class PdfOrderParseWholesaleExpress2Test extends PdfParserHelper
{

    private const FOLDER_NAME = 'WholesaleExpress2';

    protected function getFolderName(): string
    {
        return self::FOLDER_NAME . DIRECTORY_SEPARATOR;
    }

    public function test_it_parsed_1(): void
    {
        $this->createMakes('NISSAN')
            ->createStates('MI', 'IA', 'TN')
            ->createTimeZones('48507', '52732', '37122')
            ->sendPdfFile('wholesale_express_2_1')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '187748',
                    'pickup_date' => '03/24/2020',
                    'delivery_date' => '03/25/2020',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => 'JN1BJ1CRXJW260946',
                            'year' => '2018',
                            'make' => 'NISSAN',
                            'model' => 'ROGUE SPORT AWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM FLINT',
                        'state' => 'MI',
                        'city' => 'FLINT',
                        'address' => '4109 HOLIDAY DR',
                        'zip' => '48507',
                        'phones' => [],
                        'phone' => '18103411600',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'BILLION CLINTON INC',
                        'state' => 'IA',
                        'city' => 'CLINTON',
                        'address' => '2421 LINCOLN WAY',
                        'zip' => '52732',
                        'phones' => [],
                        'phone' => '15632437000',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_2(): void
    {
        $this->createMakes('TOYOTA')
            ->createStates('MO', 'CO', 'TN')
            ->createTimeZones('63134', '80033', '37122')
            ->sendPdfFile('wholesale_express_2_2')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '247947',
                    'pickup_date' => '05/18/2021',
                    'delivery_date' => '05/19/2021',
                    'instructions' => 'handicap vans. call to confirm modifications *HRS ARE M-F 8A-5P ONLY** NO AFTER HOURS DROP** MUST CALL BEFORE ARRIVAL',
                    'vehicles' => [
                        [
                            'vin' => '5TDKZ3DC4LS073281',
                            'year' => '2020',
                            'make' => 'TOYOTA',
                            'model' => 'SIENNA LE 8-PASSENGER FWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'UNITED ACCESS SAINT LOUIS',
                        'state' => 'MO',
                        'city' => 'SAINT LOUIS',
                        'address' => '9389 NATURAL BRIDGE RD',
                        'zip' => '63134',
                        'phones' => [],
                        'phone' => '13149891010',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'UNITED ACCESS WEST DENVER',
                        'state' => 'CO',
                        'city' => 'WHEAT RIDGE',
                        'address' => '9500 W 49TH AVE #C107',
                        'zip' => '80033',
                        'phones' => [],
                        'phone' => '13034679981',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_3(): void
    {
        $this->createMakes('JEEP')
            ->createStates('IL', 'PA', 'TN')
            ->createTimeZones('60050', '16148', '37122')
            ->sendPdfFile('wholesale_express_2_3')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245945',
                    'pickup_date' => '05/05/2021',
                    'delivery_date' => '05/06/2021',
                    'instructions' => 'MUST CALL PICKUP AND DROP OFF LOCATIONS BEFORE ARRIVAL 3 INCH LIFT 35 INCH TIRES CALL IN ADVANCE',
                    'vehicles' => [
                        [
                            'vin' => '1C4HJXEG0LW117349',
                            'year' => '2020',
                            'make' => 'JEEP',
                            'model' => 'WRANGLER UNLIMITED SAHARA 4X4',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'AUTO VILLA OUTLET',
                        'state' => 'IL',
                        'city' => 'MCHENRY',
                        'address' => '3017 IL-120',
                        'zip' => '60050',
                        'phones' => [
                            [
                                'number' => '12245886625'
                            ],
                        ],
                        'phone' => '18153222340',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'BRYCEN VEON',
                        'state' => 'PA',
                        'city' => 'HERMITAGE',
                        'address' => '4583 WHIPPOORWILL DR',
                        'zip' => '16148',
                        'phones' => [],
                        'phone' => '17243018366',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_4(): void
    {
        $this->createMakes('TOYOTA')
            ->createStates('OH', 'VA', 'TN')
            ->createTimeZones('43228', '23434', '37122')
            ->sendPdfFile('wholesale_express_2_4')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245477',
                    'pickup_date' => '05/05/2021',
                    'delivery_date' => '05/06/2021',
                    'instructions' => 'Mon Fri 11 am 7 pm Sat 11 am 5 pm Sun 11 am 4 pm Please call 24 hours in advance..',
                    'vehicles' => [
                        [
                            'vin' => 'JTDZN3EU7GJ043227',
                            'year' => '2016',
                            'make' => 'TOYOTA',
                            'model' => 'PRIUS V 5DR WGN (NATL)',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'AUTO PALACE, INC',
                        'state' => 'OH',
                        'city' => 'COLUMBUS',
                        'address' => '4621 W BROAD ST',
                        'zip' => '43228',
                        'phones' => [],
                        'phone' => '16148516000',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MIKE DUMAN AUTO SALES',
                        'state' => 'VA',
                        'city' => 'SUFFOLK',
                        'address' => '2300 GODWIN BOULEVARD',
                        'zip' => '23434',
                        'phones' => [],
                        'phone' => '17574499692',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_5(): void
    {
        $this->createMakes('DODGE')
            ->createStates('TN', 'IL', 'TN')
            ->createTimeZones('37075', '60014', '37122')
            ->sendPdfFile('wholesale_express_2_5')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245885',
                    'pickup_date' => '05/05/2021',
                    'delivery_date' => '05/06/2021',
                    'instructions' => 'CALL IN ADVANCE',
                    'vehicles' => [
                        [
                            'vin' => '2C3CDXL94GH257399',
                            'year' => '2016',
                            'make' => 'DODGE',
                            'model' => 'CHARGER 4DR SDN SRT HELLCAT RWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'GREEN LIGHT CAR SALES',
                        'state' => 'TN',
                        'city' => 'HENDERSONVILLE',
                        'address' => '416 WEST MAIN ST',
                        'zip' => '37075',
                        'phones' => [],
                        'phone' => '16154310786',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'BRILLIANCE HONDA OF CRYSTAL LAKE',
                        'state' => 'IL',
                        'city' => 'CRYSTAL LAKE',
                        'address' => '680 W TERRA COTTA AVE',
                        'zip' => '60014',
                        'phones' => [],
                        'phone' => '18473660426',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_6(): void
    {
        $this->createMakes('LEXUS')
            ->createStates('TX', 'LA', 'TN')
            ->createTimeZones('75141', '70003', '37122')
            ->sendPdfFile('wholesale_express_2_6')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245652',
                    'pickup_date' => '05/04/2021',
                    'delivery_date' => '05/05/2021',
                    'instructions' => 'If picking up from ADESA DALLAS: Vehicle pickup: Mon. á Fri. 8:30 a.m. á 5:00 p.m',
                    'vehicles' => [
                        [
                            'vin' => '2T2ZK1BA2FC185109',
                            'year' => '2015',
                            'make' => 'LEXUS',
                            'model' => 'RX 350 FWD 4DR',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'ADESA DALLAS',
                        'state' => 'TX',
                        'city' => 'HUTCHINS',
                        'address' => '3501 LANCASTER-HUTCHINS RD',
                        'zip' => '75141',
                        'phones' => [],
                        'phone' => '19722256000',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MATT BOWERS CHEVROLET METAIRIE',
                        'state' => 'LA',
                        'city' => 'METAIRIE',
                        'address' => '8213 AIRLINE DR',
                        'zip' => '70003',
                        'phones' => [],
                        'phone' => '15044666000',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_7(): void
    {
        $this->createMakes('JEEP', 'HONDA')
            ->createStates('NY', 'IL', 'TN')
            ->createTimeZones('10314', '60438', '37122')
            ->sendPdfFile('wholesale_express_2_7')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245750',
                    'pickup_date' => '05/03/2021',
                    'delivery_date' => '05/05/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1C4RJFBG1JC144688',
                            'year' => '2018',
                            'make' => 'JEEP',
                            'model' => 'GRAND CHEROKEE 4X4',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                        [
                            'vin' => '5J6RW2H86JL022803',
                            'year' => '2018',
                            'make' => 'HONDA',
                            'model' => 'CR-V EX-L AWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                        [
                            'vin' => '1C4RJFBG7JC365521',
                            'year' => '2018',
                            'make' => 'JEEP',
                            'model' => 'GRAND CHEROKEE 4X4',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'WE ARE THE CAR GUYS',
                        'state' => 'NY',
                        'city' => 'STATEN ISLAND',
                        'address' => '425-F WILD AVE',
                        'zip' => '10314',
                        'phones' => [],
                        'phone' => '18663114897',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'NAPLETONS RIVER OAKS CHRYSLER JEEP DODGE',
                        'state' => 'IL',
                        'city' => 'LANSING',
                        'address' => '17225 TORRENCE AVE',
                        'zip' => '60438',
                        'phones' => [],
                        'phone' => '18888113308',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_8(): void
    {
        $this->createMakes('CHEVROLET', 'NISSAN')
            ->createStates('MI', 'IA', 'TN')
            ->createTimeZones('48507', '52732', '37122')
            ->sendPdfFile('wholesale_express_2_8')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '187748',
                    'pickup_date' => '03/24/2020',
                    'delivery_date' => '03/25/2020',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1G1ZE5SX0LF029492',
                            'year' => '2020',
                            'make' => 'CHEVROLET',
                            'model' => 'MALIBU 4DR SDN PREMIER',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                        [
                            'vin' => 'JN1BJ1CRXJW260946',
                            'year' => '2018',
                            'make' => 'NISSAN',
                            'model' => 'ROGUE SPORT AWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM FLINT',
                        'state' => 'MI',
                        'city' => 'FLINT',
                        'address' => '4109 HOLIDAY DR',
                        'zip' => '48507',
                        'phones' => [],
                        'phone' => '18103411600',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'BILLION CLINTON INC',
                        'state' => 'IA',
                        'city' => 'CLINTON',
                        'address' => '2421 LINCOLN WAY',
                        'zip' => '52732',
                        'phones' => [],
                        'phone' => '15632437000',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_9(): void
    {
        $this->createMakes('FORD')
            ->createStates('TX', 'TN', 'TN')
            ->createTimeZones('77061', '38017', '37122')
            ->sendPdfFile('wholesale_express_2_9')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '187739',
                    'pickup_date' => '03/23/2020',
                    'delivery_date' => '03/24/2020',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '2FMPK4K90KBB55012',
                            'year' => '2019',
                            'make' => 'FORD',
                            'model' => 'EDGE TITANIUM AWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                        [
                            'vin' => '1FMCU0J9XKUC29968',
                            'year' => '2019',
                            'make' => 'FORD',
                            'model' => 'ESCAPE TITANIUM FWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM TEXAS HOBBY (HOBBY)',
                        'state' => 'TX',
                        'city' => 'HOUSTON',
                        'address' => '8215 KOPMAN DR.',
                        'zip' => '77061',
                        'phones' => [],
                        'phone' => '17136498233',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'LANDERS FORD INC',
                        'state' => 'TN',
                        'city' => 'COLLIERVILLE',
                        'address' => '2082 W POPLAR AVE',
                        'zip' => '38017',
                        'phones' => [],
                        'phone' => '19016268551',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_10(): void
    {
        $this->createMakes('NISSAN')
            ->createStates('IL', 'MN', 'TN')
            ->createTimeZones('61802', '55391', '37122')
            ->sendPdfFile('wholesale_express_2_10')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '187580',
                    'pickup_date' => '03/25/2020',
                    'delivery_date' => '03/26/2020',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => 'JN8AF5MR0ET358959',
                            'year' => '2014',
                            'make' => 'NISSAN',
                            'model' => 'JUKE 5DR WGN CVT S FWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'GENESIS OF URBANA',
                        'state' => 'IL',
                        'city' => 'URBANA',
                        'address' => '1101 NAPLETON WAY',
                        'zip' => '61802',
                        'phones' => [],
                        'phone' => '18887110067',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'AUTO CENTER BARGAIN LOT',
                        'state' => 'MN',
                        'city' => 'WAYZATA',
                        'address' => '1805 E WAYZATA BLVD',
                        'zip' => '55391',
                        'phones' => [],
                        'phone' => '19524753034',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_11(): void
    {
        $this->createMakes('RAM')
            ->createStates('TX', 'MS', 'TN')
            ->createTimeZones('76040', '39576', '37122')
            ->sendPdfFile('wholesale_express_2_11')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '187703',
                    'pickup_date' => '03/23/2020',
                    'delivery_date' => '03/24/2020',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1C6RR6FG3KS540493',
                            'year' => '2019',
                            'make' => 'RAM',
                            'model' => '1500 CLASSIC 4X2 QUAD CAB 6’4” BOX',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM DALLAS-FORT WORTH (DFW)',
                        'state' => 'TX',
                        'city' => 'EULESS',
                        'address' => '12101 TRINITY BLVD',
                        'zip' => '76040',
                        'phones' => [],
                        'phone' => '18173994000',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'GULF AUTO DIRECT',
                        'state' => 'MS',
                        'city' => 'WAVELAND',
                        'address' => '9050 HIGHWAY 603',
                        'zip' => '39576',
                        'phones' => [],
                        'phone' => '18772869762',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_12(): void
    {
        $this->createMakes('DODGE')
            ->createStates('VA', 'OH', 'TN')
            ->createTimeZones('22079', '43612', '37122')
            ->sendPdfFile('wholesale_express_2_12')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '187634',
                    'pickup_date' => '03/20/2020',
                    'delivery_date' => '03/21/2020',
                    'instructions' => 'M-F Pickup Only',
                    'vehicles' => [
                        [
                            'vin' => '2C4RDGCG1JR359622',
                            'year' => '2018',
                            'make' => 'DODGE',
                            'model' => 'GRAND CARAVAN SXT WAGON',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'GP16- ENTERPRISE NORTHERN VA',
                        'state' => 'VA',
                        'city' => 'LORTON',
                        'address' => '7800 CINDER BED RD',
                        'zip' => '22079',
                        'phones' => [],
                        'phone' => '15714367414',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'GROGANS TOWNE CHRYSLER JEEP DODGE RAM',
                        'state' => 'OH',
                        'city' => 'TOLEDO',
                        'address' => '6100 TELEGRAPH RD',
                        'zip' => '43612',
                        'phones' => [],
                        'phone' => '14194760761',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_13(): void
    {
        $this->createMakes('LEXUS')
            ->createStates('PA', 'TN', 'TN')
            ->createTimeZones('19462', '37122', '37122')
            ->sendPdfFile('wholesale_express_2_13')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '247236',
                    'pickup_date' => '05/15/2021',
                    'delivery_date' => '05/17/2021',
                    'instructions' => 'DELIVERY INSTRUCTIONS: CHECK VEHICLE UNDER DEALER # 5258127 WHOLESALE INC CONFIRM GATE PASSES PRIOR TO PICKUP',
                    'vehicles' => [
                        [
                            'vin' => '2T2HZMDA8MC259006',
                            'year' => '2021',
                            'make' => 'LEXUS',
                            'model' => 'RX RX 350 AUTO',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'ARMEN CADILLAC (TESTLOCB)',
                        'state' => 'PA',
                        'city' => 'PLYMOUTH MEETING',
                        'address' => '1441 E RIDGE PIKE',
                        'zip' => '19462',
                        'phones' => [],
                        'phone' => '16105448500',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MANHEIM NASHVILLE (NASH)',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8400 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [
                            [
                                'number' => '16157736558'
                            ],
                        ],
                        'phone' => '16157733800',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_14(): void
    {
        $this->createMakes('RAM')
            ->createStates('WI', 'TX', 'TN')
            ->createTimeZones('53073', '75236', '37122')
            ->sendPdfFile('wholesale_express_2_14')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '181676',
                    'pickup_date' => '03/21/2020',
                    'delivery_date' => '03/23/2020',
                    'instructions' => 'DROP INSTRUCTIONS: Write on the window Dealer #5258127 Wholesale inc.',
                    'vehicles' => [
                        [
                            'vin' => '1C6RR7FG5HS560257',
                            'year' => '2017',
                            'make' => 'RAM',
                            'model' => '1500 4X4 QUAD CAB 6’4” BOX',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'VAN HORN CHRYLSER DODGE JEEP RAM',
                        'state' => 'WI',
                        'city' => 'PLYMOUTH',
                        'address' => '3000 EASTERN AVE',
                        'zip' => '53073',
                        'phones' => [],
                        'phone' => '19208936591',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MANHEIM DALLAS (TESTLOCB)',
                        'state' => 'TX',
                        'city' => 'DALLAS',
                        'address' => '2435 S WALTON WALKER BLVD',
                        'zip' => '75236',
                        'phones' => null,
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_15(): void
    {
        $this->createMakes('KIA')
            ->createStates('PA', 'NC', 'TN')
            ->createTimeZones('19543', '27408', '37122')
            ->sendPdfFile('wholesale_express_2_15')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '187338',
                    'pickup_date' => '03/20/2020',
                    'delivery_date' => '03/21/2020',
                    'instructions' => 'CALL IN ADVANCE release attached DEALER HOURS 9A/8P',
                    'vehicles' => [
                        [
                            'vin' => '5XXGT4L30KG358168',
                            'year' => '2019',
                            'make' => 'KIA',
                            'model' => 'OPTIMA AUTO',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM AUTO AUCTION STORAGE',
                        'state' => 'PA',
                        'city' => 'MORGANTOWN',
                        'address' => '75 GRACE BLVD',
                        'zip' => '19543',
                        'phones' => [],
                        'phone' => '18008222886',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'BATTLEGROUND KIA',
                        'state' => 'NC',
                        'city' => 'GREENSBORO',
                        'address' => '2927 BATTLEGROUND AVE',
                        'zip' => '27408',
                        'phones' => [],
                        'phone' => '13362951542',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_16(): void
    {
        $this->createMakes('JEEP')
            ->createStates('TX', 'KS', 'TN')
            ->createTimeZones('77090', '66221', '37122')
            ->sendPdfFile('wholesale_express_2_16')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '248155',
                    'pickup_date' => '05/17/2021',
                    'delivery_date' => '05/18/2021',
                    'instructions' => 'MUST CALL PICKUP AND DROP OFF LOCATIONS BEFORE ARRIVAL must call 24 hrs prior to drop',
                    'vehicles' => [
                        [
                            'vin' => '1J4GA64197L131643',
                            'year' => '2007',
                            'make' => 'JEEP',
                            'model' => 'WRANGLER 4WD 2DR RUBICON',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'STERLING MCCALL CHEVEROLET',
                        'state' => 'TX',
                        'city' => 'HOUSTON',
                        'address' => '17800 NORTH FWY',
                        'zip' => '77090',
                        'phones' => [],
                        'phone' => '12812438648',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MELINDA FRENCH',
                        'state' => 'KS',
                        'city' => 'OVERLAND PARK',
                        'address' => '14608 BLUEJACKET ST',
                        'zip' => '66221',
                        'phones' => [],
                        'phone' => '19135308262',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_17(): void
    {
        $this->createMakes('GMC')
            ->createStates('OK', 'TX', 'TN')
            ->createTimeZones('74006', '77090', '37122')
            ->sendPdfFile('wholesale_express_2_17')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '248090',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/15/2021',
                    'instructions' => 'CALL IN ADVANCE',
                    'vehicles' => [
                        [
                            'vin' => '3GTU2NEC6HG315467',
                            'year' => '2017',
                            'make' => 'GMC',
                            'model' => 'SIERRA 1500 4WD CREW CAB 143.5” SLT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'PATRIOT AUTO GROUP 4',
                        'state' => 'OK',
                        'city' => 'BARTLESVILLE',
                        'address' => '3800 SE ADAMS RD',
                        'zip' => '74006',
                        'phones' => [],
                        'phone' => '19188570968',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'STERLING MCCALL CHEVEROLET',
                        'state' => 'TX',
                        'city' => 'HOUSTON',
                        'address' => '17800 NORTH FWY',
                        'zip' => '77090',
                        'phones' => [],
                        'phone' => '12812438648',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_18(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('MO', 'KY', 'TN')
            ->createTimeZones('64161', '42101', '37122')
            ->sendPdfFile('wholesale_express_2_18')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '247722',
                    'pickup_date' => '05/17/2021',
                    'delivery_date' => '05/18/2021',
                    'instructions' => 'gate pass is printed at guard shack',
                    'vehicles' => [
                        [
                            'vin' => '2G1105S39J9173086',
                            'year' => '2018',
                            'make' => 'CHEVROLET',
                            'model' => 'IMPALA 4DR SDN LT W/1LT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM KANSAS CITY (654242)',
                        'state' => 'MO',
                        'city' => 'KANSAS CITY',
                        'address' => '3901 N SKILES',
                        'zip' => '64161',
                        'phones' => [],
                        'phone' => '18164524084',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MOTOR CARS OF BOWLING GREEN LLC',
                        'state' => 'KY',
                        'city' => 'BOWLING GREEN',
                        'address' => '538 COLLEGE ST',
                        'zip' => '42101',
                        'phones' => [],
                        'phone' => '12702828138',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_19(): void
    {
        $this->createMakes('LEXUS')
            ->createStates('OH', 'CO', 'TN')
            ->createTimeZones('44720', '80905', '37122')
            ->sendPdfFile('wholesale_express_2_19')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '247056',
                    'pickup_date' => '05/15/2021',
                    'delivery_date' => '05/17/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '2T2BZMCAXHC106186',
                            'year' => '2017',
                            'make' => 'LEXUS',
                            'model' => 'RX RX 350 AWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'CAIN TOYOTA (TESTLOCB)',
                        'state' => 'OH',
                        'city' => 'NORTH CANTON',
                        'address' => '6527 WHIPPLE AVENUE NW',
                        'zip' => '44720',
                        'phones' => null,
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'LEXUS OF COLORADO SPRINGS',
                        'state' => 'CO',
                        'city' => 'COLORADO SPRINGS',
                        'address' => '604 AUTO HEIGHTS',
                        'zip' => '80905',
                        'phones' => [],
                        'phone' => '17196392671',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_20(): void
    {
        $this->createMakes('LAND')
            ->createStates('NC', 'TN', 'TN')
            ->createTimeZones('28405', '37122', '37122')
            ->sendPdfFile('wholesale_express_2_20')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246355',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/15/2021',
                    'instructions' => 'DELIVERY INSTRUCTIONS: CHECK VEHICLE UNDER DEALER # 5258127 WHOLESALE INC CONFIRM GATE PASSES PRIOR TO PICKUP',
                    'vehicles' => [
                        [
                            'vin' => 'SALGS2SV0KA559616',
                            'year' => '2019',
                            'make' => 'LAND',
                            'model' => 'ROVER RANGE ROVER V6 SUPERCHARGED HSE SWB',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM WILMINGTON',
                        'state' => 'NC',
                        'city' => 'WILMINGTON',
                        'address' => '6118 MARKET STREET',
                        'zip' => '28405',
                        'phones' => [],
                        'phone' => '19103383060',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MANHEIM NASHVILLE (NASH)',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8400 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [
                            [
                                'number' => '16157736558'
                            ],
                        ],
                        'phone' => '16157733800',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_21(): void
    {
        $this->createMakes('LEXUS')
            ->createStates('MN', 'KY', 'TN')
            ->createTimeZones('55109', '40299', '37122')
            ->sendPdfFile('wholesale_express_2_21')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '248437',
                    'pickup_date' => '05/18/2021',
                    'delivery_date' => '05/19/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => 'JTHGZ1E24M5020585',
                            'year' => '2021',
                            'make' => 'LEXUS',
                            'model' => 'IS IS 350 F SPORT AWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'LEXUS OF MAPLEWOOD',
                        'state' => 'MN',
                        'city' => 'MAPLEWOOD',
                        'address' => '3000 HIGHWAY 61 NORTH',
                        'zip' => '55109',
                        'phones' => [],
                        'phone' => '16514836111',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'LEXUS OF LOUISVILLE',
                        'state' => 'KY',
                        'city' => 'LOUISVILLE',
                        'address' => '2400 BLANKENBAKER PKWY',
                        'zip' => '40299',
                        'phones' => [],
                        'phone' => '15024101737',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_22(): void
    {
        $this->createMakes('RAM')
            ->createStates('MI', 'MO', 'TN')
            ->createTimeZones('48099', '63376', '37122')
            ->sendPdfFile('wholesale_express_2_22')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246122',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/15/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1C6RR7LG7JS221025',
                            'year' => '2018',
                            'make' => 'RAM',
                            'model' => '1500 4X4 CREW CAB 5’7” BOX',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MIKE SAVOIE CHEVROLET (TESTLOCB)',
                        'state' => 'MI',
                        'city' => 'TROY',
                        'address' => '1900 WEST MAPLE ROAD',
                        'zip' => '48099',
                        'phones' => null,
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'NAPLETONS MID RIVERS CDJRM',
                        'state' => 'MO',
                        'city' => 'SAINT PETERS',
                        'address' => '4951 VETERANS MEMORIAL PKWY',
                        'zip' => '63376',
                        'phones' => [],
                        'phone' => '16369288000',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_23(): void
    {
        $this->createMakes('GOLF')
            ->createStates('GA', 'MI', 'TN')
            ->createTimeZones('30114', '49090', '37122')
            ->sendPdfFile('wholesale_express_2_23')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '247560',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/15/2021',
                    'instructions' => '***CALL 48 HOURS IN ADVANCE TO SCHEDULE DE- LIVERY*** ***4 PASSENGER GOLFT CART***',
                    'vehicles' => [
                        [
                            'vin' => null,
                            'year' => '2021',
                            'make' => 'GOLF',
                            'model' => 'CART TOMBERLIN 4 PASSENGER',
                            'inop' => false,
                            'enclosed' => false
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'GOLF CARS OF CANTON',
                        'state' => 'GA',
                        'city' => 'CANTON',
                        'address' => '121 WALESKA ST',
                        'zip' => '30114',
                        'phones' => [],
                        'phone' => '17703651391',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'SINGING SANDS',
                        'state' => 'MI',
                        'city' => 'SOUTH HAVEN',
                        'address' => '262 74TH ST LOT 109',
                        'zip' => '49090',
                        'phones' => [],
                        'phone' => '12699108091',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_24(): void
    {
        $this->createMakes('VOLKSWAGEN')
            ->createStates('IL', 'OH', 'TN')
            ->createTimeZones('62656', '45342', '37122')
            ->sendPdfFile('wholesale_express_2_24')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '265022',
                    'pickup_date' => '08/30/2021',
                    'delivery_date' => '08/31/2021',
                    'instructions' => '****GATE CODE 323A47***',
                    'vehicles' => [
                        [
                            'vin' => '3VWSK69M83M038828',
                            'year' => '2003',
                            'make' => 'VOLKSWAGEN',
                            'model' => 'JETTA SEDAN 4DR SDN GLS AUTO',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'GRAUE CHEVROLET BUICK CADILLAC',
                        'state' => 'IL',
                        'city' => 'LINCOLN',
                        'address' => '1905 N KICKAPOO ST',
                        'zip' => '62656',
                        'phones' => [],
                        'phone' => '12177354444',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MIAMISBURG AUTO SALES',
                        'state' => 'OH',
                        'city' => 'MIAMISBURG',
                        'address' => '807-811 SOUTH MAIN STREET',
                        'zip' => '45342',
                        'phones' => [],
                        'phone' => '19378593787',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_25(): void
    {
        $this->createMakes('FORD')
            ->createStates('OK', 'FL', 'TN')
            ->createTimeZones('74074', '33144', '37122')
            ->sendPdfFile('wholesale_express_2_25')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245512',
                    'pickup_date' => '05/13/2021',
                    'delivery_date' => '05/17/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1FT8W3BT5LEC40470',
                            'year' => '2020',
                            'make' => 'FORD',
                            'model' => 'SUPER DUTY F-350 SRW 4WD CREW CAB BOX',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'WILSON CHEVROLET INC',
                        'state' => 'OK',
                        'city' => 'STILLWATER',
                        'address' => '4850 W 6TH AVE',
                        'zip' => '74074',
                        'phones' => [],
                        'phone' => '14057143390',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'FORD MIDWAY MALL, INC',
                        'state' => 'FL',
                        'city' => 'MIAMI',
                        'address' => '8155 WEST FLAGLER STREET',
                        'zip' => '33144',
                        'phones' => [],
                        'phone' => '13052663000',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_26(): void
    {
        $this->createMakes('LINCOLN')
            ->createStates('PA', 'GA', 'TN')
            ->createTimeZones('19607', '30060', '37122')
            ->sendPdfFile('wholesale_express_2_26')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246764',
                    'pickup_date' => '05/15/2021',
                    'delivery_date' => '05/17/2021',
                    'instructions' => 'CALL IN ADVANCE release attached',
                    'vehicles' => [
                        [
                            'vin' => '3LN6L5LU6JR603741',
                            'year' => '2018',
                            'make' => 'LINCOLN',
                            'model' => 'MKZ HYBRID SELECT FWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'UXX TOM MASANO FORD LINCOLN',
                        'state' => 'PA',
                        'city' => 'READING',
                        'address' => '1600 LANCASTER AVE',
                        'zip' => '19607',
                        'phones' => [],
                        'phone' => '16107771371',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'PUGMIRE LINCOLN MERCURY',
                        'state' => 'GA',
                        'city' => 'MARIETTA',
                        'address' => '1865 COBB PKWY S',
                        'zip' => '30060',
                        'phones' => [],
                        'phone' => '17709522261',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_27(): void
    {
        $this->createMakes('JEEP')
            ->createStates('IL', 'TN', 'TN')
            ->createTimeZones('62711', '37122', '37122')
            ->sendPdfFile('wholesale_express_2_27')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '248063',
                    'pickup_date' => '05/17/2021',
                    'delivery_date' => '05/18/2021',
                    'instructions' => 'DELIVERY INSTRUCTIONS: CHECK VEHICLE UNDER DEALER # 5258127 WHOLESALE INC CONFIRM GATE PASSES PRIOR TO PICKUP',
                    'vehicles' => [
                        [
                            'vin' => 'ZACCJBDT4GPD16922',
                            'year' => '2016',
                            'make' => 'JEEP',
                            'model' => 'RENEGADE 4WD 4DR LIMITED',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'LANDMARK CDJ - SPRINGFIELD',
                        'state' => 'IL',
                        'city' => 'SPRINGFIELD',
                        'address' => '2331 PRAIRIE CROSSING DR',
                        'zip' => '62711',
                        'phones' => [],
                        'phone' => '12178625300',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MANHEIM NASHVILLE (NASH)',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8400 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [
                            [
                                'number' => '16157736558'
                            ],
                        ],
                        'phone' => '16157733800',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_28(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('IL', 'SC', 'TN')
            ->createTimeZones('60443', '29461', '37122')
            ->sendPdfFile('wholesale_express_2_28')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '247851',
                    'pickup_date' => '05/15/2021',
                    'delivery_date' => '05/17/2021',
                    'instructions' => 'NO NIGHT DROPS. MUST GET SIGNATURE. NO PAY- MENT IF BOL IS NOT SIGNED.Recon lot is in the back of the DMV just through the gates right behind it. They are across the street from Sonic/Burger King. DO NOT DELIVER TO TRIDENT PAIN CENTER. DO NOT LEAVE KEY IN THE VEHICLE',
                    'vehicles' => [
                        [
                            'vin' => '1GNSCAKC6GR392072',
                            'year' => '2016',
                            'make' => 'CHEVROLET',
                            'model' => 'TAHOE 2WD 4DR LS',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM CHICAGO (MCHI)',
                        'state' => 'IL',
                        'city' => 'MATTESON',
                        'address' => '20401 COX AVE',
                        'zip' => '60443',
                        'phones' => [],
                        'phone' => '18158064222',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'DMV/RECON CENTER',
                        'state' => 'SC',
                        'city' => 'MONCKS CORNER',
                        'address' => '108-B S, N U.S. HWY 52',
                        'zip' => '29461',
                        'phones' => [],
                        'phone' => '18438091046',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_29(): void
    {
        $this->createMakes('GMC')
            ->createStates('MD', 'SC', 'TN')
            ->createTimeZones('21784', '29461', '37122')
            ->sendPdfFile('wholesale_express_2_29')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '247846',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/15/2021',
                    'instructions' => 'CALL IN ADVANCE MUST PICK UP DURING BUSINESS HOURS. CALL AHEAD TO SCHEDULE PICK UP. NO NIGHT DROPS. MUST GET SIGNATURE. NO PAY- MENT IF BOL IS NOT SIGNED.Recon lot is in the back of the DMV just through the gates right behind it. They are across the street from Sonic/Burger King. DO NOT DELIVER TO TRIDENT PAIN CENTER. DO NOT LEAVE KEY IN THE VEHICLE',
                    'vehicles' => [
                        [
                            'vin' => '1GT12YEG1FF192604',
                            'year' => '2015',
                            'make' => 'GMC',
                            'model' => 'SIERRA 2500HD 4WD CREW CAB SLE',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'TRUST AUTO',
                        'state' => 'MD',
                        'city' => 'SYKESVILLE',
                        'address' => '1551 W OLD LIBERTY RD 21784',
                        'zip' => '21784',
                        'phones' => [
                            [
                                'number' => '14435523131'
                            ],
                        ],
                        'phone' => '19176676460',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'DMV/RECON CENTER',
                        'state' => 'SC',
                        'city' => 'MONCKS CORNER',
                        'address' => '108-B S, N U.S. HWY 52',
                        'zip' => '29461',
                        'phones' => [],
                        'phone' => '18438091046',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_30(): void
    {
        $this->createMakes('HYUNDAI')
            ->createStates('GA', 'PA', 'TN')
            ->createTimeZones('30680', '17520', '37122')
            ->sendPdfFile('wholesale_express_2_30')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '247221',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/15/2021',
                    'instructions' => '****DROP CAR IN CUSTOMER PARKING LOT AND PUT KEYS ON FRONT TIRE AND CALL MAX 516-448- 1617 TO CONFIRM YOU DELIVERED SO HE CAN PICK UP **DO NOT CHECK INTO AUCTION***** ***CALL MAX 24 HRS IN ADVANCE BEFORE DELIVERING PICK HOURS M-F 9 AM-4 PM ONLY',
                    'vehicles' => [
                        [
                            'vin' => 'KMHGN4JF2FU043827',
                            'year' => '2015',
                            'make' => 'HYUNDAI',
                            'model' => 'GENESIS 4DR SDN V8 5.0L RWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'CARVANA ATLANTA IC',
                        'state' => 'GA',
                        'city' => 'WINDER',
                        'address' => '356 ATLANTA HWY NW',
                        'zip' => '30680',
                        'phones' => [],
                        'phone' => '14043710849',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'AMERICA’S AUTO AUCTION EAST PETERSBURG',
                        'state' => 'PA',
                        'city' => 'EAST PETERSBURG',
                        'address' => '1040 COMMERCIAL AVE',
                        'zip' => '17520',
                        'phones' => [],
                        'phone' => '17175695220',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_31(): void
    {
        $this->createMakes('FORD')
            ->createStates('NH', 'GA', 'TN')
            ->createTimeZones('03053', '30269', '37122')
            ->sendPdfFile('wholesale_express_2_31')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245162',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/15/2021',
                    'instructions' => 'CALL IN ADVANCE Destination is Residence MUST PICK UP DURING BUSINESS HOURS. CALL AHEAD TO SCHEDULE PICK UP. MUST CALL 24 HOURS AHEAD TO SCHEDULE DELIVERY.',
                    'vehicles' => [
                        [
                            'vin' => '1FMCU9G68MUA46315',
                            'year' => '2021',
                            'make' => 'FORD',
                            'model' => 'ESCAPE SE AWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'LONDONDERRY FORD',
                        'state' => 'NH',
                        'city' => 'LONDONDERRY',
                        'address' => '33 NASHUA RD',
                        'zip' => '03053',
                        'phones' => [],
                        'phone' => '16038672619',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'ALEX CHIRICO',
                        'state' => 'GA',
                        'city' => 'PEACHTREE',
                        'address' => '106 CHASE COURT',
                        'zip' => '30269',
                        'phones' => [],
                        'phone' => '17163931794',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_32(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('IL', 'VA', 'TN')
            ->createTimeZones('60007', '23455', '37122')
            ->sendPdfFile('wholesale_express_2_32')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '247571',
                    'pickup_date' => '05/12/2021',
                    'delivery_date' => '05/15/2021',
                    'instructions' => 'If Picking up from CASTLE CHEVROLET NORTH: PICK M-F 8-8 SAT 8-5 MUST CALL BILL 2 HRS AHEAD OF PICKUP OR WILL NOT RELEASE 847-954-9511 NO AFTER HOURS DELIVERIES OR YOU WILL NOT BE PAID',
                    'vehicles' => [
                        [
                            'vin' => '1GCWGAFP4M1227076',
                            'year' => '2021',
                            'make' => 'CHEVROLET',
                            'model' => 'EXPRESS CARGO VAN RWD 2500 135”',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'CASTLE CHEVROLET NORTH',
                        'state' => 'IL',
                        'city' => 'ELK GROVE VILLAGE',
                        'address' => '175 N. ARLINGTON HEIGHTS ROAD',
                        'zip' => '60007',
                        'phones' => [
                            [
                                'number' => '18475934614'
                            ],
                        ],
                        'phone' => '18479549511',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'DBM CORPORATION',
                        'state' => 'VA',
                        'city' => 'VIRGINIA BEACH',
                        'address' => '5529 BOTANICAL DR',
                        'zip' => '23455',
                        'phones' => [],
                        'phone' => '17577167557',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_33(): void
    {
        $this->createMakes('TOYOTA')
            ->createStates('VA', 'OH', 'TN')
            ->createTimeZones('24073', '45014', '37122')
            ->sendPdfFile('wholesale_express_2_33')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '244985',
                    'pickup_date' => '05/04/2021',
                    'delivery_date' => '05/05/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => 'JTNB11HK4J3020023',
                            'year' => '2018',
                            'make' => 'TOYOTA',
                            'model' => 'CAMRY AUTOMATIC (NATL)',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'DUNCAN HOKIE HONDA',
                        'state' => 'VA',
                        'city' => 'CHRISTIANSBURG',
                        'address' => '2040 ROANOKE ST',
                        'zip' => '24073',
                        'phones' => [],
                        'phone' => '15403813200',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'PERFORMANCE TOYOTA',
                        'state' => 'OH',
                        'city' => 'FAIRFIELD',
                        'address' => '5676 DIXIE HWY',
                        'zip' => '45014',
                        'phones' => [],
                        'phone' => '15138748797',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_34(): void
    {
        $this->createMakes('VOLVO')
            ->createStates('AL', 'TX', 'TN')
            ->createTimeZones('35216', '78752', '37122')
            ->sendPdfFile('wholesale_express_2_34')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245647',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/15/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => 'YV4102CK4J1348521',
                            'year' => '2018',
                            'make' => 'VOLVO',
                            'model' => 'XC90 T5 FWD 7-PASSENGER MOMENTUM',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'VOLVO CARS OF VESTAVIA HILLS',
                        'state' => 'AL',
                        'city' => 'VESTAVIA HILLS',
                        'address' => '3010 COLUMBIANA RD',
                        'zip' => '35216',
                        'phones' => [],
                        'phone' => '12058233100',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'VOLVO CARS OF AUSTIN',
                        'state' => 'TX',
                        'city' => 'AUSTIN',
                        'address' => '7216 N INTERSTATE HWY 35',
                        'zip' => '78752',
                        'phones' => [],
                        'phone' => '15127067000',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_35(): void
    {
        $this->createMakes('DODGE')
            ->createStates('TN', 'IN', 'TN')
            ->createTimeZones('37122', '46774', '37122')
            ->sendPdfFile('wholesale_express_2_35')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '187692',
                    'pickup_date' => '03/23/2020',
                    'delivery_date' => '03/24/2020',
                    'instructions' => 'PRIVATE PARTY DELIVERY. MUST CALL 24 HOURS IN ADVANCE TO SCHEDULE DELIVERY.',
                    'vehicles' => [
                        [
                            'vin' => '2C3CDXCTXHH556697',
                            'year' => '2017',
                            'make' => 'DODGE',
                            'model' => 'CHARGER RWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'EASTGATE RETAIL',
                        'state' => 'TN',
                        'city' => 'MT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16158668324',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'CHRISTOPHER MAYER',
                        'state' => 'IN',
                        'city' => 'NEW HAVEN',
                        'address' => '5120 GREEN RD',
                        'zip' => '46774',
                        'phones' => [],
                        'phone' => '12604108604',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_36(): void
    {
        $this->createMakes('SUBARU')
            ->createStates('MS', 'TX', 'TN')
            ->createTimeZones('39503', '76116', '37122')
            ->sendPdfFile('wholesale_express_2_36')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '247554',
                    'pickup_date' => '05/14/2021',
                    'delivery_date' => '05/15/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => 'JF2SHBDC8CH411574',
                            'year' => '2012',
                            'make' => 'SUBARU',
                            'model' => 'FORESTER 4DR AUTO 2.5X PREMIUM',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'ALLEN TOYOTA',
                        'state' => 'MS',
                        'city' => 'GULFPORT',
                        'address' => '11397 HELEN RICHARDS DR',
                        'zip' => '39503',
                        'phones' => [],
                        'phone' => '12288968220',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'JD BYRIDER FORT WORTH',
                        'state' => 'TX',
                        'city' => 'FORT WORTH',
                        'address' => '8840 CAMP BOWIE WEST BLVD',
                        'zip' => '76116',
                        'phones' => [],
                        'phone' => '18176322900',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_37(): void
    {
        $this->createMakes('FORD')
            ->createStates('MI', 'VA', 'TN')
            ->createTimeZones('48327', '20151', '37122')
            ->sendPdfFile('wholesale_express_2_37')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '247599',
                    'pickup_date' => '05/13/2021',
                    'delivery_date' => '05/14/2021',
                    'instructions' => 'CALL IN ADVANCE DELIVER ASAP',
                    'vehicles' => [
                        [
                            'vin' => '1FTFW1E50KFB45445',
                            'year' => '2019',
                            'make' => 'FORD',
                            'model' => 'F-150 4WD SUPERCREW BOX',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'SUBURBAN FORD OF WATERFORD',
                        'state' => 'MI',
                        'city' => 'WATERFORD',
                        'address' => '6975 HIGHLAND RD',
                        'zip' => '48327',
                        'phones' => [],
                        'phone' => '12486745630',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'TED BRITT FORD OF CHANTILLY',
                        'state' => 'VA',
                        'city' => 'CHANTILLY',
                        'address' => '4175 AUTO PARK CIR',
                        'zip' => '20151',
                        'phones' => [],
                        'phone' => '17036732300',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_38(): void
    {
        $this->createMakes('FORD')
            ->createStates('MI', 'VA', 'TN')
            ->createTimeZones('48114', '22030', '37122')
            ->sendPdfFile('wholesale_express_2_38')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '247600',
                    'pickup_date' => '05/13/2021',
                    'delivery_date' => '05/14/2021',
                    'instructions' => 'CALL IN ADVANCE DELIVER ASAP',
                    'vehicles' => [
                        [
                            'vin' => '1FTFW1E58KKC32694',
                            'year' => '2019',
                            'make' => 'FORD',
                            'model' => 'F-150 4WD SUPERCREW BOX',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'BRIGHTON FORD',
                        'state' => 'MI',
                        'city' => 'BRIGHTON',
                        'address' => '8240 GRAND RIVER AVE',
                        'zip' => '48114',
                        'phones' => [],
                        'phone' => '18102271171',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'TED BRITT FORD OF FAIRFAX',
                        'state' => 'VA',
                        'city' => 'FAIRFAX',
                        'address' => '11165 FAIRFAX BLVD',
                        'zip' => '22030',
                        'phones' => [],
                        'phone' => '17036598409',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_39(): void
    {
        $this->createMakes('BMW')
            ->createStates('IL', 'VA', 'TN')
            ->createTimeZones('60443', '22031', '37122')
            ->sendPdfFile('wholesale_express_2_39')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '247565',
                    'pickup_date' => '05/13/2021',
                    'delivery_date' => '05/14/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => 'WBADX7C55BE580734',
                            'year' => '2011',
                            'make' => 'BMW',
                            'model' => '3 SERIES 2DR CONV 335I',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM CHICAGO (MCHI)',
                        'state' => 'IL',
                        'city' => 'MATTESON',
                        'address' => '20401 COX AVE',
                        'zip' => '60443',
                        'phones' => [],
                        'phone' => '18158064222',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'BENZ ELITE AUTOMOTIVE',
                        'state' => 'VA',
                        'city' => 'FAIRFAX',
                        'address' => '2809- C DORR',
                        'zip' => '22031',
                        'phones' => [],
                        'phone' => '17033701221',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_40(): void
    {
        $this->createMakes('RAM')
            ->createStates('TN', 'AL', 'TN')
            ->createTimeZones('37122', '36330', '37122')
            ->sendPdfFile('wholesale_express_2_40')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '247124',
                    'pickup_date' => '05/18/2021',
                    'delivery_date' => '05/19/2021',
                    'instructions' => 'CONFIRM GATE PASSES PRIOR TO PICKUP',
                    'vehicles' => [
                        [
                            'vin' => '1C6RR7YT1GS307719',
                            'year' => '2016',
                            'make' => 'RAM',
                            'model' => '1500 4WD CREW CAB 140.5” REBEL',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM NASHVILLE (NASH)',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8400 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [
                            [
                                'number' => '16157736558'
                            ],
                        ],
                        'phone' => '16157733800',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'ENTERPRISE CHEVROLET',
                        'state' => 'AL',
                        'city' => 'ENTERPRISE',
                        'address' => '1001 RUCKER BLVD',
                        'zip' => '36330',
                        'phones' => [
                            [
                                'number' => '13343479581'
                            ],
                        ],
                        'phone' => '13347983420',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_41(): void
    {
        $this->createMakes('INFINITI')
            ->createStates('FL', 'CT', 'TN')
            ->createTimeZones('33844', '06120', '37122')
            ->sendPdfFile('wholesale_express_2_41')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245889',
                    'pickup_date' => '05/12/2021',
                    'delivery_date' => '05/14/2021',
                    'instructions' => 'MUST PICK UP DURING BUSINESS HOURS. FOR NIGHT DROP< PUT KEYS IN SERVICE NIGHT DROP BOX',
                    'vehicles' => [
                        [
                            'vin' => 'JN1CV6AR0FM520962',
                            'year' => '2015',
                            'make' => 'INFINITI',
                            'model' => 'Q40 4DR SDN AWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'CARVANA IC',
                        'state' => 'FL',
                        'city' => 'HAINES CITY',
                        'address' => '3700 BANNON ISLAND RD',
                        'zip' => '33844',
                        'phones' => [],
                        'phone' => '18332893533',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'HARTE INFINITI, INC',
                        'state' => 'CT',
                        'city' => 'HARTFORD',
                        'address' => '150 WESTON ST',
                        'zip' => '06120',
                        'phones' => [],
                        'phone' => '18609955735',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_42(): void
    {
        $this->createMakes('CHRYSLER')
            ->createStates('TX', 'IN', 'TN')
            ->createTimeZones('77505', '46996', '37122')
            ->sendPdfFile('wholesale_express_2_42')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '247014',
                    'pickup_date' => '05/11/2021',
                    'delivery_date' => '05/13/2021',
                    'instructions' => 'handicap vans. call to confirm modifications *HRS ARE M-F 8A-5P ONLY** NO AFTER HOURS DROP** MUST CALL BEFORE ARRIVAL',
                    'vehicles' => [
                        [
                            'vin' => null,
                            'year' => '2021',
                            'make' => 'CHRYSLER',
                            'model' => 'PACIFICA',
                            'inop' => false,
                            'enclosed' => false
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'ADAPTIVE DRIVING ACCESS, INC',
                        'state' => 'TX',
                        'city' => 'PASADENA',
                        'address' => '3430 EAST SAM HOUSTON PKWY S',
                        'zip' => '77505',
                        'phones' => [
                            [
                                'number' => '12814871969'
                            ],
                        ],
                        'phone' => '12815008986',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'BRAUNABILITY',
                        'state' => 'IN',
                        'city' => 'WINAMAC',
                        'address' => '631 W 11TH ST',
                        'zip' => '46996',
                        'phones' => [],
                        'phone' => '18004880359',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_43(): void
    {
        $this->createMakes('HYUNDAI')
            ->createStates('OH', 'MO', 'TN')
            ->createTimeZones('44036', '63132', '37122')
            ->sendPdfFile('wholesale_express_2_43')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '247316',
                    'pickup_date' => '05/12/2021',
                    'delivery_date' => '05/13/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => 'KM8J23A43JU808141',
                            'year' => '2018',
                            'make' => 'HYUNDAI',
                            'model' => 'TUCSON SE FWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'ELYRIA HYUNDAI',
                        'state' => 'OH',
                        'city' => 'ELYRIA',
                        'address' => '845 LEONA STREET',
                        'zip' => '44036',
                        'phones' => [],
                        'phone' => '14403247700',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'NAPLETON ST LOUIS NISSAN',
                        'state' => 'MO',
                        'city' => 'ST LOUIS',
                        'address' => '10964 PAGE AVE',
                        'zip' => '63132',
                        'phones' => [],
                        'phone' => '13142691682',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_44(): void
    {
        $this->createMakes('CHRYSLER')
            ->createStates('TX', 'IN', 'TN')
            ->createTimeZones('77505', '46996', '37122')
            ->sendPdfFile('wholesale_express_2_44')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '247014',
                    'pickup_date' => '05/11/2021',
                    'delivery_date' => '05/13/2021',
                    'instructions' => 'handicap vans. call to confirm modifications *HRS ARE M-F 8A-5P ONLY** NO AFTER HOURS DROP** MUST CALL BEFORE ARRIVAL',
                    'vehicles' => [
                        [
                            'vin' => null,
                            'year' => '2021',
                            'make' => 'CHRYSLER',
                            'model' => 'PACIFICA',
                            'inop' => false,
                            'enclosed' => false
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'ADAPTIVE DRIVING ACCESS, INC',
                        'state' => 'TX',
                        'city' => 'PASADENA',
                        'address' => '3430 EAST SAM HOUSTON PKWY S',
                        'zip' => '77505',
                        'phones' => [
                            [
                                'number' => '12814871969'
                            ],
                        ],
                        'phone' => '12815008986',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'BRAUNABILITY',
                        'state' => 'IN',
                        'city' => 'WINAMAC',
                        'address' => '631 W 11TH ST',
                        'zip' => '46996',
                        'phones' => [],
                        'phone' => '18004880359',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_45(): void
    {
        $this->createMakes('SUBARU')
            ->createStates('MS', 'TX', 'TN')
            ->createTimeZones('39503', '76116', '37122')
            ->sendPdfFile('wholesale_express_2_45')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '247554',
                    'pickup_date' => '05/17/2021',
                    'delivery_date' => '05/18/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => 'JF2SHBDC8CH411574',
                            'year' => '2012',
                            'make' => 'SUBARU',
                            'model' => 'FORESTER 4DR AUTO 2.5X PREMIUM',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'ALLEN TOYOTA',
                        'state' => 'MS',
                        'city' => 'GULFPORT',
                        'address' => '11397 HELEN RICHARDS DR',
                        'zip' => '39503',
                        'phones' => [],
                        'phone' => '12288968220',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'JD BYRIDER FORT WORTH',
                        'state' => 'TX',
                        'city' => 'FORT WORTH',
                        'address' => '8840 CAMP BOWIE WEST BLVD',
                        'zip' => '76116',
                        'phones' => [],
                        'phone' => '18176322900',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_46(): void
    {
        $this->createMakes('TOYOTA')
            ->createStates('MO', 'TX', 'TN')
            ->createTimeZones('65802', '75088', '37122')
            ->sendPdfFile('wholesale_express_2_46')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '248157',
                    'pickup_date' => '05/19/2021',
                    'delivery_date' => '05/20/2021',
                    'instructions' => 'handicap vans. call to confirm modifications *HRS ARE M-F 8A-5P ONLY** NO AFTER HOURS DROP** MUST CALL BEFORE ARRIVAL',
                    'vehicles' => [
                        [
                            'vin' => '5TDYZ3DC5LS084297',
                            'year' => '2020',
                            'make' => 'TOYOTA',
                            'model' => 'SIENNA FWD (NATL)',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'UNITED ACCESS SPRINGFIELD',
                        'state' => 'MO',
                        'city' => 'SPRINGFIELD',
                        'address' => '1389 N. CEDARBROOK AVE',
                        'zip' => '65802',
                        'phones' => [],
                        'phone' => '14178901043',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'UNITED ACCESS ROWLETT',
                        'state' => 'TX',
                        'city' => 'ROWLETT',
                        'address' => '2704 LAWING LANE STE 300',
                        'zip' => '75088',
                        'phones' => [],
                        'phone' => '19722408839',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_47(): void
    {
        $this->createMakes('TOYOTA')
            ->createStates('GA', 'NJ', 'TN')
            ->createTimeZones('30349', '08078', '37122')
            ->sendPdfFile('wholesale_express_2_47')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246777',
                    'pickup_date' => '05/11/2021',
                    'delivery_date' => '05/12/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '2T1BURHE3JC987721',
                            'year' => '2018',
                            'make' => 'TOYOTA',
                            'model' => 'COROLLA (NATL)',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM ATLANTA',
                        'state' => 'GA',
                        'city' => 'ATLANTA',
                        'address' => '4900 BUFFINGTON RD',
                        'zip' => '30349',
                        'phones' => [],
                        'phone' => '18008566107',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'TOYOTA OF RUNNEMEDE',
                        'state' => 'NJ',
                        'city' => 'RUNNEMEDE',
                        'address' => '99 SOUTH BLACK HORSE PIKE',
                        'zip' => '08078',
                        'phones' => [],
                        'phone' => '16107316005',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_48(): void
    {
        $this->createMakes('HONDA')
            ->createStates('GA', 'MO', 'TN')
            ->createTimeZones('30080', '63376', '37122')
            ->sendPdfFile('wholesale_express_2_48')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245717',
                    'pickup_date' => '05/12/2021',
                    'delivery_date' => '05/13/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1HGCR2F77HA166980',
                            'year' => '2017',
                            'make' => 'HONDA',
                            'model' => 'ACCORD SEDAN EX CVT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'GLOBAL MOTORS VIP',
                        'state' => 'GA',
                        'city' => 'SMYRNA',
                        'address' => '2833 S COBB DR SE',
                        'zip' => '30080',
                        'phones' => [],
                        'phone' => '17709891142',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'NAPLETONS MID RIVERS KIA',
                        'state' => 'MO',
                        'city' => 'ST PETERS',
                        'address' => '4955 VETERANS MEMORIAL PKWY,',
                        'zip' => '63376',
                        'phones' => [],
                        'phone' => '16362064284',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_49(): void
    {
        $this->createMakes('RAM')
            ->createStates('MN', 'MO', 'TN')
            ->createTimeZones('55379', '63376', '37122')
            ->sendPdfFile('wholesale_express_2_49')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246941',
                    'pickup_date' => '05/12/2021',
                    'delivery_date' => '05/13/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1C6SRFJT9LN420820',
                            'year' => '2020',
                            'make' => 'RAM',
                            'model' => '1500 LARAMIE 4X4 CREW CAB BOX',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM NORTHSTAR MINNESOTA',
                        'state' => 'MN',
                        'city' => 'SHAKOPEE',
                        'address' => '4908 VALLEY INDUSTRIAL BLVD N',
                        'zip' => '55379',
                        'phones' => [],
                        'phone' => '19524965601',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'ED NAPLETON HONDA- MISSOURI',
                        'state' => 'MO',
                        'city' => 'SAINT PETERS',
                        'address' => '4780 N. SERVICE RD.',
                        'zip' => '63376',
                        'phones' => [],
                        'phone' => '16363233197',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_50(): void
    {
        $this->createMakes('TOYOTA')
            ->createStates('MO', 'TN', 'TN')
            ->createTimeZones('64506', '37122', '37122')
            ->sendPdfFile('wholesale_express_2_50')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246138',
                    'pickup_date' => '05/11/2021',
                    'delivery_date' => '05/12/2021',
                    'instructions' => 'DELIVERY INSTRUCTIONS: CHECK VEHICLE UNDER DEALER # 5258127 WHOLESALE INC Please call John Pemberton with any questions at 816-671- 4500CONFIRM GATE PASSES PRIOR TO PICKUP',
                    'vehicles' => [
                        [
                            'vin' => '2T3C1RFV2KC023913',
                            'year' => '2019',
                            'make' => 'TOYOTA',
                            'model' => 'RAV4 XLE PREMIUM FWD (NATL)',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'ROLLING HILLS AUTO PLAZA',
                        'state' => 'MO',
                        'city' => 'SAINT JOSEPH',
                        'address' => '1617 CROSS ST',
                        'zip' => '64506',
                        'phones' => [],
                        'phone' => '18166714500',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MANHEIM NASHVILLE (NASH)',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8400 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [
                            [
                                'number' => '16157736558'
                            ],
                        ],
                        'phone' => '16157733800',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_51(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('LA', 'NY', 'TN')
            ->createTimeZones('70123', '10583', '37122')
            ->sendPdfFile('wholesale_express_2_51')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '247072',
                    'pickup_date' => '05/12/2021',
                    'delivery_date' => '05/12/2021',
                    'instructions' => 'CALL IN ADVANCE ready wed 5/12',
                    'vehicles' => [
                        [
                            'vin' => '1GCGTCEN4L1229971',
                            'year' => '2020',
                            'make' => 'CHEVROLET',
                            'model' => 'COLORADO 4WD CREW CAB 141” LT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'ASSETS IN MOTION',
                        'state' => 'LA',
                        'city' => 'NEW ORLEANS',
                        'address' => '1308 DISTRIBUTORS ROW',
                        'zip' => '70123',
                        'phones' => [],
                        'phone' => '15044157161',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'BEN FENTON',
                        'state' => 'NY',
                        'city' => 'SCARSDALE',
                        'address' => '24 ASPEN RD',
                        'zip' => '10583',
                        'phones' => [],
                        'phone' => '19147125406',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_52(): void
    {
        $this->createMakes('INFINITI')
            ->createStates('OK', 'LA', 'TN')
            ->createTimeZones('74133', '70471', '37122')
            ->sendPdfFile('wholesale_express_2_52')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246776',
                    'pickup_date' => '05/10/2021',
                    'delivery_date' => '05/11/2021',
                    'instructions' => 'ASK FOR DONALD BROCK AT PICKUP LOCATION MUST CALL PICKUP AND DROP OFF LOCATIONS BE- FORE ARRIVAL CALL IN ADVANCE *MUST NOTE HOW MANY KEYS AT PICK ON BOL',
                    'vehicles' => [
                        [
                            'vin' => '5N1DL0MMXHC541455',
                            'year' => '2017',
                            'make' => 'INFINITI',
                            'model' => 'QX60 AWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'JACKIE COOPER INFINITI',
                        'state' => 'OK',
                        'city' => 'TULSA',
                        'address' => '8825 S MEMORIAL DR',
                        'zip' => '74133',
                        'phones' => [],
                        'phone' => '19182499393',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'LAURA LAMANDRE',
                        'state' => 'LA',
                        'city' => 'MANDEVILLE',
                        'address' => '824 BEAU CHENE',
                        'zip' => '70471',
                        'phones' => [],
                        'phone' => '19858458145',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_53(): void
    {
        $this->createMakes('JEEP', 'FORD')
            ->createStates('MO', 'TN', 'TN')
            ->createTimeZones('65201', '37122', '37122')
            ->sendPdfFile('wholesale_express_2_53')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '248488',
                    'pickup_date' => '05/18/2021',
                    'delivery_date' => '05/19/2021',
                    'instructions' => 'Must pick up during auction business hours for gate pass. DELIVERY INSTRUCTIONS: CHECK VEHICLE UNDER DEALER # 5258127 WHOLESALE INC CONFIRM GATE PASSES PRIOR TO PICKUP',
                    'vehicles' => [
                        [
                            'vin' => '3C4NJDBB0JT264347',
                            'year' => '2018',
                            'make' => 'JEEP',
                            'model' => 'COMPASS-4 CYL. LATITUDE 4X4',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                        [
                            'vin' => '1FMCU0J98JUA74674',
                            'year' => '2018',
                            'make' => 'FORD',
                            'model' => 'ESCAPE TITANIUM FWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                        [
                            'vin' => '1FMCU0G60LUB24211',
                            'year' => '2020',
                            'make' => 'FORD',
                            'model' => 'ESCAPE FWD 3C SE FWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MISSOURI AUTO AUCTION (TESTLOCB)',
                        'state' => 'MO',
                        'city' => 'COLUMBIA',
                        'address' => '421 N RANGLINE ROAD',
                        'zip' => '65201',
                        'phones' => [],
                        'phone' => '15738860032',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MANHEIM NASHVILLE (NASH)',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8400 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [
                            [
                                'number' => '16157736558'
                            ],
                        ],
                        'phone' => '16157733800',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_54(): void
    {
        $this->createMakes('DODGE', 'FORD')
            ->createStates('LA', 'TN', 'TN')
            ->createTimeZones('71301', '37122', '37122')
            ->sendPdfFile('wholesale_express_2_54')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246759',
                    'pickup_date' => '05/10/2021',
                    'delivery_date' => '05/11/2021',
                    'instructions' => 'DELIVERY INSTRUCTIONS: CHECK VEHICLE UNDER DEALER # 5258127 WHOLESALE INC CONFIRM GATE PASSES PRIOR TO PICKUP',
                    'vehicles' => [
                        [
                            'vin' => '2C3CDZAG4LH105405',
                            'year' => '2020',
                            'make' => 'DODGE',
                            'model' => 'CHALLENGER SXT RWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                        [
                            'vin' => '1FT7W2BT6KEE66098',
                            'year' => '2019',
                            'make' => 'FORD',
                            'model' => 'F250 SUPER DUTY 4WD CREW CAB BOX',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'WALKER AUTOMOTIVE',
                        'state' => 'LA',
                        'city' => 'ALEXANDRIA',
                        'address' => '1616 MACARTHUR DR',
                        'zip' => '71301',
                        'phones' => [],
                        'phone' => '13184456421',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MANHEIM NASHVILLE (NASH)',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8400 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [
                            [
                                'number' => '16157736558'
                            ],
                        ],
                        'phone' => '16157733800',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_55(): void
    {
        $this->createMakes('ACURA')
            ->createStates('LA', 'NY', 'TN')
            ->createTimeZones('70123', '10583', '37122')
            ->sendPdfFile('wholesale_express_2_55')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245735',
                    'pickup_date' => '05/12/2021',
                    'delivery_date' => '05/14/2021',
                    'instructions' => 'ready friday 5/7',
                    'vehicles' => [
                        [
                            'vin' => '5J8TB4H35JL003362',
                            'year' => '2018',
                            'make' => 'ACURA',
                            'model' => 'RDX AWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'ASSETS IN MOTION',
                        'state' => 'LA',
                        'city' => 'NEW ORLEANS',
                        'address' => '1308 DISTRIBUTORS ROW',
                        'zip' => '70123',
                        'phones' => [],
                        'phone' => '15044157161',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'RICHARD SINGER',
                        'state' => 'NY',
                        'city' => 'SCARSDALE',
                        'address' => '2 SAGE TERRACE',
                        'zip' => '10583',
                        'phones' => [],
                        'phone' => '19149077642',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_56(): void
    {
        $this->createMakes('LEXUS')
            ->createStates('AR', 'IL', 'TN')
            ->createTimeZones('72114', '61802', '37122')
            ->sendPdfFile('wholesale_express_2_56')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246407',
                    'pickup_date' => '05/10/2021',
                    'delivery_date' => '05/11/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '2T2BZMCA6HC122899',
                            'year' => '2017',
                            'make' => 'LEXUS',
                            'model' => 'RX RX 350 AWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'POSH AUTOMOTIVE',
                        'state' => 'AR',
                        'city' => 'NORTH LITTLE ROCK',
                        'address' => '3302 E BROADWAY ST',
                        'zip' => '72114',
                        'phones' => [],
                        'phone' => '15014204474',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'NAPLETONS TOYOTA OF URBANA',
                        'state' => 'IL',
                        'city' => 'URBANA',
                        'address' => '1101 NAPLETON WAY',
                        'zip' => '61802',
                        'phones' => [],
                        'phone' => '12173671222',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_57(): void
    {
        $this->createMakes('TOYOTA')
            ->createStates('PA', 'MO', 'TN')
            ->createTimeZones('17044', '63376', '37122')
            ->sendPdfFile('wholesale_express_2_57')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '248084',
                    'pickup_date' => '05/15/2021',
                    'delivery_date' => '05/17/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => 'JTEBU5JR9K5659378',
                            'year' => '2019',
                            'make' => 'TOYOTA',
                            'model' => '4RUNNER 4WD (NATL)',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'EZ AUTO GROUP',
                        'state' => 'PA',
                        'city' => 'LEWISTOWN',
                        'address' => '10183 US HIGHWAY 522 S SUITE B',
                        'zip' => '17044',
                        'phones' => [],
                        'phone' => '17179942285',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'ED NAPLETON HONDA- MISSOURI',
                        'state' => 'MO',
                        'city' => 'SAINT PETERS',
                        'address' => '4780 N. SERVICE RD.',
                        'zip' => '63376',
                        'phones' => [],
                        'phone' => '16363233197',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_58(): void
    {
        $this->createMakes('LEXUS')
            ->createStates('IL', 'NC', 'TN')
            ->createTimeZones('60004', '27612', '37122')
            ->sendPdfFile('wholesale_express_2_58')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '248385',
                    'pickup_date' => '05/17/2021',
                    'delivery_date' => '05/18/2021',
                    'instructions' => 'CALL AHEAD OF DELIVERY GOING TO RESIDENCE NO AFTER HRS DELIVERIES WITHOUT CALLING ABE 708-983-3333',
                    'vehicles' => [
                        [
                            'vin' => 'JTHHP5AYXJA004910',
                            'year' => '2018',
                            'make' => 'LEXUS',
                            'model' => 'LC LC 500 RWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'NAPLETON OFF LEASE',
                        'state' => 'IL',
                        'city' => 'ARLINGTON HEIGHTS',
                        'address' => '3650 N WILKE RD',
                        'zip' => '60004',
                        'phones' => [],
                        'phone' => '18474266100',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'EDWARD DAVIS 1300 TRIBUTE CENTER DR',
                        'state' => 'NC',
                        'city' => 'RALEIGH',
                        'address' => 'Apt 313',
                        'zip' => '27612',
                        'phones' => [],
                        'phone' => '19193061039',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_59(): void
    {
        $this->createMakes('INFINITI')
            ->createStates('TX', 'LA', 'TN')
            ->createTimeZones('78219', '70125', '37122')
            ->sendPdfFile('wholesale_express_2_59')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246275',
                    'pickup_date' => '05/10/2021',
                    'delivery_date' => '05/11/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '5N1DL0MN5HC544059',
                            'year' => '2017',
                            'make' => 'INFINITI',
                            'model' => 'QX60 FWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM SAN ANTONIO (661944)',
                        'state' => 'TX',
                        'city' => 'SAN ANTONIO',
                        'address' => '2042 ACKERMAN ROAD',
                        'zip' => '78219',
                        'phones' => [],
                        'phone' => '12106614200',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MOSSY BUICK GMC',
                        'state' => 'LA',
                        'city' => 'NEW ORLEANS',
                        'address' => '1331 S BROAD ST',
                        'zip' => '70125',
                        'phones' => [],
                        'phone' => '15048222050',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_60(): void
    {
        $this->createMakes('NISSAN')
            ->createStates('OH', 'NE', 'TN')
            ->createTimeZones('43213', '68118', '37122')
            ->sendPdfFile('wholesale_express_2_60')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246486',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/10/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '5N1DR2MM4JC642376',
                            'year' => '2018',
                            'make' => 'NISSAN',
                            'model' => 'PATHFINDER 4X4',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'PRICE RIGHT CARS',
                        'state' => 'OH',
                        'city' => 'COLUMBUS',
                        'address' => '5100 E MAIN ST',
                        'zip' => '43213',
                        'phones' => [],
                        'phone' => '16145370163',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'NISSAN OF OMAHA',
                        'state' => 'NE',
                        'city' => 'OMAHA',
                        'address' => '17410 BURT ST',
                        'zip' => '68118',
                        'phones' => [],
                        'phone' => '14024934000',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_61(): void
    {
        $this->createMakes('VOLKSWAGEN')
            ->createStates('PA', 'MO', 'TN')
            ->createTimeZones('17545', '63122', '37122')
            ->sendPdfFile('wholesale_express_2_61')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246513',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/10/2021',
                    'instructions' => 'CALL IN ADVANCE',
                    'vehicles' => [
                        [
                            'vin' => '3VW5DAAT9KM501040',
                            'year' => '2019',
                            'make' => 'VOLKSWAGEN',
                            'model' => 'BEETLE CONVERTIBLE AUTO',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM PENNSYLVANIA (656851)',
                        'state' => 'PA',
                        'city' => 'MANHEIM',
                        'address' => '1190 LANCASTER ROAD',
                        'zip' => '17545',
                        'phones' => [],
                        'phone' => '17176653571',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'DEAN KIRKWOOD INC',
                        'state' => 'MO',
                        'city' => 'KIRKWOOD',
                        'address' => '10205 MANCHESTER RD',
                        'zip' => '63122',
                        'phones' => [],
                        'phone' => '13142200055',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_62(): void
    {
        $this->createMakes('ACURA')
            ->createStates('NC', 'IL', 'TN')
            ->createTimeZones('28625', '60642', '37122')
            ->sendPdfFile('wholesale_express_2_62')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246218',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/10/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '5J8YD4H55JL009891',
                            'year' => '2018',
                            'make' => 'ACURA',
                            'model' => 'MDX SH-AWD W/TECHNOLOGY PKG',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM STATESVILLE',
                        'state' => 'NC',
                        'city' => 'STATESVILLE',
                        'address' => '145 AUCTION LN',
                        'zip' => '28625',
                        'phones' => [],
                        'phone' => '17048761111',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MCGRATH ACURA OF DOWNTOWN CHICAGO',
                        'state' => 'IL',
                        'city' => 'CHICAGO',
                        'address' => '1301 N ELSTON AVENUE',
                        'zip' => '60642',
                        'phones' => [],
                        'phone' => '17733366300',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_63(): void
    {
        $this->createMakes('VOLVO')
            ->createStates('PA', 'NH', 'TN')
            ->createTimeZones('18103', '03101', '37122')
            ->sendPdfFile('wholesale_express_2_63')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246937',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/10/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => 'LYV402TK4JB175608',
                            'year' => '2018',
                            'make' => 'VOLVO',
                            'model' => 'S60 T5 AWD INSCRIPTION',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'SCOTT VOLVO CARS',
                        'state' => 'PA',
                        'city' => 'ALLENTOWN',
                        'address' => '3209 LEHIGH STREET.',
                        'zip' => '18103',
                        'phones' => [],
                        'phone' => '16107626247',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MERRIMACK STREET VOLVO',
                        'state' => 'NH',
                        'city' => 'MANCHESTER',
                        'address' => '40-56 MERRIMACK ST',
                        'zip' => '03101',
                        'phones' => [],
                        'phone' => '16036238015',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_64(): void
    {
        $this->createMakes('FORD')
            ->createStates('NJ', 'CO', 'TN')
            ->createTimeZones('07430', '80138', '37122')
            ->sendPdfFile('wholesale_express_2_64')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '243640',
                    'pickup_date' => '05/10/2021',
                    'delivery_date' => '05/12/2021',
                    'instructions' => 'CALL IN ADVANCE Destination is Residence MUST PICK UP DURING BUSINESS HOURS. CALL AHEAD TO SCHEDULE PICK UP. CALL 24 HOURS AHEAD TO SCHEDULE DELIVERY.',
                    'vehicles' => [
                        [
                            'vin' => '1FTFW1RG4LFA04469',
                            'year' => '2020',
                            'make' => 'FORD',
                            'model' => 'F-150 RAPTOR 4WD SUPERCREW 5.5’ BOX',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'BERGEN COUNTY AUTO GROUP',
                        'state' => 'NJ',
                        'city' => 'MAHWAH',
                        'address' => '161 FRANKLIN TURNPIKE',
                        'zip' => '07430',
                        'phones' => [],
                        'phone' => '12015006061',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'JASON FREY',
                        'state' => 'CO',
                        'city' => 'PARKER',
                        'address' => '9377 SARA GULCH WAY',
                        'zip' => '80138',
                        'phones' => [],
                        'phone' => '17202617196',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_65(): void
    {
        $this->createMakes('RAM')
            ->createStates('PA', 'CO', 'TN')
            ->createTimeZones('19440', '80923', '37122')
            ->sendPdfFile('wholesale_express_2_65')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '238685',
                    'pickup_date' => '05/10/2021',
                    'delivery_date' => '05/12/2021',
                    'instructions' => 'NO NIGHT DROPS. MUST GET SIGNATURE. NO PAY- MENT IF BOL IS NOT SIGNED.',
                    'vehicles' => [
                        [
                            'vin' => '3C6UR5GL4FG633939',
                            'year' => '2015',
                            'make' => 'RAM',
                            'model' => '2500 4WD CREW CAB 149” LONGHORN',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM PHILADELPHIA',
                        'state' => 'PA',
                        'city' => 'HATFIELD',
                        'address' => '3150 OLD BETHLEHEM PIKE',
                        'zip' => '19440',
                        'phones' => [],
                        'phone' => '12158221935',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'COLORADO SPRINGS DODGE',
                        'state' => 'CO',
                        'city' => 'COLORADO SPRINGS',
                        'address' => '7455 AUSTIN BLUFFS PKWY',
                        'zip' => '80923',
                        'phones' => [
                            [
                                'number' => '17194660263'
                            ],
                        ],
                        'phone' => '17194758550',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_66(): void
    {
        $this->createMakes('TOYOTA')
            ->createStates('IL', 'TN', 'TN')
            ->createTimeZones('60443', '37122', '37122')
            ->sendPdfFile('wholesale_express_2_66')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '248400',
                    'pickup_date' => '05/17/2021',
                    'delivery_date' => '05/18/2021',
                    'instructions' => 'DELIVERY INSTRUCTIONS: CHECK VEHICLE UNDER DEALER # 5258127 WHOLESALE INC CONFIRM GATE PASSES PRIOR TO PICKUP',
                    'vehicles' => [
                        [
                            'vin' => '4T1B11HK1KU255118',
                            'year' => '2019',
                            'make' => 'TOYOTA',
                            'model' => 'CAMRY AUTO (NATL)',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM CHICAGO (651482)',
                        'state' => 'IL',
                        'city' => 'MATTESON',
                        'address' => '20401 COX AVENUE',
                        'zip' => '60443',
                        'phones' => [],
                        'phone' => '18158064222',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MANHEIM NASHVILLE (NASH)',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8400 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [
                            [
                                'number' => '16157736558'
                            ],
                        ],
                        'phone' => '16157733800',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_67(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('FL', 'GA', 'TN')
            ->createTimeZones('33314', '30122', '37122')
            ->sendPdfFile('wholesale_express_2_67')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246837',
                    'pickup_date' => '05/07/2021',
                    'delivery_date' => '05/08/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '2GNAXTEV6K6204339',
                            'year' => '2019',
                            'make' => 'CHEVROLET',
                            'model' => 'EQUINOX AWD 4DR LT W/2FL',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM FORT LAUDERDALE',
                        'state' => 'FL',
                        'city' => 'DAVIE',
                        'address' => '5353 S STATE ROAD 7',
                        'zip' => '33314',
                        'phones' => [],
                        'phone' => '19547913520',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'AUTONATION TOYOTA THORNTON ROAD',
                        'state' => 'GA',
                        'city' => 'LITHIA SPRINGS',
                        'address' => '1000 BLAIRS BRIDGE RD',
                        'zip' => '30122',
                        'phones' => [],
                        'phone' => '17706746082',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_68(): void
    {
        $this->createMakes('CHEVROLET', 'BUICK')
            ->createStates('MO', 'KY', 'TN')
            ->createTimeZones('64153', '42420', '37122')
            ->sendPdfFile('wholesale_express_2_68')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245768',
                    'pickup_date' => '05/05/2021',
                    'delivery_date' => '05/06/2021',
                    'instructions' => 'CALL IN ADVANCE Pick hours Mon-Fri 8am-5pm. CALL AHEAD TO SCHEDULE PICK UP.',
                    'vehicles' => [
                        [
                            'vin' => '1G1ZD5ST4KF133482',
                            'year' => '2019',
                            'make' => 'CHEVROLET',
                            'model' => 'MALIBU 4DR SDN LT W/1LT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                        [
                            'vin' => 'KL4CJGSM8KB886430',
                            'year' => '2019',
                            'make' => 'BUICK',
                            'model' => 'ENCORE AWD 4DR SPORT TOURING',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'HERTZ-KANSAS CITY',
                        'state' => 'MO',
                        'city' => 'KANSAS CITY',
                        'address' => '1 NASSAU CIR',
                        'zip' => '64153',
                        'phones' => [
                            [
                                'number' => '17852152526'
                            ],
                        ],
                        'phone' => '19132319159',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'HENDERSON CHEVROLET',
                        'state' => 'KY',
                        'city' => 'HENDERSON',
                        'address' => '2746 US 41',
                        'zip' => '42420',
                        'phones' => [],
                        'phone' => '12708267600',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_69(): void
    {
        $this->createMakes('JEEP')
            ->createStates('MS', 'KY', 'TN')
            ->createTimeZones('39213', '40218', '37122')
            ->sendPdfFile('wholesale_express_2_69')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246795',
                    'pickup_date' => '05/10/2021',
                    'delivery_date' => '05/11/2021',
                    'instructions' => 'If delivering to CROSS MOTORS CORP: DELIVERY M-F 8-6 AND DROP BOX IN FRONT OF SERVICE AFT HRS',
                    'vehicles' => [
                        [
                            'vin' => '1C4HJXFN7LW292243',
                            'year' => '2020',
                            'make' => 'JEEP',
                            'model' => 'WRANGLER UNLIMITED RUBICON 4X4',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MOTORCARS OF JACKSON',
                        'state' => 'MS',
                        'city' => 'JACKSON',
                        'address' => '6105 I 55 N',
                        'zip' => '39213',
                        'phones' => [],
                        'phone' => '17692438568',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'CROSS MOTORS CORP',
                        'state' => 'KY',
                        'city' => 'LOUISVILLE',
                        'address' => '1501 GARDINER LN',
                        'zip' => '40218',
                        'phones' => [],
                        'phone' => '15024599900',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_70(): void
    {
        $this->createMakes('TOYOTA')
            ->createStates('OK', 'TN', 'TN')
            ->createTimeZones('73110', '37122', '37122')
            ->sendPdfFile('wholesale_express_2_70')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '247783',
                    'pickup_date' => '05/18/2021',
                    'delivery_date' => '05/19/2021',
                    'instructions' => 'DELIVERY INSTRUCTIONS: CHECK IN UNDER WHOLE- SALE INC #5258127 CONFIRM GATE PASSES PRIOR TO PICKUP',
                    'vehicles' => [
                        [
                            'vin' => '4T1B11HK8KU171815',
                            'year' => '2019',
                            'make' => 'TOYOTA',
                            'model' => 'CAMRY AUTO (NATL)',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'HUDIBURG CHEVROLET LLC',
                        'state' => 'OK',
                        'city' => 'MIDWEST CITY',
                        'address' => '6000 TINKER DIAGONAL',
                        'zip' => '73110',
                        'phones' => [],
                        'phone' => '18446323329',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MANHEIM NASHVILLE (NASH)',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8400 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [
                            [
                                'number' => '16157736558'
                            ],
                        ],
                        'phone' => '16157733800',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_71(): void
    {
        $this->createMakes('GMC')
            ->createStates('AL', 'SC', 'TN')
            ->createTimeZones('35124', '29461', '37122')
            ->sendPdfFile('wholesale_express_2_71')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246725',
                    'pickup_date' => '05/10/2021',
                    'delivery_date' => '05/11/2021',
                    'instructions' => 'CALL IN ADVANCE MUST PICK UP DURING BUSINESS HOURS. CALL AHEAD TO SCHEDULE PICK UP. NO NIGHT DROPS. MUST GET SIGNATURE. NO PAY- MENT IF BOL IS NOT SIGNED.Recon lot is in the back of the DMV just through the gates right behind it. They are across the street from Sonic/Burger King. DO NOT DELIVER TO TRIDENT PAIN CENTER. DO NOT LEAVE KEY IN THE VEHICLE',
                    'vehicles' => [
                        [
                            'vin' => '1GT49PEY2LF212141',
                            'year' => '2020',
                            'make' => 'GMC',
                            'model' => 'SIERRA 2500HD 4WD CREW CAB 159” AT4',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'EXCLUSIVE AUTO WHOLESALE',
                        'state' => 'AL',
                        'city' => 'PELHAM',
                        'address' => '196 CHANDALAR PLACE DR',
                        'zip' => '35124',
                        'phones' => [],
                        'phone' => '12054065644',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'DMV/RECON CENTER',
                        'state' => 'SC',
                        'city' => 'MONCKS CORNER',
                        'address' => '108-B S, N U.S. HWY 52',
                        'zip' => '29461',
                        'phones' => [],
                        'phone' => '18438091046',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_72(): void
    {
        $this->createMakes('FORD')
            ->createStates('AL', 'SC', 'TN')
            ->createTimeZones('35004', '29461', '37122')
            ->sendPdfFile('wholesale_express_2_72')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246730',
                    'pickup_date' => '05/10/2021',
                    'delivery_date' => '05/11/2021',
                    'instructions' => 'NO NIGHT DROPS. MUST GET SIGNATURE. NO PAY- MENT IF BOL IS NOT SIGNED.Recon lot is in the back of the DMV just through the gates right behind it. They are across the street from Sonic/Burger King. DO NOT DELIVER TO TRIDENT PAIN CENTER. DO NOT LEAVE KEY IN THE VEHICLE',
                    'vehicles' => [
                        [
                            'vin' => '3FMCR9B61MRA20977',
                            'year' => '2021',
                            'make' => 'FORD',
                            'model' => 'BRONCO SPORT BIG BEND 4X4',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'AMERICA’S AUTO AUCTION BIRMINGHAM (TESTLOCB)',
                        'state' => 'AL',
                        'city' => 'MOODY',
                        'address' => '1046 AE MOORE DRIVE',
                        'zip' => '35004',
                        'phones' => [],
                        'phone' => '12056404040',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'DMV/RECON CENTER',
                        'state' => 'SC',
                        'city' => 'MONCKS CORNER',
                        'address' => '108-B S, N U.S. HWY 52',
                        'zip' => '29461',
                        'phones' => [],
                        'phone' => '18438091046',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_73(): void
    {
        $this->createMakes('GMC')
            ->createStates('AL', 'SC', 'TN')
            ->createTimeZones('35210', '29461', '37122')
            ->sendPdfFile('wholesale_express_2_73')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246661',
                    'pickup_date' => '05/10/2021',
                    'delivery_date' => '05/11/2021',
                    'instructions' => 'NO NIGHT DROPS. MUST GET SIGNATURE. NO PAY- MENT IF BOL IS NOT SIGNED.Recon lot is in the back of the DMV just through the gates right behind it. They are across the street from Sonic/Burger King. DO NOT DELIVER TO TRIDENT PAIN CENTER. DO NOT LEAVE KEY IN THE VEHICLE',
                    'vehicles' => [
                        [
                            'vin' => '1GTP9EED6LZ183188',
                            'year' => '2020',
                            'make' => 'GMC',
                            'model' => 'SIERRA 1500 4WD CREW CAB AT4',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM BIRMINGHAM (648692)',
                        'state' => 'AL',
                        'city' => 'BIRMINGHAM',
                        'address' => '5750 US-78',
                        'zip' => '35210',
                        'phones' => [],
                        'phone' => '12059564700',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'DMV/RECON CENTER',
                        'state' => 'SC',
                        'city' => 'MONCKS CORNER',
                        'address' => '108-B S, N U.S. HWY 52',
                        'zip' => '29461',
                        'phones' => [],
                        'phone' => '18438091046',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_74(): void
    {
        $this->createMakes('INFINITI')
            ->createStates('IL', 'OK', 'TN')
            ->createTimeZones('60443', '74133', '37122')
            ->sendPdfFile('wholesale_express_2_74')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245267',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/10/2021',
                    'instructions' => 'CALL IN ADVANCE *MUST NOTE HOW MANY KEYS AT PICK ON BOL',
                    'vehicles' => [
                        [
                            'vin' => '5N1DL0MM8JC507861',
                            'year' => '2018',
                            'make' => 'INFINITI',
                            'model' => 'QX60 AWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM CHICAGO (651482)',
                        'state' => 'IL',
                        'city' => 'MATTESON',
                        'address' => '20401 COX AVENUE',
                        'zip' => '60443',
                        'phones' => [],
                        'phone' => '18158064222',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'JACKIE COOPER INFINITI',
                        'state' => 'OK',
                        'city' => 'TULSA',
                        'address' => '8825 S MEMORIAL DR',
                        'zip' => '74133',
                        'phones' => [],
                        'phone' => '19182499393',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_75(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('IL', 'NY', 'TN')
            ->createTimeZones('60007', '12569', '37122')
            ->sendPdfFile('wholesale_express_2_75')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245793',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/11/2021',
                    'instructions' => 'READY 5/5 AFTER 12 PM If Picking up from CASTLE CHEVROLET NORTH: PICK M-F 8-8 SAT 8-5 MUST CALL BILL 2 HRS AHEAD OF PICKUP OR WILL NOT RELEASE 847-954-9511 NO AFTER HOURS DELIVERIES OR YOU WILL NOT BE PAID',
                    'vehicles' => [
                        [
                            'vin' => '1GCWGAFP7M1229100',
                            'year' => '2021',
                            'make' => 'CHEVROLET',
                            'model' => 'EXPRESS CARGO VAN LADDER RACK RWD 2500 135”',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'CASTLE CHEVROLET NORTH',
                        'state' => 'IL',
                        'city' => 'ELK GROVE VILLAGE',
                        'address' => '175 N. ARLINGTON HEIGHTS ROAD',
                        'zip' => '60007',
                        'phones' => [
                            [
                                'number' => '18475934614'
                            ],
                        ],
                        'phone' => '18479549511',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'JIM SERRA HEATING & COOLING LLC',
                        'state' => 'NY',
                        'city' => 'PLEASANT VALLEY',
                        'address' => '392 MASTEN RD',
                        'zip' => '12569',
                        'phones' => [],
                        'phone' => '18452264459',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_76(): void
    {
        $this->createMakes('TOYOTA')
            ->createStates('FL', 'TN', 'TN')
            ->createTimeZones('32219', '37122', '37122')
            ->sendPdfFile('wholesale_express_2_76')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245066',
                    'pickup_date' => '05/07/2021',
                    'delivery_date' => '05/09/2021',
                    'instructions' => 'DELIVERY INSTRUCTIONS: CHECK VEHICLE UNDER DEALER # 5258127 WHOLESALE INC CONFIRM GATE PASSES PRIOR TO PICKUP',
                    'vehicles' => [
                        [
                            'vin' => '4T1BD1FK6GU184354',
                            'year' => '2016',
                            'make' => 'TOYOTA',
                            'model' => 'CAMRY 4DR SDN (NATL)',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'ADESA JACKSONVILLE (649606)',
                        'state' => 'FL',
                        'city' => 'JACKSONVILLE',
                        'address' => '11700 NEW KINGS ROAD',
                        'zip' => '32219',
                        'phones' => [],
                        'phone' => '19047641004',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MANHEIM NASHVILLE (NASH)',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8400 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [
                            [
                                'number' => '16157736558'
                            ],
                        ],
                        'phone' => '16157733800',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_77(): void
    {
        $this->createMakes('NISSAN')
            ->createStates('NM', 'OK', 'TN')
            ->createTimeZones('87507', '73119', '37122')
            ->sendPdfFile('wholesale_express_2_77')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245624',
                    'pickup_date' => '05/10/2021',
                    'delivery_date' => '05/11/2021',
                    'instructions' => 'CALL IN ADVANCE release attached',
                    'vehicles' => [
                        [
                            'vin' => '1N6AD0EV6KN716708',
                            'year' => '2019',
                            'make' => 'NISSAN',
                            'model' => 'FRONTIER CREW CAB 4X4',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'RIGHT CAR AUTO SALES LLC',
                        'state' => 'NM',
                        'city' => 'SANTA FE',
                        'address' => '2865 CERRILLOS RD',
                        'zip' => '87507',
                        'phones' => [],
                        'phone' => '15056032390',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'AUTO STAR LLC',
                        'state' => 'OK',
                        'city' => 'OKLAHOMA CITY',
                        'address' => '1416 SW 29TH ST',
                        'zip' => '73119',
                        'phones' => [],
                        'phone' => '14056019303',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_78(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('IL', 'MO', 'TN')
            ->createTimeZones('61554', '64118', '37122')
            ->sendPdfFile('wholesale_express_2_78')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245209',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/09/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1GCUKREC5HF114927',
                            'year' => '2017',
                            'make' => 'CHEVROLET',
                            'model' => 'SILVERADO 1500 4WD CREW CAB LT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MID-ILLINI AUTO CENTER',
                        'state' => 'IL',
                        'city' => 'NORTH PEKIN',
                        'address' => '1139 WESLEY RD',
                        'zip' => '61554',
                        'phones' => [],
                        'phone' => '13096426750',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'VAN CHEVROLET-CADILLAC',
                        'state' => 'MO',
                        'city' => 'KANSAS CITY',
                        'address' => '90 NW VIVION RD',
                        'zip' => '64118',
                        'phones' => [
                            [
                                'number' => '16025027893'
                            ],
                        ],
                        'phone' => '18165333385',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_79(): void
    {
        $this->createMakes('FORD')
            ->createStates('MD', 'SC', 'TN')
            ->createTimeZones('21047', '29461', '37122')
            ->sendPdfFile('wholesale_express_2_79')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '247099',
                    'pickup_date' => '05/19/2021',
                    'delivery_date' => '05/20/2021',
                    'instructions' => 'CALL IN ADVANCE MUST PICK UP DURING BUSINESS HOURS. CALL AHEAD TO SCHEDULE PICK UP. NO NIGHT DROPS. MUST GET SIGNATURE. NO PAY- MENT IF BOL IS NOT SIGNED.Lawhorns Drop hours M- F 8a-5p.',
                    'vehicles' => [
                        [
                            'vin' => '1FT7W2BT2GED21549',
                            'year' => '2016',
                            'make' => 'FORD',
                            'model' => 'SUPER DUTY F-250 SRW 4WD CREW CAB',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'AUTO SHOWCASE OF BEL AIR',
                        'state' => 'MD',
                        'city' => 'FALLSTON',
                        'address' => '2308 BELAIR RD',
                        'zip' => '21047',
                        'phones' => [],
                        'phone' => '14108770036',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'LAWHORNS MACHINE SHOP',
                        'state' => 'SC',
                        'city' => 'MONCKS CORNER',
                        'address' => '3550 S LIVE OAK DR',
                        'zip' => '29461',
                        'phones' => [],
                        'phone' => '18437614456',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_80(): void
    {
        $this->createMakes('GMC')
            ->createStates('TX', 'MS', 'TN')
            ->createTimeZones('75236', '38901', '37122')
            ->sendPdfFile('wholesale_express_2_80')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246301',
                    'pickup_date' => '05/17/2021',
                    'delivery_date' => '05/18/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '3GTU2NEC4JG310449',
                            'year' => '2018',
                            'make' => 'GMC',
                            'model' => 'SIERRA 1500 4WD CREW CAB 143.5” SLT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM DALLAS',
                        'state' => 'TX',
                        'city' => 'DALLAS',
                        'address' => '5333 W KIEST BLVD',
                        'zip' => '75236',
                        'phones' => [],
                        'phone' => '12143301800',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'SUNSET CHRYSLER DODGE & JEEP',
                        'state' => 'MS',
                        'city' => 'GRENADA',
                        'address' => '1463 COMMERCE STREET',
                        'zip' => '38901',
                        'phones' => [],
                        'phone' => '16622265124',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_81(): void
    {
        $this->createMakes('JEEP')
            ->createStates('OH', 'IL', 'TN')
            ->createTimeZones('44212', '60438', '37122')
            ->sendPdfFile('wholesale_express_2_81')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246087',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/10/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1C4PJLLBXKD197040',
                            'year' => '2019',
                            'make' => 'JEEP',
                            'model' => 'CHEROKEE LATITUDE PLUS FWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'BRUNSWICK AUTO MART',
                        'state' => 'OH',
                        'city' => 'BRUNSWICK',
                        'address' => '3031 CENTER RD',
                        'zip' => '44212',
                        'phones' => [],
                        'phone' => '13302733300',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'NAPLETONS RIVER OAKS HONDA',
                        'state' => 'IL',
                        'city' => 'LANSING',
                        'address' => '17220 TORRENCE AVE',
                        'zip' => '60438',
                        'phones' => [],
                        'phone' => '17088680100',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_82(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('IL', 'PA', 'TN')
            ->createTimeZones('60172', '19047', '37122')
            ->sendPdfFile('wholesale_express_2_82')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '248364',
                    'pickup_date' => '05/17/2021',
                    'delivery_date' => '05/18/2021',
                    'instructions' => 'pickup are 8:00 - 5:00 M-F If Picking up from CASTLE CHEVROLET NORTH: PICK M-F 8-8 SAT 8-5 MUST CALL BILL 2 HRS AHEAD OF PICKUP OR WILL NOT RELEASE 847-954-9511 NO AFTER HOURS DELIVERIES OR YOU WILL NOT BE PAID',
                    'vehicles' => [
                        [
                            'vin' => '1GCZGHF71M1195323',
                            'year' => '2021',
                            'make' => 'CHEVROLET',
                            'model' => 'EXPRESS CARGO VAN RWD 3500 155”',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'JON-DON INC',
                        'state' => 'IL',
                        'city' => 'ROSELLE',
                        'address' => '400 MEDINAH RD',
                        'zip' => '60172',
                        'phones' => [],
                        'phone' => '16308656135',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'JON-DON INC',
                        'state' => 'PA',
                        'city' => 'LANGHORNE',
                        'address' => '835 WHEELER WAY #B',
                        'zip' => '19047',
                        'phones' => [],
                        'phone' => '12157415805',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_83(): void
    {
        $this->createMakes('VOLVO')
            ->createStates('GA', 'NJ', 'TN')
            ->createTimeZones('31525', '07901', '37122')
            ->sendPdfFile('wholesale_express_2_83')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '244598',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/10/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => 'YV4940ND1F1197963',
                            'year' => '2015',
                            'make' => 'VOLVO',
                            'model' => 'XC70 AWD 4DR WGN 3.2L PLATINUM PZEV',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MIKE MURPHY KIA OF BRUNSWICK',
                        'state' => 'GA',
                        'city' => 'BRUNSWICK',
                        'address' => '6150 ALTAMA AVE',
                        'zip' => '31525',
                        'phones' => [],
                        'phone' => '19123424190',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'SMYTHE VOLVO',
                        'state' => 'NJ',
                        'city' => 'SUMMIT',
                        'address' => '40 RIVER RD',
                        'zip' => '07901',
                        'phones' => [],
                        'phone' => '19082734200',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_84(): void
    {
        $this->createMakes('FORD')
            ->createStates('PA', 'OK', 'TN')
            ->createTimeZones('16066', '73762', '37122')
            ->sendPdfFile('wholesale_express_2_84')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245687',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/10/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1FTFW1RG5LFB43364',
                            'year' => '2020',
                            'make' => 'FORD',
                            'model' => 'F-150 RAPTOR 4WD SUPERCREW 5.5’ BOX',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM PITTSBURGH (PITTS)',
                        'state' => 'PA',
                        'city' => 'CRANBERRY TOWNSHIP',
                        'address' => '21095 US-19',
                        'zip' => '16066',
                        'phones' => [],
                        'phone' => '17244525555',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MAINER FORD',
                        'state' => 'OK',
                        'city' => 'OKARCHE',
                        'address' => '1724 234TH ST NW',
                        'zip' => '73762',
                        'phones' => [],
                        'phone' => '18884869859',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_85(): void
    {
        $this->createMakes('TOYOTA')
            ->createStates('PA', 'AL', 'TN')
            ->createTimeZones('16101', '35218', '37122')
            ->sendPdfFile('wholesale_express_2_85')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245666',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/10/2021',
                    'instructions' => 'CALL IN ADVANCE MUST PICK UP DURING BUSINESS HOURS. CALL AHEAD TO SCHEDULE PICK UP',
                    'vehicles' => [
                        [
                            'vin' => '4T1G11AK3MU417804',
                            'year' => '2021',
                            'make' => 'TOYOTA',
                            'model' => 'CAMRY SE AUTO (NATL)',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'CAR CONNECTION',
                        'state' => 'PA',
                        'city' => 'NEW CASTLE',
                        'address' => '2757 W STATE ST',
                        'zip' => '16101',
                        'phones' => [],
                        'phone' => '17246581212',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'LIMBAUGH TOYOTA',
                        'state' => 'AL',
                        'city' => 'BIRMINGHAM',
                        'address' => '2200 AVENUE T',
                        'zip' => '35218',
                        'phones' => [],
                        'phone' => '12057800500',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_86(): void
    {
        $this->createMakes('HONDA')
            ->createStates('IL', 'TN', 'TN')
            ->createTimeZones('60443', '37122', '37122')
            ->sendPdfFile('wholesale_express_2_86')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246151',
                    'pickup_date' => '05/07/2021',
                    'delivery_date' => '05/08/2021',
                    'instructions' => 'DELIVERY INSTRUCTIONS: CHECK VEHICLE UNDER DEALER # 5258127 WHOLESALE INC CONFIRM GATE PASSES PRIOR TO PICKUP',
                    'vehicles' => [
                        [
                            'vin' => '2HKRW6H3XJH209998',
                            'year' => '2018',
                            'make' => 'HONDA',
                            'model' => 'CR-V LX AWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM CHICAGO (651482)',
                        'state' => 'IL',
                        'city' => 'MATTESON',
                        'address' => '20401 COX AVENUE',
                        'zip' => '60443',
                        'phones' => [],
                        'phone' => '18158064222',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MANHEIM NASHVILLE (NASH)',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8400 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [
                            [
                                'number' => '16157736558'
                            ],
                        ],
                        'phone' => '16157733800',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_87(): void
    {
        $this->createMakes('GMC')
            ->createStates('NC', 'TN', 'TN')
            ->createTimeZones('28403', '37122', '37122')
            ->sendPdfFile('wholesale_express_2_87')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246134',
                    'pickup_date' => '05/07/2021',
                    'delivery_date' => '05/08/2021',
                    'instructions' => 'DELIVERY INSTRUCTIONS: CHECK VEHICLE UNDER DEALER # 5258127 WHOLESALE INC CONFIRM GATE PASSES PRIOR TO PICKUP',
                    'vehicles' => [
                        [
                            'vin' => '3GTU2PEJ2HG511061',
                            'year' => '2017',
                            'make' => 'GMC',
                            'model' => 'SIERRA 1500 4WD CREW CAB 153.0” DENALI',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'SEASELL AUTO',
                        'state' => 'NC',
                        'city' => 'WILMINGTON',
                        'address' => '209 OLD EASTWOOD RD',
                        'zip' => '28403',
                        'phones' => [],
                        'phone' => '19103992995',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MANHEIM NASHVILLE (NASH)',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8400 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [
                            [
                                'number' => '16157736558'
                            ],
                        ],
                        'phone' => '16157733800',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_88(): void
    {
        $this->createMakes('JEEP')
            ->createStates('OH', 'LA', 'TN')
            ->createTimeZones('43123', '71201', '37122')
            ->sendPdfFile('wholesale_express_2_88')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246522',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/10/2021',
                    'instructions' => 'Gate passes under Customer Name',
                    'vehicles' => [
                        [
                            'vin' => '3C4NJDCB4JT397997',
                            'year' => '2018',
                            'make' => 'JEEP',
                            'model' => 'COMPASS LIMITED 4X4',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM OHIO',
                        'state' => 'OH',
                        'city' => 'GROVE CITY',
                        'address' => '3905 JACKSON PIKE',
                        'zip' => '43123',
                        'phones' => [],
                        'phone' => '16148712771',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'LEE EDWARDS MAZDA',
                        'state' => 'LA',
                        'city' => 'MONROE',
                        'address' => '2218 LOUISVILLE AVE',
                        'zip' => '71201',
                        'phones' => [],
                        'phone' => '13183883540',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_89(): void
    {
        $this->createMakes('JEEP')
            ->createStates('PA', 'TN', 'TN')
            ->createTimeZones('19320', '37122', '37122')
            ->sendPdfFile('wholesale_express_2_89')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '241579',
                    'pickup_date' => '05/07/2021',
                    'delivery_date' => '05/08/2021',
                    'instructions' => 'DELIVERY INSTRUCTIONS: CHECK VEHICLE UNDER DEALER # 5258127 WHOLESALE INC CONFIRM GATE PASSES PRIOR TO PICKUP',
                    'vehicles' => [
                        [
                            'vin' => '1C4HJWEGXJL822944',
                            'year' => '2018',
                            'make' => 'JEEP',
                            'model' => 'WRANGLER JK SAHARA 4X4',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'BRIAN HOSKINS FORD',
                        'state' => 'PA',
                        'city' => 'COATESVILLE',
                        'address' => '2601 E LINCOLN HWY',
                        'zip' => '19320',
                        'phones' => [],
                        'phone' => '16103844242',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MANHEIM NASHVILLE (NASH)',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8400 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [
                            [
                                'number' => '16157736558'
                            ],
                        ],
                        'phone' => '16157733800',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_90(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('TX', 'AR', 'TN')
            ->createTimeZones('75141', '72301', '37122')
            ->sendPdfFile('wholesale_express_2_90')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '248044',
                    'pickup_date' => '05/17/2021',
                    'delivery_date' => '05/18/2021',
                    'instructions' => 'RELEASE ATTACHED, CALL AHEAD FOR PICK UP. If picking up from ADESA DALLAS: Vehicle pickup: Mon. á Fri. 8:30 a.m. á 5:00 p.m',
                    'vehicles' => [
                        [
                            'vin' => '3GCNWAEF3LG283372',
                            'year' => '2020',
                            'make' => 'CHEVROLET',
                            'model' => 'SILVERADO 1500 2WD REG CAB 140” WORK TRUCK',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'ADESA DALLAS',
                        'state' => 'TX',
                        'city' => 'HUTCHINS',
                        'address' => '3501 LANCASTER-HUTCHINS RD',
                        'zip' => '75141',
                        'phones' => [],
                        'phone' => '19722256000',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'FORD OF WEST MEMPHIS',
                        'state' => 'AR',
                        'city' => 'WEST MEMPHIS',
                        'address' => '2400 N SERVICE RD',
                        'zip' => '72301',
                        'phones' => [],
                        'phone' => '18707359800',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_91(): void
    {
        $this->createMakes('HONDA')
            ->createStates('PA', 'TN', 'TN')
            ->createTimeZones('19382', '37122', '37122')
            ->sendPdfFile('wholesale_express_2_91')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246162',
                    'pickup_date' => '05/07/2021',
                    'delivery_date' => '05/08/2021',
                    'instructions' => 'DELIVERY INSTRUCTIONS: CHECK VEHICLE UNDER DEALER # 5258127 WHOLESALE INC CONFIRM GATE PASSES PRIOR TO PICKUP',
                    'vehicles' => [
                        [
                            'vin' => '1HGCV1F19JA061459',
                            'year' => '2018',
                            'make' => 'HONDA',
                            'model' => 'ACCORD LX CVT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'SKY MOTOR CARS',
                        'state' => 'PA',
                        'city' => 'WEST CHESTER',
                        'address' => '969 S MATLACK ST',
                        'zip' => '19382',
                        'phones' => [],
                        'phone' => '16106367284',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MANHEIM NASHVILLE (NASH)',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8400 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [
                            [
                                'number' => '16157736558'
                            ],
                        ],
                        'phone' => '16157733800',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_92(): void
    {
        $this->createMakes('INFINITI')
            ->createStates('FL', 'GA', 'TN')
            ->createTimeZones('33331', '31210', '37122')
            ->sendPdfFile('wholesale_express_2_92')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '244345',
                    'pickup_date' => '05/07/2021',
                    'delivery_date' => '05/08/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '3PCAJ5M31KF108716',
                            'year' => '2019',
                            'make' => 'INFINITI',
                            'model' => 'QX50 ESSENTIAL AWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'WESTON VOLVO CARS',
                        'state' => 'FL',
                        'city' => 'DAVIE',
                        'address' => '3650 WESTON RD',
                        'zip' => '33331',
                        'phones' => [],
                        'phone' => '18669049210',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'NAPLETON INFINITI OF MACON',
                        'state' => 'GA',
                        'city' => 'MACON',
                        'address' => '4763 RIVERSIDE DR',
                        'zip' => '31210',
                        'phones' => [],
                        'phone' => '14787870008',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_93(): void
    {
        $this->createMakes('MAZDA')
            ->createStates('IA', 'IN', 'TN')
            ->createTimeZones('51534', '46123', '37122')
            ->sendPdfFile('wholesale_express_2_93')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '247912',
                    'pickup_date' => '05/18/2021',
                    'delivery_date' => '05/19/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => 'JM1BN1W39H1104154',
                            'year' => '2017',
                            'make' => 'MAZDA',
                            'model' => 'MAZDA3 4-DOOR GRAND TOURING',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'BEST FINISH AUTO',
                        'state' => 'IA',
                        'city' => 'GLENWOOD',
                        'address' => '209 SHARP ST',
                        'zip' => '51534',
                        'phones' => [],
                        'phone' => '17128001188',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'ANDY MOHR VOLKSWAGEN OF AVON',
                        'state' => 'IN',
                        'city' => 'AVON',
                        'address' => '8791 E US HIGHWAY 36',
                        'zip' => '46123',
                        'phones' => [],
                        'phone' => '13172794788',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_94(): void
    {
        $this->createMakes('FORD')
            ->createStates('MO', 'OK', 'TN')
            ->createTimeZones('63125', '73119', '37122')
            ->sendPdfFile('wholesale_express_2_94')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '248201',
                    'pickup_date' => '05/17/2021',
                    'delivery_date' => '05/18/2021',
                    'instructions' => 'CALL IN ADVANCE RELEASE ATTACHED INOP',
                    'vehicles' => [
                        [
                            'vin' => '3FAHP0HA2AR201739',
                            'year' => '2010',
                            'make' => 'FORD',
                            'model' => 'FUSION 4DR SDN SE FWD {INOP',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'DAVE SINCLAIR FORD',
                        'state' => 'MO',
                        'city' => 'SAINT LOUIS',
                        'address' => '7466 S LINDBERGH BLVD',
                        'zip' => '63125',
                        'phones' => [],
                        'phone' => '13148922600',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'AUTO START LLC',
                        'state' => 'OK',
                        'city' => 'OKLAHOMA CITY',
                        'address' => '1416 SW 29TH STREET',
                        'zip' => '73119',
                        'phones' => [],
                        'phone' => '14052128542',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_95(): void
    {
        $this->createMakes('FORD')
            ->createStates('NJ', 'TN', 'TN')
            ->createTimeZones('08505', '37129', '37122')
            ->sendPdfFile('wholesale_express_2_95')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245146',
                    'pickup_date' => '05/06/2021',
                    'delivery_date' => '05/08/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '2FMPK4J8XHBC39758',
                            'year' => '2017',
                            'make' => 'FORD',
                            'model' => 'EDGE SEL AWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM NEW JERSEY (MNJ)',
                        'state' => 'NJ',
                        'city' => 'BORDENTOWN',
                        'address' => '730 RT 68',
                        'zip' => '08505',
                        'phones' => [],
                        'phone' => '16092983400',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'FORD OF MURFREESBORO',
                        'state' => 'TN',
                        'city' => 'MURFREESBORO',
                        'address' => '1550 N W BROAD STREET',
                        'zip' => '37129',
                        'phones' => [],
                        'phone' => '16157131436',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_96(): void
    {
        $this->createMakes('VOLKSWAGEN')
            ->createStates('NC', 'IL', 'TN')
            ->createTimeZones('27215', '60438', '37122')
            ->sendPdfFile('wholesale_express_2_96')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246335',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/10/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '3VV2B7AX6JM072893',
                            'year' => '2018',
                            'make' => 'VOLKSWAGEN',
                            'model' => 'TIGUAN 2.0T 4MOTION',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MODERN CHEVROLET CADILLAC OF BURLINGTON',
                        'state' => 'NC',
                        'city' => 'BURLINGTON',
                        'address' => '2616 ALAMANCE RD',
                        'zip' => '27215',
                        'phones' => [],
                        'phone' => '13362295501',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'NAPLETONS RIVER OAKS HONDA',
                        'state' => 'IL',
                        'city' => 'LANSING',
                        'address' => '17220 TORRENCE AVE',
                        'zip' => '60438',
                        'phones' => [],
                        'phone' => '17088680100',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_97(): void
    {
        $this->createMakes('LINCOLN')
            ->createStates('NC', 'IL', 'TN')
            ->createTimeZones('27405', '60438', '37122')
            ->sendPdfFile('wholesale_express_2_97')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246340',
                    'pickup_date' => '05/08/2021',
                    'delivery_date' => '05/10/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '5LMCJ2C97HUL31204',
                            'year' => '2017',
                            'make' => 'LINCOLN',
                            'model' => 'MKC SELECT FWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'BILL BLACK CHEVROLET CADILLAC INC',
                        'state' => 'NC',
                        'city' => 'GREENSBORO',
                        'address' => '601 E BESSEMER AVE',
                        'zip' => '27405',
                        'phones' => [
                            [
                                'number' => '13363377614'
                            ],
                        ],
                        'phone' => '13364519473',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'NAPLETONS RIVER OAKS HONDA',
                        'state' => 'IL',
                        'city' => 'LANSING',
                        'address' => '17220 TORRENCE AVE',
                        'zip' => '60438',
                        'phones' => [],
                        'phone' => '17088680100',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_98(): void
    {
        $this->createMakes('HONDA')
            ->createStates('AL', 'IN', 'TN')
            ->createTimeZones('35570', '46123', '37122')
            ->sendPdfFile('wholesale_express_2_98')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '244377',
                    'pickup_date' => '05/06/2021',
                    'delivery_date' => '05/07/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '5FNRL5H3XGB050295',
                            'year' => '2016',
                            'make' => 'HONDA',
                            'model' => 'ODYSSEY 5DR SE',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'LAMB MOTOR SHOP',
                        'state' => 'AL',
                        'city' => 'HAMILTON',
                        'address' => '4234 STATE HWY',
                        'zip' => '35570',
                        'phones' => [],
                        'phone' => '12059215364',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'ANDY MOHR KIA',
                        'state' => 'IN',
                        'city' => 'AVON',
                        'address' => '8789 E US HIGHWAY 36',
                        'zip' => '46123',
                        'phones' => [],
                        'phone' => '13176509221',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_99(): void
    {
        $this->createMakes('FORD')
            ->createStates('WV', 'TN', 'TN')
            ->createTimeZones('26362', '37122', '37122')
            ->sendPdfFile('wholesale_express_2_99')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '240868',
                    'pickup_date' => '05/06/2021',
                    'delivery_date' => '05/07/2021',
                    'instructions' => 'DELIVERY INSTRUCTIONS: CHECK VEHICLE UNDER DEALER # 5258127 WHOLESALE INC CONFIRM GATE PASSES PRIOR TO PICKUP',
                    'vehicles' => [
                        [
                            'vin' => '1FTFW1E4XKFA43092',
                            'year' => '2019',
                            'make' => 'FORD',
                            'model' => 'F150 4WD SUPERCREW BOX',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MCDONALD MOTOR CO',
                        'state' => 'WV',
                        'city' => 'HARRISVILLE',
                        'address' => '44 SKYLINE DR',
                        'zip' => '26362',
                        'phones' => [],
                        'phone' => '13046434883',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MANHEIM NASHVILLE (NASH)',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8400 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [
                            [
                                'number' => '16157736558'
                            ],
                        ],
                        'phone' => '16157733800',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_100(): void
    {
        $this->createMakes('RAM')
            ->createStates('TN', 'IL', 'TN')
            ->createTimeZones('37122', '60050', '37122')
            ->sendPdfFile('wholesale_express_2_100')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '246297',
                    'pickup_date' => '05/07/2021',
                    'delivery_date' => '05/08/2021',
                    'instructions' => 'CALL IN ADVANCE CONFIRM GATE PASSES PRIOR TO PICKUP',
                    'vehicles' => [
                        [
                            'vin' => '1C6SRFFT2MN559892',
                            'year' => '2021',
                            'make' => 'RAM',
                            'model' => '1500 4X4 CREW CAB BOX',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM NASHVILLE (NASH)',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8400 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [
                            [
                                'number' => '16157736558'
                            ],
                        ],
                        'phone' => '16157733800',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'AUTO VILLA OUTLET',
                        'state' => 'IL',
                        'city' => 'MCHENRY',
                        'address' => '3017 IL-120',
                        'zip' => '60050',
                        'phones' => [
                            [
                                'number' => '12245886625'
                            ],
                        ],
                        'phone' => '18153222340',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_101(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('PA', 'TN', 'TN')
            ->createTimeZones('19063', '37122', '37122')
            ->sendPdfFile('wholesale_express_2_101')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '247428',
                    'pickup_date' => '05/15/2021',
                    'delivery_date' => '05/17/2021',
                    'instructions' => 'DELIVERY INSTRUCTIONS: CHECK VEHICLE UNDER DEALER # 5258127 WHOLESALE INC CONFIRM GATE PASSES PRIOR TO PICKUP',
                    'vehicles' => [
                        [
                            'vin' => '3GNAXJEV2JS642442',
                            'year' => '2018',
                            'make' => 'CHEVROLET',
                            'model' => 'EQUINOX FWD 4DR LT W/1LT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'THOMAS CHEVROLET',
                        'state' => 'PA',
                        'city' => 'MEDIA',
                        'address' => '1263 W BALTIMORE PIKE',
                        'zip' => '19063',
                        'phones' => [],
                        'phone' => '16105668600',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MANHEIM NASHVILLE (NASH)',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8400 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [
                            [
                                'number' => '16157736558'
                            ],
                        ],
                        'phone' => '16157733800',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_102(): void
    {
        $this->createMakes('HYUNDAI')
            ->createStates('AR', 'MS', 'TN')
            ->createTimeZones('72712', '39120', '37122')
            ->sendPdfFile('wholesale_express_2_102')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245522',
                    'pickup_date' => '05/07/2021',
                    'delivery_date' => '05/08/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '3KPC24A32KE044521',
                            'year' => '2019',
                            'make' => 'HYUNDAI',
                            'model' => 'ACCENT SEDAN',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'CRAIN HYUNDAI OF BENTONVILLE',
                        'state' => 'AR',
                        'city' => 'BENTONVILLE',
                        'address' => '3000 SE MOBERLY LANE',
                        'zip' => '72712',
                        'phones' => [],
                        'phone' => '14796960800',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MISSISSIPPI AUTO DIRECT',
                        'state' => 'MS',
                        'city' => 'NATCHEZ',
                        'address' => '250 JOHN R JUNKIN DR',
                        'zip' => '39120',
                        'phones' => [],
                        'phone' => '18443278898',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_103(): void
    {
        $this->createMakes('JEEP')
            ->createStates('PA', 'OH', 'TN')
            ->createTimeZones('18951', '45014', '37122')
            ->sendPdfFile('wholesale_express_2_103')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245458',
                    'pickup_date' => '05/07/2021',
                    'delivery_date' => '05/08/2021',
                    'instructions' => '** VEHICLE RELEASE ATTACHED, delivery m-f 9-8 sat 9-6 sun 12-5, CALL (888) 655-1864 or (513) 829-7300 FOR DE- LIVERY AND ASK FOR USED CAR MANAGER OR SALES MANAGER ***',
                    'vehicles' => [
                        [
                            'vin' => '1C4AJWAGXDL660082',
                            'year' => '2013',
                            'make' => 'JEEP',
                            'model' => 'WRANGLER 4WD 2DR SPORT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'PERUZZI MITSUBISHI QUAKERTOWN',
                        'state' => 'PA',
                        'city' => 'QUAKERTOWN',
                        'address' => '840 S WEST END BLVD',
                        'zip' => '18951',
                        'phones' => [],
                        'phone' => '12155361009',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'PERFORMANCE HONDA',
                        'state' => 'OH',
                        'city' => 'FAIRFIELD',
                        'address' => '5760 DIXIE HWY',
                        'zip' => '45014',
                        'phones' => [
                            [
                                'number' => '15138297300'
                            ],
                        ],
                        'phone' => '18886551864',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_104(): void
    {
        $this->createMakes('RAM')
            ->createStates('AR', 'GA', 'TN')
            ->createTimeZones('72032', '30529', '37122')
            ->sendPdfFile('wholesale_express_2_104')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245475',
                    'pickup_date' => '05/06/2021',
                    'delivery_date' => '05/07/2021',
                    'instructions' => 'RELEASE AT GUARD SHACK — DELIVER ASAP',
                    'vehicles' => [
                        [
                            'vin' => '1C6SRFU94MN901771',
                            'year' => '2021',
                            'make' => 'RAM',
                            'model' => '1500 TRX 4X4 CREW CAB 5’7” BOX',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM LITTLE ROCK (TESTLOCB)',
                        'state' => 'AR',
                        'city' => 'CONWAY',
                        'address' => '282 HIGHWAY 64 E',
                        'zip' => '72032',
                        'phones' => [],
                        'phone' => '15015651790',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'AUTO GALLERY CHRYSLER DODGE JEEP RAM',
                        'state' => 'GA',
                        'city' => 'COMMERCE',
                        'address' => '2377 HOMER RD',
                        'zip' => '30529',
                        'phones' => [],
                        'phone' => '17063353196',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_105(): void
    {
        $this->createMakes('CHEVROLET')
            ->createStates('CA', 'AZ', 'TN')
            ->createTimeZones('94544', '85210', '37122')
            ->sendPdfFile('wholesale_express_2_105')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245802',
                    'pickup_date' => '05/05/2021',
                    'delivery_date' => '05/06/2021',
                    'instructions' => 'CALL IN ADVANCE Call Clinton to arrange pick up and instructions on where to pull in for pick up. Cannot load from front of dealership. You have to park in back at Import Parts parking lot and they will shuffle cars over to you.',
                    'vehicles' => [
                        [
                            'vin' => '1GCGSDEN2J1323675',
                            'year' => '2018',
                            'make' => 'CHEVROLET',
                            'model' => 'COLORADO 2WD CREW CAB 128.3” Z71',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM SAN FRANCISCO BAY (649183)',
                        'state' => 'CA',
                        'city' => 'HAYWARD',
                        'address' => '29900 AUCTION WAY',
                        'zip' => '94544',
                        'phones' => [],
                        'phone' => '15107864500',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'TRUCKS ONLY- MESA',
                        'state' => 'AZ',
                        'city' => 'MESA',
                        'address' => '550 S. COUNTRY CLUB DR',
                        'zip' => '85210',
                        'phones' => [],
                        'phone' => '14802408711',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_106(): void
    {
        $this->createMakes('FORD')
            ->createStates('PA', 'TN', 'TN')
            ->createTimeZones('16066', '37122', '37122')
            ->sendPdfFile('wholesale_express_2_106')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '247530',
                    'pickup_date' => '05/15/2021',
                    'delivery_date' => '05/17/2021',
                    'instructions' => 'DELIVERY INSTRUCTIONS: CHECK VEHICLE UNDER DEALER # 5258127 WHOLESALE INC CONFIRM GATE PASSES PRIOR TO PICKUP',
                    'vehicles' => [
                        [
                            'vin' => '1FT7W2BTXLEC44245',
                            'year' => '2020',
                            'make' => 'FORD',
                            'model' => 'F250 SUPER DUTY 4WD CREW CAB BOX',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'MANHEIM PITTSBURGH (PITTS)',
                        'state' => 'PA',
                        'city' => 'CRANBERRY TOWNSHIP',
                        'address' => '21095 US-19',
                        'zip' => '16066',
                        'phones' => [],
                        'phone' => '17244525555',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MANHEIM NASHVILLE (NASH)',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8400 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [
                            [
                                'number' => '16157736558'
                            ],
                        ],
                        'phone' => '16157733800',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_107(): void
    {
        $this->createMakes('FORD')
            ->createStates('AR', 'TX', 'TN')
            ->createTimeZones('72901', '78644', '37122')
            ->sendPdfFile('wholesale_express_2_107')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '242526',
                    'pickup_date' => '05/17/2021',
                    'delivery_date' => '05/18/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '1FTEW1CP5GKE82594',
                            'year' => '2016',
                            'make' => 'FORD',
                            'model' => 'F-150 2WD SUPERCREW',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'UXX ALLEN MOTORS',
                        'state' => 'AR',
                        'city' => 'FORT SMITH',
                        'address' => '4210 TOWSON AVE',
                        'zip' => '72901',
                        'phones' => [],
                        'phone' => '14794241099',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'CHUCK NASH PRE-OWNED',
                        'state' => 'TX',
                        'city' => 'LOCKHART',
                        'address' => '204B N. COMMERCE STREET',
                        'zip' => '78644',
                        'phones' => [],
                        'phone' => '18889030449',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_108(): void
    {
        $this->createMakes('CADILLAC')
            ->createStates('MO', 'IN', 'TN')
            ->createTimeZones('65807', '46224', '37122')
            ->sendPdfFile('wholesale_express_2_108')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245536',
                    'pickup_date' => '05/04/2021',
                    'delivery_date' => '05/05/2021',
                    'instructions' => 'CALL IN ADVANCE UNIT AVAILABLE 5/3/21 — RELEASE ATTACHED — DELIVER ASAP',
                    'vehicles' => [
                        [
                            'vin' => '1GYS4EKL9MR203689',
                            'year' => '2021',
                            'make' => 'CADILLAC',
                            'model' => 'ESCALADE 4WD 4DR SPORT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => '166 AUTO AUCTION',
                        'state' => 'MO',
                        'city' => 'SPRINGFIELD',
                        'address' => '2944 W SUNSHINE ST',
                        'zip' => '65807',
                        'phones' => [],
                        'phone' => '14178821666',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'HARDING STEINBRENNER RACING',
                        'state' => 'IN',
                        'city' => 'SPEEDWAY',
                        'address' => '1255 N MAIN ST',
                        'zip' => '46224',
                        'phones' => [],
                        'phone' => '13178008888',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_109(): void
    {
        $this->createMakes('FORD')
            ->createStates('WV', 'TN', 'TN')
            ->createTimeZones('26582', '37122', '37122')
            ->sendPdfFile('wholesale_express_2_109')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '243018',
                    'pickup_date' => '05/05/2021',
                    'delivery_date' => '05/07/2021',
                    'instructions' => 'DELIVERY INSTRUCTIONS: CHECK VEHICLE UNDER DEALER # 5258127 WHOLESALE INC CONFIRM GATE PASSES PRIOR TO PICKUP',
                    'vehicles' => [
                        [
                            'vin' => '1FM5K8D89GGD24267',
                            'year' => '2016',
                            'make' => 'FORD',
                            'model' => 'EXPLORER 4WD 4DR XLT',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'CORWIN FORD SALES',
                        'state' => 'WV',
                        'city' => 'MANNINGTON',
                        'address' => '5107 US-250',
                        'zip' => '26582',
                        'phones' => [],
                        'phone' => '13049861111',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'MANHEIM NASHVILLE (NASH)',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8400 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [
                            [
                                'number' => '16157736558'
                            ],
                        ],
                        'phone' => '16157733800',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_110(): void
    {
        $this->createMakes('HONDA')
            ->createStates('TX', 'IL', 'TN')
            ->createTimeZones('77640', '60453', '37122')
            ->sendPdfFile('wholesale_express_2_110')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '244433',
                    'pickup_date' => '05/06/2021',
                    'delivery_date' => '05/08/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '5FPYK3F7XJB011777',
                            'year' => '2018',
                            'make' => 'HONDA',
                            'model' => 'RIDGELINE RTL-E AWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'TWIN CITY NISSAN',
                        'state' => 'TX',
                        'city' => 'PORT ARTHUR',
                        'address' => '10549 MEMORIAL BLVD',
                        'zip' => '77640',
                        'phones' => [],
                        'phone' => '14097272779',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'ED NAPLETON HONDA OAK LAWN',
                        'state' => 'IL',
                        'city' => 'OAK LAWN',
                        'address' => '5800 W 95TH ST',
                        'zip' => '60453',
                        'phones' => [],
                        'phone' => '18883560134',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }

    public function test_it_parsed_111(): void
    {
        $this->createMakes('VOLKSWAGEN')
            ->createStates('MO', 'IL', 'TN')
            ->createTimeZones('64153', '60438', '37122')
            ->sendPdfFile('wholesale_express_2_111')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'load_id' => '245961',
                    'pickup_date' => '05/04/2021',
                    'delivery_date' => '05/05/2021',
                    'instructions' => null,
                    'vehicles' => [
                        [
                            'vin' => '3VV3B7AX6KM063584',
                            'year' => '2019',
                            'make' => 'VOLKSWAGEN',
                            'model' => 'TIGUAN 2.0T FWD',
                            'inop' => false,
                            'enclosed' => false,
                            'type_id' => null,
                        ],
                    ],
                    'pickup_contact' => [
                        'full_name' => 'AMERICA’S AUTO AUCTION KANSAS CITY',
                        'state' => 'MO',
                        'city' => 'KANSAS CITY',
                        'address' => '11101 N CONGRESS AVE',
                        'zip' => '64153',
                        'phones' => [],
                        'phone' => '18165023318',
                        'state_id' => $this->pickupStateId,
                        'timezone' => $this->pickupTimezone,
                    ],
                    'delivery_contact' => [
                        'full_name' => 'NAPLETONS RIVER OAKS HONDA',
                        'state' => 'IL',
                        'city' => 'LANSING',
                        'address' => '17220 TORRENCE AVE',
                        'zip' => '60438',
                        'phones' => [],
                        'phone' => '17088680100',
                        'state_id' => $this->deliveryStateId,
                        'timezone' => $this->deliveryTimezone,
                    ],
                    'shipper_contact' => [
                        'full_name' => 'WHOLESALE EXPRESS LLC',
                        'state' => 'TN',
                        'city' => 'MOUNT JULIET',
                        'address' => '8037 EASTGATE BLVD',
                        'zip' => '37122',
                        'phones' => [],
                        'phone' => '16153924100',
                        'state_id' => $this->shipperStateId,
                        'timezone' => $this->shipperTimezone,
                    ],
                ]
            ], true);
    }
}
