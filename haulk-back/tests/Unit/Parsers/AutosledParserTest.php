<?php

namespace Tests\Unit\Parsers;

use Throwable;

class AutosledParserTest extends BaseParserTest
{
    /**
     * @throws Throwable
     */
    public function test_1(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("LA", "TN", "NONE")
            ->createTimeZones("70062", "37203", "NONE")
            ->assertParsing(
                1,
                [
                    "load_id" => "A5DD2E13",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "TOYOTA",
                            "model" => "Highlander",
                            "type" => "SUV",
                            "color" => null,
                            "vin" => "5TDKZRFH0KS323223",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "John Taylor. ABG NEW ORLEANS- LA",
                        "state" => "LA",
                        "city" => "Kenner",
                        "address" => "300 Rental Blvd",
                        "zip" => "70062",
                        "phones" => [],
                        "phone" => "15042183360",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "MGR. BEAMAN TOYOTA",
                        "state" => "TN",
                        "city" => "Nashville",
                        "address" => "1525 Broadway",
                        "zip" => "37203",
                        "phones" => [],
                        "phone" => "16152518400",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Beaman Toyota (Hudson)",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_2(): void
    {
        $this->createMakes("NISSAN")
            ->createStates("IL", "GA", "NONE")
            ->createTimeZones("60443", "30291", "NONE")
            ->assertParsing(
                2,
                [
                    "load_id" => "B72E727E",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "NISSAN",
                            "model" => "Armada",
                            "type" => "SUV",
                            "color" => "White",
                            "vin" => "JN8AY2NF7L9360862",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Security. Manheim Chicago",
                        "state" => "IL",
                        "city" => "Matteson",
                        "address" => "20401 Cox Ave",
                        "zip" => "60443",
                        "phones" => [],
                        "phone" => "18158064222",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "santos. INFINITI OF SOUTH ATLANTA",
                        "state" => "GA",
                        "city" => "Union City",
                        "address" => "4201 Jonesboro Rd",
                        "zip" => "30291",
                        "phones" => [],
                        "phone" => "14049699435",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Infiniti of South Atlanta",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_3(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("OH", "SC", "NONE")
            ->createTimeZones("44512", "29203", "NONE")
            ->assertParsing(
                3,
                [
                    "load_id" => "40FD3574",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "TOYOTA",
                            "model" => "Tundra",
                            "type" => "Truck",
                            "color" => null,
                            "vin" => "5TFDY5F16LX930858",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Jamie Banks. Toyota of Boardman",
                        "state" => "OH",
                        "city" => "Youngstown",
                        "address" => "8250 Market St",
                        "zip" => "44512",
                        "phones" => [],
                        "phone" => "13303337218",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "INVENTORY. MIDLANDS TOYOTA",
                        "state" => "SC",
                        "city" => "Columbia",
                        "address" => "240 Killian Commons Parkway",
                        "zip" => "29203",
                        "phones" => [],
                        "phone" => "18037864111",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Midlands Toyota (Hudson)",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_4(): void
    {
        $this->createMakes("NISSAN")
            ->createStates("NY", "SC", "NONE")
            ->createTimeZones("10309", "29407", "NONE")
            ->assertParsing(
                4,
                [
                    "load_id" => "D74960C1",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "NISSAN",
                            "model" => "Rogue Sport",
                            "type" => "Car",
                            "color" => null,
                            "vin" => "JN1BJ1CW5LW365550",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Alex Trantino. Dealer Direct Service",
                        "state" => "NY",
                        "city" => "Staten Island",
                        "address" => "4300 Arthur Kill Road",
                        "zip" => "10309",
                        "phones" => [],
                        "phone" => "17189343900",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Stephen Hardman. Hudson Nissan of Charleston",
                        "state" => "SC",
                        "city" => "Charleston",
                        "address" => "1714 Savannah Hwy",
                        "zip" => "29407",
                        "phones" => [],
                        "phone" => "18433672848",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Hudson Nissan of Charleston (Hudson)",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_5(): void
    {
        $this->createMakes("INFINITI")
            ->createStates("NY", "SC", "NONE")
            ->createTimeZones("10309", "29407", "NONE")
            ->assertParsing(
                5,
                [
                    "load_id" => "B3B00951",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "INFINITI",
                            "model" => "Q50",
                            "type" => "Car",
                            "color" => null,
                            "vin" => "JN1EV7AR7KM590127",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Alex Trantino. Dealer Direct Service",
                        "state" => "NY",
                        "city" => "Staten Island",
                        "address" => "4350 Arthur Kill Road",
                        "zip" => "10309",
                        "phones" => [],
                        "phone" => "17189343900",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Stephen Hardman. Hudson Nissan of Charleston",
                        "state" => "SC",
                        "city" => "Charleston",
                        "address" => "1714 Savannah Hwy",
                        "zip" => "29407",
                        "phones" => [],
                        "phone" => "18433672848",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Hudson Nissan of Charleston (Hudson)",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_6(): void
    {
        $this->createMakes("SUBARU")
            ->createStates("NJ", "OH", "NONE")
            ->createTimeZones("08050", "43228", "NONE")
            ->assertParsing(
                6,
                [
                    "load_id" => "F9F67483",
                    "dispatch_instructions" => "1 key",
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "SUBARU",
                            "model" => "Forester",
                            "type" => "SUV",
                            "color" => null,
                            "vin" => "JF2SKAWC2KH582134",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Eric. Manahawkin Kia",
                        "state" => "NJ",
                        "city" => "Manahawkin",
                        "address" => "270 NJ-72",
                        "zip" => "08050",
                        "phones" => [],
                        "phone" => "16095972501",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "RICK. GERMAIN SUBARU OF COLUMBUS",
                        "state" => "OH",
                        "city" => "Columbus",
                        "address" => "1395 Auto Mall Dr",
                        "zip" => "43228",
                        "phones" => [],
                        "phone" => "16143476450",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Germain Subaru of Columbus",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_7(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("NY", "GA", "NONE")
            ->createTimeZones("14225", "30606", "NONE")
            ->assertParsing(
                7,
                [
                    "load_id" => "98F107C2",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "TOYOTA",
                            "model" => "Camry",
                            "type" => "Car",
                            "color" => "White",
                            "vin" => "4T1G11AK6MU543378",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Ryan Leavitt. ABG BUFFALO- NY",
                        "state" => "NY",
                        "city" => "Buffalo",
                        "address" => "4565 Genesee St",
                        "zip" => "14225",
                        "phones" => [],
                        "phone" => "17166321830",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Billy Mreir. ALM Athens",
                        "state" => "GA",
                        "city" => "Athens",
                        "address" => "4145 Atlanta Hwy",
                        "zip" => "30606",
                        "phones" => [],
                        "phone" => "17706527222",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "ALM Athens",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_8(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("PA", "MN", "NONE")
            ->createTimeZones("16509", "55372", "NONE")
            ->assertParsing(
                8,
                [
                    "load_id" => "34A209B0",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2017",
                            "make" => "CHEVROLET",
                            "model" => "Camaro",
                            "type" => "Car",
                            "color" => "Blue",
                            "vin" => "1G1FH1R74H0123636",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "jim. dealership",
                        "state" => "PA",
                        "city" => "ERIE",
                        "address" => "5711 Peach St",
                        "zip" => "16509",
                        "phones" => [],
                        "phone" => "18148640611",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "tom remes. residence",
                        "state" => "MN",
                        "city" => "Prior Lake",
                        "address" => "14697 maple trl se",
                        "zip" => "55372",
                        "phones" => [],
                        "phone" => "16122694198",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Roth Cadillac",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_9(): void
    {
        $this->createMakes("NISSAN")
            ->createStates("OH", "MN", "NONE")
            ->createTimeZones("44135", "55391", "NONE")
            ->assertParsing(
                9,
                [
                    "load_id" => "25C87F74",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "NISSAN",
                            "model" => "NV",
                            "type" => "Truck",
                            "color" => "White",
                            "vin" => "1N6BF0LY1KN810586",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "PAUL BOZIN. HERTZ DEALER DIRECT OH",
                        "state" => "OH",
                        "city" => "Cleveland",
                        "address" => "19025 Maplewood Ave",
                        "zip" => "44135",
                        "phones" => [],
                        "phone" => "12162999614",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "HASSAN GHANDOUR. Walser Wayzata Nissan",
                        "state" => "MN",
                        "city" => "Wayzata",
                        "address" => "15906 W Wayzata Blvd",
                        "zip" => "55391",
                        "phones" => [],
                        "phone" => "19522018075",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Walser Wayzata Nissan",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_10(): void
    {
        $this->createMakes("SUBARU")
            ->createStates("GA", "OH", "NONE")
            ->createTimeZones("30349", "43228", "NONE")
            ->assertParsing(
                10,
                [
                    "load_id" => "55F691FE",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2014",
                            "make" => "SUBARU",
                            "model" => "Outback",
                            "type" => "SUV",
                            "color" => null,
                            "vin" => "4S4BRBCC5E3312557",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Security. Manheim Atlanta",
                        "state" => "GA",
                        "city" => "College Park",
                        "address" => "4900 Buffington Rd",
                        "zip" => "30349",
                        "phones" => [],
                        "phone" => "14047629211",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "RICK. GERMAIN SUBARU OF COLUMBUS",
                        "state" => "OH",
                        "city" => "Columbus",
                        "address" => "1395 Auto Mall Dr",
                        "zip" => "43228",
                        "phones" => [],
                        "phone" => "16143476450",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Germain Subaru of Columbus",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_11(): void
    {
        $this->createMakes("RAM")
            ->createStates("OH", "MN", "NONE")
            ->createTimeZones("43615", "55343", "NONE")
            ->assertParsing(
                11,
                [
                    "load_id" => "45740C0A",
                    "dispatch_instructions" => "2 keys",
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "RAM",
                            "model" => "1500",
                            "type" => "Truck",
                            "color" => null,
                            "vin" => "1C6SRFFT9KN716069",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "MGR. YARK AUTOMOTIVE GROUP",
                        "state" => "OH",
                        "city" => "Toledo",
                        "address" => "6019 Central Ave",
                        "zip" => "43615",
                        "phones" => [],
                        "phone" => "18883113309",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "MGR. WALSER CHRYSLER JEEP RAM -",
                        "state" => "MN",
                        "city" => "314 Mainstreet Hopkins",
                        "address" => "HOPKINS",
                        "zip" => "55343",
                        "phones" => [],
                        "phone" => "18663230559",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Walser Chrysler Jeep Ram - Hopkins",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_12(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("NC", "MN", "NONE")
            ->createTimeZones("27407", "55437", "NONE")
            ->assertParsing(
                12,
                [
                    "load_id" => "F4DFF30A",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "TOYOTA",
                            "model" => "RAV4",
                            "type" => "SUV",
                            "color" => null,
                            "vin" => "4T3RWRFVXNU076017",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "MGR. Impex Auto Sales",
                        "state" => "NC",
                        "city" => "Greensboro",
                        "address" => "3518 S Holden Rd",
                        "zip" => "27407",
                        "phones" => [],
                        "phone" => "13362715987",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "mgr. WALSER TOYOTA",
                        "state" => "MN",
                        "city" => "Bloomington",
                        "address" => "4401 American Blvd W",
                        "zip" => "55437",
                        "phones" => [],
                        "phone" => "16122940220",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Walser Toyota",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_13(): void
    {
        $this->createMakes("JEEP")
            ->createStates("MI", "MN", "NONE")
            ->createTimeZones("48042", "55431", "NONE")
            ->assertParsing(
                13,
                [
                    "load_id" => "3730632C",
                    "dispatch_instructions" => "Car is dusty",
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "JEEP",
                            "model" => "Grand Cherokee",
                            "type" => "SUV",
                            "color" => null,
                            "vin" => "1C4RJKDG4M8182431",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Jonathan Russo. J-Rus Inc.",
                        "state" => "MI",
                        "city" => "Macomb",
                        "address" => "15977 Leone Dr",
                        "zip" => "48042",
                        "phones" => [],
                        "phone" => "12485255490",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "mgr. Bloomington Chrysler Jeep Dodge Ram",
                        "state" => "MN",
                        "city" => "Minneapolis",
                        "address" => "8000 Penn Ave S",
                        "zip" => "55431",
                        "phones" => [],
                        "phone" => "19523141248",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Bloomington CDJR",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_14(): void
    {
        $this->createMakes("RAM")
            ->createStates("MD", "IL", "NONE")
            ->createTimeZones("20878", "60103", "NONE")
            ->assertParsing(
                14,
                [
                    "load_id" => "B3D72D1D",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "RAM",
                            "model" => "4500",
                            "type" => "Other",
                            "color" => "White",
                            "vin" => "3C7WRLEL6NG313418",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Daniel Hornick. Criswell Commercial",
                        "state" => "MD",
                        "city" => "Gaithersburg",
                        "address" => "503 Quince Orchard Rd",
                        "zip" => "20878",
                        "phones" => [],
                        "phone" => "14109849667",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Jason Holmes. Auto Truck Group",
                        "state" => "IL",
                        "city" => "Bartlett",
                        "address" => "1420 Brewster Creek Blvd",
                        "zip" => "60103",
                        "phones" => [],
                        "phone" => "16308605600",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Criswell CJDR",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_15(): void
    {
        $this->createMakes("INFINITI")
            ->createStates("GA", "MD", "NONE")
            ->createTimeZones("30331", "21061", "NONE")
            ->assertParsing(
                15,
                [
                    "load_id" => "2A01452F",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "INFINITI",
                            "model" => "QX80",
                            "type" => "SUV",
                            "color" => null,
                            "vin" => "JN8AZ2NE3L9255952",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Security. Manheim Georgia",
                        "state" => "GA",
                        "city" => "Atlanta",
                        "address" => "7205 Campbellton Rd",
                        "zip" => "30331",
                        "phones" => [],
                        "phone" => "14043495555",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "JOE BLANCO. SHEEHY NISSAN OF GLEN BURNIE",
                        "state" => "MD",
                        "city" => "Glen Burnie",
                        "address" => "7232 Ritchie Hwy",
                        "zip" => "21061",
                        "phones" => [],
                        "phone" => "14107541777",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Sheehy Nissan of Glen Burnie",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_16(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("NC", "OH", "NONE")
            ->createTimeZones("27834", "43228", "NONE")
            ->assertParsing(
                16,
                [
                    "load_id" => "9A0474A0",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "TOYOTA",
                            "model" => "RAV4",
                            "type" => "SUV",
                            "color" => null,
                            "vin" => "2T3Y1RFV7KW020669",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Mgr. Greenville Auto Auction",
                        "state" => "NC",
                        "city" => "Greenville",
                        "address" => "4330 Dickinson Ave",
                        "zip" => "27834",
                        "phones" => [],
                        "phone" => "12523554111",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Inventory. Germain Toyota West",
                        "state" => "OH",
                        "city" => "Columbus",
                        "address" => "1500 Auto Mall Dr",
                        "zip" => "43228",
                        "phones" => [],
                        "phone" => "16144654852",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Germain Toyota West",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_17(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("VT", "VA", "NONE")
            ->createTimeZones("05301", "22207", "NONE")
            ->assertParsing(
                17,
                [
                    "load_id" => "BE82F8A9",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "CHEVROLET",
                            "model" => "Suburban",
                            "type" => "SUV",
                            "color" => null,
                            "vin" => "1GNSKDKL9NR324382",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Manager. Brattleboro Auto mall",
                        "state" => "VT",
                        "city" => "Brattleboro",
                        "address" => "800 Putney Rd 9058",
                        "zip" => "05301",
                        "phones" => [],
                        "phone" => "18556032430",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Cathy. Mike Katona- Private",
                        "state" => "VA",
                        "city" => "Arlington",
                        "address" => "2546 N Granada St",
                        "zip" => "22207",
                        "phones" => [],
                        "phone" => "19137024915",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Autosled Direct",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_18(): void
    {
        $this->createMakes("JEEP")
            ->createStates("GA", "MN", "NONE")
            ->createTimeZones("30291", "55343", "NONE")
            ->assertParsing(
                18,
                [
                    "load_id" => "124A7FAB",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "JEEP",
                            "model" => "Grand Cherokee",
                            "type" => "SUV",
                            "color" => null,
                            "vin" => "1C4RJKBG3M8103169",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "MGR. Heritage Volkswagen",
                        "state" => "GA",
                        "city" => "Union City",
                        "address" => "4305 Jonesboro Rd",
                        "zip" => "30291",
                        "phones" => [],
                        "phone" => "16782831896",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "MGR. WALSER CHRYSLER JEEP RAM -",
                        "state" => "MN",
                        "city" => "314 Mainstreet Hopkins",
                        "address" => "HOPKINS",
                        "zip" => "55343",
                        "phones" => [],
                        "phone" => "18663230559",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Walser Chrysler Jeep Ram - Hopkins",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_19(): void
    {
        $this->createMakes("JEEP")
            ->createStates("FL", "TN", "NONE")
            ->createTimeZones("33916", "37130", "NONE")
            ->assertParsing(
                19,
                [
                    "load_id" => "EC824B3F",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "JEEP",
                            "model" => "Gladiator",
                            "type" => "Truck",
                            "color" => null,
                            "vin" => "1C6HJTFG0LL198918",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Security. Manheim Fort Myers",
                        "state" => "FL",
                        "city" => "Ft. Myers",
                        "address" => "2100 Rockfill Rd",
                        "zip" => "33916",
                        "phones" => [],
                        "phone" => "12394769800",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "INVENTORY. BEAMAN CDJR",
                        "state" => "TN",
                        "city" => "Murfreesboro",
                        "address" => "1705 S Church St",
                        "zip" => "37130",
                        "phones" => [],
                        "phone" => "16158955092",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Beaman CDJR (Hudson)",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_20(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("TN", "AL", "NONE")
            ->createTimeZones("37919", "35071", "NONE")
            ->assertParsing(
                20,
                [
                    "load_id" => "72CC2E82",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2018",
                            "make" => "CHEVROLET",
                            "model" => "Silverado",
                            "type" => "Truck",
                            "color" => null,
                            "vin" => "3GCUKSEC1JG220464",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "mgr. Ted Russell Ford",
                        "state" => "TN",
                        "city" => "Knoxville",
                        "address" => "8551 Kingston Pike",
                        "zip" => "37919",
                        "phones" => [],
                        "phone" => "18047664532",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Mgr. Serra Kia",
                        "state" => "AL",
                        "city" => "Gardendale",
                        "address" => "630 Fieldstown Rd",
                        "zip" => "35071",
                        "phones" => [],
                        "phone" => "12056312277",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Serra Gardendale Kia",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_21(): void
    {
        $this->createMakes("NISSAN")
            ->createStates("IL", "MN", "NONE")
            ->createTimeZones("60666", "55391", "NONE")
            ->assertParsing(
                21,
                [
                    "load_id" => "8D10B1E9",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2018",
                            "make" => "NISSAN",
                            "model" => "Rogue",
                            "type" => "SUV",
                            "color" => "Black",
                            "vin" => "KNMAT2MV0JP531995",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "SCOTT REBHOLZ OR JOE. HERTZ DEALER DIRECT IL",
                        "state" => "IL",
                        "city" => "Chicago",
                        "address" => "1000 Bessie Coleman Dr",
                        "zip" => "60666",
                        "phones" => [],
                        "phone" => "17734491272",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "HASSAN GHANDOUR. Walser Wayzata Nissan",
                        "state" => "MN",
                        "city" => "Wayzata",
                        "address" => "15906 W Wayzata Blvd",
                        "zip" => "55391",
                        "phones" => [],
                        "phone" => "19522018075",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Walser Wayzata Nissan",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_22(): void
    {
        $this->createMakes("NISSAN")
            ->createStates("IL", "MN", "NONE")
            ->createTimeZones("60666", "55391", "NONE")
            ->assertParsing(
                22,
                [
                    "load_id" => "9173FA4D",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2018",
                            "make" => "NISSAN",
                            "model" => "Rogue",
                            "type" => "SUV",
                            "color" => "Black",
                            "vin" => "5N1AT2MV0JC745999",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "SCOTT REBHOLZ OR JOE. HERTZ DEALER DIRECT IL",
                        "state" => "IL",
                        "city" => "Chicago",
                        "address" => "1000 Bessie Coleman Dr",
                        "zip" => "60666",
                        "phones" => [],
                        "phone" => "17734491272",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "HASSAN GHANDOUR. Walser Wayzata Nissan",
                        "state" => "MN",
                        "city" => "Wayzata",
                        "address" => "15906 W Wayzata Blvd",
                        "zip" => "55391",
                        "phones" => [],
                        "phone" => "19522018075",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Walser Wayzata Nissan",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_23(): void
    {
        $this->createMakes("INFINITI")
            ->createStates("IL", "OH", "NONE")
            ->createTimeZones("60443", "44146", "NONE")
            ->assertParsing(
                23,
                [
                    "load_id" => "5714F1D9",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2015",
                            "make" => "INFINITI",
                            "model" => "QX60",
                            "type" => "SUV",
                            "color" => "Gray",
                            "vin" => "5N1AL0MM9FC514893",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "James. Carvana",
                        "state" => "IL",
                        "city" => "Matteson",
                        "address" => "20401 Cox Ave",
                        "zip" => "60443",
                        "phones" => [],
                        "phone" => "18833289353",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Tony. Auto Rite",
                        "state" => "OH",
                        "city" => "Bedford Heights",
                        "address" => "5100 Richmond Rd",
                        "zip" => "44146",
                        "phones" => [],
                        "phone" => "14406684947",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Auto Rite",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_24(): void
    {
        $this->createMakes("NISSAN")
            ->createStates("OH", "SC", "NONE")
            ->createTimeZones("45377", "29730", "NONE")
            ->assertParsing(
                24,
                [
                    "load_id" => "5F37B886",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "NISSAN",
                            "model" => "Sentra",
                            "type" => "Car",
                            "color" => null,
                            "vin" => "3N1AB8CVXMY252907",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Byron Porter. ABG DAYTON- OH",
                        "state" => "OH",
                        "city" => "Vandalia",
                        "address" => "3300 Valet Dr",
                        "zip" => "45377",
                        "phones" => [],
                        "phone" => "16142074761",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "MGR. Rock Hill Nissan",
                        "state" => "SC",
                        "city" => "Rock Hill",
                        "address" => "550 Galleria Blvd",
                        "zip" => "29730",
                        "phones" => [],
                        "phone" => "18033668171",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Rock Hill Nissan (Hudson)",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_25(): void
    {
        $this->createMakes("KIA")
            ->createStates("IN", "GA", "NONE")
            ->createTimeZones("46241", "30076", "NONE")
            ->assertParsing(
                25,
                [
                    "load_id" => "C313027B",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "KIA",
                            "model" => "Rio",
                            "type" => "Car",
                            "color" => "Red",
                            "vin" => "3KPA24AD4ME368637",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Tom Henry. ABG INDIANAPOLIS- IN",
                        "state" => "IN",
                        "city" => "Indianapolis",
                        "address" => "2621 S High School Rd",
                        "zip" => "46241",
                        "phones" => [],
                        "phone" => "13179452269",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Julio Rueda. ALM ROSWELL",
                        "state" => "GA",
                        "city" => "Roswell",
                        "address" => "891 Mansell Rd",
                        "zip" => "30076",
                        "phones" => [],
                        "phone" => "16789393744",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "ALM Roswell",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_26(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("MD", "MN", "NONE")
            ->createTimeZones("21784", "55437", "NONE")
            ->assertParsing(
                26,
                [
                    "load_id" => "D7D4175D",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "TOYOTA",
                            "model" => "RAV4",
                            "type" => "SUV",
                            "color" => null,
                            "vin" => "JTMEWRFV7LD540681",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "MGR. Trust Auto",
                        "state" => "MD",
                        "city" => "Sykesville",
                        "address" => "1551 W Old Liberty Rd",
                        "zip" => "21784",
                        "phones" => [],
                        "phone" => "14435523131",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "mgr. WALSER TOYOTA",
                        "state" => "MN",
                        "city" => "Bloomington",
                        "address" => "4401 American Blvd W",
                        "zip" => "55437",
                        "phones" => [],
                        "phone" => "16122940220",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Walser Toyota",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_27(): void
    {
        $this->createMakes("NISSAN")
            ->createStates("IL", "MN", "NONE")
            ->createTimeZones("60666", "55391", "NONE")
            ->assertParsing(
                27,
                [
                    "load_id" => "724CC9BA",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "NISSAN",
                            "model" => "NV",
                            "type" => "Truck",
                            "color" => "White",
                            "vin" => "1N6BF0LY5KN811417",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "ANDREW STRATTON OR. HERTZ DEALER DIRECT",
                        "state" => "IL",
                        "city" => "Chicago",
                        "address" => "1000 Bessie Coleman Dr",
                        "zip" => "60666",
                        "phones" => [],
                        "phone" => "15634956020",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "HASSAN GHANDOUR. Walser Wayzata Nissan",
                        "state" => "MN",
                        "city" => "Wayzata",
                        "address" => "15906 W Wayzata Blvd",
                        "zip" => "55391",
                        "phones" => [],
                        "phone" => "19522018075",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Walser Wayzata Nissan",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_28(): void
    {
        $this->createMakes("BUICK")
            ->createStates("IL", "MN", "NONE")
            ->createTimeZones("60666", "55306", "NONE")
            ->assertParsing(
                28,
                [
                    "load_id" => "24DCE777",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2017",
                            "make" => "BUICK",
                            "model" => "Encore",
                            "type" => "SUV",
                            "color" => null,
                            "vin" => "KL4CJFSB9HB250181",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Scott Rebholz/Joe Messer. Hertz Chicago",
                        "state" => "IL",
                        "city" => "Chicago",
                        "address" => "10000 Bessie Coleman Dr",
                        "zip" => "60666",
                        "phones" => [],
                        "phone" => "17734491272",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "MGR. WALSER HONDA",
                        "state" => "MN",
                        "city" => "Burnsville",
                        "address" => "14800 Buck Hill Rd",
                        "zip" => "55306",
                        "phones" => [],
                        "phone" => "16122940883",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Walser Honda",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_29(): void
    {
        $this->createMakes("CADILLAC")
            ->createStates("MI", "TX", "NONE")
            ->createTimeZones("48307", "77075", "NONE")
            ->assertParsing(
                29,
                [
                    "load_id" => "F62BA0D4",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "CADILLAC",
                            "model" => "Escalade",
                            "type" => "SUV",
                            "color" => null,
                            "vin" => "1GYS4BKL9NR341023",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Joon Kim. Joon Kim - Residence",
                        "state" => "MI",
                        "city" => "Rochester Hills",
                        "address" => "2922 Fair Acres Dr",
                        "zip" => "48307",
                        "phones" => [],
                        "phone" => "12482284466",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Daisy Dominquez. Fiesta Insurance",
                        "state" => "TX",
                        "city" => "Houston",
                        "address" => "8500 Almeda Genoa Rd",
                        "zip" => "77075",
                        "phones" => [],
                        "phone" => "18323396585",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Autosled Direct",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_30(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("IL", "MN", "NONE")
            ->createTimeZones("60443", "55337", "NONE")
            ->assertParsing(
                30,
                [
                    "load_id" => "27EB07A0",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "CHEVROLET",
                            "model" => "Malibu",
                            "type" => "Car",
                            "color" => null,
                            "vin" => "1G1ZD5ST6LF098736",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Security. Manheim Chicago",
                        "state" => "IL",
                        "city" => "Matteson",
                        "address" => "20401 Cox Ave",
                        "zip" => "60443",
                        "phones" => [],
                        "phone" => "18158064222",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "MGR. WALSER EXPERIENCED AUTOS",
                        "state" => "MN",
                        "city" => "Burnsville",
                        "address" => "600 W 121st St",
                        "zip" => "55337",
                        "phones" => [],
                        "phone" => "19523141022",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Walser Experienced Autos",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_31(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("IL", "GA", "NONE")
            ->createTimeZones("60666", "30265", "NONE")
            ->assertParsing(
                31,
                [
                    "load_id" => "530AFC7E",
                    "dispatch_instructions" => "A lot of small scratches and chips all around",
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "TOYOTA",
                            "model" => "Camry",
                            "type" => "Car",
                            "color" => "Black",
                            "vin" => "4T1C11AKXMU454346",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Ronnie Tadros. ABG CHICAGO OHARE- IL",
                        "state" => "IL",
                        "city" => "Chicago",
                        "address" => "10000 Bessie Coleman Dr",
                        "zip" => "60666",
                        "phones" => [],
                        "phone" => "17738157209",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Ilich Ontiveros. ALM Newnan",
                        "state" => "GA",
                        "city" => "Newnan",
                        "address" => "40 International Park",
                        "zip" => "30265",
                        "phones" => [],
                        "phone" => "17707670000",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "ALM Newnan",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_32(): void
    {
        $this->createMakes("RAM")
            ->createStates("NC", "OH", "NONE")
            ->createTimeZones("28214", "43213", "NONE")
            ->assertParsing(
                32,
                [
                    "load_id" => "9C15F832",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "RAM",
                            "model" => "Promaster 1500",
                            "type" => "Truck",
                            "color" => "White",
                            "vin" => "3C6TRVBG8KE550056",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "NA. Not Available",
                        "state" => "NC",
                        "city" => "Charlotte",
                        "address" => "6515 Rackham Dr Bldg B",
                        "zip" => "28214",
                        "phones" => [],
                        "phone" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "John. Miracle Motor Mart",
                        "state" => "OH",
                        "city" => "Columbus",
                        "address" => "5100 E Main St",
                        "zip" => "43213",
                        "phones" => [],
                        "phone" => "16147454880",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Miracle Motor Mart",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_33(): void
    {
        $this->createMakes("NISSAN")
            ->createStates("IN", "MN", "NONE")
            ->createTimeZones("46168", "55306", "NONE")
            ->assertParsing(
                33,
                [
                    "load_id" => "7582B3BF",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2018",
                            "make" => "NISSAN",
                            "model" => "Sentra",
                            "type" => "Car",
                            "color" => null,
                            "vin" => "3N1AB7AP8JL635753",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Security. Adesa Indianapolis",
                        "state" => "IN",
                        "city" => "Plainfield",
                        "address" => "2950 East Main Street",
                        "zip" => "46168",
                        "phones" => [],
                        "phone" => "13178388000",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "MGR. WALSER NISSAN BURNSVILLE",
                        "state" => "MN",
                        "city" => "Burnsville",
                        "address" => "14750 Buck Hill Rd",
                        "zip" => "55306",
                        "phones" => [],
                        "phone" => "16123954999",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Walser Nissan Burnsville",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_34(): void
    {
        $this->createMakes("INFINITI")
            ->createStates("WI", "MO", "NONE")
            ->createTimeZones("53713", "63366", "NONE")
            ->assertParsing(
                34,
                [
                    "load_id" => "75AFDB5A",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2013",
                            "make" => "INFINITI",
                            "model" => "G37",
                            "type" => "Car",
                            "color" => "Black",
                            "vin" => "JN1CV6FE9DM771904",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "John Larson. ZIMBRICK INFINITI OF MADISON",
                        "state" => "WI",
                        "city" => "Madison",
                        "address" => "1601 W Beltline Hwy",
                        "zip" => "53713",
                        "phones" => [],
                        "phone" => "16082300595",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "GRANK TRULASKE. HOME",
                        "state" => "MO",
                        "city" => "O'Fallon",
                        "address" => "865 Hoff Rd",
                        "zip" => "63366",
                        "phones" => [],
                        "phone" => "13144966301",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Zimbrick Infiniti",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_35(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("NJ", "NC", "NONE")
            ->createTimeZones("07114", "27804", "NONE")
            ->assertParsing(
                35,
                [
                    "load_id" => "678A8D39",
                    "dispatch_instructions" => "Vehicle has some scratches not to bad, couple of paint chips, and dent on"
                        . " driver side by trunk",
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "TOYOTA",
                            "model" => "RAV4",
                            "type" => "SUV",
                            "color" => null,
                            "vin" => "2T3H1RFV5LC053317",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Carlos Gomes. ABG NEWARK- NJ",
                        "state" => "NJ",
                        "city" => "Newark",
                        "address" => "86 Olympia Dr",
                        "zip" => "07114",
                        "phones" => [],
                        "phone" => "19732613310",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Inventory Manager. Rocky Mount Toyota",
                        "state" => "NC",
                        "city" => "Rocky Mount",
                        "address" => "943 N Wesleyan Blvd",
                        "zip" => "27804",
                        "phones" => [],
                        "phone" => "12529770224",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Rockymount Toyota Superstore (Hudson)",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_36(): void
    {
        $this->createMakes("NISSAN")
            ->createStates("NJ", "NC", "NONE")
            ->createTimeZones("07105", "27804", "NONE")
            ->assertParsing(
                36,
                [
                    "load_id" => "274DC4E6",
                    "dispatch_instructions" => "Car has scretches everywere, dent on roof, big scuffes on front bumper both"
                        . " corners. Passenger side bumper clips are not attached, chips on doors, rear bumper passenger side"
                        . " is not clipped",
                    "vehicles" => [
                        [
                            "year" => "2018",
                            "make" => "NISSAN",
                            "model" => "Rogue",
                            "type" => "SUV",
                            "color" => null,
                            "vin" => "5N1AT2MT3JC765341",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Steve Keogh. Hertz Newark",
                        "state" => "NJ",
                        "city" => "Newark",
                        "address" => "104 Foundry St",
                        "zip" => "07105",
                        "phones" => [],
                        "phone" => "19734656941",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Inventory Manager. Rocky Mount Toyota",
                        "state" => "NC",
                        "city" => "Rocky Mount",
                        "address" => "943 N Wesleyan Blvd",
                        "zip" => "27804",
                        "phones" => [],
                        "phone" => "12529770224",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Rockymount Toyota Superstore (Hudson)",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_37(): void
    {
        $this->createMakes("RAM")
            ->createStates("PA", "TX", "NONE")
            ->createTimeZones("16066", "78249", "NONE")
            ->assertParsing(
                37,
                [
                    "load_id" => "F619300C",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "RAM",
                            "model" => "1500",
                            "type" => "Truck",
                            "color" => null,
                            "vin" => "1C6SRFFT5LN166953",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Security. Manheim Pittsburgh",
                        "state" => "PA",
                        "city" => "Cranberry Township",
                        "address" => "21095 Route 19",
                        "zip" => "16066",
                        "phones" => [],
                        "phone" => "17244525555",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Mgr. Land Rover San Antonio",
                        "state" => "TX",
                        "city" => "San Antonio",
                        "address" => "13660 I-10",
                        "zip" => "78249",
                        "phones" => [],
                        "phone" => "12103195062",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Land Rover San Antonio",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_38(): void
    {
        $this->createMakes("HONDA")
            ->createStates("TX", "MN", "NONE")
            ->createTimeZones("75238", "55306", "NONE")
            ->assertParsing(
                38,
                [
                    "load_id" => "FEC6E8B4",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "HONDA",
                            "model" => "Civic",
                            "type" => "Car",
                            "color" => null,
                            "vin" => "2HGFE2F55NH501478",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "JAY. SELECT CITY CARS",
                        "state" => "TX",
                        "city" => "Dallas",
                        "address" => "10650 Control Pl",
                        "zip" => "75238",
                        "phones" => [],
                        "phone" => "14695691405",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "MGR. WALSER HONDA",
                        "state" => "MN",
                        "city" => "Burnsville",
                        "address" => "14800 Buck Hill Rd",
                        "zip" => "55306",
                        "phones" => [],
                        "phone" => "16122940883",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Walser Honda",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_39(): void
    {
        $this->createMakes("SUBARU")
            ->createStates("RI", "MN", "NONE")
            ->createTimeZones("02864", "55306", "NONE")
            ->assertParsing(
                39,
                [
                    "load_id" => "31398B45",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2013",
                            "make" => "SUBARU",
                            "model" => "XV CrossTrek",
                            "type" => "SUV",
                            "color" => null,
                            "vin" => "JF2GPACC8D1215973",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Alan Albert. APOLLO AUTO SALES, INC",
                        "state" => "RI",
                        "city" => "Cumberland",
                        "address" => "625 Broad St",
                        "zip" => "02864",
                        "phones" => [],
                        "phone" => "14017288998",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "MGR. WALSER NISSAN BURNSVILLE",
                        "state" => "MN",
                        "city" => "Burnsville",
                        "address" => "14750 Buck Hill Rd",
                        "zip" => "55306",
                        "phones" => [],
                        "phone" => "16123954999",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Walser Nissan Burnsville",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_40(): void
    {
        $this->createMakes("DODGE")
            ->createStates("LA", "GA", "NONE")
            ->createTimeZones("70062", "30265", "NONE")
            ->assertParsing(
                40,
                [
                    "load_id" => "05F6250B",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "DODGE",
                            "model" => "Charger",
                            "type" => "Car",
                            "color" => "White",
                            "vin" => "2C3CDXCT0MH517854",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "John Taylor. ABG NEW ORLEANS- LA",
                        "state" => "LA",
                        "city" => "Kenner",
                        "address" => "300 Rental Blvd",
                        "zip" => "70062",
                        "phones" => [],
                        "phone" => "15042183360",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Ilich Ontiveros. ALM Newnan",
                        "state" => "GA",
                        "city" => "Newnan",
                        "address" => "40 International Park",
                        "zip" => "30265",
                        "phones" => [],
                        "phone" => "17707670000",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "ALM Newnan",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_41(): void
    {
        $this->createMakes("NISSAN")
            ->createStates("KY", "MN", "NONE")
            ->createTimeZones("40209", "55391", "NONE")
            ->assertParsing(
                41,
                [
                    "load_id" => "89C5B290",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "NISSAN",
                            "model" => "NV",
                            "type" => "Truck",
                            "color" => "White",
                            "vin" => "1N6BF0LY3KN810900",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "SCOTT REBHOLZ/JOE. HERTZ DEALER DIRECT",
                        "state" => "KY",
                        "city" => "Louisville",
                        "address" => "440 Huron Ave",
                        "zip" => "40209",
                        "phones" => [],
                        "phone" => "18154740684",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "HASSAN GHANDOUR. Walser Wayzata Nissan",
                        "state" => "MN",
                        "city" => "Wayzata",
                        "address" => "15906 W Wayzata Blvd",
                        "zip" => "55391",
                        "phones" => [],
                        "phone" => "19522018075",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Walser Wayzata Nissan",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_42(): void
    {
        $this->createMakes("JEEP")
            ->createStates("IN", "AL", "NONE")
            ->createTimeZones("46307", "35244", "NONE")
            ->assertParsing(
                42,
                [
                    "load_id" => "561B518A",
                    "dispatch_instructions" => "No flor mats 2 keys Manual",
                    "vehicles" => [
                        [
                            "year" => "2018",
                            "make" => "JEEP",
                            "model" => "Wrangler",
                            "type" => "SUV",
                            "color" => null,
                            "vin" => "1C4HJXEG9JW155370",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Scott Fitts. 231 Auto Exchange",
                        "state" => "IN",
                        "city" => "Crown Point",
                        "address" => "5101 US-231",
                        "zip" => "46307",
                        "phones" => [],
                        "phone" => "12192261231",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Inventory Manager. Hoover Toyota",
                        "state" => "AL",
                        "city" => "Hoover",
                        "address" => "2686 John Hawkins Pkwy",
                        "zip" => "35244",
                        "phones" => [],
                        "phone" => "12059782600",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Hoover Toyota (Hudson)",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_43(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("PA", "KY", "NONE")
            ->createTimeZones("19057", "40356", "NONE")
            ->assertParsing(
                43,
                [
                    "load_id" => "EF2FBB3D",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2017",
                            "make" => "CHEVROLET",
                            "model" => "Malibu",
                            "type" => "Car",
                            "color" => null,
                            "vin" => "1G1ZJ5SU2HF183923",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "MGR. PA Direct Auto Sales",
                        "state" => "PA",
                        "city" => "Levittown",
                        "address" => "6500 Headley Ave",
                        "zip" => "19057",
                        "phones" => [],
                        "phone" => "12159491999",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "MANAGER. PINNACLE FORD",
                        "state" => "KY",
                        "city" => "Nicholasville",
                        "address" => "4080 Lexington Rd",
                        "zip" => "40356",
                        "phones" => [],
                        "phone" => "18599030322",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Pinnacle Ford (Hudson)",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_44(): void
    {
        $this->createMakes("BUICK")
            ->createStates("IL", "MD", "NONE")
            ->createTimeZones("60443", "21228", "NONE")
            ->assertParsing(
                44,
                [
                    "load_id" => "867848FA",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2017",
                            "make" => "BUICK",
                            "model" => "Encore",
                            "type" => "SUV",
                            "color" => "Black",
                            "vin" => "KL4CJBSB4HB154323",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Security. Manheim Chicago",
                        "state" => "IL",
                        "city" => "Matteson",
                        "address" => "20401 Cox Ave",
                        "zip" => "60443",
                        "phones" => [],
                        "phone" => "18158064222",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "CALVIN. ANTWERPEN PRE-OWNED",
                        "state" => "MD",
                        "city" => "Baltimore",
                        "address" => "6440 Baltimore National Pike",
                        "zip" => "21228",
                        "phones" => [],
                        "phone" => "14432068087",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Antwerpen Pre-owned",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_45(): void
    {
        $this->createMakes("JEEP")
            ->createStates("MO", "TN", "NONE")
            ->createTimeZones("63044", "37311", "NONE")
            ->assertParsing(
                45,
                [
                    "load_id" => "A9C517BE",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "JEEP",
                            "model" => "Cherokee",
                            "type" => "SUV",
                            "color" => null,
                            "vin" => "1C4PJMLX9KD343672",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Security. Manheim St. Louis",
                        "state" => "MO",
                        "city" => "Bridgeton",
                        "address" => "13813 St. Charles Rock Rd",
                        "zip" => "63044",
                        "phones" => [],
                        "phone" => "13147391300",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "MARC DAUGHERTY. VEHICLES DIRECT CLEVELAND",
                        "state" => "TN",
                        "city" => "Cleveland",
                        "address" => "2490 S Lee Hwy",
                        "zip" => "37311",
                        "phones" => [],
                        "phone" => "14237907273",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Vehicles Direct (Hudson)",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_46(): void
    {
        $this->createMakes("HYUNDAI")
            ->createStates("TN", "SC", "NONE")
            ->createTimeZones("37087", "29730", "NONE")
            ->assertParsing(
                46,
                [
                    "load_id" => "070098A0",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2018",
                            "make" => "HYUNDAI",
                            "model" => "Santa Fe Sport",
                            "type" => "SUV",
                            "color" => null,
                            "vin" => "5NMZT3LB5JH071631",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "MGR. South Cumberland Auto",
                        "state" => "TN",
                        "city" => "Lebanon",
                        "address" => "500 S Cumberland St",
                        "zip" => "37087",
                        "phones" => [],
                        "phone" => "16157844944",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "MGR. Rock Hill Nissan",
                        "state" => "SC",
                        "city" => "Rock Hill",
                        "address" => "550 Galleria Blvd",
                        "zip" => "29730",
                        "phones" => [],
                        "phone" => "18033668171",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Rock Hill Nissan (Hudson)",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_47(): void
    {
        $this->createMakes("KIA")
            ->createStates("MI", "GA", "NONE")
            ->createTimeZones("49512", "30291", "NONE")
            ->assertParsing(
                47,
                [
                    "load_id" => "A2BB2A61",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "KIA",
                            "model" => "Sportage",
                            "type" => "SUV",
                            "color" => "Gray",
                            "vin" => "KNDPM3AC2L7778339",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Kellie Coykendall. ABG GRAND RAPIDS- MI",
                        "state" => "MI",
                        "city" => "Grand Rapids",
                        "address" => "4560 Pederson Ct SE",
                        "zip" => "49512",
                        "phones" => [],
                        "phone" => "16169774090",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Ben McIntyre. ALM KIA SOUTH",
                        "state" => "GA",
                        "city" => "Union City",
                        "address" => "4310 Jonesboro Rd",
                        "zip" => "30291",
                        "phones" => [],
                        "phone" => "14049844990",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "ALM Kia South",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_48(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("MI", "GA", "NONE")
            ->createTimeZones("49512", "30096", "NONE")
            ->assertParsing(
                48,
                [
                    "load_id" => "17E0AD53",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "TOYOTA",
                            "model" => "Camry",
                            "type" => "Car",
                            "color" => "Red",
                            "vin" => "4T1G11AK0MU444023",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Kellie Coykendall. ABG GRAND RAPIDS- MI",
                        "state" => "MI",
                        "city" => "Grand Rapids",
                        "address" => "4560 Pederson Ct SE",
                        "zip" => "49512",
                        "phones" => [],
                        "phone" => "16169774090",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Issac Vera. ALM Gwinnett",
                        "state" => "GA",
                        "city" => "Duluth",
                        "address" => "2520 Pleasant Hill Rd",
                        "zip" => "30096",
                        "phones" => [],
                        "phone" => "13154781320",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "ALM Gwinnett",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_49(): void
    {
        $this->createMakes("KIA")
            ->createStates("MI", "GA", "NONE")
            ->createTimeZones("49512", "30096", "NONE")
            ->assertParsing(
                49,
                [
                    "load_id" => "17E0AD53",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "KIA",
                            "model" => "K5",
                            "type" => "Car",
                            "color" => "Silver",
                            "vin" => "5XXG14J21MG069609",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Kellie Coykendall. ABG GRAND RAPIDS- MI",
                        "state" => "MI",
                        "city" => "Grand Rapids",
                        "address" => "4560 Pederson Ct SE",
                        "zip" => "49512",
                        "phones" => [],
                        "phone" => "16169774090",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Issac Vera. ALM Gwinnett",
                        "state" => "GA",
                        "city" => "Duluth",
                        "address" => "2520 Pleasant Hill Rd",
                        "zip" => "30096",
                        "phones" => [],
                        "phone" => "13154781320",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "ALM Gwinnett",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_50(): void
    {
        $this->createMakes("FORD")
            ->createStates("VA", "MI", "NONE")
            ->createTimeZones("22664", "48150", "NONE")
            ->assertParsing(
                50,
                [
                    "load_id" => "7A0C140B",
                    "dispatch_instructions" => "2 keys",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "FORD",
                            "model" => "F-150",
                            "type" => "Truck",
                            "color" => "White",
                            "vin" => "1FTMF1CB2NKE48786",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Dan Hornick. Criswell Ford",
                        "state" => "VA",
                        "city" => "Woodstock",
                        "address" => "430 Hoover Rd",
                        "zip" => "22664",
                        "phones" => [],
                        "phone" => "14109849667",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Manager. Lennox International",
                        "state" => "MI",
                        "city" => "Livonia",
                        "address" => "37671 Schoolcraft St",
                        "zip" => "48150",
                        "phones" => [],
                        "phone" => "13133329001",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Criswell Ford",
                    ],
                    "pickup_date" => "",
                    "delivery_date" => "",
                ]
            );
    }
}
