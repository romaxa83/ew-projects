<?php

namespace Tests\Unit\Parsers;

use Throwable;

class DirectConnectLogistixParserTest extends BaseParserTest
{
    /**
     * @throws Throwable
     */
    public function test_1(): void
    {
        $this->createMakes("HONDA")
            ->createStates("TN", "MO", "IN")
            ->createTimeZones("38128", "64153", "46202")
            ->assertParsing(
                1,
                [
                    "load_id" => "5347525",
                    "pickup_date" => "01/27/2020",
                    "delivery_date" => "01/28/2020",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2014",
                            "make" => "Honda",
                            "model" => "ACCORD",
                            "vin" => "1HGCR3F86EA004221",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Drive Time Memphis",
                        "state" => "TN",
                        "city" => "MEMPHIS",
                        "address" => "2177 Covington Pike",
                        "zip" => "38128",
                        "phones" => [],
                        "phone" => "19013343290",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "KCI",
                        "state" => "MO",
                        "city" => "KANSAS CITY",
                        "address" => "11101 N Congress Ave",
                        "zip" => "64153",
                        "phones" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Direct Connect Logistix",
                        "address" => "314 W Michigan St",
                        "city" => "Indianapolis",
                        "state" => "IN",
                        "zip" => "46202",
                        "phones" => [],
                        "fax" => "13175363795",
                        "email" => "operations@dclogistix.com",
                        "phone" => "13177064747",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 400,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_2(): void
    {
        $this->createMakes("JEEP")
            ->createStates("WI", "IN", "IN")
            ->createTimeZones("53224", "46140", "46202")
            ->assertParsing(
                2,
                [
                    "load_id" => "5355410",
                    "pickup_date" => "02/25/2020",
                    "delivery_date" => "02/26/2020",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => null,
                            "make" => "jeep",
                            "model" => "compass",
                            "vin" => "1C4NJDEB6HD175975",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Greater Milwaukee Auto Auction",
                        "state" => "WI",
                        "city" => "MILWAUKEE",
                        "address" => "8711 W Brown Deer Rd",
                        "zip" => "53224",
                        "phones" => [],
                        "phone" => "14143653500",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Carvana IN",
                        "state" => "IN",
                        "city" => "GREENFIELD",
                        "address" => "6299 W 300 N",
                        "zip" => "46140",
                        "phones" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Direct Connect Logistix",
                        "address" => "314 W Michigan St",
                        "city" => "Indianapolis",
                        "state" => "IN",
                        "zip" => "46202",
                        "phones" => [],
                        "fax" => "13175363795",
                        "email" => "operations@dclogistix.com",
                        "phone" => "13177064747",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 225,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_3(): void
    {
        $this->createMakes("KIA")
            ->createStates("MI", "IN", "IN")
            ->createTimeZones("49423", "46140", "46202")
            ->assertParsing(
                3,
                [
                    "load_id" => "5354018",
                    "pickup_date" => "02/20/2020",
                    "delivery_date" => "02/20/2020",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => null,
                            "make" => "kia",
                            "model" => "sport",
                            "vin" => "KNDPMCAC8H7163141",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Elhart Kia",
                        "state" => "MI",
                        "city" => "HOLLAND",
                        "address" => "870 Chicago Dr suite b",
                        "zip" => "49423",
                        "phones" => [],
                        "phone" => "16163960232",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Carvana IN",
                        "state" => "IN",
                        "city" => "GREENFIELD",
                        "address" => "6299 W 300 N",
                        "zip" => "46140",
                        "phones" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Direct Connect Logistix",
                        "address" => "314 W Michigan St",
                        "city" => "Indianapolis",
                        "state" => "IN",
                        "zip" => "46202",
                        "phones" => [],
                        "fax" => "13175363795",
                        "email" => "operations@dclogistix.com",
                        "phone" => "13177064747",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 275,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_4(): void
    {
        $this->createMakes("FORD")
            ->createStates("WI", "IN", "IN")
            ->createTimeZones("53226", "46140", "46202")
            ->assertParsing(
                4,
                [
                    "load_id" => "5355407",
                    "pickup_date" => "02/25/2020",
                    "delivery_date" => "02/26/2020",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => null,
                            "make" => "ford",
                            "model" => "escape",
                            "vin" => "1FMCU0GD8HUC77361",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Uptown Ford Lincoln",
                        "state" => "WI",
                        "city" => "MILWAUKEE",
                        "address" => "2111 N Mayfair Rd",
                        "zip" => "53226",
                        "phones" => [],
                        "phone" => "14147719000",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Carvana IN",
                        "state" => "IN",
                        "city" => "GREENFIELD",
                        "address" => "6299 W 300 N",
                        "zip" => "46140",
                        "phones" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Direct Connect Logistix",
                        "address" => "314 W Michigan St",
                        "city" => "Indianapolis",
                        "state" => "IN",
                        "zip" => "46202",
                        "phones" => [],
                        "fax" => "13175363795",
                        "email" => "operations@dclogistix.com",
                        "phone" => "13177064747",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 225,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_5(): void
    {
        $this->createMakes("LEXUS")
            ->createStates("OH", "NC", "IN")
            ->createTimeZones("45044", "28602", "46202")
            ->assertParsing(
                5,
                [
                    "load_id" => "5342101",
                    "pickup_date" => "01/10/2020",
                    "delivery_date" => "01/13/2020",
                    "dispatch_instructions" => "*Fee will result from any early loading attempts *Call one hour prior to"
                        . " loading DAYLIGHT HOURS ONLY *Call one hour prior to delivery",
                    "vehicles" => [
                        [
                            "year" => "2012",
                            "make" => "Lexus",
                            "model" => "RX350",
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Joseph Reinhardt",
                        "state" => "OH",
                        "city" => "LIBERTY TOWNSH",
                        "address" => "6608 Tree View Drive",
                        "zip" => "45044",
                        "phones" => [],
                        "phone" => "15132620049",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Joseph Reinhardt",
                        "state" => "NC",
                        "city" => "HICKORY",
                        "address" => "2101 21st Street SE",
                        "zip" => "28602",
                        "phones" => [],
                        "phone" => "15132620049",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Direct Connect Logistix",
                        "address" => "314 W Michigan St",
                        "city" => "Indianapolis",
                        "state" => "IN",
                        "zip" => "46202",
                        "phones" => [],
                        "fax" => "13175363795",
                        "email" => "operations@dclogistix.com",
                        "phone" => "13177064747",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 350,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_6(): void
    {
        $this->createMakes("DODGE")
            ->createStates("IL", "TN", "IN")
            ->createTimeZones("60610", "37087", "46202")
            ->assertParsing(
                6,
                [
                    "load_id" => "5359650",
                    "pickup_date" => "03/16/2020",
                    "delivery_date" => "03/17/2020",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => null,
                            "make" => "dodge",
                            "model" => "journey",
                            "vin" => "3C4PDCAB5HT601858",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Volkswagen of Downtown Chicago",
                        "state" => "IL",
                        "city" => "CHICAGO",
                        "address" => "407 E 25th St",
                        "zip" => "60610",
                        "phones" => [],
                        "phone" => "17739497824",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Carvana Nashville",
                        "state" => "TN",
                        "city" => "LEBANON",
                        "address" => "1420 Toshiba Dr",
                        "zip" => "37087",
                        "phones" => [],
                        "phone" => "18592004681",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Direct Connect Logistix",
                        "address" => "314 W Michigan St",
                        "city" => "Indianapolis",
                        "state" => "IN",
                        "zip" => "46202",
                        "phones" => [],
                        "fax" => "13175363795",
                        "email" => "operations@dclogistix.com",
                        "phone" => "13177064747",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 350,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_7(): void
    {
        $this->createMakes("ACURA")
            ->createStates("MN", "NC", "IN")
            ->createTimeZones("55123", "28104", "46202")
            ->assertParsing(
                7,
                [
                    "load_id" => "5362305",
                    "pickup_date" => "03/23/2020",
                    "delivery_date" => "03/26/2020",
                    "dispatch_instructions" => "*Fee will result from any early loading attempts *Call one hour prior to"
                        . " loading DAYLIGHT HOURS ONLY *Call one hour prior to delivery",
                    "vehicles" => [
                        [
                            "year" => "2015",
                            "make" => "ACURA",
                            "model" => "MDX",
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Manij Shrestha c/o Rapan",
                        "state" => "MN",
                        "city" => "SAINT PAUL",
                        "address" => "1347 Shadow Creek Curve",
                        "zip" => "55123",
                        "phones" => [],
                        "phone" => "16123067980",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Manij Shrestha",
                        "state" => "NC",
                        "city" => "WEDDINGTON",
                        "address" => "105 Enclave Meadows Lane",
                        "zip" => "28104",
                        "phones" => [],
                        "phone" => "16122276441",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Direct Connect Logistix",
                        "address" => "314 W Michigan St",
                        "city" => "Indianapolis",
                        "state" => "IN",
                        "zip" => "46202",
                        "phones" => [],
                        "fax" => "13175363795",
                        "email" => "operations@dclogistix.com",
                        "phone" => "13177064747",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 750,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_8(): void
    {
        $this->createMakes("BUICK")
            ->createStates("IL", "TX", "IN")
            ->createTimeZones("62207", "76131", "46202")
            ->assertParsing(
                8,
                [
                    "load_id" => "5387478",
                    "pickup_date" => "07/14/2020",
                    "delivery_date" => "07/15/2020",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => null,
                            "make" => "buick",
                            "model" => "envision",
                            "vin" => "LRBFXBSA1JD008141",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "America's Auto Auction",
                        "state" => "IL",
                        "city" => "EAST SAINT LOUI",
                        "address" => "721 S 45th St",
                        "zip" => "62207",
                        "phones" => [],
                        "phone" => "16183321227",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Carvana Blue Mound",
                        "state" => "TX",
                        "city" => "FORT WORTH",
                        "address" => "1123 Cantrell Sansom Rd",
                        "zip" => "76131",
                        "phones" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Direct Connect Logistix",
                        "address" => "314 W Michigan St",
                        "city" => "Indianapolis",
                        "state" => "IN",
                        "zip" => "46202",
                        "phones" => [],
                        "fax" => "13175363795",
                        "email" => "operations@dclogistix.com",
                        "phone" => "13177064747",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 550,
                    ],
                ]
            );
    }
}
