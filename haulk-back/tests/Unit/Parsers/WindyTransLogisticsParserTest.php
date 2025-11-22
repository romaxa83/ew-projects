<?php

namespace Tests\Unit\Parsers;

use App\Exceptions\Parser\PdfFileException;
use Throwable;

class WindyTransLogisticsParserTest extends BaseParserTest
{
    /**
     * @throws Throwable
     */
    public function test_1(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("PA", "NY", "MI")
            ->createTimeZones("16148", "13039", "48174")
            ->assertParsing(
                1,
                [
                    "load_id" => "69757403",
                    "pickup_date" => "03/07/2023",
                    "delivery_date" => "03/09/2023",
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "TOYOTA",
                            "model" => "4RUNNER",
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "MEL GRATA CHEVROLET TOYOTA",
                        "state" => "PA",
                        "city" => "HERMITAGE",
                        "address" => "2757 E STATE ST",
                        "zip" => "16148",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17243477702",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "ADESA Syracuse",
                        "state" => "NY",
                        "city" => "Cicero",
                        "address" => "5930 New York 31",
                        "zip" => "13039",
                        "phones" => [
                            [
                                "number" => "13156980553",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "13156992792",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD NSC - BUSINESS",
                        "address" => "10701 MIDDLEBELT ROAD",
                        "city" => "ROMULUS",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18882782793",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 375,
                    ],
                    "dispatch_instructions" => "Pickup: **Contact Sales Office SALES OFFICE 724-734- 4074 this is a US Bank/Defi"
                        . " unit** MUST SCAN FULL 17 DIGIT VIN AND TAKE PHOTO / MUST DECODE WITH YEAR,MAKE,MODEL BEFORE YOU"
                        . " DELIVER OUT ** \": Drivers must have Adesa BOL to pick up unit. Origin signature required. \"\nDelivery:"
                        . " Wednesday auction sale day do not arrive until after 3 pm. Make them wait while you inspect and"
                        . " sign for units. \"STI is absolutely not permitted. Driver must leave a copy of the Adesa BOL on"
                        . " the dash of unit upon delivery. Signed Adesa and United Road BOL BOLs are required. \" . Please"
                        . " email signed Adesa and United Road BOL to AcctsPayURS@unitedroad.com or fax to 855-407-8355 for"
                        . " payment approval. NO STI.",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_2(): void
    {
        $this->expectException(PdfFileException::class);
        $this->createMakes("NONE")
            ->createStates("PA", "NY", "MI")
            ->createTimeZones("16148", "13039", "48174")
            ->assertParsing(
                2,
                []
            );
    }

    /**
     * @throws Throwable
     */
    public function test_3(): void
    {
        $this->createMakes("GMC")
            ->createStates("TN", "NC", "TN")
            ->createTimeZones("37174", "27609", "37174")
            ->assertParsing(
                3,
                [
                    "load_id" => "63599506",
                    "pickup_date" => "03/08/2022",
                    "delivery_date" => "03/08/2022",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "GMC",
                            "model" => "Acadia",
                            "vin" => "1GKKNPLSXNZ137888",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "SPRING HILL GM MFG-PLANT",
                        "state" => "TN",
                        "city" => "Spring Hill",
                        "address" => "100 SATURN PKWY",
                        "zip" => "37174",
                        "phones" => [],
                        "fax" => "18554078355",
                        "phone" => "19314862771",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "THOMPSON BUICK GMC CADILLAC",
                        "state" => "NC",
                        "city" => "RALEIGH",
                        "address" => "2600 WAKE FOREST ROAD",
                        "zip" => "27609",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19198340311",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD SPRING HILL",
                        "address" => "1000 SATURN PARKWAY",
                        "city" => "SPRING HILL",
                        "state" => "TN",
                        "zip" => "37174",
                        "phones" => [],
                        "fax" => "18554078355",
                        "phone" => "19314862771",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 439.72,
                    ],
                    "dispatch_instructions" => "Pickup: VTAS MOBILE APP MUST BE USED @ PICKUP & DELIVERY! Office 931-486-2771\nDelivery:"
                        . " Night drop on creekside dr next to dealership, drop box is in the Quick Lube area",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_4(): void
    {
        $this->createMakes("NISSAN")
            ->createStates("IL", "MA", "MI")
            ->createTimeZones("60443", "02128", "48174")
            ->assertParsing(
                4,
                [
                    "load_id" => "65135914",
                    "pickup_date" => "06/07/2022",
                    "delivery_date" => "06/08/2022",
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "Nissan",
                            "model" => "Frontier",
                            "vin" => "1N6ED0EB4MN704194",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2021",
                            "make" => "Nissan",
                            "model" => "Titan",
                            "vin" => "1N6AA1ED8MN523663",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2020",
                            "make" => "Nissan",
                            "model" => "Altima",
                            "vin" => "1N4AL4FV0LC139110",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "COX Automotive",
                        "state" => "IL",
                        "city" => "MATTESON",
                        "address" => "20401 COX AVE",
                        "zip" => "60443",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "BOSTON AP DS (01700-98)",
                        "state" => "MA",
                        "city" => "Boston",
                        "address" => "450 William F McClellan Highway",
                        "zip" => "02128",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD LOGISTICS SINGLE VEHICLE",
                        "address" => "10701 Middlebelt Road",
                        "city" => "Romulus",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => "17349477923",
                        "phone" => "18882782793",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 2099.8,
                    ],
                    "dispatch_instructions" => "Pickup: RELEASE ATTACHED Pick up hours Mon-Sun 6a-10p RELEASE ATTACHED Pick"
                        . " up hours Mon-Sun 6a-10p RELEASE ATTACHED Pick up hours Mon-Sun 6a-10p\nDelivery: (617) 561-3070"
                        . " NO STI DELIVERIES. MUST DELIVER DURING BUSINESS HOURS AND MUST OBTAIN SIGNATURE NO STI DELIVERIES."
                        . " MUST DELIVER DURING BUSINESS HOURS AND MUST OBTAIN SIGNATURE NO STI DELIVERIES. MUST DELIVER DURING"
                        . " BUSINESS HOURS AND MUST OBTAIN SIGNATURE",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_5(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("TX", "TX", "TX")
            ->createTimeZones("77417", "79703", "77029")
            ->assertParsing(
                5,
                [
                    "load_id" => "65144020",
                    "pickup_date" => "06/26/2022",
                    "delivery_date" => "06/27/2022",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Chevrolet",
                            "model" => "Equinox",
                            "vin" => "3GNAXKEV7NS192704",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "ROSENBERG TX-RAMP",
                        "state" => "TX",
                        "city" => "Beasley",
                        "address" => "11538 Gin Road",
                        "zip" => "77417",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18328417899",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "LITHIA CM, INC STORE#L011 V#85453",
                        "state" => "TX",
                        "city" => "MIDLAND",
                        "address" => "4104 W WALL",
                        "zip" => "79703",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14326949601",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD HOUSTON",
                        "address" => "131 East Loop N",
                        "city" => "Houston",
                        "state" => "TX",
                        "zip" => "77029",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17136733400",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 375.6,
                    ],
                    "dispatch_instructions" => "Pickup: Send damage L.O.N to csi@unitedroad.com or fax to 888-275-8051."
                        . " Please include pictures and estimate of damage. Claims Investigation Department. Phone 405-619-1400"
                        . " MUST STOP AND SIGN IN AT GUARD GATE. FLASHERS NEED TO BE FLASHING WHEN ON YARD. DRIVER MUST BRING"
                        . " COPY OF MANIFEST TO LOAD - RAMP WILL NOT PRINT OUT PAPERWORK FOR DRIVER\nDelivery: I-20 TO EXIT"
                        . " 134 MIDKIFF ROAD. NORTH TO 3RD LIGHT, WEST ON WALL STREET. ENTER FAR WEST GATE AND DRIVE TO BACK"
                        . " OF LOT TO (CONDITIONING CENTER). SEE PERSONNEL INSIDE PRIOR TO DROPPING. NO NIGHT DROPS",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_6(): void
    {
        $this->createMakes("MITSUBISHI")
            ->createStates("TX", "CO", "MI")
            ->createTimeZones("75061", "80249", "48174")
            ->assertParsing(
                6,
                [
                    "load_id" => "65148993",
                    "pickup_date" => "06/07/2022",
                    "delivery_date" => "06/07/2022",
                    "vehicles" => [
                        [
                            "year" => "2018",
                            "make" => "Mitsubishi",
                            "model" => "Outlander Sport",
                            "vin" => "JA4AP3AWXJU025321",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Ally Financial",
                        "state" => "TX",
                        "city" => "Irving",
                        "address" => "219 N Loop 12",
                        "zip" => "75061",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "DENVER AP DS (02100-11)",
                        "state" => "CO",
                        "city" => "DENVER",
                        "address" => "24890 E 78TH AVE",
                        "zip" => "80249",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD LOGISTICS SINGLE VEHICLE",
                        "address" => "10701 Middlebelt Road",
                        "city" => "Romulus",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => "17349477923",
                        "phone" => "18882782793",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 404.49,
                    ],
                    "dispatch_instructions" => "Pickup: RELEASE ATTACHED\nDelivery: (866) 434-2226 NO STI DELIVERIES. MUST"
                        . " DELIVER DURING BUSINESS HOURS AND MUST OBTAIN SIGNATURE",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_7(): void
    {
        $this->createMakes("MAZDA")
            ->createStates("AR", "TN", "MI")
            ->createTimeZones("72142", "37849", "48174")
            ->assertParsing(
                7,
                [
                    "load_id" => "65156911",
                    "pickup_date" => "06/09/2022",
                    "delivery_date" => "06/10/2022",
                    "vehicles" => [
                        [
                            "year" => "1994",
                            "make" => "Mazda",
                            "model" => "MX3",
                            "vin" => "JM1EC4350R0322355",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "IAA Little Rock",
                        "state" => "AR",
                        "city" => "Scott",
                        "address" => "4900 S Kerr Rd",
                        "zip" => "72142",
                        "phones" => [],
                        "fax" => null,
                        "instruction" => null,
                        "phone" => "15019612886",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Bryan Johnson",
                        "state" => "TN",
                        "city" => "Powell",
                        "address" => "3319 Clinton Hwy",
                        "zip" => "37849",
                        "phones" => [
                            [
                                "number" => "18653882468",
                            ],
                        ],
                        "fax" => null,
                        "instruction" => null,
                        "phone" => "18659453406",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD LOGISTICS SINGLE VEHICLE",
                        "address" => "10701 Middlebelt Road",
                        "city" => "Romulus",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => "17349477923",
                        "phone" => "18882782793",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 513.5,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_8(): void
    {
        $this->createMakes("FORD", "DODGE")
            ->createStates("TN", "SC", "MI")
            ->createTimeZones("37129", "29170", "48174")
            ->assertParsing(
                8,
                [
                    "load_id" => "65165434",
                    "pickup_date" => "06/09/2022",
                    "delivery_date" => "06/10/2022",
                    "vehicles" => [
                        [
                            "year" => "2015",
                            "make" => "Ford",
                            "model" => "Explorer",
                            "vin" => "1FM5K7B81FGC28550",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2016",
                            "make" => "Dodge",
                            "model" => "Charger",
                            "vin" => "2C3CDXBG4GH237217",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "DEALERS AUTO AUCTION of MURFREESBORO",
                        "state" => "TN",
                        "city" => "MURFREESBORO",
                        "address" => "1815 OLD FORT PARKWAY",
                        "zip" => "37129",
                        "phones" => [],
                        "fax" => null,
                        "instruction" => null,
                        "phone" => "16152172848",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "SOUTH CAROLINA SURPLUS PROPERTY OFFICE",
                        "state" => "SC",
                        "city" => "WEST COLUMBIA",
                        "address" => "1441 BOSTON AVENUE",
                        "zip" => "29170",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18038966880",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD NSC - BUSINESS",
                        "address" => "10701 MIDDLEBELT ROAD",
                        "city" => "ROMULUS",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18882782793",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 955,
                    ],
                    "dispatch_instructions" => "Delivery: NO STI",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_9(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("TN", "OH", "MI")
            ->createTimeZones("38301", "43229", "48174")
            ->assertParsing(
                9,
                [
                    "load_id" => "65301178",
                    "pickup_date" => "06/21/2022",
                    "delivery_date" => "06/22/2022",
                    "vehicles" => [
                        [
                            "year" => "1971",
                            "make" => "CHEVROLET",
                            "model" => "SUBURBAN",
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Dennis Mitchell Auto.",
                        "state" => "TN",
                        "city" => "Jackson",
                        "address" => "896 Hollywood Dr",
                        "zip" => "38301",
                        "phones" => [],
                        "fax" => null,
                        "instruction" => null,
                        "phone" => "17314241486",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Drive Direct",
                        "state" => "OH",
                        "city" => "Columbus",
                        "address" => "2361 Morse Road",
                        "zip" => "43229",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD NSC - BUSINESS",
                        "address" => "10701 MIDDLEBELT ROAD",
                        "city" => "ROMULUS",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18882782793",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 740,
                    ],
                    "dispatch_instructions" => "Delivery: (614) 476-4118 NO STI, If you drop at the wrong delivery location"
                        . " you will be back charged to correct this! please drop at this address only!",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_10(): void
    {
        $this->createMakes("MERCEDES-BENZ")
            ->createStates("AL", "PA", "MS")
            ->createTimeZones("35490", "15301", "39046")
            ->assertParsing(
                10,
                [
                    "load_id" => "65313010",
                    "pickup_date" => "06/22/2022",
                    "delivery_date" => "06/23/2022",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Mercedes-Benz",
                            "model" => "GLE",
                            "vin" => "4JGFB4KB2NA762110",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "VANCE DOMESTIC MARSHALLING YARD",
                        "state" => "AL",
                        "city" => "Vance",
                        "address" => "1765 Vance Municipal Drive",
                        "zip" => "35490",
                        "phones" => [],
                        "fax" => null,
                        "instruction" => null,
                        "phone" => "11234567890",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "WASHINGTON APA NMB, LLC",
                        "state" => "PA",
                        "city" => "Washington",
                        "address" => "470 Washington Road",
                        "zip" => "15301",
                        "phones" => null,
                        "fax" => null,
                        "instruction" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD CANTON",
                        "address" => "300 Nissan Parkway Gate 6",
                        "city" => "Canton",
                        "state" => "MS",
                        "zip" => "39046",
                        "phones" => [],
                        "fax" => "16018552665",
                        "phone" => "16018550402",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 529,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_11(): void
    {
        $this->createMakes("MERCEDES-BENZ")
            ->createStates("AL", "PA", "MS")
            ->createTimeZones("35490", "15090", "39046")
            ->assertParsing(
                11,
                [
                    "load_id" => "65328686",
                    "pickup_date" => "06/22/2022",
                    "delivery_date" => "06/23/2022",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Mercedes-Benz",
                            "model" => "GLE",
                            "vin" => "4JGFB5KB5NA742097",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "VANCE DOMESTIC MARSHALLING YARD",
                        "state" => "AL",
                        "city" => "Vance",
                        "address" => "1765 Vance Municipal Drive",
                        "zip" => "35490",
                        "phones" => [],
                        "fax" => null,
                        "instruction" => null,
                        "phone" => "11234567890",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "TEAM RAHAL OF PITTSBURGH, INC.",
                        "state" => "PA",
                        "city" => "WEXFORD",
                        "address" => "10701 PERRY HIGHWAY",
                        "zip" => "15090",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17249359300",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD CANTON",
                        "address" => "300 Nissan Parkway Gate 6",
                        "city" => "Canton",
                        "state" => "MS",
                        "zip" => "39046",
                        "phones" => [],
                        "fax" => "16018552665",
                        "phone" => "16018550402",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 555.8,
                    ],
                    "dispatch_instructions" => "Delivery: M-Thur 8-8pm, Fri 8-6pm, Sat 8-5pm, STI ok. Deliver to drop lot"
                        . " behind Whole Foods at 10576 Perry Hwy.",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_12(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("NH", "OH", "MI")
            ->createTimeZones("03079", "44301", "48174")
            ->assertParsing(
                12,
                [
                    "load_id" => "65354516",
                    "pickup_date" => "06/21/2022",
                    "delivery_date" => "06/22/2022",
                    "vehicles" => [
                        [
                            "year" => "2002",
                            "make" => "Toyota",
                            "model" => "Sequoia",
                            "vin" => "5TDBT44A92S092929",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "IAA Manchester",
                        "state" => "NH",
                        "city" => "Salem",
                        "address" => "75 Lowell Rd",
                        "zip" => "03079",
                        "phones" => [],
                        "fax" => null,
                        "instruction" => null,
                        "phone" => "16038932300",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "idrissa sapato braima",
                        "state" => "OH",
                        "city" => "Akron",
                        "address" => "434 Clinton Ave",
                        "zip" => "44301",
                        "phones" => [],
                        "fax" => null,
                        "instruction" => null,
                        "phone" => "13304755678",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD LOGISTICS SINGLE VEHICLE",
                        "address" => "10701 Middlebelt Road",
                        "city" => "Romulus",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => "17349477923",
                        "phone" => "18882782793",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 637.12,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_13(): void
    {
        $this->createMakes("FORD")
            ->createStates("IA", "OH", "MI")
            ->createTimeZones("50111", "43229", "48174")
            ->assertParsing(
                13,
                [
                    "load_id" => "65364657",
                    "pickup_date" => "06/22/2022",
                    "delivery_date" => "06/23/2022",
                    "vehicles" => [
                        [
                            "year" => "2013",
                            "make" => "Ford",
                            "model" => "Edge",
                            "vin" => "2FMDK4JC0DBB74405",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "ADESA DES MOINES, INC",
                        "state" => "IA",
                        "city" => "GRIMES",
                        "address" => "1800 GATEWAY DRIVE",
                        "zip" => "50111",
                        "phones" => null,
                        "fax" => null,
                        "instruction" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Drive Direct",
                        "state" => "OH",
                        "city" => "Columbus",
                        "address" => "2361 Morse Road",
                        "zip" => "43229",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD NSC - BUSINESS",
                        "address" => "10701 MIDDLEBELT ROAD",
                        "city" => "ROMULUS",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18882782793",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 710,
                    ],
                    "dispatch_instructions" => "Delivery: (614) 476-4118 NO STI, If you drop at the wrong delivery location"
                        . " you will be back charged to correct this! please drop at this address only!",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_14(): void
    {
        $this->createMakes("MERCEDES-BENZ")
            ->createStates("AL", "OH", "MS")
            ->createTimeZones("35490", "44515", "39046")
            ->assertParsing(
                14,
                [
                    "load_id" => "65369743",
                    "pickup_date" => "06/24/2022",
                    "delivery_date" => "06/25/2022",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Mercedes-Benz",
                            "model" => "GLE",
                            "vin" => "4JGFB4KB3NA783435",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "VANCE DOMESTIC MARSHALLING YARD",
                        "state" => "AL",
                        "city" => "Vance",
                        "address" => "1765 Vance Municipal Drive",
                        "zip" => "35490",
                        "phones" => [],
                        "fax" => null,
                        "instruction" => null,
                        "phone" => "11234567890",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "FRED MARTIN FORD, INC.",
                        "state" => "OH",
                        "city" => "YOUNGSTOWN",
                        "address" => "4701 MAHONING AVE.",
                        "zip" => "44515",
                        "phones" => [],
                        "fax" => null,
                        "instruction" => null,
                        "phone" => "13307932444",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD CANTON",
                        "address" => "300 Nissan Parkway Gate 6",
                        "city" => "Canton",
                        "state" => "MS",
                        "zip" => "39046",
                        "phones" => [],
                        "fax" => "16018552665",
                        "phone" => "16018550402",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 555.89,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_15(): void
    {
        $this->createMakes("FORD")
            ->createStates("NJ", "MI", "MI")
            ->createTimeZones("07114", "49079", "48174")
            ->assertParsing(
                15,
                [
                    "load_id" => "65370951",
                    "pickup_date" => "06/22/2022",
                    "delivery_date" => "06/23/2022",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Ford",
                            "model" => "Transit Cargo",
                            "vin" => "1FTRU8XG3NKA06111",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2022",
                            "make" => "Ford",
                            "model" => "Transit Cargo",
                            "vin" => "1FTRU8XG0NKA06115",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "NEWARK-FAPS",
                        "state" => "NJ",
                        "city" => "Newark",
                        "address" => "371 Craneway St",
                        "zip" => "07114",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19737159606",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "TAPPERS FORD",
                        "state" => "MI",
                        "city" => "PAW PAW",
                        "address" => "816 Kalamazoo",
                        "zip" => "49079",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12696573134",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD LOGISTICS SINGLE VEHICLE",
                        "address" => "10701 Middlebelt Road",
                        "city" => "Romulus",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => "17349477923",
                        "phone" => "18882782793",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 2940,
                    ],
                    "dispatch_instructions" => "Pickup: Open 7am to 9pm (FAPS staff on 7am-3pm) 7 Days Per Week Dents and"
                        . " scratches that break the paint surface will be signed off for. 973-589-5656 during FAPS hours.\nDelivery:"
                        . " Effective immediately, drivers are required to wear face masks when arriving at all Ford facilities"
                        . " due to the recent rise in COVID- 19 cases. Thank you for your cooperation. Deliver:Customer parking"
                        . " area. North side of building 2nd: DO NOT DELIVER: After Hours:Service drop box Addtl:",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_16(): void
    {
        $this->createMakes("FORD")
            ->createStates("NJ", "MI", "MI")
            ->createTimeZones("07114", "49079", "48174")
            ->assertParsing(
                16,
                [
                    "load_id" => "65371292",
                    "pickup_date" => "06/23/2022",
                    "delivery_date" => "06/24/2022",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Ford",
                            "model" => "Transit Cargo",
                            "vin" => "1FTRU8XG2NKA06116",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2022",
                            "make" => "Ford",
                            "model" => "Transit Cargo",
                            "vin" => "1FTRU8XG9NKA21146",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "NEWARK-FAPS",
                        "state" => "NJ",
                        "city" => "Newark",
                        "address" => "371 Craneway St",
                        "zip" => "07114",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19737159606",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "TAPPERS FORD",
                        "state" => "MI",
                        "city" => "PAW PAW",
                        "address" => "816 Kalamazoo",
                        "zip" => "49079",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12696573134",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD LOGISTICS SINGLE VEHICLE",
                        "address" => "10701 Middlebelt Road",
                        "city" => "Romulus",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => "17349477923",
                        "phone" => "18882782793",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 2940,
                    ],
                    "dispatch_instructions" => "Pickup: Open 7am to 9pm (FAPS staff on 7am-3pm) 7 Days Per Week Dents and"
                        . " scratches that break the paint surface will be signed off for. 973-589-5656 during FAPS hours.\nDelivery:"
                        . " Effective immediately, drivers are required to wear face masks when arriving at all Ford facilities"
                        . " due to the recent rise in COVID- 19 cases. Thank you for your cooperation. Deliver:Customer parking"
                        . " area. North side of building 2nd: DO NOT DELIVER: After Hours:Service drop box Addtl:",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_17(): void
    {
        $this->createMakes("FORD")
            ->createStates("NJ", "MI", "MI")
            ->createTimeZones("07114", "49079", "48174")
            ->assertParsing(
                17,
                [
                    "load_id" => "65371557",
                    "pickup_date" => "06/23/2022",
                    "delivery_date" => "06/24/2022",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Ford",
                            "model" => "Transit Cargo",
                            "vin" => "1FTRU8XG5NKA06143",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2022",
                            "make" => "Ford",
                            "model" => "Transit Cargo",
                            "vin" => "1FTRU8XG7NKA06161",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "NEWARK-FAPS",
                        "state" => "NJ",
                        "city" => "Newark",
                        "address" => "371 Craneway St",
                        "zip" => "07114",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19737159606",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "TAPPERS FORD",
                        "state" => "MI",
                        "city" => "PAW PAW",
                        "address" => "816 Kalamazoo",
                        "zip" => "49079",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12696573134",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD LOGISTICS SINGLE VEHICLE",
                        "address" => "10701 Middlebelt Road",
                        "city" => "Romulus",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => "17349477923",
                        "phone" => "18882782793",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 2940,
                    ],
                    "dispatch_instructions" => "Pickup: Open 7am to 9pm (FAPS staff on 7am-3pm) 7 Days Per Week Dents and"
                        . " scratches that break the paint surface will be signed off for. 973-589-5656 during FAPS hours.\nDelivery:"
                        . " Effective immediately, drivers are required to wear face masks when arriving at all Ford facilities"
                        . " due to the recent rise in COVID- 19 cases. Thank you for your cooperation. Deliver:Customer parking"
                        . " area. North side of building 2nd: DO NOT DELIVER: After Hours:Service drop box Addtl:",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_18(): void
    {
        $this->createMakes("FORD")
            ->createStates("NJ", "MI", "MI")
            ->createTimeZones("07114", "49079", "48174")
            ->assertParsing(
                18,
                [
                    "load_id" => "65378319",
                    "pickup_date" => "06/23/2022",
                    "delivery_date" => "06/24/2022",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Ford",
                            "model" => "Transit Cargo",
                            "vin" => "1FTRU8XGXNKA06154",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2022",
                            "make" => "Ford",
                            "model" => "Transit Cargo",
                            "vin" => "1FTRU8XG9NKA06159",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "NEWARK-FAPS",
                        "state" => "NJ",
                        "city" => "Newark",
                        "address" => "371 Craneway St",
                        "zip" => "07114",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19737159606",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "TAPPERS FORD",
                        "state" => "MI",
                        "city" => "PAW PAW",
                        "address" => "816 Kalamazoo",
                        "zip" => "49079",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12696573134",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD LOGISTICS SINGLE VEHICLE",
                        "address" => "10701 Middlebelt Road",
                        "city" => "Romulus",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => "17349477923",
                        "phone" => "18882782793",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 2940,
                    ],
                    "dispatch_instructions" => "Pickup: Open 7am to 9pm (FAPS staff on 7am-3pm) 7 Days Per Week Dents and"
                        . " scratches that break the paint surface will be signed off for. 973-589-5656 during FAPS hours.\nDelivery:"
                        . " Effective immediately, drivers are required to wear face masks when arriving at all Ford facilities"
                        . " due to the recent rise in COVID- 19 cases. Thank you for your cooperation. Deliver:Customer parking"
                        . " area. North side of building 2nd: DO NOT DELIVER: After Hours:Service drop box Addtl:",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_19(): void
    {
        $this->createMakes("GMC")
            ->createStates("OH", "WI", "MI")
            ->createTimeZones("43302", "53566", "48174")
            ->assertParsing(
                19,
                [
                    "load_id" => "68231849",
                    "pickup_date" => "12/26/2022",
                    "delivery_date" => "12/27/2022",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "GMC",
                            "model" => "Sierra 1500",
                            "vin" => "1GTUUGEL6NZ568922",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "MARION INDUSTRIAL",
                        "state" => "OH",
                        "city" => "Marion",
                        "address" => "3007 Harding Highway East",
                        "zip" => "43302",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "DEARTH BUICK GMC",
                        "state" => "WI",
                        "city" => "MONROE",
                        "address" => "602 8TH ST",
                        "zip" => "53566",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16083288181",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD LOGISTICS SINGLE VEHICLE",
                        "address" => "10701 Middlebelt Road",
                        "city" => "Romulus",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => "17349477923",
                        "phone" => "18882782793",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 800,
                    ],
                    "dispatch_instructions" => "Pickup: 555.555.5555 * Magnus app 100% required *Pick up must be within"
                        . " 48 hours of assigning to order *Call UR immediately for any issues 888.278.2793 *Dates will not"
                        . " be pushed; if requesting push you will removed from order\nDelivery: M-F 7:30-4:30, Sat 8-12,"
                        . " STI OK Drop Box on West side of be building.",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_20(): void
    {
        $this->createMakes("RAM")
            ->createStates("TX", "CO", "MI")
            ->createTimeZones("79118", "80231", "48174")
            ->assertParsing(
                20,
                [
                    "load_id" => "68348672",
                    "pickup_date" => "12/23/2022",
                    "delivery_date" => "12/26/2022",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "RAM",
                            "model" => "RAM PICKUP 1500",
                            "vin" => "1C6RREMT3NN253667",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "IAA Amarillo",
                        "state" => "TX",
                        "city" => "Amarillo",
                        "address" => "11150 S.FM 1541",
                        "zip" => "79118",
                        "phones" => [],
                        "fax" => null,
                        "instruction" => null,
                        "phone" => "18066221322",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Javier Solis-Ramos",
                        "state" => "CO",
                        "city" => "DENVER",
                        "address" => "2150 S VALENTIA ST",
                        "zip" => "80231",
                        "phones" => [],
                        "fax" => null,
                        "instruction" => null,
                        "phone" => "13032839000",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD LOGISTICS SINGLE VEHICLE",
                        "address" => "10701 Middlebelt Road",
                        "city" => "Romulus",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => "17349477923",
                        "phone" => "18882782793",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 850,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_21(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("OH", "TX", "MI")
            ->createTimeZones("43302", "77065", "48174")
            ->assertParsing(
                21,
                [
                    "load_id" => "68509633",
                    "pickup_date" => "12/24/2022",
                    "delivery_date" => "12/27/2022",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "CHEVROLET",
                            "model" => "SILVERADO 1500",
                            "vin" => "1GCPADED0NZ542510",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2022",
                            "make" => "CHEVROLET",
                            "model" => "SILVERADO 1500",
                            "vin" => "1GCPADED8NZ562570",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "MARION INDUSTRIAL",
                        "state" => "OH",
                        "city" => "Marion",
                        "address" => "3007 Harding Highway East",
                        "zip" => "43302",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "LONE STAR CHEVROLET",
                        "state" => "TX",
                        "city" => "JERSEY VILLAGE",
                        "address" => "18900 NORTHWEST FWY",
                        "zip" => "77065",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12815177000",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD LOGISTICS SINGLE VEHICLE",
                        "address" => "10701 Middlebelt Road",
                        "city" => "Romulus",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => "17349477923",
                        "phone" => "18882782793",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 2000,
                    ],
                    "dispatch_instructions" => "Pickup: 555.555.5555 * Magnus app 100% required *Pick up must be within"
                        . " 48 hours of assigning to order *Call UR immediately for any issues 888.278.2793 *Dates will not"
                        . " be pushed; if requesting push you will removed from order * Magnus app 100% required *Pick up"
                        . " must be within 48 hours of assigning to order *Call UR immediately for any issues 888.278.2793"
                        . " *Dates will not be pushed; if requesting push you will removed from order\nDelivery: 281.517.1968"
                        . " TELE # 281-517-7015 CONTACT RICHARD be",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_22(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("OH", "WI", "MI")
            ->createTimeZones("43302", "53576", "48174")
            ->assertParsing(
                22,
                [
                    "load_id" => "68514192",
                    "pickup_date" => "12/26/2022",
                    "delivery_date" => "12/27/2022",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Chevrolet",
                            "model" => "Silverado 1500",
                            "vin" => "1GCPDBEK8NZ567570",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "MARION INDUSTRIAL",
                        "state" => "OH",
                        "city" => "Marion",
                        "address" => "3007 Harding Highway East",
                        "zip" => "43302",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "BURTNESS CHEVROLET, INC.",
                        "state" => "WI",
                        "city" => "ORFORDVILLE",
                        "address" => "802 Genesis Dr.",
                        "zip" => "53576",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16088792973",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD LOGISTICS SINGLE VEHICLE",
                        "address" => "10701 Middlebelt Road",
                        "city" => "Romulus",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => "17349477923",
                        "phone" => "18882782793",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 700,
                    ],
                    "dispatch_instructions" => "Pickup: 555.555.5555\nDelivery: 608.879.2784",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_23(): void
    {
        $this->createMakes("HONDA")
            ->createStates("LA", "IN", "MI")
            ->createTimeZones("70809", "46280", "48174")
            ->assertParsing(
                23,
                [
                    "load_id" => "68537880",
                    "pickup_date" => "12/20/2022",
                    "delivery_date" => "12/28/2022",
                    "vehicles" => [
                        [
                            "year" => "2014",
                            "make" => "Honda",
                            "model" => "Accord Sedan",
                            "vin" => "1HGCR2F31EA120067",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "CARMAX 7187",
                        "state" => "LA",
                        "city" => "BATON ROUGE",
                        "address" => "6768 SIEGEN LANE",
                        "zip" => "70809",
                        "phones" => [],
                        "fax" => "12256637297",
                        "phone" => "12256637292",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "CARMAX 7144",
                        "state" => "IN",
                        "city" => "INDIANAPOLIS",
                        "address" => "9750 NORTH GRAY ROAD",
                        "zip" => "46280",
                        "phones" => [
                            [
                                "number" => "13175747806",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "13175749336",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD CARMAX",
                        "address" => "10701 Middlebelt Road",
                        "city" => "Romulus",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17349477900",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 600,
                    ],
                    "dispatch_instructions" => "Pickup: Receiving Mon - Fri 8AM to 7PM Sat & Sun Closed No shipping/receiving"
                        . " on Sat. Store is closed on Sundays.\nDelivery: Receiving Hours: Mon – Fri 8 AM to 7 PM & Sat 8-5pm"
                        . " If staffing allows, Customers Transfers will be accepted outside delivery hours. Please call store"
                        . " prior for permission",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_24(): void
    {
        $this->createMakes("FORD")
            ->createStates("VA", "MS", "MI")
            ->createTimeZones("24153", "38701", "48174")
            ->assertParsing(
                24,
                [
                    "load_id" => "68542460",
                    "pickup_date" => "12/24/2022",
                    "delivery_date" => "12/25/2022",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Ford",
                            "model" => "Edge",
                            "vin" => "2FMPK4G96NBA74691",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "BERGLUND FORD",
                        "state" => "VA",
                        "city" => "SALEM",
                        "address" => "834 East Main Street",
                        "zip" => "24153",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15403897291",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Paul Hughes",
                        "state" => "MS",
                        "city" => "Greenville",
                        "address" => "HIGHWAY 1 S",
                        "zip" => "38701",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD NSC - BUSINESS",
                        "address" => "10701 MIDDLEBELT ROAD",
                        "city" => "ROMULUS",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18882782793",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 1000,
                    ],
                    "dispatch_instructions" => "Pickup: For Booking, issues or delays please call/text please call/text"
                        . " Shayna Mears @ 734.853.1913 or 2355 email at smears@unitedroad.com or call General office 888-278-2793."
                        . " My office hours are Monday- (662) Friday 7:00AM-4:00PM MAGNUS DRIVER app is required to be used"
                        . " when hauling for United Road. If you do not have this app please down load it off the app store."
                        . " Difficulties w/ the app reach out to Compliance Department and they will help you: 800-221-5127"
                        . " X1452\nDelivery: 335-5300 Paul Hughes (662) 347-0221 or (662) 335-5300",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_25(): void
    {
        $this->createMakes("MERCEDES-BENZ")
            ->createStates("NV", "CA", "MI")
            ->createTimeZones("89144", "94901", "48174")
            ->assertParsing(
                25,
                [
                    "load_id" => "68559990",
                    "pickup_date" => "12/22/2022",
                    "delivery_date" => "12/26/2022",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "MERCEDES-BENZ",
                            "model" => "GLB",
                            "vin" => "W1N4M4GB0NW170151",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "SWICKARD BUSINESS OFFICE",
                        "state" => "NV",
                        "city" => "Las Vegas",
                        "address" => "1180 N TOWN CENTER DR SUITE 250",
                        "zip" => "89144",
                        "phones" => [],
                        "fax" => null,
                        "instruction" => null,
                        "phone" => "17029123522",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "MERCEDES BENZ OF MARIN",
                        "state" => "CA",
                        "city" => "SAN RAFAEL",
                        "address" => "540 FRANCISCO BLVD",
                        "zip" => "94901",
                        "phones" => [],
                        "fax" => null,
                        "instruction" => null,
                        "phone" => "14154540582",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD LOGISTICS SINGLE VEHICLE",
                        "address" => "10701 Middlebelt Road",
                        "city" => "Romulus",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => "17349477923",
                        "phone" => "18882782793",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 760,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_26(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("OH", "PA", "MI")
            ->createTimeZones("43302", "18103", "48174")
            ->assertParsing(
                26,
                [
                    "load_id" => "68576388",
                    "pickup_date" => "12/24/2022",
                    "delivery_date" => "12/25/2022",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Chevrolet",
                            "model" => "Silverado 1500",
                            "vin" => "1GCRDBEK2NZ567413",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "MARION INDUSTRIAL",
                        "state" => "OH",
                        "city" => "Marion",
                        "address" => "3007 Harding Highway East",
                        "zip" => "43302",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "SCOTT CHEVROLET* CADILLAC",
                        "state" => "PA",
                        "city" => "Allentown",
                        "address" => "3333 LEHIGH ST",
                        "zip" => "18103",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16104390700",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD LOGISTICS SINGLE VEHICLE",
                        "address" => "10701 Middlebelt Road",
                        "city" => "Romulus",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => "17349477923",
                        "phone" => "18882782793",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 600,
                    ],
                    "dispatch_instructions" => "Pickup: 555.555.5555 * MAGNUS DRIVER APP IS 100% REQUIRED * PICKUP MUST"
                        . " BE WITHIN 48 HOURS OF ASSIGNMENT. * Once vehicle is picked up it be mark LOADED/SENT through App"
                        . " DO NOT DELAY * DATES WILL NOT BE PUSHED OUT – YOU WILL BE REMOVED FROM ORDER AFTER 48 HOURS\nDelivery:"
                        . " M TO F 7:30-5 NIGHT DROPS OK",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_27(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("GA", "TX", "MI")
            ->createTimeZones("30354", "78634", "48174")
            ->assertParsing(
                27,
                [
                    "load_id" => "69389364",
                    "pickup_date" => "02/18/2023",
                    "delivery_date" => "02/20/2023",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "CHEVROLET",
                            "model" => "SILVERADO",
                            "vin" => "2GC4YREY3P1718640",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "RAILHEAD- HAPEVILLE",
                        "state" => "GA",
                        "city" => "HAPEVILLE",
                        "address" => "33 SOUTHWOODS PKWY",
                        "zip" => "30354",
                        "phones" => [
                            [
                                "number" => "17135807316",
                            ],
                        ],
                        "fax" => null,
                        "instruction" => null,
                        "phone" => "16786832295",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "COVERT CHEVROLET OF HUTTO",
                        "state" => "TX",
                        "city" => "HUTTO",
                        "address" => "1200A HWY 79 E",
                        "zip" => "78634",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15127591515",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD LOGISTICS SINGLE VEHICLE",
                        "address" => "10701 Middlebelt Road",
                        "city" => "Romulus",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => "17349477923",
                        "phone" => "18882782793",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 925,
                    ],
                    "dispatch_instructions" => "Delivery: I35 EXIT 79 N DEALER ON LEFT USE LAST DRIVE ENTER AND EXIT THRU"
                        . " REAR ENTRANCE OF DEALERSHIP OFF OF LIMMER LOOP AND CR136 DROPS KEYS IN SERVICE WINDOW 512-759-1515",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_28(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("GA", "OH", "MI")
            ->createTimeZones("30213", "43229", "48174")
            ->assertParsing(
                28,
                [
                    "load_id" => "69390032",
                    "pickup_date" => "02/15/2023",
                    "delivery_date" => "02/16/2023",
                    "vehicles" => [
                        [
                            "year" => "2017",
                            "make" => "Chevrolet",
                            "model" => "Bolt EV",
                            "vin" => "1G1FX6S03H4185213",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "ADESA Atlanta",
                        "state" => "GA",
                        "city" => "Fairburn",
                        "address" => "5055 Oakley Industrial Blvd",
                        "zip" => "30213",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Drive Direct",
                        "state" => "OH",
                        "city" => "Columbus",
                        "address" => "2361 Morse Road",
                        "zip" => "43229",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD NSC - BUSINESS",
                        "address" => "10701 MIDDLEBELT ROAD",
                        "city" => "ROMULUS",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18882782793",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 300,
                    ],
                    "dispatch_instructions" => "Pickup: (833) 289-3533 RELEASE ATTACHED\nDelivery: (614) 476-4118 NO STI,"
                        . " If you drop at the wrong delivery location you will be back charged to correct this! please drop"
                        . " at this address only!",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_29(): void
    {
        $this->createMakes("RAM")
            ->createStates("KY", "CT", "MI")
            ->createTimeZones("42164", "06776", "48174")
            ->assertParsing(
                29,
                [
                    "load_id" => "69390601",
                    "pickup_date" => "02/15/2023",
                    "delivery_date" => "02/16/2023",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Ram",
                            "model" => "1500",
                            "vin" => "1C6SRFU93NN246049",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Manheim Scottsville KY",
                        "state" => "KY",
                        "city" => "Scottsville",
                        "address" => "1263 Franklin Rd",
                        "zip" => "42164",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12702370336",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Wetmores CJDR",
                        "state" => "CT",
                        "city" => "NEW MILFORD",
                        "address" => "333 DANBURY RD",
                        "zip" => "06776",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18603543963",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD NSC - BUSINESS",
                        "address" => "10701 MIDDLEBELT ROAD",
                        "city" => "ROMULUS",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18882782793",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 775,
                    ],
                    "dispatch_instructions" => "Pickup: RELEASE ATTACHED\nDelivery: 8:30-5 M-Sat, drop box available for"
                        . " after hours. Contact sales 860-354-3963 8:30-5 M-Sat, drop box available for after hours. Contact"
                        . " sales 860-354-3963",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_30(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("MS", "KY", "TN")
            ->createTimeZones("38828", "40505", "38109")
            ->assertParsing(
                30,
                [
                    "load_id" => "69392368",
                    "pickup_date" => "02/21/2023",
                    "delivery_date" => "02/22/2023",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "TOYOTA",
                            "model" => "Corolla",
                            "vin" => "5YFS4MCEXPP146000",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "TUPELO - BLUE SPRINGS, MS",
                        "state" => "MS",
                        "city" => "BLUE SPRINGS",
                        "address" => "1200 Magnolia Way",
                        "zip" => "38828",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15028674611",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "GREEN'S TOYOTA OF LEXINGTON",
                        "state" => "KY",
                        "city" => "LEXINGTON",
                        "address" => "630 NEW CIRCLE ROAD NE",
                        "zip" => "40505",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD MEMPHIS",
                        "address" => "2401 Florida Street",
                        "city" => "Memphis",
                        "state" => "TN",
                        "zip" => "38109",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17349477900",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 282.38,
                    ],
                    "dispatch_instructions" => "Pickup: Any questions or issues please call Crystal 734.280.0372\nDelivery:"
                        . " (859) 254-5751",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_31(): void
    {
        $this->createMakes("FORD")
            ->createStates("IL", "MI", "IN")
            ->createTimeZones("60633", "49442", "46320")
            ->assertParsing(
                31,
                [
                    "load_id" => "69392494",
                    "pickup_date" => "03/07/2023",
                    "delivery_date" => "03/08/2023",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "Ford",
                            "model" => "Explorer",
                            "vin" => "1FM5K8HC4PGA51527",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "FORD CHICAGO, IL",
                        "state" => "IL",
                        "city" => "Chicago",
                        "address" => "2001 East 122nd Street",
                        "zip" => "60633",
                        "phones" => [],
                        "fax" => "17739133678",
                        "phone" => "17733368500",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "GREAT LAKES FORD, INC. (STI OK)",
                        "state" => "MI",
                        "city" => "MUSKEGON",
                        "address" => "2469 Apple Avenue",
                        "zip" => "49442",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12317733673",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD HAMMOND",
                        "address" => "3000 Calumet Avenue",
                        "city" => "Hammond",
                        "state" => "IN",
                        "zip" => "46320",
                        "phones" => [],
                        "fax" => "17739133678",
                        "phone" => "17733368500",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 194.83,
                    ],
                    "dispatch_instructions" => "Pickup: ALL DAMAGES MUST BE NOTED ON MANIFEST! SEVERITY 3 AND ABOVE MUST"
                        . " BE NOTED AND SIGNED OFF IN BAY BEFORE YOU MOVE IT! YOU MUST PHOTOGRAPH THE DAMAGES NOTED AT PRELOAD"
                        . " AND THE MANIFEST YOU LEAVE THE GATE WITH AND UPLOAD TO VTAS! ENSURE MANIFEST COPY WITH INSPECTION"
                        . " DATA IS TURNED INTO LOAD SUPERVISOR OR DROPPED IN UR MAILBOX AT THE END OF ROW 10! WHEN LEAVING"
                        . " STONEY TURN RIGHT ONTO 122ND, FOLLOW TO INTERSECTION, TURN RIGHT ON TORRENCE AVE, FOLLOW TO 130TH"
                        . " STREET AND TURN RIGHT AND FOLLOW TO 94. N\nDelivery: 231.767.4192 Effective immediately, drivers"
                        . " are required to wear face masks when arriving at all Ford facilities due to the recent rise in"
                        . " COVID- 19 cases. Thank you for your cooperation.",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_32(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("TX", "KS", "TX")
            ->createTimeZones("77417", "67104", "77029")
            ->assertParsing(
                32,
                [
                    "load_id" => "69398044",
                    "pickup_date" => "02/24/2023",
                    "delivery_date" => "02/24/2023",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "Chevrolet",
                            "model" => "Equinox",
                            "vin" => "3GNAXUEG5PS174573",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "ROSENBERG TX-RAMP",
                        "state" => "TX",
                        "city" => "Beasley",
                        "address" => "11538 Gin Road",
                        "zip" => "77417",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18328417899",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "BOWE CHEVROLET BUICK, INC. (No STI)",
                        "state" => "KS",
                        "city" => "MEDICINE LODGE",
                        "address" => "201-205 E FOWLER",
                        "zip" => "67104",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16208865622",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD HOUSTON",
                        "address" => "131 East Loop N",
                        "city" => "Houston",
                        "state" => "TX",
                        "zip" => "77029",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17136733400",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 405.72,
                    ],
                    "dispatch_instructions" => "Pickup: Send damage L.O.N to csi@unitedroad.com or fax to 888-275-8051."
                        . " Please include pictures and estimate of damage. Claims Investigation Department. Phone 405-619-1400"
                        . " MUST STOP AND SIGN IN AT GUARD GATE. FLASHERS NEED TO BE FLASHING WHEN ON YARD. DRIVER MUST BRING"
                        . " COPY OF MANIFEST TO LOAD - RAMP WILL NOT PRINT OUT PAPERWORK FOR DRIVER\nDelivery: M-F 8-6, SAT"
                        . " 8-12 NO STI NO NIGHT DROPS",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_33(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("MS", "MO", "TN")
            ->createTimeZones("38828", "65202", "38109")
            ->assertParsing(
                33,
                [
                    "load_id" => "69401566",
                    "pickup_date" => "02/20/2023",
                    "delivery_date" => "02/21/2023",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "Toyota",
                            "model" => "Corolla",
                            "vin" => "5YFB4MDE5PP018326",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "TUPELO - BLUE SPRINGS, MS",
                        "state" => "MS",
                        "city" => "BLUE SPRINGS",
                        "address" => "1200 Magnolia Way",
                        "zip" => "38828",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15028674611",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "JOE MACHENS TOYOTA",
                        "state" => "MO",
                        "city" => "COLUMBIA",
                        "address" => "1180 VANDIVER DRIVE",
                        "zip" => "65202",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD MEMPHIS",
                        "address" => "2401 Florida Street",
                        "city" => "Memphis",
                        "state" => "TN",
                        "zip" => "38109",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17349477900",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 309.55,
                    ],
                    "dispatch_instructions" => "Pickup: Any questions or issues please call Crystal 734.280.0372\nDelivery:"
                        . " (573) 445-4450 STI OK, dropbox on the eastside of the building near the service dept.",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_34(): void
    {
        $this->createMakes("GMC")
            ->createStates("TX", "LA", "TX")
            ->createTimeZones("77417", "70760", "77029")
            ->assertParsing(
                34,
                [
                    "load_id" => "69404496",
                    "pickup_date" => "02/28/2023",
                    "delivery_date" => "03/01/2023",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "GMC",
                            "model" => "Terrain",
                            "vin" => "3GKALPEG0PL201781",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "ROSENBERG TX-RAMP",
                        "state" => "TX",
                        "city" => "Beasley",
                        "address" => "11538 Gin Road",
                        "zip" => "77417",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18328417899",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "MAGGIO MOTORS, INC.",
                        "state" => "LA",
                        "city" => "NEW ROADS",
                        "address" => "310 NEW ROADS ST",
                        "zip" => "70760",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12256388383",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD HOUSTON",
                        "address" => "131 East Loop N",
                        "city" => "Houston",
                        "state" => "TX",
                        "zip" => "77029",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17136733400",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 214.07,
                    ],
                    "dispatch_instructions" => "Pickup: Send damage L.O.N to csi@unitedroad.com or fax to 888-275-8051."
                        . " Please include pictures and estimate of damage. Claims Investigation Department. Phone 405-619-1400"
                        . " MUST STOP AND SIGN IN AT GUARD GATE. FLASHERS NEED TO BE FLASHING WHEN ON YARD. DRIVER MUST BRING"
                        . " COPY OF MANIFEST TO LOAD - RAMP WILL NOT PRINT OUT PAPERWORK FOR DRIVER\nDelivery: +",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_35(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("TX", "OK", "TX")
            ->createTimeZones("77417", "74055", "77029")
            ->assertParsing(
                35,
                [
                    "load_id" => "69404521",
                    "pickup_date" => "03/06/2023",
                    "delivery_date" => "03/07/2023",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "Chevrolet",
                            "model" => "Equinox",
                            "vin" => "3GNAXHEG3PL199490",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "ROSENBERG TX-RAMP",
                        "state" => "TX",
                        "city" => "Beasley",
                        "address" => "11538 Gin Road",
                        "zip" => "77417",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18328417899",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "CLASSIC CHEVROLET, INC.",
                        "state" => "OK",
                        "city" => "OWASSO",
                        "address" => "8501 NORTH OWASSO EXPRESSWAY",
                        "zip" => "74055",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19182721101",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD HOUSTON",
                        "address" => "131 East Loop N",
                        "city" => "Houston",
                        "state" => "TX",
                        "zip" => "77029",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17136733400",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 331.37,
                    ],
                    "dispatch_instructions" => "Pickup: Send damage L.O.N to csi@unitedroad.com or fax to 888-275-8051."
                        . " Please include pictures and estimate of damage. Claims Investigation Department. Phone 405-619-1400"
                        . " MUST STOP AND SIGN IN AT GUARD GATE. FLASHERS NEED TO BE FLASHING WHEN ON YARD. DRIVER MUST BRING"
                        . " COPY OF MANIFEST TO LOAD - RAMP WILL NOT PRINT OUT PAPERWORK FOR DRIVER\nDelivery: M-F 8-5 SAT"
                        . " 8-1 NO STI Do not driver on the lot call for instructions new lot do not drive on the lot",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_36(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("TX", "OK", "TX")
            ->createTimeZones("77417", "74432", "77029")
            ->assertParsing(
                36,
                [
                    "load_id" => "69404556",
                    "pickup_date" => "02/27/2023",
                    "delivery_date" => "02/28/2023",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "Chevrolet",
                            "model" => "Equinox",
                            "vin" => "3GNAXMEG2PL202162",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "ROSENBERG TX-RAMP",
                        "state" => "TX",
                        "city" => "Beasley",
                        "address" => "11538 Gin Road",
                        "zip" => "77417",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18328417899",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "DUNN COUNTRY MOTORS, LLC",
                        "state" => "OK",
                        "city" => "EUFAULA",
                        "address" => "700 BIRKES RD",
                        "zip" => "74432",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19186892595",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD HOUSTON",
                        "address" => "131 East Loop N",
                        "city" => "Houston",
                        "state" => "TX",
                        "zip" => "77029",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17136733400",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 311.05,
                    ],
                    "dispatch_instructions" => "Pickup: Send damage L.O.N to csi@unitedroad.com or fax to 888-275-8051."
                        . " Please include pictures and estimate of damage. Claims Investigation Department. Phone 405-619-1400"
                        . " MUST STOP AND SIGN IN AT GUARD GATE. FLASHERS NEED TO BE FLASHING WHEN ON YARD. DRIVER MUST BRING"
                        . " COPY OF MANIFEST TO LOAD - RAMP WILL NOT PRINT OUT PAPERWORK FOR DRIVER\nDelivery: M-F 8-5 STI"
                        . " OK",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_37(): void
    {
        $this->createMakes("GMC")
            ->createStates("TX", "AR", "TX")
            ->createTimeZones("77417", "72761", "77029")
            ->assertParsing(
                37,
                [
                    "load_id" => "69404623",
                    "pickup_date" => "02/27/2023",
                    "delivery_date" => "02/28/2023",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "GMC",
                            "model" => "Terrain",
                            "vin" => "3GKALTEG8PL191560",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "ROSENBERG TX-RAMP",
                        "state" => "TX",
                        "city" => "Beasley",
                        "address" => "11538 Gin Road",
                        "zip" => "77417",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18328417899",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "SUPERIOR 6, INC.",
                        "state" => "AR",
                        "city" => "SILOAM SPRINGS",
                        "address" => "490 HWY 412 E",
                        "zip" => "72761",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14795243152",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD HOUSTON",
                        "address" => "131 East Loop N",
                        "city" => "Houston",
                        "state" => "TX",
                        "zip" => "77029",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17136733400",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 369.01,
                    ],
                    "dispatch_instructions" => "Pickup: Send damage L.O.N to csi@unitedroad.com or fax to 888-275-8051."
                        . " Please include pictures and estimate of damage. Claims Investigation Department. Phone 405-619-1400"
                        . " MUST STOP AND SIGN IN AT GUARD GATE. FLASHERS NEED TO BE FLASHING WHEN ON YARD. DRIVER MUST BRING"
                        . " COPY OF MANIFEST TO LOAD - RAMP WILL NOT PRINT OUT PAPERWORK FOR DRIVER\nDelivery: M-SAT 8AM-6PM"
                        . " STIS OKAY PUT KEYS IN DROP BOX ON EAST SIDE SERVICE ENTRANCE PLEASE ALSO PARK CARS ON EAST SIDE.",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_38(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("MS", "KY", "TN")
            ->createTimeZones("38828", "41048", "38109")
            ->assertParsing(
                38,
                [
                    "load_id" => "69404692",
                    "pickup_date" => "02/21/2023",
                    "delivery_date" => "02/22/2023",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "Toyota",
                            "model" => "Corolla",
                            "vin" => "5YFB4MDE2PP018414",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "TUPELO - BLUE SPRINGS, MS",
                        "state" => "MS",
                        "city" => "BLUE SPRINGS",
                        "address" => "1200 Magnolia Way",
                        "zip" => "38828",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15028674611",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "BUDGET RAC",
                        "state" => "KY",
                        "city" => "HEBRON",
                        "address" => "2667 DONALDSON HWY",
                        "zip" => "41048",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD MEMPHIS",
                        "address" => "2401 Florida Street",
                        "city" => "Memphis",
                        "state" => "TN",
                        "zip" => "38109",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17349477900",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 309.77,
                    ],
                    "dispatch_instructions" => "Pickup: Any questions or issues please call Crystal 734.280.0372\nDelivery:"
                        . " (859) 644-9062 I-275 exit 4 towards airport. Exit Donaldson, turn right. At Stop sign turn left"
                        . " on Loomis. Unload in parking lot on right hand side",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_39(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("TX", "OK", "TX")
            ->createTimeZones("77417", "74868", "77029")
            ->assertParsing(
                39,
                [
                    "load_id" => "69407438",
                    "pickup_date" => "03/06/2023",
                    "delivery_date" => "03/07/2023",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "Chevrolet",
                            "model" => "Equinox",
                            "vin" => "3GNAXHEG6PL174681",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "ROSENBERG TX-RAMP",
                        "state" => "TX",
                        "city" => "Beasley",
                        "address" => "11538 Gin Road",
                        "zip" => "77417",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18328417899",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "SEMINOLE CHEVROLET BUICK GMC",
                        "state" => "OK",
                        "city" => "Seminole",
                        "address" => "1405 North Milt Phillips Avenue",
                        "zip" => "74868",
                        "phones" => [],
                        "fax" => null,
                        "instruction" => null,
                        "phone" => "14053826130",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD HOUSTON",
                        "address" => "131 East Loop N",
                        "city" => "Houston",
                        "state" => "TX",
                        "zip" => "77029",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17136733400",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 296.18,
                    ],
                    "dispatch_instructions" => "Pickup: Send damage L.O.N to csi@unitedroad.com or fax to 888-275-8051."
                        . " Please include pictures and estimate of damage. Claims Investigation Department. Phone 405-619-1400"
                        . " MUST STOP AND SIGN IN AT GUARD GATE. FLASHERS NEED TO BE FLASHING WHEN ON YARD. DRIVER MUST BRING"
                        . " COPY OF MANIFEST TO LOAD - RAMP WILL NOT PRINT OUT PAPERWORK FOR DRIVER",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_40(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("IL", "IN", "IN")
            ->createTimeZones("60411", "465141733", "46320")
            ->assertParsing(
                40,
                [
                    "load_id" => "69408875",
                    "pickup_date" => "02/25/2023",
                    "delivery_date" => "02/25/2023",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "Chevrolet",
                            "model" => "Traverse",
                            "vin" => "1GNEVHKW7PJ198272",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "CHICAGO HEIGHTS-RAMP",
                        "state" => "IL",
                        "city" => "CHICAGO HEIGHTS",
                        "address" => "203 SOUTH STATE STREET",
                        "zip" => "60411",
                        "phones" => [],
                        "fax" => "17087574681",
                        "phone" => "17087574237",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "delivery reciepts)",
                        "state" => "IN",
                        "city" => "ELKHART",
                        "address" => "2500 Lexington Ave",
                        "zip" => "465141733",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15742938621",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD HAMMOND",
                        "address" => "3000 Calumet Avenue",
                        "city" => "Hammond",
                        "state" => "IN",
                        "zip" => "46320",
                        "phones" => [],
                        "fax" => "17739133678",
                        "phone" => "17733368500",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 104.66,
                    ],
                    "dispatch_instructions" => "Pickup: Must print out load sheet manifest prior to loading. Send damage"
                        . " L.O.N to csi@unitedroad.com or fax STI to 888-275-8051. Please include pictures and estimate of"
                        . " damage. Claims Investigation Department. Phone 844-556-2625. FOR DAMAGES IN YARD CALL RAY 708-515-2052\nDelivery:"
                        . " NIGHT DROP BOX LOCATED BETWEEN TWO SERVICE OVERHEAD DOORS DO NOT PARK UNITS IN FRONT OF ANY OVERHEAD"
                        . " DOORS ESPECIALLY THE SHIPPING AND RECEIVING DOORS!!!!",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_41(): void
    {
        $this->createMakes("MAZDA", "NISSAN")
            ->createStates("IL", "KY", "MI")
            ->createTimeZones("60443", "40155", "48174")
            ->assertParsing(
                41,
                [
                    "load_id" => "69411238",
                    "pickup_date" => "02/17/2023",
                    "delivery_date" => "02/17/2023",
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "Mazda",
                            "model" => "CX-5",
                            "vin" => "JM3KFBCM0M0344393",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2020",
                            "make" => "Nissan",
                            "model" => "Versa",
                            "vin" => "3N1CN8EV5LL861444",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Manheim Chicago",
                        "state" => "IL",
                        "city" => "Matteson",
                        "address" => "20401 Cox Avenue",
                        "zip" => "60443",
                        "phones" => [],
                        "fax" => null,
                        "instruction" => null,
                        "phone" => "18158064222",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Knox Budget Car Sales",
                        "state" => "KY",
                        "city" => "Muldraugh",
                        "address" => "716 S. Hwy 31 West",
                        "zip" => "40155",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD NSC - BUSINESS",
                        "address" => "10701 MIDDLEBELT ROAD",
                        "city" => "ROMULUS",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18882782793",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 510,
                    ],
                    "dispatch_instructions" => "Delivery: Dan Holley (502) 942-3368 270-268-0144",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_42(): void
    {
        $this->createMakes("GMC")
            ->createStates("IL", "MI", "IN")
            ->createTimeZones("60411", "49047", "46320")
            ->assertParsing(
                42,
                [
                    "load_id" => "69413774",
                    "pickup_date" => "03/02/2023",
                    "delivery_date" => "03/03/2023",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "GMC",
                            "model" => "Terrain",
                            "vin" => "3GKALTEGXPL202641",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "CHICAGO HEIGHTS-RAMP",
                        "state" => "IL",
                        "city" => "CHICAGO HEIGHTS",
                        "address" => "203 SOUTH STATE STREET",
                        "zip" => "60411",
                        "phones" => [],
                        "fax" => "17087574681",
                        "phone" => "17087574237",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "C. WIMBERLEY",
                        "state" => "MI",
                        "city" => "DOWAGIAC",
                        "address" => "57333 M51 S",
                        "zip" => "49047",
                        "phones" => [],
                        "fax" => null,
                        "instruction" => null,
                        "phone" => "12697825181",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD HAMMOND",
                        "address" => "3000 Calumet Avenue",
                        "city" => "Hammond",
                        "state" => "IN",
                        "zip" => "46320",
                        "phones" => [],
                        "fax" => "17739133678",
                        "phone" => "17733368500",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 97.03,
                    ],
                    "dispatch_instructions" => "Pickup: Must print out load sheet manifest prior to loading. Send damage"
                        . " L.O.N to csi@unitedroad.com or fax to 888-275-8051. Please include pictures and estimate of damage."
                        . " Claims Investigation Department. Phone 844-556-2625. FOR DAMAGES IN YARD CALL RAY 708-515-2052",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_43(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("MS", "KY", "TN")
            ->createTimeZones("38828", "42431", "38109")
            ->assertParsing(
                43,
                [
                    "load_id" => "69418224",
                    "pickup_date" => "03/01/2023",
                    "delivery_date" => "03/02/2023",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "Toyota",
                            "model" => "Corolla",
                            "vin" => "5YFS4MCE0PP146202",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "TUPELO - BLUE SPRINGS, MS",
                        "state" => "MS",
                        "city" => "BLUE SPRINGS",
                        "address" => "1200 Magnolia Way",
                        "zip" => "38828",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15028674611",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "WATERMARK TOYOTA",
                        "state" => "KY",
                        "city" => "MADISONVILLE",
                        "address" => "1055 CROSSING PLACE",
                        "zip" => "42431",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD MEMPHIS",
                        "address" => "2401 Florida Street",
                        "city" => "Memphis",
                        "state" => "TN",
                        "zip" => "38109",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17349477900",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 205.32,
                    ],
                    "dispatch_instructions" => "Pickup: Any questions or issues please call Crystal 734.280.0372\nDelivery:"
                        . " (270) 821-3372 8-5 NO STI",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_44(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("MN", "MN", "MI")
            ->createTimeZones("55077", "55428", "48174")
            ->assertParsing(
                44,
                [
                    "load_id" => "69423021",
                    "pickup_date" => "03/14/2023",
                    "delivery_date" => "03/15/2023",
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "Chevrolet",
                            "model" => "Equinox",
                            "vin" => "3GNAXUEVXLL236942",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "BILLY MAUER CHEVROLET",
                        "state" => "MN",
                        "city" => "INVER GROVE HEIGHTS",
                        "address" => "1055 HWY 110",
                        "zip" => "55077",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "CARMAX 6008",
                        "state" => "MN",
                        "city" => "BROOKLYN PARK",
                        "address" => "6900 LAKELAND AVENUE N",
                        "zip" => "55428",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17635609329",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD CARMAX",
                        "address" => "10701 Middlebelt Road",
                        "city" => "Romulus",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17349477900",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 110,
                    ],
                    "dispatch_instructions" => "Pickup: Orders to a CarMax store with the same origin and destination that"
                        . " are moved on the same load must be built into a load. If you have units to build into a load,"
                        . " reach out to your United Road dispatcher for CarMax or call 1-888-278-2793. Driver must have a"
                        . " paper copy of the OpenLane release prior to arrival at origin. The release is available through"
                        . " the Magnus app or by calling United Road dispatch for CarMax, 1-888-278-2793. / pick\nDelivery:"
                        . " Mon 12PM to 7PM / Tues - Fri 10AM - 6PM / Sat 10AM - 12PM / CLOSED SUNDAY No Pickup/Deliveries"
                        . " on Mondays before 12PM due to on-site auction ***********CARRIERS ARE STRICTLY PROHIBITED TO PARK/LOAD/UNLOAD"
                        . " on 70TH AVE**********No parking on 70 street or you will ticketed********************* Any questions"
                        . " on directions or drop off up please have the driver call 14022899989 x 5090.",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_45(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("MS", "TN", "TN")
            ->createTimeZones("38828", "37620", "38109")
            ->assertParsing(
                45,
                [
                    "load_id" => "69526339",
                    "pickup_date" => "03/15/2023",
                    "delivery_date" => "03/16/2023",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "TOYOTA",
                            "model" => "COROLLA",
                            "vin" => "5YFS4MCE0PP146555",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "TUPELO - BLUE SPRINGS, MS",
                        "state" => "MS",
                        "city" => "BLUE SPRINGS",
                        "address" => "1200 Magnolia Way",
                        "zip" => "38828",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15028674611",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "TOYOTA OF BRISTOL",
                        "state" => "TN",
                        "city" => "Bristol",
                        "address" => "3045 West State Street",
                        "zip" => "37620",
                        "phones" => [],
                        "fax" => null,
                        "instruction" => null,
                        "phone" => "14237643155",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD MEMPHIS",
                        "address" => "2401 Florida Street",
                        "city" => "Memphis",
                        "state" => "TN",
                        "zip" => "38109",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17349477900",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 305.89,
                    ],
                    "dispatch_instructions" => "Pickup: Any questions or issues please call Crystal 734.280.0372",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_46(): void
    {
        $this->createMakes("GMC")
            ->createStates("IL", "MI", "IN")
            ->createTimeZones("60411", "49601", "46320")
            ->assertParsing(
                46,
                [
                    "load_id" => "69695976",
                    "pickup_date" => "03/14/2023",
                    "delivery_date" => "03/16/2023",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "GMC",
                            "model" => "Canyon",
                            "vin" => "1GTG6CEN2N1313054",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "CHICAGO HEIGHTS-RAMP",
                        "state" => "IL",
                        "city" => "CHICAGO HEIGHTS",
                        "address" => "203 SOUTH STATE STREET",
                        "zip" => "60411",
                        "phones" => [],
                        "fax" => "17087574681",
                        "phone" => "17087574237",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "7555 U.S. 131 Business",
                        "state" => "MI",
                        "city" => "Cadillac",
                        "address" => "loading.",
                        "zip" => "49601",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD HAMMOND",
                        "address" => "3000 Calumet Avenue",
                        "city" => "Hammond",
                        "state" => "IN",
                        "zip" => "46320",
                        "phones" => [],
                        "fax" => "17739133678",
                        "phone" => "17733368500",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 396.83,
                    ],
                    "dispatch_instructions" => "Pickup: Must print out load sheet manifest prior to Send damage L.O.N to"
                        . " csi@unitedroad.com or to 888-275-8051. Please include pictures and estimate of damage. Claims"
                        . " Investigation Department. Phone 844-556-2625. FOR DAMAGES IN YARD CALL RAY 708-515-2052\nDelivery:"
                        . " fax 231-775-1222",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_47(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("TX", "KS", "MI")
            ->createTimeZones("75050", "67219", "48174")
            ->assertParsing(
                47,
                [
                    "load_id" => "69739793",
                    "pickup_date" => "03/13/2023",
                    "delivery_date" => "03/15/2023",
                    "vehicles" => [
                        [
                            "year" => "2018",
                            "make" => "Chevrolet",
                            "model" => "Malibu",
                            "vin" => "1G1ZD5ST8JF165947",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "IAA Dallas/Ft Worth",
                        "state" => "TX",
                        "city" => "Grand Prairie",
                        "address" => "4226 E. Main St",
                        "zip" => "75050",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19725225000",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Amine Bennani",
                        "state" => "KS",
                        "city" => "Park City",
                        "address" => "6159 N Broadway Ave",
                        "zip" => "67219",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13163644692",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD LOGISTICS SINGLE VEHICLE",
                        "address" => "10701 Middlebelt Road",
                        "city" => "Romulus",
                        "state" => "MI",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => "17349477923",
                        "phone" => "18882782793",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 300,
                    ],
                    "dispatch_instructions" => "Pickup: *MAGNUS DRIVER APP IS REQUIRED LOCATION SERVICES MUST BE TURNED"
                        . " ON FOR TRANSPORT * P/U within 48 hours of assigning to order NO EXCEPTIONS* * ISSUES CONTACT UR"
                        . " 888.278.2793 *MUST BE ABLE TO OFFLOAD VEHICLE\nDelivery: This buyer has reached out stating that"
                        . " there might not be anyone at delivery location today. He stated that the driver can leave the"
                        . " key in the drop box. Contact # 316-807-1170.",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_48(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("MS", "TN", "TN")
            ->createTimeZones("38828", "37660", "38109")
            ->assertParsing(
                48,
                [
                    "load_id" => "69771788",
                    "pickup_date" => "03/15/2023",
                    "delivery_date" => "03/16/2023",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "Toyota",
                            "model" => "Corolla",
                            "vin" => "5YFB4MDE0PP024115",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "TUPELO - BLUE SPRINGS, MS",
                        "state" => "MS",
                        "city" => "BLUE SPRINGS",
                        "address" => "1200 Magnolia Way",
                        "zip" => "38828",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15028674611",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "TOYOTA OF KINGSPORT",
                        "state" => "TN",
                        "city" => "KINGSPORT",
                        "address" => "2525 EAST STONE DRIVE",
                        "zip" => "37660",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD MEMPHIS",
                        "address" => "2401 Florida Street",
                        "city" => "Memphis",
                        "state" => "TN",
                        "zip" => "38109",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17349477900",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 299.9,
                    ],
                    "dispatch_instructions" => "Pickup: Any questions or issues please call Crystal 734.280.0372\nDelivery:"
                        . " (423) 246-6611 til 5:30pm sti ok",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_49(): void
    {
        $this->createMakes("MERCEDES-BENZ")
            ->createStates("AL", "OH", "MS")
            ->createTimeZones("35490", "43017", "39046")
            ->assertParsing(
                49,
                [
                    "load_id" => "69817546",
                    "pickup_date" => "03/15/2023",
                    "delivery_date" => "03/16/2023",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "Mercedes-Benz",
                            "model" => "GLE",
                            "vin" => "4JGFB4KB0PA950028",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "VANCE DOMESTIC MARSHALLING YARD",
                        "state" => "AL",
                        "city" => "Vance",
                        "address" => "1765 Vance Municipal Drive",
                        "zip" => "35490",
                        "phones" => [],
                        "fax" => null,
                        "instruction" => null,
                        "phone" => "11234567890",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "CROWN EUROCARS",
                        "state" => "OH",
                        "city" => "Dublin",
                        "address" => "6500 Perimeter Loop Road",
                        "zip" => "43017",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD CANTON",
                        "address" => "300 Nissan Parkway Gate 6",
                        "city" => "Canton",
                        "state" => "MS",
                        "zip" => "39046",
                        "phones" => [],
                        "fax" => "16018552665",
                        "phone" => "16018550402",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 419.65,
                    ],
                    "dispatch_instructions" => "Delivery: (614) 761-2360",
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_50(): void
    {
        $this->createMakes("MERCEDES-BENZ")
            ->createStates("AL", "OH", "MS")
            ->createTimeZones("35490", "45236", "39046")
            ->assertParsing(
                50,
                [
                    "load_id" => "69827146",
                    "pickup_date" => "03/15/2023",
                    "delivery_date" => "03/16/2023",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "Mercedes-Benz",
                            "model" => "EQS",
                            "vin" => "4JGDM2EB1PA017272",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "VANCE DOMESTIC MARSHALLING YARD",
                        "state" => "AL",
                        "city" => "Vance",
                        "address" => "1765 Vance Municipal Drive",
                        "zip" => "35490",
                        "phones" => [],
                        "fax" => null,
                        "instruction" => null,
                        "phone" => "11234567890",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Cougar Cincinnati Acquisition LLC",
                        "state" => "OH",
                        "city" => "Cincinnati",
                        "address" => "8727 Montgomery Road",
                        "zip" => "45236",
                        "phones" => null,
                        "fax" => null,
                        "instruction" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD CANTON",
                        "address" => "300 Nissan Parkway Gate 6",
                        "city" => "Canton",
                        "state" => "MS",
                        "zip" => "39046",
                        "phones" => [],
                        "fax" => "16018552665",
                        "phone" => "16018550402",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 389.44,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_51(): void
    {
        $this->createMakes("MERCEDES-BENZ")
            ->createStates("AL", "OH", "MS")
            ->createTimeZones("35490", "43017", "39046")
            ->assertParsing(
                51,
                [
                    "load_id" => "69835761",
                    "pickup_date" => "03/15/2023",
                    "delivery_date" => "03/16/2023",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "Mercedes-Benz",
                            "model" => "GLS",
                            "vin" => "4JGFF8KE8PA953247",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "VANCE DOMESTIC MARSHALLING YARD",
                        "state" => "AL",
                        "city" => "Vance",
                        "address" => "1765 Vance Municipal Drive",
                        "zip" => "35490",
                        "phones" => [],
                        "fax" => null,
                        "instruction" => null,
                        "phone" => "11234567890",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "CROWN EUROCARS",
                        "state" => "OH",
                        "city" => "Dublin",
                        "address" => "6500 Perimeter Loop Road",
                        "zip" => "43017",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "UNITED ROAD CANTON",
                        "address" => "300 Nissan Parkway Gate 6",
                        "city" => "Canton",
                        "state" => "MS",
                        "zip" => "39046",
                        "phones" => [],
                        "fax" => "16018552665",
                        "phone" => "16018550402",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 419.65,
                    ],
                    "dispatch_instructions" => "Delivery: (614) 761-2360",
                ]
            );
    }
}
