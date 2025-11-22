<?php

namespace Tests\Unit\Parsers;

use Throwable;

class CentralDispatchParserTest extends BaseParserTest
{
    /**
     * @throws Throwable
     */
    public function test_1(): void
    {
        $this->createMakes("FORD")
            ->createStates("CO", "IL", "IL")
            ->createTimeZones("80011", "61822", "61874")
            ->assertParsing(
                1,
                [
                    "load_id" => "28620005",
                    "pickup_date" => "07/21/2021",
                    "delivery_date" => "07/24/2021",
                    "dispatch_instructions" => "This should be picked up within 2 days of 07/21/2021. This should be delivered"
                        . " within 2 days of 07/24/2021.",
                    "vehicles" => [
                        [
                            "year" => "2018",
                            "make" => "ford",
                            "model" => "edge",
                            "color" => "gray",
                            "license_plate" => null,
                            "vin" => "2FMPK4J95JBB99846",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2018",
                            "make" => "ford",
                            "model" => "edge",
                            "color" => "black",
                            "license_plate" => null,
                            "vin" => "2FMPK4J94JBC11274",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2018",
                            "make" => "ford",
                            "model" => "explorer",
                            "color" => "gray",
                            "license_plate" => null,
                            "vin" => "1FM5K8D84JGB54231",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Manheim Denver",
                        "state" => "CO",
                        "city" => "Aurora",
                        "address" => "*CONTACT DISPATCHER*",
                        "zip" => "80011",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Champaign Ford City",
                        "state" => "IL",
                        "city" => "Champaign",
                        "address" => "701 W Marketview Drive",
                        "zip" => "61822",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12175492368",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Bob Fricke",
                        "full_name" => "Worden Martin, Inc",
                        "state" => "IL",
                        "city" => "Savoy",
                        "address" => "1404 N Dunlap Ave",
                        "zip" => "61874",
                        "phones" => [
                            [
                                "number" => "12175492368",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "12173527901",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 1200,
                        "terms" => null,
                        "customer_payment_amount" => 1200,
                        "customer_payment_method_id" => 15,
                        "customer_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_2(): void
    {
        $this->createMakes("LEXUS", "TOYOTA")
            ->createStates("OK", "IL", "IL")
            ->createTimeZones("73108", "60016", "60042")
            ->assertParsing(
                2,
                [
                    "load_id" => "Oklahoma",
                    "pickup_date" => "08/12/2021",
                    "delivery_date" => "08/13/2021",
                    "dispatch_instructions" => "Dealers Auto Auction of Oklahoma City. 1028 S Portland Ave, Oklahoma City,"
                        . " OK 73108 405-947-2886 This should be picked up within 2 days of 08/12/2021. This should be delivered"
                        . " within 2 days of 08/13/2021.",
                    "vehicles" => [
                        [
                            "year" => "2013",
                            "make" => "lexus",
                            "model" => "ct 200h",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "JTHKD5BH0D2164113",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2016",
                            "make" => "toyota",
                            "model" => "prius",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "JTDKARFU1G3015300",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Dealers Auto Auction of Oklahoma City",
                        "state" => "OK",
                        "city" => "Oklahoma City",
                        "address" => "1028 S Portland Ave",
                        "zip" => "73108",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14059472886",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "amin 773 895 9281",
                        "state" => "IL",
                        "city" => "Des Plaines",
                        "address" => "480 potter road",
                        "zip" => "60016",
                        "phones" => [
                            [
                                "number" => "17737153673",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "17738959281",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Yousef Dabbagh",
                        "full_name" => "Friends Auto Sales, Inc",
                        "state" => "IL",
                        "city" => "Island Lake",
                        "address" => "2438 Fen View Circle",
                        "zip" => "60042",
                        "phones" => [],
                        "fax" => "18155788467",
                        "phone" => "17737153673",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 800,
                        "terms" => null,
                        "customer_payment_amount" => 800,
                        "customer_payment_method_id" => 14,
                        "customer_payment_method" => [
                            "id" => 14,
                            "title" => "Cash",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_3(): void
    {
        $this->createMakes("HYUNDAI", "NISSAN")
            ->createStates("AZ", "WI", "CA")
            ->createTimeZones("85251", "53590", "90038")
            ->assertParsing(
                3,
                [
                    "load_id" => "70991-XI",
                    "pickup_date" => "08/17/2021",
                    "delivery_date" => "08/24/2021",
                    "dispatch_instructions" => "This should be picked up within 2 days of 08/17/2021. This should be delivered"
                        . " within 2 days of 08/24/2021.",
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "hyundai",
                            "model" => "palisade",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                        [
                            "year" => "2012",
                            "make" => "nissan",
                            "model" => "titan",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => null,
                        "state" => "AZ",
                        "city" => "Scottsdale",
                        "address" => "8771 E Pinchot Ave",
                        "zip" => "85251",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14145811253",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => null,
                        "state" => "WI",
                        "city" => "Sun Prairie",
                        "address" => "571 N Musket Ridge Dr",
                        "zip" => "53590",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14145811253",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Dispatch / Support",
                        "full_name" => "Crystal Car Shipping Inc",
                        "state" => "CA",
                        "city" => "Los Angeles",
                        "address" => "708 N Manhattan Pl",
                        "zip" => "90038",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14244881452",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 2300,
                        "terms" => null,
                        "customer_payment_amount" => 2300,
                        "customer_payment_method_id" => 14,
                        "customer_payment_method" => [
                            "id" => 14,
                            "title" => "Cash",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_4(): void
    {
        $this->createMakes("AUDI", "VOLKSWAGEN")
            ->createStates("CA", "IA", "IA")
            ->createTimeZones("95377", "52246", "52240")
            ->assertParsing(
                4,
                [
                    "load_id" => "28900502",
                    "pickup_date" => "08/13/2021",
                    "delivery_date" => "08/16/2021",
                    "dispatch_instructions" => "This should be picked up within 2 days of 08/13/2021. This should be delivered"
                        . " within 2 days of 08/16/2021.",
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "audi",
                            "model" => "s4",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "WAUB4AF41KA092050",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2019",
                            "make" => "volkswagen",
                            "model" => "golf alltrack",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "3VWH17AU8KM505825",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2018",
                            "make" => "volkswagen",
                            "model" => "atlas",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "1V2AP2CA7JC541948",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "ADESA GOLDEN GATE",
                        "state" => "CA",
                        "city" => "Tracy",
                        "address" => "18501 Stanford Rd",
                        "zip" => "95377",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12098398000",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "CAROUSEL MOTORS",
                        "state" => "IA",
                        "city" => "Iowa City",
                        "address" => "809 Hwy 1 west",
                        "zip" => "52246",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13193542550",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Chris Hester",
                        "full_name" => "Autohaus Ltd",
                        "state" => "IA",
                        "city" => "Iowa City",
                        "address" => "809 Highway 1 West",
                        "zip" => "52240",
                        "phones" => [],
                        "fax" => "13193376030",
                        "phone" => "13193542550",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 2400,
                        "terms" => null,
                        "customer_payment_amount" => 2400,
                        "customer_payment_method_id" => 15,
                        "customer_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_5(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("CO", "IL", "IL")
            ->createTimeZones("80817", "62703", "62703")
            ->assertParsing(
                5,
                [
                    "load_id" => "28620628",
                    "pickup_date" => "07/21/2021",
                    "delivery_date" => "07/24/2021",
                    "dispatch_instructions" => "text Ray for Dispatch 217-416-7006 This should be picked up within 2 days"
                        . " of 07/21/2021. This should be delivered within 2 days of 07/24/2021.",
                    "vehicles" => [
                        [
                            "year" => "2018",
                            "make" => "chevrolet",
                            "model" => "malibu",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "1G1ZD5ST4JF266080",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2018",
                            "make" => "chevrolet",
                            "model" => "malibu",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "1G1ZD5ST5JF271417",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2018",
                            "make" => "chevrolet",
                            "model" => "malibu",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "1G1ZD5ST1JF266473",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2018",
                            "make" => "chevrolet",
                            "model" => "malibu",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "1G1ZD5ST0JF266688",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2018",
                            "make" => "chevrolet",
                            "model" => "malibu",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "1G1ZD5ST7JF263402",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2018",
                            "make" => "chevrolet",
                            "model" => "malibu",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "1G1ZD5ST9JF270853",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Adesa Colorado Springs",
                        "state" => "CO",
                        "city" => "Fountain",
                        "address" => "10680 Charter Oak Ranch Rd",
                        "zip" => "80817",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17193916600",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Auto Mall of Springfield",
                        "state" => "IL",
                        "city" => "Springfield",
                        "address" => "920 S Dirksen PKWY",
                        "zip" => "62703",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12174167006",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Ray Rehan",
                        "full_name" => "Auto Mall of Springfield Inc",
                        "state" => "IL",
                        "city" => "Springfield",
                        "address" => "920 S Dirksen Pkwy",
                        "zip" => "62703",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12174167006",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 3000,
                        "terms" => null,
                        "customer_payment_amount" => 3000,
                        "customer_payment_method_id" => 15,
                        "customer_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_6(): void
    {
        $this->createMakes("GMC", "CADILLAC")
            ->createStates("PA", "MN", "MN")
            ->createTimeZones("17545", "55303", "55303")
            ->assertParsing(
                6,
                [
                    "load_id" => "23057519",
                    "pickup_date" => "02/22/2020",
                    "delivery_date" => "02/25/2020",
                    "dispatch_instructions" => "This should be picked up within 2 days of 02/22/2020. This should be delivered"
                        . " within 2 days of 02/25/2020.",
                    "vehicles" => [
                        [
                            "year" => "2017",
                            "make" => "gmc",
                            "model" => "acadia",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "1GKKNULS9HZ223096",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2016",
                            "make" => "cadillac",
                            "model" => "escalade",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "1GYS4CKJ7GR324065",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Manheim Pennsylvania",
                        "state" => "PA",
                        "city" => "Manheim",
                        "address" => "1190 Lancaster Rd Manheim, PA 17545-9746",
                        "zip" => "17545",
                        "phones" => [
                            [
                                "number" => "16127508927",
                            ],
                            [
                                "number" => "17633555511",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "17176653571",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Druk Auto Sales",
                        "state" => "MN",
                        "city" => "Ramsey",
                        "address" => "8000 HWY 10 NW",
                        "zip" => "55303",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16127508927",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Danny",
                        "full_name" => "Druk Auto Sales",
                        "state" => "MN",
                        "city" => "Ramsey",
                        "address" => "8000 highway 10",
                        "zip" => "55303",
                        "phones" => [
                            [
                                "number" => "16127508927",
                            ],
                        ],
                        "fax" => "16512725462",
                        "phone" => "16512725461",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 1300,
                        "terms" => null,
                        "customer_payment_amount" => 1300,
                        "customer_payment_method_id" => 15,
                        "customer_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_7(): void
    {
        $this->createMakes("NISSAN")
            ->createStates("TN", "MN", "MN")
            ->createTimeZones("37122", "55077", "55077")
            ->assertParsing(
                7,
                [
                    "load_id" => "P16068-70",
                    "pickup_date" => "02/20/2020",
                    "delivery_date" => "02/24/2020",
                    "dispatch_instructions" => "This should be picked up within 2 days of 02/20/2020. This should be delivered"
                        . " within 2 days of 02/24/2020.",
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "nissan",
                            "model" => "murano",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "5N1AZ2MS5KN130228",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2019",
                            "make" => "nissan",
                            "model" => "murano",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "5N1AZ2MS8KN129655",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2018",
                            "make" => "nissan",
                            "model" => "murano",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "5N1AZ2MH7JN187887",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "MANHEIM NASHVILLE",
                        "state" => "TN",
                        "city" => "Mount Juliet",
                        "address" => "8400 EASTGATE BLVD",
                        "zip" => "37122",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16157733800",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "LUTHER NISSAN KIA",
                        "state" => "MN",
                        "city" => "Inver Grove Heights",
                        "address" => "1470 50TH STREET EAST",
                        "zip" => "55077",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16127993225",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Dave Rife , Craig Brink",
                        "full_name" => "Luther Nissan Kia",
                        "state" => "MN",
                        "city" => "Inver Grove Heights",
                        "address" => "1470 50th St E",
                        "zip" => "55077",
                        "phones" => [
                            [
                                "number" => "16127993225",
                            ],
                        ],
                        "fax" => "16514575009",
                        "phone" => "16514575757",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 1350,
                        "terms" => null,
                        "customer_payment_amount" => 1350,
                        "customer_payment_method_id" => 15,
                        "customer_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_8(): void
    {
        $this->createMakes("BMW", "DODGE")
            ->createStates("PA", "ND", "ND")
            ->createTimeZones("17545", "58504", "58504")
            ->assertParsing(
                8,
                [
                    "load_id" => "23061663",
                    "pickup_date" => "02/22/2020",
                    "delivery_date" => "02/25/2020",
                    "dispatch_instructions" => "This should be picked up within 2 days of 02/22/2020. This should be delivered"
                        . " within 2 days of 02/25/2020.",
                    "vehicles" => [
                        [
                            "year" => "2013",
                            "make" => "bmw",
                            "model" => "535ia drive",
                            "color" => "white",
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                        [
                            "year" => "2010",
                            "make" => "dodge",
                            "model" => "journey",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => null,
                        "state" => "PA",
                        "city" => "Manheim",
                        "address" => "*CONTACT DISPATCHER*",
                        "zip" => "17545",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => null,
                        "state" => "ND",
                        "city" => "Bismarck",
                        "address" => "*CONTACT DISPATCHER*",
                        "zip" => "58504",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Evon Allen",
                        "full_name" => "G4 Autosports Inc",
                        "state" => "ND",
                        "city" => "Bismarck",
                        "address" => "1245 South 12th Street",
                        "zip" => "58504",
                        "phones" => [
                            [
                                "number" => "17012203388",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "17012223447",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 2700,
                        "terms" => null,
                        "customer_payment_amount" => 2700,
                        "customer_payment_method_id" => 15,
                        "customer_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_9(): void
    {
        $this->createMakes("CHRYSLER")
            ->createStates("MO", "MN", "MN")
            ->createTimeZones("63044", "55352", "55352")
            ->assertParsing(
                9,
                [
                    "load_id" => "Copart",
                    "pickup_date" => "02/24/2020",
                    "delivery_date" => "02/25/2020",
                    "dispatch_instructions" => "Call or text only Tony 9522206368. Have forklift to unload This must be"
                        . " picked up exactly on 02/24/2020. This should be delivered within 2 days of 02/25/2020.",
                    "vehicles" => [
                        [
                            "year" => "2017",
                            "make" => "chrysler",
                            "model" => "pacifica",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "2C4RC1BG6HR576515",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2012",
                            "make" => "chrysler",
                            "model" => "town & country",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "2C4RC1CG4CR176685",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => null,
                        "state" => "MO",
                        "city" => "Bridgeton",
                        "address" => "13033 TAUSSIG AVE",
                        "zip" => "63044",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Ace Auto LLC",
                        "state" => "MN",
                        "city" => "Jordan",
                        "address" => "16302 Jordan Ave",
                        "zip" => "55352",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19522206368",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Ed Tabakov",
                        "full_name" => "Ace Auto LLC",
                        "state" => "MN",
                        "city" => "Jordan",
                        "address" => "16302 Jordan Avenue",
                        "zip" => "55352",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19522176274",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 800,
                        "terms" => null,
                        "customer_payment_amount" => 800,
                        "customer_payment_method_id" => 15,
                        "customer_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_10(): void
    {
        $this->createMakes("FORD")
            ->createStates("IN", "MN", "MN")
            ->createTimeZones("46168", "55110", "55110")
            ->assertParsing(
                10,
                [
                    "load_id" => "2 adesa indy",
                    "pickup_date" => "02/25/2020",
                    "delivery_date" => "02/26/2020",
                    "dispatch_instructions" => "This should be picked up within 2 days of 02/25/2020. This should be delivered"
                        . " within 2 days of 02/26/2020.",
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "ford",
                            "model" => "super duty f-250 srw",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "1FT7W2BT2KED56116",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2019",
                            "make" => "ford",
                            "model" => "edge",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "2FMPK3K91KBB10333",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "adesa indianpolis",
                        "state" => "IN",
                        "city" => "Plainfield",
                        "address" => "2950 e main st",
                        "zip" => "46168",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "white bear lincoln",
                        "state" => "MN",
                        "city" => "White Bear Lake",
                        "address" => "3425 hwy 61 n",
                        "zip" => "55110",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16514832631",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Travis Peterson",
                        "full_name" => "White Bear Linc Merc Inc",
                        "state" => "MN",
                        "city" => "White Bear Lake",
                        "address" => "3425 N Highway 61",
                        "zip" => "55110",
                        "phones" => [],
                        "fax" => "16514830837",
                        "phone" => "16514832631",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 900,
                        "terms" => null,
                        "customer_payment_amount" => 900,
                        "customer_payment_method_id" => 15,
                        "customer_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
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
            ->createStates("MD", "PA", "MO")
            ->createTimeZones("21740", "19153", "64133")
            ->assertParsing(
                11,
                [
                    "load_id" => "57999-UI",
                    "pickup_date" => "02/27/2020",
                    "delivery_date" => "02/28/2020",
                    "dispatch_instructions" => "CALL DANIEL WITH ANY TRANSPORT QUESTIONS ISUES OR DELAYS 214-534-8845 This"
                        . " must be picked up on or up to 2 days prior to 02/27/2020. This must be delivered on or up to 2"
                        . " days prior to 02/28/2020.",
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "mercedes-benz",
                            "model" => "sprinter 144 wb hr",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "WD4PF0CD0KT014970",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2019",
                            "make" => "mercedes-benz",
                            "model" => "sprinter 170 wb hr",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "WD4PF1CD4KP162070",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "ARTIC VANS-MD",
                        "state" => "MD",
                        "city" => "Hagerstown",
                        "address" => "16105 Business Pkwy",
                        "zip" => "21740",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14105855906",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "CSTK EAST",
                        "state" => "PA",
                        "city" => "Philadelphia",
                        "address" => "7301 HOLSTEIN AVE",
                        "zip" => "19153",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12675812405",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Name & Phone in Dispatch Instructions",
                        "full_name" => "Nations Auto Transport",
                        "state" => "MO",
                        "city" => "Kansas City",
                        "address" => "12032 E 46 Terrace",
                        "zip" => "64133",
                        "phones" => [
                            [
                                "number" => "18773313100",
                            ],
                        ],
                        "fax" => "18162953102",
                        "phone" => "18162723544",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 800,
                        "terms" => "Nations Auto Transport agrees to pay GIG Logistics Inc $800.00 within 5"
                            . " business days of receiving a signed Bill of Lading. Payment will be made with Company Check.",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 800,
                        "broker_payment_method_id" => 15,
                        "broker_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "broker_payment_days" => 5,
                        "broker_payment_begins" => "invoice-sent",
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_12(): void
    {
        $this->createMakes("SUZUKI", "FORD", "MITSUBISHI")
            ->createStates("MN", "OH", "FL")
            ->createTimeZones("55303", "43110", "33487")
            ->assertParsing(
                12,
                [
                    "load_id" => "1422360",
                    "pickup_date" => "02/25/2020",
                    "delivery_date" => "02/27/2020",
                    "dispatch_instructions" => "This should be picked up within 2 days of 02/25/2020. This should be delivered"
                        . " within 2 days of 02/27/2020.",
                    "vehicles" => [
                        [
                            "year" => "2006",
                            "make" => "suzuki",
                            "model" => "ml7",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                        [
                            "year" => "2005",
                            "make" => "ford",
                            "model" => "taurus",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                        [
                            "year" => "2004",
                            "make" => "mitsubishi",
                            "model" => "endeavor",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => null,
                        "state" => "MN",
                        "city" => "Anoka",
                        "address" => "8050 147th Lane NW",
                        "zip" => "55303",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17634389624",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "IAA",
                        "state" => "OH",
                        "city" => "Canal Winchester",
                        "address" => "8929 Oak Tree Rd.",
                        "zip" => "43110",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16142604270",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Anyone",
                        "full_name" => "ShipYourCarNow LLC",
                        "state" => "FL",
                        "city" => "Boca Raton",
                        "address" => "1160 South Rogers Circle Suite 1",
                        "zip" => "33487",
                        "phones" => [],
                        "fax" => "15614224730",
                        "phone" => "19179335723",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 800,
                        "terms" => null,
                        "customer_payment_amount" => 800,
                        "customer_payment_method_id" => 14,
                        "customer_payment_method" => [
                            "id" => 14,
                            "title" => "Cash",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_13(): void
    {
        $this->createMakes("MAZDA", "GENESIS")
            ->createStates("NV", "NE", "UT")
            ->createTimeZones("89104", "68147", "84087")
            ->assertParsing(
                13,
                [
                    "load_id" => "ATS2352 Plase call 385-777-2403",
                    "pickup_date" => "02/25/2020",
                    "delivery_date" => "02/27/2020",
                    "dispatch_instructions" => "**READ DISPATCH INSTRUCTIONS** **READ DISPATCH INSTRUCTIONS** **READ DISPATCH"
                        . " INSTRUCTIONS** *IF ANY DELAYS ARE EXPECTED FOR DELIVERY OR PICK UP THE CARRIER MUST CALL THE SHIPPING"
                        . " TEAM AT (385)- 777-2403 NOT THE PICKUP OR DELIVERY LOCATION *CARRIER MUST SEND SIGNED BOL AND"
                        . " CURRENT W-9 TO --> shipping@myautosource.com TO START THE PAYMENT PROCESS *MUST CALL ONE HOUR"
                        . " PRIOR TO PICK UP OR DROP OFF NO EXCEPTIONS *INCORRECTLY SHIPPED VEHICLES MAY BE SUBJECT TO RATE"
                        . " DEDUCTION *LOADING/UNLOADING HOURS ARE: MON-FRI, 9AM--5PM *DROPPING ON SATURDAYS OR SUNDAYS CONSTITUTES"
                        . " A VIOLATION OF THE CONTRACT AND WILL BE SUBJECT TO RATE REDUCTION. *NIGHT DROPS OR AFTER HOUR"
                        . " DROPS COST $50 PER VEHICLE **IF A LATE DELIVERY IS NOT COMMUNICATED TO THE SHIPPING TEAM, OR IF"
                        . " THE DELAY IS DUE TO PREVENTABLE CIRCUMSTANCES AND NOT COMMUNICATED WITH PROOF TO AUTOSOURCE 5%"
                        . " OF THE CONTRACTED RATE WILL BE DEDUCTED FOR EACH DAY LATE UP TO 40% OF THE CONTRACTED RATE. This"
                        . " must be picked up exactly on 02/25/2020. This must be delivered exactly on 02/27/2020.",
                    "vehicles" => [
                        [
                            "year" => "2018",
                            "make" => "mazda",
                            "model" => "cx-5",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "JM3KFBBM6J0352771",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2017",
                            "make" => "genesis",
                            "model" => "g80",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "KMHGN4JE8HU175131",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Autosource - Las Vegas",
                        "state" => "NV",
                        "city" => "Las Vegas",
                        "address" => "2121 E SAHARA AVE",
                        "zip" => "89104",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17024691754",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Autosource - Bellevue",
                        "state" => "NE",
                        "city" => "Bellevue",
                        "address" => "608 Fort Cook Road North",
                        "zip" => "68147",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14028807588",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Josh/Trey/Alexis",
                        "full_name" => "Autosource Motors (prev. AutoSource Transport)",
                        "state" => "UT",
                        "city" => "Woods Cross",
                        "address" => "2023 S 625 W",
                        "zip" => "84087",
                        "phones" => [
                            [
                                "number" => "13857772403",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "18012928949",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 1300,
                        "terms" => "Autosource Motors (prev. AutoSource Transport) agrees to pay GIG Logistics"
                            . " Inc $1,300.00 within 10 business days of receiving a signed Bill of Lading. Payment will be made"
                            . " with Company Check.",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 1300,
                        "broker_payment_method_id" => 15,
                        "broker_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "broker_payment_days" => 10,
                        "broker_payment_begins" => "invoice-sent",
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_14(): void
    {
        $this->createMakes("FORD")
            ->createStates("OH", "AL", "AL")
            ->createTimeZones("45150", "35810", "35816")
            ->assertParsing(
                14,
                [
                    "load_id" => "0565",
                    "pickup_date" => "02/27/2020",
                    "delivery_date" => "02/28/2020",
                    "dispatch_instructions" => "**ABSOLUTELY MUST CALL STORING DEALER OR AUCTION PRIOR TO PICKUP TO MAKE"
                        . " ARRANGEMENTS AND TO VERIFY PICKUP ADDRESS!!! ASK FOR LEASE MANAGER IF AT A DEALERSHIP. LEASE or"
                        . " F&I MANAGER WILL ALSO HAVE KEYS. MUST HAVE VEHICLE RELEASE (LIKE A GATE PASS) - CONTACT NIKKI"
                        . " (256-200-4556 or DEALER.DISPATCH@ZOHOMAIL.COM) FOR THIS. **ALL AUCTION UNITS MUST HAVE ANY AND"
                        . " ALL DAMAGES MARKED ON A BILL OF LADING BEFORE LEAVING THE LOT OR YOU WILL BE RESPONSIBLE FOR ANY"
                        . " DAMAGE - THIS IS EVERY AUCTION'S POLICY** IF YOU HAVE ANY ISSUES OR QUESTIONS, CALL NIKKI @256-200-4556."
                        . " ** PLEASE be sure to make note of ANY damages, more than average scratches, dents, missing equip,"
                        . " *ESPECIALLY WINDSHIELD CRACKS/CHIPS* etc on your BOL and note AT TIME OF PICKUP. This protects"
                        . " you, should any damages be found upon delivery. In order to receive the quickest payment, please"
                        . " send your BOL/Invoice to DEALER.DISPATCH@ZOHOMAIL.COM or by submitting invoices through Central"
                        . " Dispatch. SAFE TRAVELS AND THANK YOU! Please be sure to rate us on Central Dispatch!!! PLEASE"
                        . " be sure to read special instructions and contract thoroughly! This should be picked up within"
                        . " 2 days of 02/27/2020. This should be delivered within 2 days of 02/28/2020.",
                    "vehicles" => [
                        [
                            "year" => "2017",
                            "make" => "ford",
                            "model" => "escape",
                            "color" => "Silver",
                            "license_plate" => null,
                            "vin" => "1FMCU0GD9HUD07516",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2017",
                            "make" => "ford",
                            "model" => "escape",
                            "color" => "Black",
                            "license_plate" => null,
                            "vin" => "1FMCU0J90HUB00565",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Mike Castrucci Ford",
                        "state" => "OH",
                        "city" => "Milford",
                        "address" => "1020 STATE ROUTE 28",
                        "zip" => "45150",
                        "phones" => [
                            [
                                "number" => "15138317010",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "15132751048",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Woody Anderson Ford",
                        "state" => "AL",
                        "city" => "Huntsville",
                        "address" => "2500 Jordan Lane",
                        "zip" => "35810",
                        "phones" => [
                            [
                                "number" => "12562004556",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "12564177385",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Nikki Jackson",
                        "full_name" => "Woody Anderson Ford",
                        "state" => "AL",
                        "city" => "Huntsville",
                        "address" => "2500 Jordan Ln",
                        "zip" => "35816",
                        "phones" => [
                            [
                                "number" => "12562004556",
                            ],
                        ],
                        "fax" => "12565171234",
                        "phone" => "12565399441",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 650,
                        "terms" => "Woody Anderson Ford agrees to pay GIG Logistics Inc $650.00 within 10 business"
                            . " days of receiving a signed Bill of Lading. Payment will be made with Company Check.",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 650,
                        "broker_payment_method_id" => 15,
                        "broker_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "broker_payment_days" => 10,
                        "broker_payment_begins" => "invoice-sent",
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_15(): void
    {
        $this->createMakes("HONDA")
            ->createStates("IL", "KS", "KS")
            ->createTimeZones("60586", "67207", "67002")
            ->assertParsing(
                15,
                [
                    "load_id" => "Sh0723at",
                    "pickup_date" => "12/14/2019",
                    "delivery_date" => "12/16/2019",
                    "dispatch_instructions" => "Text 316-708-7894 24/7 Hebeifhg5gdveit0hdvect5gdvejtb This should be picked"
                        . " up within 2 days of 12/14/2019. This should be delivered within 2 days of 12/16/2019.",
                    "vehicles" => [
                        [
                            "year" => "2015",
                            "make" => "honda",
                            "model" => "pilot",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "5FNYF3H73FB030723",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Hawk Subaru",
                        "state" => "IL",
                        "city" => "Plainfield",
                        "address" => "2401 Route 59",
                        "zip" => "60586",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18157257110",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Scholfield Honda",
                        "state" => "KS",
                        "city" => "Wichita",
                        "address" => "7017 E Kellogg Dr",
                        "zip" => "67207",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13166886400",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Brad Train",
                        "full_name" => "AutoFast Logistics LLC 2",
                        "state" => "KS",
                        "city" => "Andover",
                        "address" => "PO BOX 156",
                        "zip" => "67002",
                        "phones" => [
                            [
                                "number" => "18442327828",
                            ],
                        ],
                        "fax" => "13162011726",
                        "phone" => "13162183882",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 400,
                        "terms" => "AutoFast Logistics LLC 2 agrees to pay GIG Logistics Inc $400.00 within"
                            . " 15 business days of receiving a signed Bill of Lading. Payment will be made with Company Check.",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 400,
                        "broker_payment_method_id" => 15,
                        "broker_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "broker_payment_days" => 15,
                        "broker_payment_begins" => "invoice-sent",
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_16(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("WI", "NJ", "NJ")
            ->createTimeZones("53089", "07036", "07435")
            ->assertParsing(
                16,
                [
                    "load_id" => "20190221 call vince 2158680076",
                    "pickup_date" => "10/12/2019",
                    "delivery_date" => "10/14/2019",
                    "dispatch_instructions" => "call dispatcher 215 868 0076 vince This should be picked up within 2 days"
                        . " of 10/12/2019. This should be delivered within 2 days of 10/14/2019.",
                    "vehicles" => [
                        [
                            "year" => "2003",
                            "make" => "toyota",
                            "model" => "tundra",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "insurance auction",
                        "state" => "WI",
                        "city" => "Sussex",
                        "address" => "N70W25277 Indian Grass Lane Sussex, WI 53089-2578",
                        "zip" => "53089",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12622468822",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "target international shipping",
                        "state" => "NJ",
                        "city" => "Linden",
                        "address" => "1065 edward street",
                        "zip" => "07036",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19088621777",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Chris Amaefuna",
                        "full_name" => "Motorways Corp",
                        "state" => "NJ",
                        "city" => "Newfoundland",
                        "address" => "2925 State Rt 23 Suite G1",
                        "zip" => "07435",
                        "phones" => [
                            [
                                "number" => "12154164473",
                            ],
                        ],
                        "fax" => "12154164473",
                        "phone" => "17329216152",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 400,
                        "terms" => null,
                        "customer_payment_amount" => 400,
                        "customer_payment_method_id" => 14,
                        "customer_payment_method" => [
                            "id" => 14,
                            "title" => "Cash",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_17(): void
    {
        $this->createMakes("LEXUS")
            ->createStates("IL", "OH", "OH")
            ->createTimeZones("61614", "44135", "44135")
            ->assertParsing(
                17,
                [
                    "load_id" => "210348T FROM PEORIA",
                    "pickup_date" => "11/16/2020",
                    "delivery_date" => "11/17/2020",
                    "dispatch_instructions" => "CONTACT TONY @TPANCZYK@METROLEXUS.COM OR 2162012131 This should be picked"
                        . " up within 2 days of 11/16/2020. This should be delivered within 2 days of 11/17/2020.",
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "lexus",
                            "model" => "rx",
                            "color" => "WHITE",
                            "license_plate" => null,
                            "vin" => "2T2HZMDAXMC262733",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "OBRIEN LEXUS OF PEORIA",
                        "state" => "IL",
                        "city" => "Peoria",
                        "address" => "7301 NORTH ALLEN RD",
                        "zip" => "61614",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13096893000",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Metro Lexus",
                        "state" => "OH",
                        "city" => "Cleveland",
                        "address" => "13600 Brookpark Rd",
                        "zip" => "44135",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12162012131",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "TOM BRENNAN, TONY PANCYZK",
                        "full_name" => "Metro Lexus",
                        "state" => "OH",
                        "city" => "Brookpark",
                        "address" => "13600 Brookpark Rd",
                        "zip" => "44135",
                        "phones" => [
                            [
                                "number" => "12162012106",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "12169166000",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 425,
                        "terms" => "Metro Lexus agrees to pay GIG Logistics Inc $425.00 within 10 business days"
                            . " of delivery. Payment will be made with Company Check.",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 425,
                        "broker_payment_method_id" => 15,
                        "broker_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "broker_payment_days" => 10,
                        "broker_payment_begins" => "delivery",
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_18(): void
    {
        $this->createMakes("LEXUS", "TOYOTA")
            ->createStates("OH", "VA", "VA")
            ->createTimeZones("44115", "23294", "23113")
            ->assertParsing(
                18,
                [
                    "load_id" => "2",
                    "pickup_date" => "11/17/2020",
                    "delivery_date" => "11/18/2020",
                    "dispatch_instructions" => "This should be picked up within 2 days of 11/17/2020. This should be delivered"
                        . " within 2 days of 11/18/2020.",
                    "vehicles" => [
                        [
                            "year" => "2017",
                            "make" => "lexus",
                            "model" => "rx",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "2T2BZMCAXHC094878",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2013",
                            "make" => "toyota",
                            "model" => "rav4",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "JTMDFREV2D5018291",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "CENTRAL CADILLAC",
                        "state" => "OH",
                        "city" => "Cleveland",
                        "address" => "2801 CARNEGIE AVENUE",
                        "zip" => "44115",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12168615800",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Hyman Bros Automobiles",
                        "state" => "VA",
                        "city" => "Richmond",
                        "address" => "8066 West Broad Street",
                        "zip" => "23294",
                        "phones" => [
                            [
                                "number" => "18047477007",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "18043799922",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Jenny",
                        "full_name" => "Hyman Bros Automobiles",
                        "state" => "VA",
                        "city" => "Midlothian",
                        "address" => "11840 Midlothian Tpke",
                        "zip" => "23113",
                        "phones" => [],
                        "fax" => "18043799929",
                        "phone" => "18043799922",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 700,
                        "terms" => null,
                        "customer_payment_amount" => 700,
                        "customer_payment_method_id" => 15,
                        "customer_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_19(): void
    {
        $this->createMakes("DODGE")
            ->createStates("PA", "VA", "VA")
            ->createTimeZones("15370", "23294", "23113")
            ->assertParsing(
                19,
                [
                    "load_id" => "JH329860",
                    "pickup_date" => "11/17/2020",
                    "delivery_date" => "11/18/2020",
                    "dispatch_instructions" => "This should be picked up within 2 days of 11/17/2020. This should be delivered"
                        . " within 2 days of 11/18/2020.",
                    "vehicles" => [
                        [
                            "year" => "2018",
                            "make" => "dodge",
                            "model" => "charger",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "2C3CDXGJ2JH329860",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "RON LEWIS CHRYSLER DODGE JEEP RAM",
                        "state" => "PA",
                        "city" => "Waynesburg",
                        "address" => "1625 EAST HIGH STREET",
                        "zip" => "15370",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17246277111",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Hyman Bros Automobiles",
                        "state" => "VA",
                        "city" => "Richmond",
                        "address" => "8066 West Broad Street",
                        "zip" => "23294",
                        "phones" => [
                            [
                                "number" => "18047477007",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "18043799922",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Jenny",
                        "full_name" => "Hyman Bros Automobiles",
                        "state" => "VA",
                        "city" => "Midlothian",
                        "address" => "11840 Midlothian Tpke",
                        "zip" => "23113",
                        "phones" => [],
                        "fax" => "18043799929",
                        "phone" => "18043799922",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 400,
                        "terms" => null,
                        "customer_payment_amount" => 400,
                        "customer_payment_method_id" => 15,
                        "customer_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_20(): void
    {
        $this->createMakes("PONTIAC")
            ->createStates("VA", "MI", "FL")
            ->createTimeZones("23832", "48021", "34219")
            ->assertParsing(
                20,
                [
                    "load_id" => "2701584",
                    "pickup_date" => "11/18/2020",
                    "delivery_date" => "11/20/2020",
                    "dispatch_instructions" => "This should be picked up within 2 days of 11/18/2020. This should be delivered"
                        . " within 2 days of 11/20/2020.",
                    "vehicles" => [
                        [
                            "year" => "2006",
                            "make" => "pontiac",
                            "model" => "grand prix",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => null,
                        "state" => "VA",
                        "city" => "Chesterfield",
                        "address" => "15841 Evergreen Ave",
                        "zip" => "23832",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13137375003",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => null,
                        "state" => "MI",
                        "city" => "Eastpointe",
                        "address" => "10301 Colony village way apt 314",
                        "zip" => "48021",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13133043885",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Hayley Dunford",
                        "full_name" => "Auto Transport On Demand LLC",
                        "state" => "FL",
                        "city" => "Parrish",
                        "address" => "11103 Blue Magnolia Lane",
                        "zip" => "34219",
                        "phones" => [],
                        "fax" => "18669455979",
                        "phone" => "18669455979",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 400,
                        "terms" => null,
                        "customer_payment_amount" => 400,
                        "customer_payment_method_id" => 14,
                        "customer_payment_method" => [
                            "id" => 14,
                            "title" => "Cash",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_21(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("VA", "MI", "FL")
            ->createTimeZones("22312", "48917", "33130")
            ->assertParsing(
                21,
                [
                    "load_id" => "1236829-ZZ",
                    "pickup_date" => "11/18/2020",
                    "delivery_date" => "11/19/2020",
                    "dispatch_instructions" => "This should be picked up within 2 days of 11/18/2020. This should be delivered"
                        . " within 2 days of 11/19/2020.",
                    "vehicles" => [
                        [
                            "year" => "2007",
                            "make" => "toyota",
                            "model" => "prius",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => null,
                        "state" => "VA",
                        "city" => "Alexandria",
                        "address" => "6100 Declaration Square",
                        "zip" => "22312",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17038888023",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => null,
                        "state" => "MI",
                        "city" => "Lansing",
                        "address" => "6150 W Michigan Ave",
                        "zip" => "48917",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17038888023",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Manny, Juan,Gustavo,Laura,Ruth,Florencia, Maria",
                        "full_name" => "AutoStar Transport Express LLC (prev. AutoStar Transport & Logistics)",
                        "state" => "FL",
                        "city" => "Miami",
                        "address" => "80 SW 8th Street, Suite 2000",
                        "zip" => "33130",
                        "phones" => [
                            [
                                "number" => "18888028250",
                            ],
                        ],
                        "fax" => "19042120366",
                        "phone" => "18502547250",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 325,
                        "terms" => null,
                        "customer_payment_amount" => 325,
                        "customer_payment_method_id" => 14,
                        "customer_payment_method" => [
                            "id" => 14,
                            "title" => "Cash",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_22(): void
    {
        $this->createMakes("MERCEDES-BENZ")
            ->createStates("IN", "GA", "GA")
            ->createTimeZones("46140", "31210", "31210")
            ->assertParsing(
                22,
                [
                    "load_id" => "carvana lot to jackson recon center txt 4785381681",
                    "pickup_date" => "12/16/2020",
                    "delivery_date" => "12/17/2020",
                    "dispatch_instructions" => "TEXT CODY 4785381681 Mark all damage on gate pass or its on you Unload in"
                        . " turning lane on Sheraton drive and bring vehicle(s) under the front shelter. -Accounting hours"
                        . " are 9-5 for COD MONDAY-FRIDAY ONLY  Night drop available, located on the side of the building"
                        . " This should be picked up within 2 days of 12/16/2020. This should be delivered within 2 days of"
                        . " 12/17/2020.",
                    "vehicles" => [
                        [
                            "year" => "2013",
                            "make" => "mercedes-benz",
                            "model" => "c-class",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "WDDGJ4HBXDG085263",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "carvana lot",
                        "state" => "IN",
                        "city" => "Greenfield",
                        "address" => "6508 W FW Marks Dr.",
                        "zip" => "46140",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18332893533",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "jackson recon center",
                        "state" => "GA",
                        "city" => "Macon",
                        "address" => "4821 sheraton DRIVE",
                        "zip" => "31210",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14785381681",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "CODY SUDDETH",
                        "full_name" => "Jackson Automotive Group Inc.",
                        "state" => "GA",
                        "city" => "Macon",
                        "address" => "4781 Riverside Dr",
                        "zip" => "31210",
                        "phones" => [],
                        "fax" => "14784755167",
                        "phone" => "14785381681",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 400,
                        "terms" => null,
                        "customer_payment_amount" => 400,
                        "customer_payment_method_id" => 15,
                        "customer_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_23(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("NJ", "IN", "IN")
            ->createTimeZones("07632", "46032", "46032")
            ->assertParsing(
                23,
                [
                    "load_id" => "3175269291 Svetlana call or write text",
                    "pickup_date" => "01/07/2021",
                    "delivery_date" => "01/08/2021",
                    "dispatch_instructions" => "This should be picked up within 2 days of 01/07/2021. This should be delivered"
                        . " within 2 days of 01/08/2021.",
                    "vehicles" => [
                        [
                            "year" => "2013",
                            "make" => "toyota",
                            "model" => "prius",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "JTDKN3DU1D1726216",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Parkway Toyota",
                        "state" => "NJ",
                        "city" => "Englewood Cliffs",
                        "address" => "50 Sylvan Ave",
                        "zip" => "07632",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "CARMEL AUTO GALLERY",
                        "state" => "IN",
                        "city" => "Carmel",
                        "address" => "488 GRADLE DRIVE",
                        "zip" => "46032",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13176697000",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "OFFICE",
                        "full_name" => "Carmel Auto Gallery",
                        "state" => "IN",
                        "city" => "Carmel",
                        "address" => "488 Gradle Dr",
                        "zip" => "46032",
                        "phones" => [],
                        "fax" => "18885065664",
                        "phone" => "13176697000",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 500,
                        "terms" => null,
                        "customer_payment_amount" => 500,
                        "customer_payment_method_id" => 15,
                        "customer_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_24(): void
    {
        $this->createMakes("HONDA")
            ->createStates("NJ", "OH", "FL")
            ->createTimeZones("08035", "43040", "33130")
            ->assertParsing(
                24,
                [
                    "load_id" => "1235054-WP",
                    "pickup_date" => "11/20/2020",
                    "delivery_date" => "11/21/2020",
                    "dispatch_instructions" => "Car runs but battery may be dead. Pick up is at a retirement home, please"
                        . " contact Florence at 8566170928 when picking up. If no answer call me at 9375943045 and I will"
                        . " get a hold of her. Car runs but battery may be dead. Pick up is at a retirement home, please contact"
                        . " Florence at 8566170928 when picking up. If no answer call me at 9375943045 and I will get a hold"
                        . " of her. This should be picked up within 2 days of 11/20/2020. This should be delivered within"
                        . " 2 days of 11/21/2020.",
                    "vehicles" => [
                        [
                            "year" => "2015",
                            "make" => "honda",
                            "model" => "civic",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => null,
                        "state" => "NJ",
                        "city" => "Haddon Heights",
                        "address" => "117 E.Atlantic Ave. Unit 409",
                        "zip" => "08035",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18566170928",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => null,
                        "state" => "OH",
                        "city" => "Marysville",
                        "address" => "1640 Valley Drive",
                        "zip" => "43040",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19375943045",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Manny, Juan,Gustavo,Laura,Ruth,Florencia, Maria",
                        "full_name" => "AutoStar Transport Express LLC (prev. AutoStar Transport & Logistics)",
                        "state" => "FL",
                        "city" => "Miami",
                        "address" => "80 SW 8th Street, Suite 2000",
                        "zip" => "33130",
                        "phones" => [
                            [
                                "number" => "18888028250",
                            ],
                        ],
                        "fax" => "19042120366",
                        "phone" => "18502547250",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 300,
                        "terms" => null,
                        "customer_payment_amount" => 300,
                        "customer_payment_method_id" => 14,
                        "customer_payment_method" => [
                            "id" => 14,
                            "title" => "Cash",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_25(): void
    {
        $this->createMakes("RAM")
            ->createStates("IL", "TN", "MO")
            ->createTimeZones("60195", "38343", "63125")
            ->assertParsing(
                25,
                [
                    "load_id" => "R501",
                    "pickup_date" => "01/05/2021",
                    "delivery_date" => "01/06/2021",
                    "dispatch_instructions" => "Comchek/ACH Wire/Company Check ***FOR PAYMENT, MUST ADHERE TO INSTRUCTIONS"
                        . " BELOW*** ** This is NOT COD** NO RATES ON BOL / NO STI OR NIGHT DROPS BILL OF LADING MUST BE SIGNED"
                        . " AT BOTH ORIGIN AND DESTINATION WITH VEHICLE CONDITION NOTED PRIOR  TO OBTAINING SIGNATURES *If"
                        . " BOLs sent in are missing origin or destination signatures, payment will be held until your company"
                        . " either acquires those signatures in person or by fax/email from those parties the driver failed"
                        . " to obtain signatures from. **WE DO NOT PAY DRY-RUN FEES. IT IS DRIVERS RESPONSIBILITY TO CALL"
                        . " AHEAD AND CONFIRM PICK-UP & DELIVERY AVAILABILITY, AS WELL AS VIN# MATCHING VEHICLE TO DISPATCH"
                        . " ORDER BEFORE LOADING ** *** Send BOL to: info@allfourtransport.com This should be picked up within"
                        . " 2 days of 01/05/2021. This should be delivered within 2 days of 01/06/2021.",
                    "vehicles" => [
                        [
                            "year" => "2017",
                            "make" => "ram",
                            "model" => "1500",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "1C6RR7NT7HS800441",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Zeigler Chrysler Dodge Jeep",
                        "state" => "IL",
                        "city" => "Schaumburg",
                        "address" => "208 W GOLF RD",
                        "zip" => "60195",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18478828400",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "HUMBOLDT CHRYSLER DODGE",
                        "state" => "TN",
                        "city" => "Humboldt",
                        "address" => "3301 Eastend Dr",
                        "zip" => "38343",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17317844500",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "DISPATCH",
                        "full_name" => "All Four Transport",
                        "state" => "MO",
                        "city" => "Saint Louis",
                        "address" => "2518 LEMAY FERRY ROAD #1006",
                        "zip" => "63125",
                        "phones" => [
                            [
                                "number" => "18447317200",
                            ],
                        ],
                        "fax" => "13144508556",
                        "phone" => "13149745833",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 500,
                        "terms" => "All Four Transport agrees to pay GIG Logistics Inc $500.00 within 2 business"
                            . " days (Quick Pay) of receiving a signed Bill of Lading. Payment will be made with Certified Funds.",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 500,
                        "broker_payment_method_id" => 16,
                        "broker_payment_method" => [
                            "id" => 16,
                            "title" => "Certified Funds",
                        ],
                        "broker_payment_days" => 2,
                        "broker_payment_begins" => "invoice-sent",
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_26(): void
    {
        $this->createMakes("CHRYSLER")
            ->createStates("VA", "MI", "MI")
            ->createTimeZones("22408", "48237", "48228")
            ->assertParsing(
                26,
                [
                    "load_id" => "town & country va to det",
                    "pickup_date" => "01/12/2021",
                    "delivery_date" => "01/13/2021",
                    "dispatch_instructions" => "PLZ CALL OR TXT ABE @ 313.333.0477 This should be picked up within 2 days"
                        . " of 01/12/2021. This should be delivered within 2 days of 01/13/2021.",
                    "vehicles" => [
                        [
                            "year" => "2011",
                            "make" => "chrysler",
                            "model" => "town and country",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Fredericksburg- South (VA) iaai",
                        "state" => "VA",
                        "city" => "Fredericksburg",
                        "address" => "99 Industrial Drive",
                        "zip" => "22408",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15407100207",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "dealer connected H&I troy",
                        "state" => "MI",
                        "city" => "Oak Park",
                        "address" => "10721 TROY ST",
                        "zip" => "48237",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12487472883",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Abe Charara",
                        "full_name" => "Dealer Connected Inc",
                        "state" => "MI",
                        "city" => "Detroit",
                        "address" => "15511 Tireman St",
                        "zip" => "48228",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13133330477",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 500,
                        "terms" => "GIG Logistics Inc agrees to pay Dealer Connected Inc $100.00 within 2 business"
                            . " days (Quick Pay) of delivery. Payment will be made with Company Check.",
                        "customer_payment_amount" => 600,
                        "customer_payment_method_id" => 15,
                        "customer_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => 100,
                        "broker_fee_method_id" => 15,
                        "broker_fee_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "broker_fee_days" => 2,
                        "broker_fee_begins" => "delivery",
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_27(): void
    {
        $this->createMakes("LAND")
            ->createStates("IL", "PA", "FL")
            ->createTimeZones("60045", "18109", "33324")
            ->assertParsing(
                27,
                [
                    "load_id" => "BS51430",
                    "pickup_date" => "01/15/2021",
                    "delivery_date" => "01/16/2021",
                    "dispatch_instructions" => "This must be picked up on or up to 2 days prior to 01/15/2021. This must"
                        . " be delivered on or up to 2 days prior to 01/16/2021.",
                    "vehicles" => [
                        [
                            "year" => "2014",
                            "make" => "land",
                            "model" => "rover range rover",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "SALGS3TF8EA171003",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Executive Motor Carz",
                        "state" => "IL",
                        "city" => "Lake Forest",
                        "address" => "13885 W Polo Trail Drive",
                        "zip" => "60045",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18474940102",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Auto proved",
                        "state" => "PA",
                        "city" => "Allentown",
                        "address" => "1081 E. Congress St.",
                        "zip" => "18109",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16104352886",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Dante Torres - After Hours 954-325-3200",
                        "full_name" => "Blue Star Auto Movers",
                        "state" => "FL",
                        "city" => "Plantation",
                        "address" => "2 S University Dr, #220",
                        "zip" => "33324",
                        "phones" => [],
                        "fax" => "19549453692",
                        "phone" => "19547179884",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 550,
                        "terms" => "Blue Star Auto Movers agrees to pay GIG Logistics Inc $550.00 within 2 business"
                            . " days (Quick Pay) of receiving a signed Bill of Lading. Payment will be made with Company Check.",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 550,
                        "broker_payment_method_id" => 15,
                        "broker_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "broker_payment_days" => 2,
                        "broker_payment_begins" => "invoice-sent",
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_28(): void
    {
        $this->createMakes("LEXUS")
            ->createStates("IA", "IN", "IN")
            ->createTimeZones("50325", "46240", "47996")
            ->assertParsing(
                28,
                [
                    "load_id" => "21A-078A",
                    "pickup_date" => "01/18/2021",
                    "delivery_date" => "01/19/2021",
                    "dispatch_instructions" => "Dealer trade. Return vehicle available IF DRIVER NOTICES ANY DAMAGES TO"
                        . " THIS VEHICLE UPON PICK UP PLEASE CALL US IMMEDIATELY! Superior Auto Transport must receive a certificate"
                        . " of insurance being listed as a holder prior to pickup. Failure to do so will result in a $50 deduction"
                        . " in rate and will delay payment until a damage free delivery is verified by the recipient. The"
                        . " signed BOL and W9, must be also received before the payment will be sent out to the address listed"
                        . " on the W9. The documents can be emailed to dispatch.superiorauto@gmail.com  This must be picked"
                        . " up on or up to 2 days prior to 01/18/2021. This must be delivered on or up to 2 days prior to"
                        . " 01/19/2021.",
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "lexus",
                            "model" => "rx",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "2T2AZMDAXMC272891",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Willis Lexus",
                        "state" => "IA",
                        "city" => "Clive",
                        "address" => "2121 N.W. 100th Street",
                        "zip" => "50325",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15152539900",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Tom Wood Lexus",
                        "state" => "IN",
                        "city" => "Indianapolis",
                        "address" => "4610 E 96th St",
                        "zip" => "46240",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13175806888",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Stephanie",
                        "full_name" => "Superior Auto Transport LLC",
                        "state" => "IN",
                        "city" => "West Lafayette",
                        "address" => "PO Box 2535",
                        "zip" => "47996",
                        "phones" => [
                            [
                                "number" => "17655329867",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "17654254894",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 350,
                        "terms" => "Superior Auto Transport LLC agrees to pay GIG Logistics Inc $350.00 within"
                            . " 2 business days (Quick Pay) of receiving a signed Bill of Lading. Payment will be made with Company"
                            . " Check.",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 350,
                        "broker_payment_method_id" => 15,
                        "broker_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "broker_payment_days" => 2,
                        "broker_payment_begins" => "invoice-sent",
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_29(): void
    {
        $this->createMakes("LEXUS")
            ->createStates("TX", "MO", "MO")
            ->createTimeZones("78233", "64701", "64701")
            ->assertParsing(
                29,
                [
                    "load_id" => "San Antonio#1",
                    "pickup_date" => "02/10/2021",
                    "delivery_date" => "02/11/2021",
                    "dispatch_instructions" => "NO PHONE CALLS - Text 816-616-2727 ONLY This must be picked up exactly on"
                        . " 02/10/2021. This must be delivered exactly on 02/11/2021.",
                    "vehicles" => [
                        [
                            "year" => "2015",
                            "make" => "lexus",
                            "model" => "rx 350",
                            "color" => "White",
                            "license_plate" => null,
                            "vin" => "2T2BK1BA2FC258335",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "San Antonio Auto Auction",
                        "state" => "TX",
                        "city" => "San Antonio",
                        "address" => "13510 Toepperwein Rd",
                        "zip" => "78233",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12102985477",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "JKB Auto Sales",
                        "state" => "MO",
                        "city" => "Harrisonville",
                        "address" => "2406 N 291 Hwy",
                        "zip" => "64701",
                        "phones" => [
                            [
                                "number" => "18163802224",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "18166162727",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "J Knapp",
                        "full_name" => "JKB AUTO SALES",
                        "state" => "MO",
                        "city" => "Harrisonville",
                        "address" => "2406 N State Route 291",
                        "zip" => "64701",
                        "phones" => [
                            [
                                "number" => "18165106553",
                            ],
                        ],
                        "fax" => "18163802083",
                        "phone" => "18163802224",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 525,
                        "terms" => null,
                        "customer_payment_amount" => 525,
                        "customer_payment_method_id" => 15,
                        "customer_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_30(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("MO", "AR", "AL")
            ->createTimeZones("64161", "72204", "36352")
            ->assertParsing(
                30,
                [
                    "load_id" => "21230",
                    "pickup_date" => "02/21/2021",
                    "delivery_date" => "02/22/2021",
                    "dispatch_instructions" => "russ 3347918520 or mike 334-698-0010 TEN DAY DIRECT DEPOSIT This should"
                        . " be picked up within 2 days of 02/21/2021. This must be delivered on or up to 2 days prior to 02/22/2021.",
                    "vehicles" => [
                        [
                            "year" => "2018",
                            "make" => "toyota",
                            "model" => "camry",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "4T1B11HK4JU548445",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Manheim Kansas City",
                        "state" => "MO",
                        "city" => "Kansas City",
                        "address" => "3901 N Skiles Ave",
                        "zip" => "64161",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18164524084",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Steve Landers Toyota",
                        "state" => "AR",
                        "city" => "Little Rock",
                        "address" => "10825 Colonel Glenn Rd",
                        "zip" => "72204",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15015685800",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Mike Culver",
                        "full_name" => "Culver Carriers Brokerage LLC",
                        "state" => "AL",
                        "city" => "Newton",
                        "address" => "751 Hill Top Rd",
                        "zip" => "36352",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13346980010",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 350,
                        "terms" => "Culver Carriers Brokerage LLC agrees to pay GIG Logistics Inc $350.00 within"
                            . " 10 business days of receiving a signed Bill of Lading. Payment will be made with Company Check.",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 350,
                        "broker_payment_method_id" => 15,
                        "broker_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "broker_payment_days" => 10,
                        "broker_payment_begins" => "invoice-sent",
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_31(): void
    {
        $this->createMakes("CHRYSLER")
            ->createStates("TX", "NE", "NE")
            ->createTimeZones("79106", "68154", "68154")
            ->assertParsing(
                31,
                [
                    "load_id" => "CUSTOMER TO DEALER",
                    "pickup_date" => "02/22/2021",
                    "delivery_date" => "02/23/2021",
                    "dispatch_instructions" => "Our facility is located at I-680 and Dodge. To access our facility, take"
                        . " the Old Mill exit and head north on 108th Avenue. Keep in the right hand lane and follow the loop"
                        . " around the Sheraton Hotel. Park on the east side of the Sheraton Hotel. There is a YELLOW sign"
                        . " indicating where to park. DO NOT PULL ALL THE WAY INTO THE PARKING LOT! You will not be able to"
                        . " get out. Stop in the circle and unload there! This should be picked up within 2 days of 02/22/2021."
                        . " This should be delivered within 2 days of 02/23/2021.",
                    "vehicles" => [
                        [
                            "year" => "2014",
                            "make" => "chrysler",
                            "model" => "town & country",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "2C4RC1BG5ER156353",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "LYNZEE",
                        "state" => "TX",
                        "city" => "Amarillo",
                        "address" => "6117 JAMESON RD",
                        "zip" => "79106",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18062208339",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Schrier Automotive Inc",
                        "state" => "NE",
                        "city" => "Omaha",
                        "address" => "601 N 108th Circle",
                        "zip" => "68154",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14027331191",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Jill Yechout",
                        "full_name" => "Schrier Automotive",
                        "state" => "NE",
                        "city" => "Omaha",
                        "address" => "601 N 108th Circle",
                        "zip" => "68154",
                        "phones" => [],
                        "fax" => "14027331881",
                        "phone" => "14027331191",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 700,
                        "terms" => null,
                        "customer_payment_amount" => 700,
                        "customer_payment_method_id" => 15,
                        "customer_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_32(): void
    {
        $this->createMakes("KIA")
            ->createStates("TX", "SD", "GA")
            ->createTimeZones("79121", "57030", "30269")
            ->assertParsing(
                32,
                [
                    "load_id" => "1501981090-QB",
                    "pickup_date" => "02/22/2021",
                    "delivery_date" => "02/24/2021",
                    "dispatch_instructions" => "Door to door service, location permitting. Please give customer advanced"
                        . " (24 hours) notice prior to pick up and delivery. PLEASE MARK THE DATES OF PICK UP AND DELIVERY"
                        . " ON CENTRAL DISPATCH WHEN THEY OCCUR. 100 LBS in the vehicle. This should be picked up within 2"
                        . " days of 02/22/2021. This should be delivered within 2 days of 02/24/2021.",
                    "vehicles" => [
                        [
                            "year" => "2017",
                            "make" => "kia",
                            "model" => "sorento",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => null,
                        "state" => "TX",
                        "city" => "Amarillo",
                        "address" => "3401 Mccall Place",
                        "zip" => "79121",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18062365889",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => null,
                        "state" => "SD",
                        "city" => "Garretson",
                        "address" => "331 North Main St",
                        "zip" => "57030",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "80667630324",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Kailee Lanterman",
                        "full_name" => "AmeriFreight",
                        "state" => "GA",
                        "city" => "Peachtree City",
                        "address" => "417 Dividend Drive Suite D",
                        "zip" => "30269",
                        "phones" => [
                            [
                                "number" => "17705744436",
                            ],
                        ],
                        "fax" => "18666488719",
                        "phone" => "17704861010",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 800,
                        "terms" => null,
                        "customer_payment_amount" => 800,
                        "customer_payment_method_id" => 14,
                        "customer_payment_method" => [
                            "id" => 14,
                            "title" => "Cash",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_33(): void
    {
        $this->createMakes("RAM")
            ->createStates("MI", "PA", "PA")
            ->createTimeZones("48911", "17315", "17545")
            ->assertParsing(
                33,
                [
                    "load_id" => "6260820",
                    "pickup_date" => "02/20/2021",
                    "delivery_date" => "02/22/2021",
                    "dispatch_instructions" => "CALL/TEXT CONNIE 267-767-6745 PICK UP INSTRUCTIONS: Please call pick up"
                        . " location to confirm that the vehicle is ready for pick up. DELIVERY INSTRUCTIONS: Please get a"
                        . " signature if possible. If you have to STI, please take pictures. PAYMENT: Please send invoice"
                        . " and BOL to accounting@adcocktransport.com ACH AND QUICK PAY OPTIONS AVAILABLE. ACCOUNTING: Contact"
                        . " Joe for any accounting questions/issues. Joe - 717 879 0065 (jaldrich@adcocktransport.com This"
                        . " should be picked up within 2 days of 02/20/2021. This should be delivered within 2 days of 02/22/2021.",
                    "vehicles" => [
                        [
                            "year" => "2017",
                            "make" => "ram",
                            "model" => "1500",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "1C6RR7MT4HS786774",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Motorcars Of Lansing Inc",
                        "state" => "MI",
                        "city" => "Lansing",
                        "address" => "6505 S Pennsylvania Ave",
                        "zip" => "48911",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18888095072",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "THORNTON AUTOMOTIVE DOVER",
                        "state" => "PA",
                        "city" => "Dover",
                        "address" => "3885 CARLISLE RD",
                        "zip" => "17315",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17173220371",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Anastasia Moneymaker",
                        "full_name" => "Adcock Direct LLC",
                        "state" => "PA",
                        "city" => "Manheim",
                        "address" => "14 Anthony Drive",
                        "zip" => "17545",
                        "phones" => [
                            [
                                "number" => "18669668348",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "17176650313",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 550,
                        "terms" => "Adcock Direct LLC agrees to pay GIG Logistics Inc $550.00 within 30 business"
                            . " days of receiving a signed Bill of Lading. Payment will be made with Company Check.",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 550,
                        "broker_payment_method_id" => 15,
                        "broker_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "broker_payment_days" => 30,
                        "broker_payment_begins" => "invoice-sent",
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_34(): void
    {
        $this->createMakes("JEEP")
            ->createStates("MD", "WI", "WI")
            ->createTimeZones("21075", "54729", "54729")
            ->assertParsing(
                34,
                [
                    "load_id" => "Manheim Balt-wash- to chippewa-802965",
                    "pickup_date" => "02/20/2021",
                    "delivery_date" => "02/22/2021",
                    "dispatch_instructions" => "This should be picked up within 2 days of 02/20/2021. This should be delivered"
                        . " within 2 days of 02/22/2021.",
                    "vehicles" => [
                        [
                            "year" => "2015",
                            "make" => "jeep",
                            "model" => "grand cherokee",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "1C4RJFAG4FC802965",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "manheim baltimore washington",
                        "state" => "MD",
                        "city" => "Elkridge",
                        "address" => "7120 Dorsey run road",
                        "zip" => "21075",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14107968899",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Chippewa Valley Mazda",
                        "state" => "WI",
                        "city" => "Chippewa Falls",
                        "address" => "1821 South Prairie view Road",
                        "zip" => "54729",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17154567400",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Austin Smith",
                        "full_name" => "Chippewa Valley Motor Car Sales Inc..",
                        "state" => "WI",
                        "city" => "Chippewa Falls",
                        "address" => "2329 S Prairie View Rd",
                        "zip" => "54729",
                        "phones" => [
                            [
                                "number" => "17154567400",
                            ],
                        ],
                        "fax" => "17157387171",
                        "phone" => "17157387118",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 850,
                        "terms" => null,
                        "customer_payment_amount" => 850,
                        "customer_payment_method_id" => 15,
                        "customer_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_35(): void
    {
        $this->createMakes("AUDI")
            ->createStates("MN", "IL", "IL")
            ->createTimeZones("55330", "60638", "60638")
            ->assertParsing(
                35,
                [
                    "load_id" => "21L019",
                    "pickup_date" => "02/24/2021",
                    "delivery_date" => "02/25/2021",
                    "dispatch_instructions" => "Copart Could be inop Copart Could be inop This must be picked up exactly"
                        . " on 02/24/2021. This should be delivered within 2 days of 02/25/2021.",
                    "vehicles" => [
                        [
                            "year" => "2012",
                            "make" => "audi",
                            "model" => "a6",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "WAUHGAFC2CN090097",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "ELK RIVER SUBLOT",
                        "state" => "MN",
                        "city" => "Elk River",
                        "address" => "15932 JARVIS ST NE",
                        "zip" => "55330",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17637720700",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Baltic Auto Shipping - Chicago",
                        "state" => "IL",
                        "city" => "Bedford Park",
                        "address" => "5811 W 66th st",
                        "zip" => "60638",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17089247474",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Dispatch",
                        "full_name" => "Baltic Auto Shipping, Inc",
                        "state" => "IL",
                        "city" => "Bedford Park",
                        "address" => "5811 W 66Th St",
                        "zip" => "60638",
                        "phones" => [
                            [
                                "number" => "17089247481",
                            ],
                        ],
                        "fax" => "17738883880",
                        "phone" => "18722225842",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 300,
                        "terms" => null,
                        "customer_payment_amount" => 300,
                        "customer_payment_method_id" => 15,
                        "customer_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_36(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("MN", "MI", "MI")
            ->createTimeZones("55304", "48174", "48174")
            ->assertParsing(
                36,
                [
                    "load_id" => "141",
                    "pickup_date" => "02/24/2021",
                    "delivery_date" => "02/25/2021",
                    "dispatch_instructions" => "This should be picked up within 2 days of 02/24/2021. This should be delivered"
                        . " within 2 days of 02/25/2021.",
                    "vehicles" => [
                        [
                            "year" => "1999",
                            "make" => "chevrolet",
                            "model" => "corvette",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "copart",
                        "state" => "MN",
                        "city" => "Ham Lake",
                        "address" => "1526 Bunker Lake Blvd",
                        "zip" => "55304",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17637720700",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "AES",
                        "state" => "MI",
                        "city" => "Romulus",
                        "address" => "28501 Goddard Rd Suite 107",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17346581053",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Twymen Burrough",
                        "full_name" => "Auto & Equipment Specialists Auto Sales LLC",
                        "state" => "MI",
                        "city" => "Romulus",
                        "address" => "28501 Goddard Rd",
                        "zip" => "48174",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17346581053",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 450,
                        "terms" => null,
                        "customer_payment_amount" => 450,
                        "customer_payment_method_id" => 15,
                        "customer_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_37(): void
    {
        $this->createMakes("FORD")
            ->createStates("IN", "IL", "FL")
            ->createTimeZones("47201", "62918", "33403")
            ->assertParsing(
                37,
                [
                    "load_id" => "23169-XI",
                    "pickup_date" => "02/23/2021",
                    "delivery_date" => "02/24/2021",
                    "dispatch_instructions" => "This should be picked up within 2 days of 02/23/2021. This should be delivered"
                        . " within 2 days of 02/24/2021.",
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "ford",
                            "model" => "fusion",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Ford of Columbus",
                        "state" => "IN",
                        "city" => "Columbus",
                        "address" => "3560 N National Road",
                        "zip" => "47201",
                        "phones" => [
                            [
                                "number" => "18124479531",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "18123721561",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => null,
                        "state" => "IL",
                        "city" => "Carterville",
                        "address" => "733 S Division St",
                        "zip" => "62918",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16187139043",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Dispatch Department",
                        "full_name" => "2 J's Auto Transport Inc.",
                        "state" => "FL",
                        "city" => "Lake Park",
                        "address" => "909 Lake Shore Drive, Apt 203",
                        "zip" => "33403",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18773257288",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 325,
                        "terms" => "2 J's Auto Transport Inc. agrees to pay GIG Logistics Inc $325.00 within"
                            . " 2 business days (Quick Pay) of delivery. Payment will be made with Certified Funds.",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 325,
                        "broker_payment_method_id" => 16,
                        "broker_payment_method" => [
                            "id" => 16,
                            "title" => "Certified Funds",
                        ],
                        "broker_payment_days" => 2,
                        "broker_payment_begins" => "delivery",
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_38(): void
    {
        $this->createMakes("INFINITI")
            ->createStates("NC", "IA", "AZ")
            ->createTimeZones("28027", "50677", "85281")
            ->assertParsing(
                38,
                [
                    "load_id" => "2000830323",
                    "pickup_date" => "02/24/2021",
                    "delivery_date" => "02/26/2021",
                    "dispatch_instructions" => "Register with us here: https://tinyurl.com/y9s764hs This must be picked"
                        . " up on or up to 2 days after 02/24/2021. This must be delivered on or up to 2 days prior to 02/26/2021.",
                    "vehicles" => [
                        [
                            "year" => "2017",
                            "make" => "infiniti",
                            "model" => "qx50",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "JN1BJ0RR0HM401546",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "1 CONCORD",
                        "state" => "NC",
                        "city" => "Concord",
                        "address" => "2321 Concord Pkwy S",
                        "zip" => "28027",
                        "phones" => [
                            [
                                "number" => "14804477959",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "14806451929",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "RESIDENTIAL DELIVERY - CALL 24 HOURS PRIOR",
                        "state" => "IA",
                        "city" => "Waverly",
                        "address" => "523 5th NW St",
                        "zip" => "50677",
                        "phones" => [
                            [
                                "number" => "14804477959",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "14435366159",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Carvana Dispatch",
                        "full_name" => "Carvana LLC",
                        "state" => "AZ",
                        "city" => "Tempe",
                        "address" => "1930 W Rio Salado Pkwy",
                        "zip" => "85281",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14804477959",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 650,
                        "terms" => "Carvana LLC agrees to pay GIG Logistics Inc $650.00 within 5 business days"
                            . " of receiving a signed Bill of Lading. Payment will be made with Certified Funds.",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 650,
                        "broker_payment_method_id" => 16,
                        "broker_payment_method" => [
                            "id" => 16,
                            "title" => "Certified Funds",
                        ],
                        "broker_payment_days" => 5,
                        "broker_payment_begins" => "invoice-sent",
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_39(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("MO", "MN", "IL")
            ->createTimeZones("63385", "55406", "60148")
            ->assertParsing(
                39,
                [
                    "load_id" => "080-050064",
                    "pickup_date" => "02/24/2021",
                    "delivery_date" => "02/25/2021",
                    "dispatch_instructions" => "Dispatch instructions: THIS IS NOT C.O.D. DO NOT ASK THE CUSTOMER FOR PAYMENT"
                        . " VERIFY the vehicle VIN matches paperwork. Email signed BOL to JTAYLOR@AUTODRIVEAWAY.COM within"
                        . " 24 hours of delivery to ensure prompt payment. Payments with Comcheck will be paid within 5 days"
                        . " after receiving BOL. Payments made with ACH or Company Check will be paid within 30 days after"
                        . " receiving the BOL. Proof of Delivery MUST include: SIGNATURES pickup/delivery and DATES at pickup/delivery"
                        . " CALL 60-90 MINUTES BEFORE ARRIVING Please contact Joni immediately with any problems or delays"
                        . " at 913-350-0168 This must be picked up exactly on 02/24/2021. This must be delivered exactly on"
                        . " 02/25/2021.",
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "toyota",
                            "model" => "rav4",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "4T3L6RFVXMU017974",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2021",
                            "make" => "toyota",
                            "model" => "rav4",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "4T3L6RFV7MU021321",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "HILTI: BUSINESS-M-F 6AM-1PM",
                        "state" => "MO",
                        "city" => "Wentzville",
                        "address" => "251 ENTERPRISE DRIVE",
                        "zip" => "63385",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16363324562",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "ADA MINNEAPOLIS",
                        "state" => "MN",
                        "city" => "Minneapolis",
                        "address" => "2500 EAST 25TH STREET",
                        "zip" => "55406",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16127289200",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Amy Nicosia",
                        "full_name" => "Auto Driveaway",
                        "state" => "IL",
                        "city" => "Lombard",
                        "address" => "1 E. 22nd. Street, Suite 107",
                        "zip" => "60148",
                        "phones" => [
                            [
                                "number" => "13312418091",
                            ],
                        ],
                        "fax" => "13123419100",
                        "phone" => "13123411900",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 800,
                        "terms" => "Auto Driveaway agrees to pay GIG Logistics Inc $800.00 within 5 business"
                            . " days of receiving a signed Bill of Lading. Payment will be made with Comchek.",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 800,
                        "broker_payment_method_id" => 15,
                        "broker_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "broker_payment_days" => 5,
                        "broker_payment_begins" => "invoice-sent",
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_40(): void
    {
        $this->createMakes("JEEP")
            ->createStates("NC", "MO", "VA")
            ->createTimeZones("27407", "63129", "23294")
            ->assertParsing(
                40,
                [
                    "load_id" => "2018jeep",
                    "pickup_date" => "02/25/2021",
                    "delivery_date" => "02/26/2021",
                    "dispatch_instructions" => "please call or text Dmitriy at 804-651-4886 This must be picked up exactly"
                        . " on 02/25/2021. This must be delivered exactly on 02/26/2021.",
                    "vehicles" => [
                        [
                            "year" => "2018",
                            "make" => "jeep",
                            "model" => "grand crerokee",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "crown jeep",
                        "state" => "NC",
                        "city" => "Greensboro",
                        "address" => "3902 w wendover avenue",
                        "zip" => "27407",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13366630442",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Anthony Broyles",
                        "state" => "MO",
                        "city" => "Oakville",
                        "address" => "2822 gladwood drive",
                        "zip" => "63129",
                        "phones" => [
                            [
                                "number" => "13148006902",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "13146292130",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Ian Weir",
                        "full_name" => "Richmond BMW- Crown Mini",
                        "state" => "VA",
                        "city" => "Richmond",
                        "address" => "8710 West Broad Street",
                        "zip" => "23294",
                        "phones" => [
                            [
                                "number" => "18043471408",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "18043460812",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 500,
                        "terms" => "Richmond BMW-Crown Mini agrees to pay GIG Logistics Inc $500.00 within 5"
                            . " business days of delivery. Payment will be made with Company Check.",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 500,
                        "broker_payment_method_id" => 15,
                        "broker_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "broker_payment_days" => 5,
                        "broker_payment_begins" => "delivery",
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_41(): void
    {
        $this->createMakes("RAM")
            ->createStates("LA", "OH", "LA")
            ->createTimeZones("70607", "45240", "70607")
            ->assertParsing(
                41,
                [
                    "load_id" => "TEXT AARON @ 318-792-1220 FOR ASSIGNEMENT",
                    "pickup_date" => "02/24/2021",
                    "delivery_date" => "02/25/2021",
                    "dispatch_instructions" => "CALL DAVID STAIGER TO SCHEDULE DELIVERY5132274880 5132274880 CALL DAVID"
                        . " STAIGER TO SCHEDULE DELIVERY This must be picked up on or up to 2 days prior to 02/24/2021. This"
                        . " must be delivered on or up to 2 days prior to 02/25/2021.",
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "ram",
                            "model" => "1500",
                            "color" => "BLACK",
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "MARL DODGE CHRYSLER JEEP",
                        "state" => "LA",
                        "city" => "Lake Charles",
                        "address" => "3777 GERSTNER MEMORIAL",
                        "zip" => "70607",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13374804406",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => null,
                        "state" => "OH",
                        "city" => "Cincinnati",
                        "address" => "1380 KEMPER MEADOW DR",
                        "zip" => "45240",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15132274880",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Marsh Buice",
                        "full_name" => "Mark Dodge, Chrysler, Jeep L.L.C.",
                        "state" => "LA",
                        "city" => "Lake Charles",
                        "address" => "3777 Gerstner Memorial Dr",
                        "zip" => "70607",
                        "phones" => [
                            [
                                "number" => "13374804406",
                            ],
                        ],
                        "fax" => "13374804472",
                        "phone" => "13374742640",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 925,
                        "terms" => null,
                        "customer_payment_amount" => 925,
                        "customer_payment_method_id" => 15,
                        "customer_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_42(): void
    {
        $this->createMakes("GMC")
            ->createStates("MI", "WI", "FL")
            ->createTimeZones("48307", "53527", "32765")
            ->assertParsing(
                42,
                [
                    "load_id" => "4246-B",
                    "pickup_date" => "02/26/2021",
                    "delivery_date" => "03/01/2021",
                    "dispatch_instructions" => "Dealer to Dealer ** Please read and pay close attention to our contract"
                        . " below. These are brand new vehicle(s) with less than 100 miles, please note mileage on the BOL."
                        . " It is very important that the load be treated and transported with care. Once the vehicle(s) have"
                        . " been loaded on the trailer please take a picture of the load and the signed BOL at pickup and"
                        . " send it Catherine in Dispatch: (386)339-8959 or email to: eagleautorelocation@gmail.com. Dispatch"
                        . " can be reached 24/7 and will help you with anything you may need. If you have any questions or"
                        . " concerns, please feel free to give us a call. We look forward to working with you. Drive safe!"
                        . " **notify dispatch of any dead batteries & mark it on the BOL** This should be picked up within"
                        . " 2 days of 02/26/2021. This should be delivered within 2 days of 03/01/2021.",
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "gmc",
                            "model" => "yukon",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                        [
                            "year" => "2020",
                            "make" => "gmc",
                            "model" => "terrain",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Shelton Buick GMC -",
                        "state" => "MI",
                        "city" => "Rochester",
                        "address" => "855 S. Rochester",
                        "zip" => "48307",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12482662156",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Zimbrick Buick GMC West -",
                        "state" => "WI",
                        "city" => "Cottage Grove",
                        "address" => "1601 W. Beltline Highway",
                        "zip" => "53527",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16082300257",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Dispatch Ex 102",
                        "full_name" => "Eagle Auto Relocation, Inc.",
                        "state" => "FL",
                        "city" => "Oviedo",
                        "address" => "307 Aulin Ave. Suite #400",
                        "zip" => "32765",
                        "phones" => [
                            [
                                "number" => "14075425922",
                            ],
                        ],
                        "fax" => "14077925487",
                        "phone" => "13863398959",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 1100,
                        "terms" => "Eagle Auto Relocation, Inc. agrees to pay GIG Logistics Inc $1,100.00 within"
                            . " 15 business days of receiving a signed Bill of Lading. Payment will be made with Company Check.",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 1100,
                        "broker_payment_method_id" => 15,
                        "broker_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "broker_payment_days" => 15,
                        "broker_payment_begins" => "invoice-sent",
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_43(): void
    {
        $this->createMakes("RAM")
            ->createStates("CO", "TX", "CO")
            ->createTimeZones("NONE", "NONE", "80651")
            ->assertParsing(
                43,
                [
                    "load_id" => "1327",
                    "pickup_date" => "04/19/2021",
                    "delivery_date" => "04/20/2021",
                    "dispatch_instructions" => "Ready now, mini van No & on bol Email bol and invoice to bigdoghaulers@hotmail.com"
                        . " This should be picked up within 2 days of 04/19/2021. This should be delivered within 2 days of"
                        . " 04/20/2021.",
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "ram",
                            "model" => "promaster city cargo",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Johnson’s auto plaza",
                        "state" => "CO",
                        "city" => "Brighton",
                        "address" => "12410 e 136th ave",
                        "zip" => null,
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19703314173",
                        "state_id" => $this->states[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "True north 1025",
                        "state" => "TX",
                        "city" => "Keller",
                        "address" => "1547 briar meadow dr",
                        "zip" => null,
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18177055038",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Charleton Jeffrey",
                        "full_name" => "Rocky Mountain Auto Recyclers",
                        "state" => "CO",
                        "city" => "Platteville",
                        "address" => "16541 CR 33",
                        "zip" => "80651",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19703314173",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 600,
                        "terms" => "Rocky Mountain Auto Recyclers agrees to pay GIG Logistics Inc $600.00 within"
                            . " 5 business days of receiving a signed Bill of Lading. Payment will be made with Company Check.",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 600,
                        "broker_payment_method_id" => 15,
                        "broker_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "broker_payment_days" => 5,
                        "broker_payment_begins" => "invoice-sent",
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_44(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("AR", "TX", "GA")
            ->createTimeZones("72744", "77033", "31405")
            ->assertParsing(
                44,
                [
                    "load_id" => "27492241",
                    "pickup_date" => "04/19/2021",
                    "delivery_date" => "04/20/2021",
                    "dispatch_instructions" => "2014 CHEVROLET CRUZE Vin-1G1PA5SH9E7197320 Lot-30049197 Buyer-455457 \"NO"
                        . " PHOTOS & SIGNED BOL & CASH APP/ZELLE - NO PAYMENT\" !TAKING PICTURES ON PICK UP AND DELIVERY LOCATION"
                        . " IS OBLIGATORY! (4 pictures outside and 1 inside - key,pictures of roof,keys and title) !PLEASE"
                        . " SEND PICTURES & BOL & CASH APP/ZELLE ONLY TO THIS EMAIL - dispatch@seawayexport.com! \"\"\"IF"
                        . " TITLE PENDING OR MAILED.IT'S NOT OBLIGATORY TO TAKE IT\"\"\" This must be picked up exactly on"
                        . " 04/19/2021. This must be delivered exactly on 04/20/2021.",
                    "vehicles" => [
                        [
                            "year" => "2014",
                            "make" => "chevrolet",
                            "model" => "cruze",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "1G1PA5SH9E7197320",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Fayetteville (AR) IAAI",
                        "state" => "AR",
                        "city" => "Lincoln",
                        "address" => "2801 E Pridemore Dr.",
                        "zip" => "72744",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14798246200",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "SEAWAY EXPORT",
                        "state" => "TX",
                        "city" => "Houston",
                        "address" => "6801 SILSBEE ST",
                        "zip" => "77033",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "11407782452",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Alex Slobodyanik",
                        "full_name" => "Dispatch Sheet Seaway Export UA",
                        "state" => "GA",
                        "city" => "Savannah",
                        "address" => "2361 TREMONT RD",
                        "zip" => "31405",
                        "phones" => [],
                        "fax" => "19122324625",
                        "phone" => "19129631281",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 400,
                        "terms" => "Seaway Export UA agrees to pay GIG Logistics Inc $400.00 within 2 business"
                            . " days (Quick Pay) of receiving a signed Bill of Lading. Payment will be made with Certified Funds.",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 400,
                        "broker_payment_method_id" => 16,
                        "broker_payment_method" => [
                            "id" => 16,
                            "title" => "Certified Funds",
                        ],
                        "broker_payment_days" => 2,
                        "broker_payment_begins" => "invoice-sent",
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_45(): void
    {
        $this->createMakes("GMC")
            ->createStates("SD", "WI", "SD")
            ->createTimeZones("57032", "54913", "57032")
            ->assertParsing(
                45,
                [
                    "load_id" => "78988",
                    "pickup_date" => "04/20/2021",
                    "delivery_date" => "04/21/2021",
                    "dispatch_instructions" => "This should be picked up within 2 days of 04/20/2021. This should be delivered"
                        . " within 2 days of 04/21/2021.",
                    "vehicles" => [
                        [
                            "year" => "2014",
                            "make" => "gmc",
                            "model" => "terrain",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "2GKFLXE35E6343600",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Interstate Auto Center",
                        "state" => "SD",
                        "city" => "Harrisburg",
                        "address" => "27276 Kenworth Place",
                        "zip" => "57032",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16053682181",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Jim Gradl",
                        "state" => "WI",
                        "city" => "Appleton",
                        "address" => "N4460 CTY RD PP",
                        "zip" => "54913",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19204750437",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Dispatch",
                        "full_name" => "Interstate Auto Center Inc",
                        "state" => "SD",
                        "city" => "Harrisburg",
                        "address" => "27276 Kenworth Pl",
                        "zip" => "57032",
                        "phones" => [],
                        "fax" => "16053682182",
                        "phone" => "16053682181",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 350,
                        "terms" => null,
                        "customer_payment_amount" => 350,
                        "customer_payment_method_id" => 15,
                        "customer_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_46(): void
    {
        $this->createMakes("FORD")
            ->createStates("IN", "OK", "OK")
            ->createTimeZones("46403", "73162", "73153")
            ->assertParsing(
                46,
                [
                    "load_id" => "27492836",
                    "pickup_date" => "04/20/2021",
                    "delivery_date" => "04/21/2021",
                    "dispatch_instructions" => "Last 8 of VIN is; GL270944 This should be picked up within 2 days of 04/20/2021."
                        . " This should be delivered within 2 days of 04/21/2021.",
                    "vehicles" => [
                        [
                            "year" => "2016",
                            "make" => "ford",
                            "model" => "focus",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Chicagoland Auto Auction",
                        "state" => "IN",
                        "city" => "Gary",
                        "address" => "7900 Melton Rd.",
                        "zip" => "46403",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12198652361",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Battison Honda",
                        "state" => "OK",
                        "city" => "Oklahoma City",
                        "address" => "8700 NW Expressway",
                        "zip" => "73162",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14052097700",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Jerry Scott",
                        "full_name" => "Auto Dude Wholesale LLC",
                        "state" => "OK",
                        "city" => "Moore",
                        "address" => "Po Box 6222",
                        "zip" => "73153",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14052097700",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 500,
                        "terms" => "Auto Dude Wholesale LLC agrees to pay GIG Logistics Inc $500.00 within 2"
                            . " business days (Quick Pay) of receiving a signed Bill of Lading. Payment will be made with Company"
                            . " Check.",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 500,
                        "broker_payment_method_id" => 15,
                        "broker_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "broker_payment_days" => 2,
                        "broker_payment_begins" => "invoice-sent",
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_47(): void
    {
        $this->createMakes("NISSAN")
            ->createStates("CO", "AR", "MO")
            ->createTimeZones("80401", "72712", "65757")
            ->assertParsing(
                47,
                [
                    "load_id" => "716832",
                    "pickup_date" => "04/19/2021",
                    "delivery_date" => "04/20/2021",
                    "dispatch_instructions" => "RELEASE ON DESK PLEASE EMAIL INVOICE & BOL TO JOHN@SMITHAUTOTRANSPORT.COM"
                        . " This should be picked up within 2 days of 04/19/2021. This should be delivered within 2 days of"
                        . " 04/20/2021.",
                    "vehicles" => [
                        [
                            "year" => "2018",
                            "make" => "nissan",
                            "model" => "leaf",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "1N4AZ1CP3JC314630",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "EMPIRE LAKEWOOD NISSAN",
                        "state" => "CO",
                        "city" => "Golden",
                        "address" => "14707 W COLFAX AVE",
                        "zip" => "80401",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13032328881",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "CADILLAC OFBENTONVILLE",
                        "state" => "AR",
                        "city" => "Bentonville",
                        "address" => "2300 SE MOBERLY LN",
                        "zip" => "72712",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14794262155",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "John Lea",
                        "full_name" => "Smith Auto Transport / BS Auto Logistics LLC",
                        "state" => "MO",
                        "city" => "Strafford",
                        "address" => "851 W EVERGREEN STREET",
                        "zip" => "65757",
                        "phones" => [],
                        "fax" => "18888486781",
                        "phone" => "14178485310",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 700,
                        "terms" => "Smith Auto Transport / BS Auto Logistics LLC agrees to pay GIG Logistics"
                            . " Inc $700.00 within 15 business days of receiving a signed Bill of Lading. Payment will be made"
                            . " with Company Check.",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 700,
                        "broker_payment_method_id" => 15,
                        "broker_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "broker_payment_days" => 15,
                        "broker_payment_begins" => "invoice-sent",
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_48(): void
    {
        $this->createMakes("FORD")
            ->createStates("TX", "CO", "CO")
            ->createTimeZones("77073", "80220", "80015")
            ->assertParsing(
                48,
                [
                    "load_id" => "RUN AND DRIVE",
                    "pickup_date" => "04/19/2021",
                    "delivery_date" => "04/21/2021",
                    "dispatch_instructions" => "BROKER FEE MUST BE SENT VIA CASH APP TO 6786653156 IMMEDIATELY AFTER DELIVERY,"
                        . " FAILURE TO DO SO WILL RESULT ON COMPANIES RATING This should be picked up within 2 days of 04/19/2021."
                        . " This should be delivered within 2 days of 04/21/2021.",
                    "vehicles" => [
                        [
                            "year" => "2016",
                            "make" => "ford",
                            "model" => "transit connect",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "NM0LS7E73G1241199",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => null,
                        "state" => "TX",
                        "city" => "Houston",
                        "address" => "1655 RANKIN ROAD",
                        "zip" => "77073",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => null,
                        "state" => "CO",
                        "city" => "Denver",
                        "address" => "7440 E Colfax Ave",
                        "zip" => "80220",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17204227068",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Telman Fatulov",
                        "full_name" => "TA Express LLC",
                        "state" => "CO",
                        "city" => "Centennial",
                        "address" => "5111 S Gibraltar Ct",
                        "zip" => "80015",
                        "phones" => [
                            [
                                "number" => "16786653156",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "17209984444",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 550,
                        "terms" => "GIG Logistics Inc agrees to pay TA Express LLC $100.00 immediately upon"
                            . " delivery. Payment will be made with Certified Funds.",
                        "customer_payment_amount" => 650,
                        "customer_payment_method_id" => 14,
                        "customer_payment_method" => [
                            "id" => 14,
                            "title" => "Cash",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => 100,
                        "broker_fee_method_id" => 16,
                        "broker_fee_method" => [
                            "id" => 16,
                            "title" => "Certified Funds",
                        ],
                        "broker_fee_days" => 0,
                        "broker_fee_begins" => "delivery",
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_49(): void
    {
        $this->createMakes("BUICK")
            ->createStates("AR", "MI", "MI")
            ->createTimeZones("72753", "48238", "48212")
            ->assertParsing(
                49,
                [
                    "load_id" => "29374602",
                    "pickup_date" => "09/22/2021",
                    "delivery_date" => "09/24/2021",
                    "dispatch_instructions" => "This must be picked up exactly on 09/22/2021. This should be delivered within"
                        . " 2 days of 09/24/2021.",
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "buick",
                            "model" => "encore",
                            "color" => "BLUE",
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "COPART",
                        "state" => "AR",
                        "city" => "Prairie Grove",
                        "address" => "15976 BILL CAMPBELL ROAD PRAIRIRE",
                        "zip" => "72753",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "SPECIAL WAY CAR CENTER",
                        "state" => "MI",
                        "city" => "Detroit",
                        "address" => "621 E MCNICHOLS RD",
                        "zip" => "48238",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Ramy Yaldo",
                        "full_name" => "Special Way Auto Center Inc",
                        "state" => "MI",
                        "city" => "Detroit",
                        "address" => "2315 E Mcnichols Rd",
                        "zip" => "48212",
                        "phones" => [
                            [
                                "number" => "15869433604",
                            ],
                        ],
                        "fax" => "13138261203",
                        "phone" => "13138261202",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 625,
                        "terms" => null,
                        "customer_payment_amount" => 625,
                        "customer_payment_method_id" => 15,
                        "customer_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_50(): void
    {
        $this->createMakes("HONDA")
            ->createStates("AR", "WI", "WI")
            ->createTimeZones("72714", "53182", "53182")
            ->assertParsing(
                50,
                [
                    "load_id" => "G4002388",
                    "pickup_date" => "04/19/2021",
                    "delivery_date" => "04/21/2021",
                    "dispatch_instructions" => "weekend p/u & del ok This must be picked up on or up to 2 days prior to"
                        . " 04/19/2021. This must be delivered on or up to 2 days prior to 04/21/2021.",
                    "vehicles" => [
                        [
                            "year" => "2016",
                            "make" => "honda",
                            "model" => "pioneer",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Khan",
                        "state" => "AR",
                        "city" => "Bella Vista",
                        "address" => "8 Cambeck Ln",
                        "zip" => "72714",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14799360037",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "ENE Motors",
                        "state" => "WI",
                        "city" => "Union Grove",
                        "address" => "4330 Conifer Ct",
                        "zip" => "53182",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12622881330",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Diti Xhabiri",
                        "full_name" => "ENE Motors",
                        "state" => "WI",
                        "city" => "Union Grove",
                        "address" => "4330 Conifer Ct",
                        "zip" => "53182",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12622881330",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 500,
                        "terms" => null,
                        "customer_payment_amount" => 500,
                        "customer_payment_method_id" => 15,
                        "customer_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_51(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("TX", "CO", "TX")
            ->createTimeZones("77065", "80905", "77339")
            ->assertParsing(
                51,
                [
                    "load_id" => "27498362",
                    "pickup_date" => "04/19/2021",
                    "delivery_date" => "04/21/2021",
                    "dispatch_instructions" => "This should be picked up within 2 days of 04/19/2021. This should be delivered"
                        . " within 2 days of 04/21/2021.",
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "chevrolet",
                            "model" => "silverado 1500",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Pax Power. LLC",
                        "state" => "TX",
                        "city" => "Houston",
                        "address" => "11115 Neeshaw Dr",
                        "zip" => "77065",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18323696852",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Daniels Long Chevrolet",
                        "state" => "CO",
                        "city" => "Colorado Springs",
                        "address" => "670 Automotive Dr",
                        "zip" => "80905",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17192162736",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Ben Norrell",
                        "full_name" => "Pax Power LLC",
                        "state" => "TX",
                        "city" => "Kingwood",
                        "address" => "2707 N Cotswold Manor Dr",
                        "zip" => "77339",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12147978624",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 850,
                        "terms" => null,
                        "customer_payment_amount" => 850,
                        "customer_payment_method_id" => 15,
                        "customer_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "customer_payment_location" => "pickup",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_52(): void
    {
        $this->createMakes("HONDA")
            ->createStates("TN", "LA", "LA")
            ->createTimeZones("37218", "70062", "70126")
            ->assertParsing(
                52,
                [
                    "load_id" => "27497769",
                    "pickup_date" => "04/20/2021",
                    "delivery_date" => "04/20/2021",
                    "dispatch_instructions" => "This must be picked up exactly on 04/20/2021. This should be delivered within"
                        . " 2 days of 04/20/2021.",
                    "vehicles" => [
                        [
                            "year" => "2018",
                            "make" => "honda",
                            "model" => "accord",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Iaa Nashville",
                        "state" => "TN",
                        "city" => "Nashville",
                        "address" => "3896 Stewarts Ln",
                        "zip" => "37218",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16157420006",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => null,
                        "state" => "LA",
                        "city" => "Kenner",
                        "address" => "*CONTACT DISPATCHER*",
                        "zip" => "70062",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15049203842",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Chip",
                        "full_name" => "NSEW Transport",
                        "state" => "LA",
                        "city" => "New Orleans",
                        "address" => "4757 Tulip Street",
                        "zip" => "70126",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15046109725",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 475,
                        "terms" => "GIG Logistics Inc agrees to pay NSEW Transport $25.00 immediately upon delivery."
                            . " Payment will be made with Certified Funds.",
                        "customer_payment_amount" => 500,
                        "customer_payment_method_id" => 14,
                        "customer_payment_method" => [
                            "id" => 14,
                            "title" => "Cash",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => 25,
                        "broker_fee_method_id" => 16,
                        "broker_fee_method" => [
                            "id" => 16,
                            "title" => "Certified Funds",
                        ],
                        "broker_fee_days" => 0,
                        "broker_fee_begins" => "delivery",
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_53(): void
    {
        $this->createMakes("VW")
            ->createStates("IL", "MI", "MI")
            ->createTimeZones("62040", "49348", "49464")
            ->assertParsing(
                53,
                [
                    "load_id" => "14 VW",
                    "pickup_date" => "01/13/2022",
                    "delivery_date" => "01/14/2022",
                    "dispatch_instructions" => "This should be picked up within 2 days of 01/13/2022. This should be delivered"
                        . " within 2 days of 01/14/2022.",
                    "vehicles" => [
                        [
                            "year" => "2014",
                            "make" => "vw",
                            "model" => "passat run drive call 616 366 8608",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "IAAI ST Louis",
                        "state" => "IL",
                        "city" => "Granite City",
                        "address" => "4460 state highway 162",
                        "zip" => "62040",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "dent tec llc",
                        "state" => "MI",
                        "city" => "Wayland",
                        "address" => "90 133rd ave",
                        "zip" => "49348",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Scott Hoekstra",
                        "full_name" => "SRH Enterprises",
                        "state" => "MI",
                        "city" => "Zeeland",
                        "address" => "6564 chicago drive",
                        "zip" => "49464",
                        "phones" => [],
                        "fax" => "16168963101",
                        "phone" => "16168961408",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 450,
                        "terms" => null,
                        "customer_payment_amount" => 450,
                        "customer_payment_method_id" => 14,
                        "customer_payment_method" => [
                            "id" => 14,
                            "title" => "Cash",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_54(): void
    {
        $this->createMakes("RAM")
            ->createStates("NY", "IN", "IN")
            ->createTimeZones("11373", "46227", "46227")
            ->assertParsing(
                54,
                [
                    "load_id" => "36642189",
                    "pickup_date" => "05/30/2023",
                    "delivery_date" => "06/01/2023",
                    "dispatch_instructions" => "Drop Off Information: Indy Auto Man Service Hours: Monday-Friday 9:00am-6:00pm"
                        . " No Saturdays unless scheduled This is a bright colored building next to the RPM Performance shop(DO"
                        . " NOT PARK THERE). You can pull in the back of our parking lot or on the street. If we do not receive"
                        . " BOL you will not receive your check! We DO NOT DO Zelle,Cash app, Venmo, only check on delivery."
                        . " If dropping off after hours or on Sundays, please make sure the vehicle is locked and windows"
                        . " are up. Drop keys in the key slot(on the front door) at the front of the building and we will"
                        . " mail the check out first thing the next business morning. Please leave BOL in the drop box or"
                        . " in the car. DO NOT drop the vehicle off at 4031 S East St dealership! If you have any questions,"
                        . " please call 317-814-7520 and ask for someone in the Acquisitions Department. This should be picked"
                        . " up within 2 days of 05/30/2023. This should be delivered within 2 days of 06/01/2023.",
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "ram",
                            "model" => "promaster 2500 cargo van",
                            "color" => null,
                            "license_plate" => null,
                            "vin" => "3C6TRVDG6LE100968",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "MR CARS AUTO SALE INC",
                        "state" => "NY",
                        "city" => "Elmhurst",
                        "address" => "7433 Queens Blvd",
                        "zip" => "11373",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Indy Auto Man-Service Shop",
                        "state" => "IN",
                        "city" => "Indianapolis",
                        "address" => "3130 Madison Ave",
                        "zip" => "46227",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13178147520",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "contact_name" => "Lexi",
                        "full_name" => "Indy Auto Man LLC",
                        "state" => "IN",
                        "city" => "Indianapolis",
                        "address" => "3130 Madison Ave",
                        "zip" => "46227",
                        "phones" => [],
                        "fax" => "13176833420",
                        "phone" => "13176595269",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 700,
                        "terms" => null,
                        "customer_payment_amount" => 700,
                        "customer_payment_method_id" => 15,
                        "customer_payment_method" => [
                            "id" => 15,
                            "title" => "Check",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => null,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => null,
                        "broker_payment_begins" => null,
                        "broker_fee_amount" => null,
                        "broker_fee_method_id" => null,
                        "broker_fee_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_fee_days" => null,
                        "broker_fee_begins" => null,
                    ],
                ]
            );
    }
}
