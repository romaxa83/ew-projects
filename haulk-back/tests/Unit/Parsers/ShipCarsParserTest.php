<?php

namespace Tests\Unit\Parsers;

use Throwable;

class ShipCarsParserTest extends BaseParserTest
{
    /**
     * @throws Throwable
     */
    public function test_1(): void
    {
        $this->createMakes("DODGE")
            ->createStates("AL", "IL", "IL")
            ->createTimeZones("35901", "60914", "60173")
            ->assertParsing(
                1,
                [
                    "load_id" => "1739523",
                    "pickup_date" => "03/10/2023",
                    "delivery_date" => "03/11/2023",
                    "dispatch_instructions" => "Pickup and delivery hours are 9 am to 6 pm Monday - Friday and Saturday"
                        . " 9 am to 4 pm - closed Sunday\n1. Check Make/Model & VIN # of vehicle(s) – Must match your dispatch"
                        . " sheet! If different, DO NOT LOAD & call Montway first for approval!!!\n2. If you pick up any vehicle"
                        . " different than what Montway dispatched, we reserve the rights not to pay!\n3. If loading from"
                        . " Auction, Carrier responsible for calling ahead to confirm vehicle location & availability.\n4."
                        . " If loading from Auction any preexisting damages must be documented on the gate pass for each corresponding"
                        . " vehicle/VIN. You are required to retain\na copy of the gate pass with the verified preexisting"
                        . " damages. If this is not done, the carrier assumes all responsibility for any claims resulting"
                        . " from this\nrule not being followed.\n5. Driver MUST do a proper & detailed inspection at pickup"
                        . " and delivery! Must leave a copy of BOL at both locations!\n6. Absolutely NO drops/deliveries after"
                        . " business hours otherwise we will apply a penalty of $150\n7. Any issues call our logistics department"
                        . " at 224-300-5434",
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "Dodge",
                            "model" => "Challenger",
                            "vin" => "2C3CDZBT6MH559961",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Sales Manager. Team One Chrysler Dodge Jeep Ram Of Gadsden",
                        "state" => "AL",
                        "city" => "Gadsden",
                        "address" => "1149 1st Ave",
                        "zip" => "35901",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12565634309",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Joe. Taylor Chrysler Dodge Jeep Ram",
                        "state" => "IL",
                        "city" => "Bourbonnais",
                        "address" => "1497 Il-50",
                        "zip" => "60914",
                        "phones" => [
                            [
                                "number" => "18152141757",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "18159357900",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "logistics@montway.com",
                        "phone" => "12242203712",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 400,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $400"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 400,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_2(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("GA", "FL", "IL")
            ->createTimeZones("30228", "33324", "60173")
            ->assertParsing(
                2,
                [
                    "load_id" => "1723963",
                    "pickup_date" => "02/17/2023",
                    "delivery_date" => "",
                    "dispatch_instructions" => "DELIVERY CONTACT IS ONLY AVAILABLE AFTER 6PM ON WEEKDAYS\ndelivery by 2/23",
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "Toyota",
                            "model" => "Camry",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Adebayo Afon",
                        "state" => "GA",
                        "city" => "Hampton",
                        "address" => "1112 Venetian Lane",
                        "zip" => "30228",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14043946822",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Ifeoluwa Afon",
                        "state" => "FL",
                        "city" => "Plantation",
                        "address" => "651 Northwest 82nd Avenue",
                        "zip" => "33324",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17244670661",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 350,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $350"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 350,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_3(): void
    {
        $this->createMakes("BUICK")
            ->createStates("IL", "KY", "IL")
            ->createTimeZones("60156", "42211", "60173")
            ->assertParsing(
                3,
                [
                    "load_id" => "1726524",
                    "pickup_date" => "02/20/2023",
                    "delivery_date" => "02/21/2023",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2002",
                            "make" => "Buick",
                            "model" => "Rendezvous",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Amy Rizzo / Scott Rizzo",
                        "state" => "IL",
                        "city" => "Lake In The Hills",
                        "address" => "5421 Chancery Way",
                        "zip" => "60156",
                        "phones" => [
                            [
                                "number" => "18476090898",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "18476690898",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Jean Martin",
                        "state" => "KY",
                        "city" => "Cadiz",
                        "address" => "41 Point Dr",
                        "zip" => "42211",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12703500751",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 425,
                        "terms" => "GIG Logistics Inc agrees to pay Montway Auto Transport $0 within 5 business"
                            . " days of delivery.\n Payment will be made with Company Check",
                        "customer_payment_amount" => 425,
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
        $this->createMakes("GMC")
            ->createStates("TN", "TX", "IL")
            ->createTimeZones("37129", "78217", "60173")
            ->assertParsing(
                4,
                [
                    "load_id" => "1726172",
                    "pickup_date" => "02/18/2023",
                    "delivery_date" => "02/20/2023",
                    "dispatch_instructions" => "PICKUP HOURS:\nMonday - Saturday: 8am - 6:30pm\nSunday: Closed\n-------------------\nDELIVERY"
                        . " HOURS:\nMonday - Friday: 9 AM–7 PM\nSaturday: 9 AM–6 PM\nSunday: Closed\n1. Check Make/Model &"
                        . " VIN # of vehicle(s) – Must match your dispatch sheet! If different, DO NOT LOAD & call Montway"
                        . " first for approval!!!\n2. If you pick up any vehicle different than what Montway dispatched, we"
                        . " reserve the rights not to pay!\n3. If loading from Auction, Carrier responsible for calling ahead"
                        . " to confirm vehicle location & availability.\n\n4. If loading from Auction any preexisting damages"
                        . " must be documented on the gate pass for each corresponding vehicle/VIN. You are required to retain\na"
                        . " copy of the gate pass with the verified preexisting damages. If this is not done, the carrier"
                        . " assumes all responsibility for any claims resulting from this\nrule not being followed.\n5. Driver"
                        . " MUST do a proper & detailed inspection at pickup and delivery! Must leave a copy of BOL at both"
                        . " locations!\n6. Absolutely NO drops/deliveries after business hours otherwise we will apply a penalty"
                        . " of $150\n7. Any issues call our logistics department at 224-300-5434",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "GMC",
                            "model" => "Yukon",
                            "vin" => "1GKS2CKL4NR205629",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Hans Niknejad. Auto Collection Of Murfreesboro Inc",
                        "state" => "TN",
                        "city" => "Murfreesboro",
                        "address" => "363 Southeast Broad Street",
                        "zip" => "37129",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16156248470",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Nikki. American Auto Brokers",
                        "state" => "TX",
                        "city" => "San Antonio",
                        "address" => "10750 Iota Drive",
                        "zip" => "78217",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12103798839",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "logistics@montway.com",
                        "phone" => "12242203712",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 600,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $600"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 600,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_5(): void
    {
        $this->createMakes("FORD")
            ->createStates("TX", "NC", "IL")
            ->createTimeZones("77471", "27511", "60173")
            ->assertParsing(
                5,
                [
                    "load_id" => "1727725",
                    "pickup_date" => "02/20/2023",
                    "delivery_date" => "02/22/2023",
                    "dispatch_instructions" => "PICKUP HOURS:\nMonday - Friday: 9 AM–8 PM\nSaturday: 9 AM–8 PM\nSunday:"
                        . " Closed\n-------------------\nDELIVERY HOURS:\nMonday - Friday: 9 AM–6 PM\nSaturday - Sunday: Closed\n1."
                        . " Check Make/Model & VIN # of vehicle(s) – Must match your dispatch sheet! If different, DO NOT"
                        . " LOAD & call Montway first for approval!!!\n2. If you pick up any vehicle different than what Montway"
                        . " dispatched, we reserve the rights not to pay!\n\n3. If loading from Auction, Carrier responsible"
                        . " for calling ahead to confirm vehicle location & availability.\n4. If loading from Auction any"
                        . " preexisting damages must be documented on the gate pass for each corresponding vehicle/VIN. You"
                        . " are required to retain\na copy of the gate pass with the verified preexisting damages. If this"
                        . " is not done, the carrier assumes all responsibility for any claims resulting from this\nrule not"
                        . " being followed.\n5. Driver MUST do a proper & detailed inspection at pickup and delivery! Must"
                        . " leave a copy of BOL at both locations!\n6. Absolutely NO drops/deliveries after business hours"
                        . " otherwise we will apply a penalty of $150\n7. Any issues call our logistics department at 224-300-5434",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Ford",
                            "model" => "F-150 Crew Cab Short Bed",
                            "vin" => "1FTFW1E89NKF28029",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Salvador Trevino. Legacy Ford",
                        "state" => "TX",
                        "city" => "Rosenberg",
                        "address" => "27225 Southwest Freeway",
                        "zip" => "77471",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18327975165",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Michael Paul Davis",
                        "state" => "NC",
                        "city" => "Cary",
                        "address" => "1140 Kildaire Farm Road",
                        "zip" => "27511",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18039720655",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "logistics@montway.com",
                        "phone" => "12242203712",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 750,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $750"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 750,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_6(): void
    {
        $this->createMakes("ACURA")
            ->createStates("TX", "TN", "IL")
            ->createTimeZones("75181", "38028", "60173")
            ->assertParsing(
                6,
                [
                    "load_id" => "1727352",
                    "pickup_date" => "02/20/2023",
                    "delivery_date" => "02/20/2023",
                    "dispatch_instructions" => "\"text for the driver Montway #1727352 - Customer requires: driver must"
                        . " use Ship.Cars E-POD for vehicle inspection. Vehicle cannot be returned to pickup\nlocation for"
                        . " any reason. No after-hour deliveries.\n------------------\nMust Use ship.cars app for inspection\n------------------\nPICKUP"
                        . " FROM PRIVATE RESIDENCE PLEASE CALL 24 HOURS IN ADVANCE TO SCHEDULE PICKUP APPOINTMENT\n----------------------------------------------\nThe"
                        . " vehicle CANNOT be returned to the pickup location under any circumstances, if any issues call"
                        . " Montway immediately\nIf any delays in pickup or delivery call Montway\n--------------------\n\nDelivery"
                        . " Hours:\nMonday - Tuesday - Wednesday: 8:30am - 5pm,\nThursday: 7am - 5pm,\nFriday: 8:30am - 3pm\nSaturday"
                        . " - Sunday: Closed\n--------------------\nNO AFTERHOURS DELIVERY!!!\n1. Must call as soon as possible"
                        . " to schedule a pickup appointment and confirm the vehicle is ready for pick up. 24 hour notice"
                        . " is preferred.\n2. Must call 24 hours before delivery to arrange drop off\n3. Absolutely NO drops/deliveries"
                        . " after business hours otherwise the customer may apply a penalty of $150\n4. Must complete a vehicle"
                        . " inspection and obtain signatures at pick up and delivery!",
                    "vehicles" => [
                        [
                            "year" => "2010",
                            "make" => "Acura",
                            "model" => "TL",
                            "vin" => "19UUA8F53AA024810",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Roderick Givan",
                        "state" => "TX",
                        "city" => "Mesquite",
                        "address" => "1411 Springwood Drive",
                        "zip" => "75181",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14696156339",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Matt Jackson. Dealer's Auto Auction Of Memphis, Llc",
                        "state" => "TN",
                        "city" => "Eads",
                        "address" => "11713 U.s. 64",
                        "zip" => "38028",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "logistics@montway.com",
                        "phone" => "12242203712",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 299,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $299"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 299,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_7(): void
    {
        $this->createMakes("GMC")
            ->createStates("MO", "OH", "IL")
            ->createTimeZones("64114", "44320", "60173")
            ->assertParsing(
                7,
                [
                    "load_id" => "1726305",
                    "pickup_date" => "02/21/2023",
                    "delivery_date" => "",
                    "dispatch_instructions" => "The pickup and delivery location is 24 notice between 8 am to 4 pm Monday"
                        . " - Friday only - please call both locations upon dispatch to schedule an\nappointment and communicate"
                        . " if any delays\n**************************\nTHE PICKUP LOCATION WILL BE ON SUMMIT STREET DIRECTLY"
                        . " OFF OF BANNISTER IN FRONT OF ELECTRIC CAR CHARGERS\n1. Check Make/Model & VIN # of vehicle(s)"
                        . " – Must match your dispatch sheet! If different, DO NOT LOAD & call Montway first for approval!!!\n2."
                        . " If you pick up any vehicle different than what Montway dispatched, we reserve the rights not to"
                        . " pay!\n3. If loading from Auction, Carrier responsible for calling ahead to confirm vehicle location"
                        . " & availability.\n4. If loading from Auction any preexisting damages must be documented on the"
                        . " gate pass for each corresponding vehicle/VIN. You are required to retain\n\na copy of the gate"
                        . " pass with the verified preexisting damages. If this is not done, the carrier assumes all responsibility"
                        . " for any claims resulting from this\nrule not being followed.\n5. Driver MUST do a proper & detailed"
                        . " inspection at pickup and delivery! Must leave a copy of BOL at both locations!\n6. Absolutely"
                        . " NO drops/deliveries after business hours otherwise we will apply a penalty of $150\n7. Any issues"
                        . " call our logistics department at 224-300-5434",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "GMC",
                            "model" => "Sierra 1500 Crew Cab Short Bed",
                            "vin" => "1GTRUAED6PZ184573",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Robert-or-aaron. Burns & Mcdonnell",
                        "state" => "MO",
                        "city" => "Kansas City",
                        "address" => "9400 Ward Parkway Park On Summit Street North Of Banister St",
                        "zip" => "64114",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18168224280",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Alex",
                        "state" => "OH",
                        "city" => "Akron",
                        "address" => "544 White Pond Dr., Suite 300",
                        "zip" => "44320",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14405700435",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "logistics@montway.com",
                        "phone" => "12242203712",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 550,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $550"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 550,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_8(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("MO", "MD", "IL")
            ->createTimeZones("64108", "21030", "60173")
            ->assertParsing(
                8,
                [
                    "load_id" => "1710060",
                    "pickup_date" => "02/21/2023",
                    "delivery_date" => "02/23/2023",
                    "dispatch_instructions" => "Pickup no later than 02/23 is preferred",
                    "vehicles" => [
                        [
                            "year" => "2014",
                            "make" => "Toyota",
                            "model" => "RAV4",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "George Thomas",
                        "state" => "MO",
                        "city" => "Kansas City",
                        "address" => "2705 Mcgee Trfy Apt 4118",
                        "zip" => "64108",
                        "phones" => [
                            [
                                "number" => "17329391278",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "17327594783",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Thomas Almuttil",
                        "state" => "MD",
                        "city" => "Cockeysville",
                        "address" => "920 Dennisford Ct",
                        "zip" => "21030",
                        "phones" => [
                            [
                                "number" => "17327594783",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "17329391278",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 550,
                        "terms" => "GIG Logistics Inc agrees to pay Montway Auto Transport $0 within 5 business"
                            . " days of delivery.\n Payment will be made with Company Check",
                        "customer_payment_amount" => 550,
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
    public function test_9(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("TX", "MS", "IL")
            ->createTimeZones("75062", "38652", "60173")
            ->assertParsing(
                9,
                [
                    "load_id" => "1728413",
                    "pickup_date" => "02/20/2023",
                    "delivery_date" => "02/21/2023",
                    "dispatch_instructions" => "text for the driver Montway #1728413 - Customer requires: driver must use"
                        . " Ship.Cars E-POD for vehicle inspection. Vehicle cannot be returned to pickup\nlocation for any"
                        . " reason. No after-hour deliveries.\n------------------\nMust Use ship.cars app for inspection\n------------------\nPLEASE"
                        . " CALL 24 HOURS IN ADVANCE TO SCHEDULE A PICKUP APPOINTMENT\nPick-up hours:\nMonday-Friday: 9 am-9"
                        . " pm\nSaturday-Sunday: Closed\n----------------------------------------------\n\nThe vehicle CANNOT"
                        . " be returned to the pickup location under any circumstances, if any issues call Montway immediately\nIf"
                        . " any delays in pickup or delivery call Montway\n--------------------\nDelivery Hours:\nMonday -"
                        . " Saturday: 8 AM–8 PM\nSunday: Closed\n--------------------\nNO AFTERHOURS DELIVERY!!!\n1. Must"
                        . " call as soon as possible to schedule a pickup appointment and confirm the vehicle is ready for"
                        . " pick up. 24 hour notice is preferred.\n2. Must call 24 hours before delivery to arrange drop off\n3."
                        . " Absolutely NO drops/deliveries after business hours otherwise the customer may apply a penalty"
                        . " of $150\n4. Must complete a vehicle inspection and obtain signatures at pick up and delivery!",
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "Chevrolet",
                            "model" => "Cruze",
                            "vin" => "1G1BE5SM3K7110986",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Gbenga Aremu. Clay Cooley Chevrolet",
                        "state" => "TX",
                        "city" => "Irving",
                        "address" => "1251 East Airport Freeway",
                        "zip" => "75062",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17738083975",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Jake Dorr",
                        "state" => "MS",
                        "city" => "New Albany",
                        "address" => "706 Carter Avenue",
                        "zip" => "38652",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12054273060",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "logistics@montway.com",
                        "phone" => "12242203712",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 350,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $350"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 350,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_10(): void
    {
        $this->createMakes("NISSAN")
            ->createStates("TX", "OK", "IL")
            ->createTimeZones("78216", "74012", "60173")
            ->assertParsing(
                10,
                [
                    "load_id" => "1727519",
                    "pickup_date" => "02/20/2023",
                    "delivery_date" => "02/21/2023",
                    "dispatch_instructions" => "PICKUP HOURS:\nMonday - Friday: 10am - 7pm\nSaturday: 10am - 6pm\nSunday:"
                        . " Closed\n---------------------------------\nDELIVERY HOURS:\nMonday - Saturday: 8:30 AM - 9 PM\nSunday:"
                        . " Closed\n1. Check Make/Model & VIN # of vehicle(s) – Must match your dispatch sheet! If different,"
                        . " DO NOT LOAD & call Montway first for approval!!!\n2. If you pick up any vehicle different than"
                        . " what Montway dispatched, we reserve the rights not to pay!\n3. If loading from Auction, Carrier"
                        . " responsible for calling ahead to confirm vehicle location & availability.\n\n4. If loading from"
                        . " Auction any preexisting damages must be documented on the gate pass for each corresponding vehicle/VIN."
                        . " You are required to retain\na copy of the gate pass with the verified preexisting damages. If"
                        . " this is not done, the carrier assumes all responsibility for any claims resulting from this\nrule"
                        . " not being followed.\n5. Driver MUST do a proper & detailed inspection at pickup and delivery!"
                        . " Must leave a copy of BOL at both locations!\n6. Absolutely NO drops/deliveries after business"
                        . " hours otherwise we will apply a penalty of $150\n7. Any issues call our logistics department at"
                        . " 224-300-5434",
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "Nissan",
                            "model" => "Titan XD Crew Cab",
                            "vin" => "1N6BA1F42KN512450",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Euro Speed International Corp",
                        "state" => "TX",
                        "city" => "San Antonio",
                        "address" => "6143 San Pedro Avenue",
                        "zip" => "78216",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12107319091",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Michael Barnard. Jim Norton Chevrolet",
                        "state" => "OK",
                        "city" => "Broken Arrow",
                        "address" => "3131 North Aspen Avenue",
                        "zip" => "74012",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "logistics@montway.com",
                        "phone" => "12242203712",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 375,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $375"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 375,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_11(): void
    {
        $this->createMakes("CADILLAC")
            ->createStates("FL", "MO", "IL")
            ->createTimeZones("33980", "65202", "60173")
            ->assertParsing(
                11,
                [
                    "load_id" => "1725182",
                    "pickup_date" => "02/20/2023",
                    "delivery_date" => "02/22/2023",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "1993",
                            "make" => "Cadillac",
                            "model" => "Allante",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Allen",
                        "state" => "FL",
                        "city" => "Punta Gorda",
                        "address" => "24437 Harbor View Rd",
                        "zip" => "33980",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15738813350",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Jim Robertson",
                        "state" => "MO",
                        "city" => "Columbia",
                        "address" => "3125 N Rte Z",
                        "zip" => "65202",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15733103377",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 650,
                        "terms" => "GIG Logistics Inc agrees to pay Montway Auto Transport $0 within 5 business"
                            . " days of delivery.\n Payment will be made with Company Check",
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
        $this->createMakes("CHEVROLET")
            ->createStates("MD", "OH", "IL")
            ->createTimeZones("21061", "45241", "60173")
            ->assertParsing(
                12,
                [
                    "load_id" => "1724013",
                    "pickup_date" => "02/15/2023",
                    "delivery_date" => "02/16/2023",
                    "dispatch_instructions" => "PU&DEL Working hours:\n9 - 5PM - MON - FRI\nSat- SUN - N/A\n*IMPORTANT*\nCarrier"
                        . " understands and agrees that:\n1. Carrier shall collect the uShip Code on Delivery (“C.O.D”) prior"
                        . " to releasing the Vehicle to the Consignee\n2. Carrier shall not request the uShip C.O.D. prior"
                        . " to Vehicle arriving at the designated delivery location\n3. Carrier is solely responsible for"
                        . " collecting and verifying the validity of the uShip C.O.D. and waives any and all claims against"
                        . " Montway for such\npayments, if it fails to obtain and provide the C.O.D.\n4. Carrier is not allowed"
                        . " to request any other form of payment from a uShip customer than the Code on Delivery and if it"
                        . " does, Carrier is violating\nMontway’s Dispatch Contract and uShip’s Terms and Conditions\n*If"
                        . " the order is set as Billing:*\n5. Montway will only issue payment if the Carrier has collected"
                        . " and provided the uShip C.O.D.\n6. Payment will be issued as per the terms set in the Order Information"
                        . " and after providing all documents, as agreed upon in the Dispatch Contract",
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "Chevrolet",
                            "model" => "Silverado 1500",
                            "vin" => "3GCPYAEH2KG145213",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Ed Zolano. Window Nation",
                        "state" => "MD",
                        "city" => "Glen Burnie",
                        "address" => "871 Cromwell Park Drive, Suite E-m",
                        "zip" => "21061",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15405565590",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Mike Sowers. Window Nation",
                        "state" => "OH",
                        "city" => "West Chester",
                        "address" => "11935 Tramway Drive, Suite B",
                        "zip" => "45241",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15134155174",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 450,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $450"
                            . " (5) business days of Receiving a uShip code.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 450,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_13(): void
    {
        $this->createMakes("CADILLAC")
            ->createStates("FL", "KY", "IL")
            ->createTimeZones("33907", "42071", "60173")
            ->assertParsing(
                13,
                [
                    "load_id" => "1724547",
                    "pickup_date" => "02/20/2023",
                    "delivery_date" => "",
                    "dispatch_instructions" => "Pick up: Call at least 24h in advance",
                    "vehicles" => [
                        [
                            "year" => "2016",
                            "make" => "Cadillac",
                            "model" => "SRX",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Joseph Topper / Laureen Topper",
                        "state" => "FL",
                        "city" => "Fort Myers",
                        "address" => "12626 South Tamiami Trail",
                        "zip" => "33907",
                        "phones" => [
                            [
                                "number" => "13124202623",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "17032697161",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Joseph Topper / Jessica Topper",
                        "state" => "KY",
                        "city" => "Murray",
                        "address" => "1407 Poplar St. 102",
                        "zip" => "42071",
                        "phones" => [
                            [
                                "number" => "17033714703",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "17032697161",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 650,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $650"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 650,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_14(): void
    {
        $this->createMakes("JEEP")
            ->createStates("TX", "MS", "IL")
            ->createTimeZones("75056", "39759", "60173")
            ->assertParsing(
                14,
                [
                    "load_id" => "1726023",
                    "pickup_date" => "02/23/2023",
                    "delivery_date" => "02/24/2023",
                    "dispatch_instructions" => "text for the driver Montway #1726023 - Customer requires: driver must use"
                        . " Ship.Cars E-POD for vehicle inspection. Vehicle cannot be returned to pickup\nlocation for any"
                        . " reason. No after-hour deliveries.\n------------------\nMust Use ship.cars app for inspection\n------------------\nPLEASE"
                        . " CALL 24 HOURS IN ADVANCE TO SCHEDULE PICKUP APPOINTMENT AND REQUEST THE GATE PASS\n----------------------------------------------\nThe"
                        . " vehicle CANNOT be returned to the pickup location under any circumstances, if any issues call"
                        . " Montway immediately\nIf any delays in pickup or delivery call Montway\n--------------------\nDelivery"
                        . " Hours:\nMonday - Friday:8 AM–5 PM\n\nSaturday - Sunday: Closed\n--------------------\nNO AFTERHOURS"
                        . " DELIVERY!!!\n1. Must call as soon as possible to schedule a pickup appointment and confirm the"
                        . " vehicle is ready for pick up. 24 hour notice is preferred.\n2. Must call 24 hours before delivery"
                        . " to arrange drop off\n3. Absolutely NO drops/deliveries after business hours otherwise the customer"
                        . " may apply a penalty of $150\n4. Must complete a vehicle inspection and obtain signatures at pick"
                        . " up and delivery!",
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "Jeep",
                            "model" => "Renegade",
                            "vin" => "ZACNJDAB7MPN20042",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Best Obaiz",
                        "state" => "TX",
                        "city" => "Lewisville",
                        "address" => "1836 Midway Road",
                        "zip" => "75056",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14694925920",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Packy Sullivan",
                        "state" => "MS",
                        "city" => "Starkville",
                        "address" => "801 Ms-12",
                        "zip" => "39759",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16015277619",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "logistics@montway.com",
                        "phone" => "12242203712",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 425,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $425"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 425,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_15(): void
    {
        $this->createMakes("GMC")
            ->createStates("NONE", "NONE", "IL")
            ->createTimeZones("NONE", "NONE", "60173")
            ->assertParsing(
                15,
                [
                    "load_id" => "1728897",
                    "pickup_date" => "02/22/2023",
                    "delivery_date" => "02/23/2023",
                    "dispatch_instructions" => "text for the driver - Montway #1728897 - Customer requests the driver to"
                        . " bring all paperwork printed out (release forms, BOLs, etc). Wear a reflective\nsafety vest. Masks"
                        . " required to enter the office. No after-hour deliveries.\n--------------------------------------------------------------------\nDriver"
                        . " must have the dispatch sheet with no rates and BOL at time of pickup & delivery.\nDRIVER MUST"
                        . " HAVE GENERAL MOTORS HOTLINE REPORT PRINTED OUT!\n----------------------------\nPickup Hours:\nMust"
                        . " email the pick up location 24 hours in advance to schedule the pick up appointment\nMonday - Friday:"
                        . " 7am - 4pm\nSaturday & Sunday: Closed\n* MUST HAVE A PAPER BOL AT THE TIME OF PICK UP *\n\n----------------------------\nDelivery"
                        . " Hours:\nMonday - Friday: 7:30am - 5:30pm\nSaturday & Sunday: Closed\nNO AFTER HOUR DELIVERIES!"
                        . " MUST GET SIGNATURE!!!!!\n----------------------------\n1. The driver must print out and bring"
                        . " all paperwork (release forms, BOLs, etc) to the pickup location. The driver must have a safety"
                        . " reflective vest and\nmask if they are coming into the office.\n2. Must call as soon as possible"
                        . " to schedule a pickup appointment and confirm the vehicle is ready for pick up. 24 hour notice"
                        . " is preferred.\n3. Must call 24 hours before delivery to arrange drop off\n4. Absolutely NO drops/deliveries"
                        . " after business hours otherwise the customer may apply a penalty of $150\n5. Must complete a vehicle"
                        . " inspection and obtain signatures at pick up and delivery!",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "GMC",
                            "model" => "Yukon",
                            "vin" => "1GKS17KD7PR283991",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Midtexas Customer Support",
                        "state" => null,
                        "city" => null,
                        "address" => null,
                        "zip" => null,
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19727235522",
                    ],
                    "delivery_contact" => [
                        "full_name" => "Main Phone Office // Mark Drexler. Beaman Automotive Collision Center - Nashville, Tn",
                        "state" => null,
                        "city" => null,
                        "address" => null,
                        "zip" => null,
                        "phones" => [
                            [
                                "number" => "16159615053",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "16152518450",
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "logistics@montway.com",
                        "phone" => "12242203712",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 400,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $400"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 400,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
        $this->createMakes("HONDA")
            ->createStates("KY", "PA", "IL")
            ->createTimeZones("40019", "18109", "60173")
            ->assertParsing(
                16,
                [
                    "load_id" => "1729959",
                    "pickup_date" => "02/22/2023",
                    "delivery_date" => "02/23/2023",
                    "dispatch_instructions" => "text for the driver - Montway #1729959 - Customer requests: Pickup MON-FRI"
                        . " 8am - 4:30pm. Delivery needs 24hr & 1hr notice. Do NOT collect COD.\n----------------------------------------------\n**"
                        . " LINK WITH PICTURES:\nhttps://vis.iaai.com/resizer?imageKeys=36076901~SID&width=640&height=480\n----------------------------------------------\nMONTWAY"
                        . " CAN NOT GUARANTEE THAT THE VEHICLE IS RUNNING.\nFORKLIFT IS AVAILABLE AT PICK UP\n----------------------\nPick-up"
                        . " hours\nMonday - Friday 8am - 4:30pm\n\"If for any reason the driver decides not to pickup the"
                        . " unit from the branch\n\nhe should inform a representative at the branch of the cancelation \"\n----------------------\nDelivery"
                        . " Hours:\nMonday - Friday\nSaturday - Sunday: Closed\n----------\nDelivery customer does not have"
                        . " the right to refuse delivery once transport has been arranged. Order will be paid through direct"
                        . " deposit, do not collect\npayment at delivery!\n1. Check Make/Model & VIN # of vehicle(s) – Must"
                        . " match your dispatch sheet! If different, DO NOT LOAD & call Montway first for approval!!!\n2."
                        . " If you pick up any vehicle different than what Montway dispatched, we reserve the rights not to"
                        . " pay!\n3. If loading from Auction, Carrier responsible for calling ahead to confirm vehicle location"
                        . " & availability.\n4. If loading from Auction any preexisting damages must be documented on the"
                        . " gate pass for each corresponding vehicle/VIN. You are required to retain\na copy of the gate pass"
                        . " with the verified preexisting damages. If this is not done, the carrier assumes all responsibility"
                        . " for any claims resulting from this\nrule not being followed.\n5. Driver MUST do a proper & detailed"
                        . " inspection at pickup and delivery! Must leave a copy of BOL at both locations!\n6. Absolutely"
                        . " NO drops/deliveries after business hours otherwise we will apply a penalty of $150\n7. Any issues"
                        . " call our logistics department at 224-300-5434",
                    "vehicles" => [
                        [
                            "year" => "2017",
                            "make" => "Honda",
                            "model" => "Pilot",
                            "vin" => "5FNYF6H53HB052630",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Branch Manager. Iaa Louisville North",
                        "state" => "KY",
                        "city" => "Eminence",
                        "address" => "891 Ballardsville Rd",
                        "zip" => "40019",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15022154804",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Branch Manager",
                        "state" => "PA",
                        "city" => "Allentown",
                        "address" => "668 E Highland St",
                        "zip" => "18109",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14842741543",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "logistics@montway.com",
                        "phone" => "12242203712",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 500,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $500"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 500,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_17(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("WA", "CA", "IL")
            ->createTimeZones("98072", "92105", "60173")
            ->assertParsing(
                17,
                [
                    "load_id" => "1727733",
                    "pickup_date" => "02/21/2023",
                    "delivery_date" => "02/23/2023",
                    "dispatch_instructions" => "BOTH LOCATIONS ARE ACCESSIBLE FOR LARGE TRUCKS\nCALL 2 HOURS PRIOR PICKUP"
                        . " AND DELIVER",
                    "vehicles" => [
                        [
                            "year" => "2001",
                            "make" => "Toyota",
                            "model" => "4Runner",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Sean Patterson",
                        "state" => "WA",
                        "city" => "Woodinville",
                        "address" => "2430 85th Ave Se",
                        "zip" => "98072",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12035257870",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Samantha Patterson",
                        "state" => "CA",
                        "city" => "San Diego",
                        "address" => "4384 38th St",
                        "zip" => "92105",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17142723229",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 700,
                        "terms" => "GIG Logistics Inc agrees to pay Montway Auto Transport $0 within 0 business"
                            . " days of delivery.\n Payment will be made with Company Check",
                        "customer_payment_amount" => 700,
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
    public function test_18(): void
    {
        $this->createMakes("JEEP")
            ->createStates("IL", "PA", "NV")
            ->createTimeZones("60185", "18102", "89120")
            ->assertParsing(
                18,
                [
                    "load_id" => "30328",
                    "pickup_date" => "02/24/2023",
                    "delivery_date" => "02/25/2023",
                    "dispatch_instructions" => "Authority to transport this vehicle is hereby assigned to GIG Logistics"
                        . " Inc. By accepting this agreement GIG Logistics Inc certifies that it has the proper\nlegal authority"
                        . " and insurance to carry the above-described vehicle, only on trucks owned by GIG Logistics Inc."
                        . " All invoices must be accompanied by a\nsigned delivery receipt and faxed to American Auto Shipping."
                        . " The above agreed-upon price includes any and all surcharges unless otherwise agreed to by\nboth"
                        . " [GIG Logistics Inc and American Auto Shipping. The agreement between GIG Logistics Inc and American"
                        . " Auto Shipping, as described in this\ndispatch sheet, is solely between GIG Logistics Inc and American"
                        . " Auto Shipping. GIG Logistics Inc agrees and understands that we at American Auto\nShipping explain"
                        . " to the customer that the driver will call and make an appointment to pick up and deliver the vehicles"
                        . " in advance. Our customers are told\nthat door to door means as close as the carrier can safely"
                        . " get to their door. If the carrier, at the driver's discretion, cannot safely get to the customer's\ndoor,"
                        . " that arrangements will be made to meet at a nearby parking lot. We explain to our customers that"
                        . " the driver cannot make an appointment until\nthey are done loading or unloading the last customer"
                        . " before them. We also ask and expect that drivers will promptly return calls and texts from the\ncustomers"
                        . " regarding pickup and delivery status. GIG Logistics Inc agrees and understands that customers"
                        . " can and will cancel before a pickup,\nsometimes without any notice to us (American Auto Shipping)."
                        . " Carrier agrees not to hold us liable for any fees (Dry Run Fee) or liabilities associated with\nthese"
                        . " cancellations prior to pick up and agrees to give us any bad rating or reviews. Carrier also agrees"
                        . " that if the delivery takes longer than 14 days to\ndeliver the vehicle they will pay for the customer's"
                        . " car rental fees at the amount of $35/day for up to 15 days. COD payment will be accepted as cash,\ncashiers"
                        . " check or money order on delivery. All Carrier payments will be sent within 30 calendar days once"
                        . " the BOL has been emailed to\naccounting@americanautoshipping.com. Please provide your credentials"
                        . " for Paypal, Cash App, Venmo, or ACH when you send a copy of the BOL. We\ncan also mail a check"
                        . " to the address on your invoice.\nDispatch Contract #30328 Page 1 of 1",
                    "vehicles" => [
                        [
                            "year" => "2017",
                            "make" => "Jeep",
                            "model" => "Grand Cherokee",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Sonnie Krozek",
                        "state" => "IL",
                        "city" => "West Chicago",
                        "address" => "830 East Sterling Avenue",
                        "zip" => "60185",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16308905090",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Maryse Kleckner",
                        "state" => "PA",
                        "city" => "Allentown",
                        "address" => "7620 Cetronia Road",
                        "zip" => "18102",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14842234980",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "American Auto Shipping",
                        "address" => "3283 E Warm Springs Rd. , STE 100",
                        "city" => "Las Vegas",
                        "state" => "NV",
                        "zip" => "89120",
                        "phones" => [
                            [
                                "number" => "18009307417",
                            ],
                        ],
                        "email" => "dispatch@americanautoshipping.com",
                        "phone" => "17023421058",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 500,
                        "terms" => "American Auto Shipping agrees to send payment to GIG Logistics Inc $500"
                            . " (30) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 500,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_19(): void
    {
        $this->createMakes("VOLKSWAGEN")
            ->createStates("OH", "IN", "IL")
            ->createTimeZones("44053", "47879", "60173")
            ->assertParsing(
                19,
                [
                    "load_id" => "1735769",
                    "pickup_date" => "03/06/2023",
                    "delivery_date" => "03/07/2023",
                    "dispatch_instructions" => "text for the driver - Montway #1735769 - Customer requests: Pickup MON-FRI"
                        . " 8:00 AM - 4:30 PM. Delivery needs 24hr & 1hr notice. Do NOT collect COD.\n----------------------------------------------\nDriver"
                        . " needs to take pictures of keys at pickup and delivery\n----------------------------------------------\n**"
                        . " LINK WITH PICTURES:\nhttps://vis.iaai.com/resizer?imageKeys=36327546~SID&width=640&height=480\n----------------------------------------------\nMONTWAY"
                        . " CAN NOT GUARANTEE THAT THE VEHICLE IS RUNNING.\nFORKLIFT IS AVAILABLE AT PICK UP BUT NOT AT DELIVERY"
                        . " !\n----------------------\nPick-up hours\n\nMonday-Friday: 8:00 AM - 4:30 PM\nSaturday-Sunday:"
                        . " closed\n\"If for any reason the driver decides not to pickup the unit from the branch\nhe should"
                        . " inform a representative at the branch of the cancelation \"\n----------------------\n** DELIVERY"
                        . " IS A PRIVATE LOCATION NEED TO CALL IN ADVANCE **\n----------------------\nDelivery customer does"
                        . " not have the right to refuse delivery once transport has been arranged. Order will be paid through"
                        . " direct deposit, do not collect\npayment at delivery!\n1. Check Make/Model & VIN # of vehicle(s)"
                        . " – Must match your dispatch sheet! If different, DO NOT LOAD & call Montway first for approval!!!\n2."
                        . " If you pick up any vehicle different than what Montway dispatched, we reserve the rights not to"
                        . " pay!\n3. If loading from Auction, Carrier responsible for calling ahead to confirm vehicle location"
                        . " & availability.\n4. If loading from Auction any preexisting damages must be documented on the"
                        . " gate pass for each corresponding vehicle/VIN. You are required to retain\na copy of the gate pass"
                        . " with the verified preexisting damages. If this is not done, the carrier assumes all responsibility"
                        . " for any claims resulting from this\nrule not being followed.\n5. Driver MUST do a proper & detailed"
                        . " inspection at pickup and delivery! Must leave a copy of BOL at both locations!\n6. Absolutely"
                        . " NO drops/deliveries after business hours otherwise we will apply a penalty of $150\n7. Any issues"
                        . " call our logistics department at 224-300-5434",
                    "vehicles" => [
                        [
                            "year" => "2011",
                            "make" => "Volkswagen",
                            "model" => "GTI",
                            "vin" => "WVWHV7AJ8BW063206",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Branch Manager. Iaa Cleveland",
                        "state" => "OH",
                        "city" => "Lorain",
                        "address" => "7437 Deer Trail Lane",
                        "zip" => "44053",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14409601050",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Trevors",
                        "state" => "IN",
                        "city" => "Shelburn",
                        "address" => "6169 N Co Rd 550 E",
                        "zip" => "47879",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18128708520",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "logistics@montway.com",
                        "phone" => "12242203712",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 500,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $500"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 500,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_20(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("AR", "TX", "IL")
            ->createTimeZones("71902", "77477", "60173")
            ->assertParsing(
                20,
                [
                    "load_id" => "1739231",
                    "pickup_date" => "03/07/2023",
                    "delivery_date" => "03/08/2023",
                    "dispatch_instructions" => "\"text for the driver - Montway #1739231- Customer requests: Schedule pickup"
                        . " as soon as possible. Inform contacts you are shipping on behalf of Vroom.\n-----------------------\nMust"
                        . " call Pickup contact at least 24 hours in advance.\nMust identify as the “Carrier picking up on"
                        . " behalf of Vroom”\n---------------------\nDELIVERY HOURS:\nMonday - Sunday 7 am - 4: 30 pm\nThe"
                        . " drivers can park on Nations Ave for pickup and drop\noff of units.\n\nCHECK-IN UNDER VROOM RETAIL"
                        . " #4992085\n1. Must call as soon as possible to schedule a pickup appointment and confirm the vehicle"
                        . " is ready for pick up. 24 hour notice is preferred.\n2. Absolutely NO drops/deliveries after business"
                        . " hours otherwise the customer may apply a penalty of $150.\n3. Must complete a vehicle inspection"
                        . " and obtain signatures at pick up and delivery!",
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "Toyota",
                            "model" => "C-HR",
                            "vin" => "JTNKHMBX3L1072713",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Kennedy Slayton",
                        "state" => "AR",
                        "city" => "Hot Springs",
                        "address" => "124 Marcus Trail",
                        "zip" => "71902",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15016177382",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Vroom Stafford",
                        "state" => "TX",
                        "city" => "Stafford",
                        "address" => "12002 Southwest Fwy",
                        "zip" => "77477",
                        "phones" => [
                            [
                                "number" => "12814148920",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "13467620496",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "logistics@montway.com",
                        "phone" => "12242203712",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 400,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $400"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 400,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_21(): void
    {
        $this->createMakes("BUICK")
            ->createStates("MS", "IN", "IL")
            ->createTimeZones("38860", "46410", "60173")
            ->assertParsing(
                21,
                [
                    "load_id" => "1736953",
                    "pickup_date" => "03/06/2023",
                    "delivery_date" => "03/10/2023",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2004",
                            "make" => "Buick",
                            "model" => "Century",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Cassandra Moore",
                        "state" => "MS",
                        "city" => "Okolona",
                        "address" => "853 County Road 132",
                        "zip" => "38860",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16624226690",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Sharhonda Gildon",
                        "state" => "IN",
                        "city" => "Merrillville",
                        "address" => "5901 Roosevelt Place",
                        "zip" => "46410",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12193067796",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 450,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $10 (15)"
                            . " business days of Receiving a signed BOL.\n Payment will be made with Company Check",
                        "customer_payment_amount" => 440,
                        "customer_payment_method_id" => 14,
                        "customer_payment_method" => [
                            "id" => 14,
                            "title" => "Cash",
                        ],
                        "customer_payment_location" => "delivery",
                        "broker_payment_amount" => 10,
                        "broker_payment_method_id" => 3,
                        "broker_payment_method" => [
                            "id" => 3,
                            "title" => "Comcheck",
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
    public function test_22(): void
    {
        $this->createMakes("RAM")
            ->createStates("TX", "KY", "IL")
            ->createTimeZones("77583", "42069", "60173")
            ->assertParsing(
                22,
                [
                    "load_id" => "1739943",
                    "pickup_date" => "03/08/2023",
                    "delivery_date" => "03/09/2023",
                    "dispatch_instructions" => "text for the driver - Montway #1739943- Customer requests: Pickup Monday"
                        . " - Friday 8am - 4:30pm (CT). Delivery needs 24hr & 1hr notice. Do NOT collect\nCOD.\n2nd Text (preferred):"
                        . " Montway #1739943- https://vis.iaai.com/resizer?imageKeys=35990006~SID&width=640&height=480\n----------------------------------------------\nDriver"
                        . " needs to take pictures of keys at pickup and delivery\n----------------------------------------------\n**"
                        . " LINK WITH PICTURES:\nhttps://vis.iaai.com/resizer?imageKeys=35990006~SID&width=640&height=480\n\n----------------------------------------------\nMONTWAY"
                        . " CAN NOT GUARANTEE THAT THE VEHICLE IS RUNNING.\nFORKLIFT IS AVAILABLE AT PICK UP- THE BUYER WILL"
                        . " HAVE A SPARE TIRE AT DELIVERY\n----------------------\nPick-up hours\nMonday - Friday 8am - 4:30pm"
                        . " (CT)\nSaturday - Sunday: Closed\nIf for any reason the driver decides not to pickup the unit from"
                        . " the branch\nhe should inform a representative at the branch of the cancelation\n----------------------\nDelivery"
                        . " Hours:\n*** THE DRIVER MUST CALL THE DELIVERY LOCATION AT LEAST 24H IN ADVANCE TO SCHEDULE THE"
                        . " DELIVERY APPOINTMENT AND 1H PRIOR TO\nDELIVERY. NO EXCEPTIONS! ***\n----------\nDelivery customer"
                        . " does not have the right to refuse delivery once transport has been arranged. Order will be paid"
                        . " through direct deposit, do not collect\npayment at delivery!\n1. Check Make/Model & VIN # of vehicle(s)"
                        . " – Must match your dispatch sheet! If different, DO NOT LOAD & call Montway first for approval!!!\n2."
                        . " If you pick up any vehicle different than what Montway dispatched, we reserve the rights not to"
                        . " pay!\n3. If loading from Auction, Carrier responsible for calling ahead to confirm vehicle location"
                        . " & availability.\n4. If loading from Auction any preexisting damages must be documented on the"
                        . " gate pass for each corresponding vehicle/VIN. You are required to retain\na copy of the gate pass"
                        . " with the verified preexisting damages. If this is not done, the carrier assumes all responsibility"
                        . " for any claims resulting from this\nrule not being followed.\n5. Driver MUST do a proper & detailed"
                        . " inspection at pickup and delivery! Must leave a copy of BOL at both locations!\n6. Absolutely"
                        . " NO drops/deliveries after business hours otherwise we will apply a penalty of $150\n7. Any issues"
                        . " call our logistics department at 224-300-5434",
                    "vehicles" => [
                        [
                            "year" => "2016",
                            "make" => "Ram",
                            "model" => "Ram Pickup 2500 Crew Cab Short Bed",
                            "vin" => "3C6UR5FL8GG167287",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Branch Manager",
                        "state" => "TX",
                        "city" => "Arcola",
                        "address" => "2839 Farm To Market 1462",
                        "zip" => "77583",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12813691010",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => null,
                        "state" => "KY",
                        "city" => "Melber",
                        "address" => "477 Bethel Church Rd",
                        "zip" => "42069",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12705620876",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "logistics@montway.com",
                        "phone" => "12242203712",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 700,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $700"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 700,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_23(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("PA", "TX", "IL")
            ->createTimeZones("15042", "77345", "60173")
            ->assertParsing(
                23,
                [
                    "load_id" => "1721926",
                    "pickup_date" => "02/18/2023",
                    "delivery_date" => "02/20/2023",
                    "dispatch_instructions" => "Delivery on or after 02/20",
                    "vehicles" => [
                        [
                            "year" => "2012",
                            "make" => "Chevrolet",
                            "model" => "Cruze",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Robert",
                        "state" => "PA",
                        "city" => "Freedom",
                        "address" => "420 Rolling Hills Road",
                        "zip" => "15042",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14123987995",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Robert",
                        "state" => "TX",
                        "city" => "Kingwood",
                        "address" => "4920 Magnolia Cove Dr",
                        "zip" => "77345",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14123987995",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 1000,
                        "terms" => "GIG Logistics Inc agrees to pay Montway Auto Transport $0 within 5 business"
                            . " days of delivery.\n Payment will be made with Company Check",
                        "customer_payment_amount" => 1000,
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
    public function test_24(): void
    {
        $this->createMakes("LEXUS")
            ->createStates("OH", "IL", "IL")
            ->createTimeZones("44053", "60621", "60173")
            ->assertParsing(
                24,
                [
                    "load_id" => "1738490",
                    "pickup_date" => "03/09/2023",
                    "delivery_date" => "03/10/2023",
                    "dispatch_instructions" => "\"text for the driver - Montway #1738490 - Customer requests: Pickup Monday-Friday:"
                        . " 8:00 AM - 4:30 PM\nDelivery needs 24hr & 1hr notice. Do NOT collect COD.\n----------------------------------------------\nDriver"
                        . " needs to take pictures of keys at pickup and delivery\n----------------------------------------------\n**"
                        . " LINK WITH PICTURES: https://vis.iaai.com/resizer?imageKeys=36323758~SID&width=640&height=480\n----------------------------------------------\nMONTWAY"
                        . " CAN NOT GUARANTEE THAT THE VEHICLE IS RUNNING.\nFORKLIFT IS AVAILABLE AT PICK UP BUT NOT AT DELIVERY\n----------------------\n\nPick-up"
                        . " hours\nMonday-Friday: 8:00 AM - 4:30 PM\nSaturday-Sunday: closed\n\"If for any reason the driver"
                        . " decides not to pickup the unit from the branch\nhe should inform a representative at the branch"
                        . " of the cancelation \"\n----------------------\nDelivery is a private residence:\n*DELIVERY ANY"
                        . " DAY AFTER 4PM *\n* MUST CALL 24 HOURS IN AND CALL 1 HOUR PRIOR TO ARRIVAL AT THE DELIVERY LOCATION"
                        . " *\n----------\nDelivery customer does not have the right to refuse delivery once transport has"
                        . " been arranged. Order will be paid through direct deposit, do not collect\npayment at delivery!\n1."
                        . " Check Make/Model & VIN # of vehicle(s) – Must match your dispatch sheet! If different, DO NOT"
                        . " LOAD & call Montway first for approval!!!\n2. If you pick up any vehicle different than what Montway"
                        . " dispatched, we reserve the rights not to pay!\n3. If loading from Auction, Carrier responsible"
                        . " for calling ahead to confirm vehicle location & availability.\n4. If loading from Auction any"
                        . " preexisting damages must be documented on the gate pass for each corresponding vehicle/VIN. You"
                        . " are required to retain\na copy of the gate pass with the verified preexisting damages. If this"
                        . " is not done, the carrier assumes all responsibility for any claims resulting from this\nrule not"
                        . " being followed.\n5. Driver MUST do a proper & detailed inspection at pickup and delivery! Must"
                        . " leave a copy of BOL at both locations!\n6. Absolutely NO drops/deliveries after business hours"
                        . " otherwise we will apply a penalty of $150\n7. Any issues call our logistics department at 224-300-5434",
                    "vehicles" => [
                        [
                            "year" => "2013",
                            "make" => "Lexus",
                            "model" => "CT 200h",
                            "vin" => "JTHKD5BH5D2129406",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Iaa Cleveland",
                        "state" => "OH",
                        "city" => "Lorain",
                        "address" => "7437 Deer Trail Lane",
                        "zip" => "44053",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14409601050",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Jacob",
                        "state" => "IL",
                        "city" => "Chicago",
                        "address" => "6930 S Morgan St",
                        "zip" => "60621",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15673771634",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "logistics@montway.com",
                        "phone" => "12242203712",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 450,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $450"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 450,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_25(): void
    {
        $this->createMakes("GMC")
            ->createStates("NC", "PA", "IL")
            ->createTimeZones("28078", "17601", "60173")
            ->assertParsing(
                25,
                [
                    "load_id" => "1741317",
                    "pickup_date" => "03/09/2023",
                    "delivery_date" => "03/10/2023",
                    "dispatch_instructions" => "★★★Pick Up: Mon-Fri:8Am-4 Pm(NO EXCEPTIONS)!!!!★★★\n★★★Delivery: Mon-Fri:8Am-4"
                        . " Pm(NO EXCEPTIONS)!!!!★★★\n★★★CALL CUSTOMER AT LEAST 2 HOURS BEFORE PICK UP AND DELIVERY SO HE"
                        . " CAN GIVE NOTICE!!★★★\n★★★CUSTOMER REQUIRES A PICTURE PROOF OF THE VIN NUMBER ON PICK UP! MAKE"
                        . " SURE VIN NUMBER MATCHES!!!!★★★",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "GMC",
                            "model" => "Terrain",
                            "vin" => "3GKALTEG8PL141659",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Nick Bolick",
                        "state" => "NC",
                        "city" => "Huntersville",
                        "address" => "13701 Statesville Road",
                        "zip" => "28078",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13368704588",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Rud",
                        "state" => "PA",
                        "city" => "Lancaster",
                        "address" => "1828 William Penn Way",
                        "zip" => "17601",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13368704588",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 350,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $350"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 350,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_26(): void
    {
        $this->createMakes("NISSAN")
            ->createStates("PA", "MS", "IL")
            ->createTimeZones("17111", "38637", "60173")
            ->assertParsing(
                26,
                [
                    "load_id" => "1741322",
                    "pickup_date" => "03/08/2023",
                    "delivery_date" => "03/09/2023",
                    "dispatch_instructions" => "Pick-up Hours:\nMonday - Friday 9 AM–4:30 PM\nSaturday Closed\nSunday Closed",
                    "vehicles" => [
                        [
                            "year" => "2011",
                            "make" => "Nissan",
                            "model" => "Titan Crew Cab Short Bed",
                            "vin" => null,
                            "color" => "Silver",
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Gregory Browser. Dauphin County Coroner And Forensic Center",
                        "state" => "PA",
                        "city" => "Harrisburg",
                        "address" => "1271 S 28th St",
                        "zip" => "17111",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17175644567",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Mildred Davenport",
                        "state" => "MS",
                        "city" => "Horn Lake",
                        "address" => "3085 Latimer Road",
                        "zip" => "38637",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16623030506",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
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
    public function test_27(): void
    {
        $this->createMakes("ACURA")
            ->createStates("NC", "IA", "IL")
            ->createTimeZones("28206", "50265", "60173")
            ->assertParsing(
                27,
                [
                    "load_id" => "1738663",
                    "pickup_date" => "03/08/2023",
                    "delivery_date" => "03/09/2023",
                    "dispatch_instructions" => "PICK UP: MON-FRI 8 am till 5 pm SAT-SUN COSED\n------------------------------------\nBOTH"
                        . " LOCATIONS ARE ACCESSIBLE FOR LARGE TRUCKS\nTHE CUSTOMER NEEDS 2HRS PRIOR NOTICE AS THE PICKUP"
                        . " AND DROP OFF LOCATION",
                    "vehicles" => [
                        [
                            "year" => "2016",
                            "make" => "Acura",
                            "model" => "MDX",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Safwan Abdullah. Auction",
                        "state" => "NC",
                        "city" => "Charlotte",
                        "address" => "1710 Starita Road",
                        "zip" => "28206",
                        "phones" => [
                            [
                                "number" => "15155253600",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "17045965854",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Safwan Abdullah",
                        "state" => "IA",
                        "city" => "West Des Moines",
                        "address" => "1187 South 51st Street",
                        "zip" => "50265",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15155253600",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 650,
                        "terms" => "GIG Logistics Inc agrees to pay Montway Auto Transport $0 within 5 business"
                            . " days of delivery.\n Payment will be made with Company Check",
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
        $this->createMakes("PORSCHE")
            ->createStates("MO", "KY", "IL")
            ->createTimeZones("63139", "40502", "60173")
            ->assertParsing(
                28,
                [
                    "load_id" => "1739727",
                    "pickup_date" => "03/08/2023",
                    "delivery_date" => "03/09/2023",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2006",
                            "make" => "Porsche",
                            "model" => "911",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Richard Johnston",
                        "state" => "MO",
                        "city" => "Saint Louis",
                        "address" => "2121 Franz Park Lane",
                        "zip" => "63139",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13145800952",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Doug Evans",
                        "state" => "KY",
                        "city" => "Lexington",
                        "address" => "370 Andover Drive",
                        "zip" => "40502",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18595760779",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 250,
                        "terms" => "GIG Logistics Inc agrees to pay Montway Auto Transport $0 within 5 business"
                            . " days of delivery.\n Payment will be made with Company Check",
                        "customer_payment_amount" => 250,
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
    public function test_29(): void
    {
        $this->createMakes("HONDA")
            ->createStates("IL", "PA", "IL")
            ->createTimeZones("60012", "18914", "60173")
            ->assertParsing(
                29,
                [
                    "load_id" => "1739481",
                    "pickup_date" => "03/11/2023",
                    "delivery_date" => "03/13/2023",
                    "dispatch_instructions" => "Pick-up business hours:\nMon-Fri: 7AM - 5PM\nSat: 8AM - 12PM\nSun: closed",
                    "vehicles" => [
                        [
                            "year" => "2002",
                            "make" => "Honda",
                            "model" => "Odyssey Passenger",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Mark Barchard. Foxcroft Meadows",
                        "state" => "IL",
                        "city" => "Crystal Lake",
                        "address" => "5402 Edgewood Road",
                        "zip" => "60012",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18152363678",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Richard Barchard",
                        "state" => "PA",
                        "city" => "Chalfont",
                        "address" => "102 Palace Court",
                        "zip" => "18914",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15635054965",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 500,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $500"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 500,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_30(): void
    {
        $this->createMakes("DODGE")
            ->createStates("AL", "IL", "IL")
            ->createTimeZones("35901", "60914", "60173")
            ->assertParsing(
                30,
                [
                    "load_id" => "1739523",
                    "pickup_date" => "03/10/2023",
                    "delivery_date" => "03/11/2023",
                    "dispatch_instructions" => "Pickup and delivery hours are 9 am to 6 pm Monday - Friday and Saturday"
                        . " 9 am to 4 pm - closed Sunday\n1. Check Make/Model & VIN # of vehicle(s) – Must match your dispatch"
                        . " sheet! If different, DO NOT LOAD & call Montway first for approval!!!\n2. If you pick up any vehicle"
                        . " different than what Montway dispatched, we reserve the rights not to pay!\n3. If loading from"
                        . " Auction, Carrier responsible for calling ahead to confirm vehicle location & availability.\n4."
                        . " If loading from Auction any preexisting damages must be documented on the gate pass for each corresponding"
                        . " vehicle/VIN. You are required to retain\na copy of the gate pass with the verified preexisting"
                        . " damages. If this is not done, the carrier assumes all responsibility for any claims resulting"
                        . " from this\nrule not being followed.\n5. Driver MUST do a proper & detailed inspection at pickup"
                        . " and delivery! Must leave a copy of BOL at both locations!\n6. Absolutely NO drops/deliveries after"
                        . " business hours otherwise we will apply a penalty of $150\n7. Any issues call our logistics department"
                        . " at 224-300-5434",
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "Dodge",
                            "model" => "Challenger",
                            "vin" => "2C3CDZBT6MH559961",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Sales Manager. Team One Chrysler Dodge Jeep Ram Of Gadsden",
                        "state" => "AL",
                        "city" => "Gadsden",
                        "address" => "1149 1st Ave",
                        "zip" => "35901",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12565634309",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Joe. Taylor Chrysler Dodge Jeep Ram",
                        "state" => "IL",
                        "city" => "Bourbonnais",
                        "address" => "1497 Il-50",
                        "zip" => "60914",
                        "phones" => [
                            [
                                "number" => "18152141757",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "18159357900",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "logistics@montway.com",
                        "phone" => "12242203712",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 400,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $400"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 400,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_31(): void
    {
        $this->createMakes("FORD")
            ->createStates("CT", "MA", "IL")
            ->createTimeZones("06069", "02025", "60173")
            ->assertParsing(
                31,
                [
                    "load_id" => "1737491",
                    "pickup_date" => "03/09/2023",
                    "delivery_date" => "03/10/2023",
                    "dispatch_instructions" => "PICK-UP LOCATION WORKING HOURS 24/7 IT'S A HOTEL THE KEYS ARE AT THE FRONT"
                        . " DESK",
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "Ford",
                            "model" => "Expedition MAX",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Mary Mcnamara. Sharon Country Inn",
                        "state" => "CT",
                        "city" => "Sharon",
                        "address" => "1 Calkinstown Road",
                        "zip" => "06069",
                        "phones" => [
                            [
                                "number" => "18603640036",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "17814281614",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Mary Mcnamara",
                        "state" => "MA",
                        "city" => "Cohasset",
                        "address" => "150 Beach Street",
                        "zip" => "02025",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17814281614",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 325,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $325"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 325,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_32(): void
    {
        $this->createMakes("CADILLAC")
            ->createStates("VA", "MO", "IL")
            ->createTimeZones("22827", "65807", "60173")
            ->assertParsing(
                32,
                [
                    "load_id" => "1741300",
                    "pickup_date" => "03/11/2023",
                    "delivery_date" => "03/13/2023",
                    "dispatch_instructions" => "33\" tires",
                    "vehicles" => [
                        [
                            "year" => "2003",
                            "make" => "Cadillac",
                            "model" => "Escalade",
                            "vin" => "1GYEK63N53R142445",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Lee Eppard",
                        "state" => "VA",
                        "city" => "Elkton",
                        "address" => "17620 Quiet Knoll Lane",
                        "zip" => "22827",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15404350320",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Stephen Davis",
                        "state" => "MO",
                        "city" => "Springfield",
                        "address" => "1062 S Broadway Ave",
                        "zip" => "65807",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14176938307",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
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
        $this->createMakes("CHEVROLET")
            ->createStates("IL", "TX", "IL")
            ->createTimeZones("60453", "78660", "60173")
            ->assertParsing(
                33,
                [
                    "load_id" => "1742642",
                    "pickup_date" => "03/10/2023",
                    "delivery_date" => "03/11/2023",
                    "dispatch_instructions" => "PICKUP WORKING HOURS\nM-F - 9 AM TO 9 PM\nSAT - 9 AM TO 6 PM\nSUN - CLOSED",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "Chevrolet",
                            "model" => "Silverado 1500 Regular Cab Short Bed",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Wilson Wade. Webb Chevrolet",
                        "state" => "IL",
                        "city" => "Oak Lawn",
                        "address" => "9440 S. Cicero Ave",
                        "zip" => "60453",
                        "phones" => [
                            [
                                "number" => "17084239440",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "13129147666",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Wilson Wade",
                        "state" => "TX",
                        "city" => "Pflugerville",
                        "address" => "3309 Grail Hollows Rd",
                        "zip" => "78660",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13129147666",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 700,
                        "terms" => "GIG Logistics Inc agrees to pay Montway Auto Transport $0 within 5 business"
                            . " days of delivery.\n Payment will be made with Company Check",
                        "customer_payment_amount" => 700,
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
    public function test_34(): void
    {
        $this->createMakes("KIA")
            ->createStates("TN", "CO", "IL")
            ->createTimeZones("37312", "80011", "60173")
            ->assertParsing(
                34,
                [
                    "load_id" => "1725731",
                    "pickup_date" => "02/16/2023",
                    "delivery_date" => "02/19/2023",
                    "dispatch_instructions" => "text for the driver - Montway #1725731 - Customer requests: Bring printed"
                        . " release form(s) to pick up location. 24hr & 1 hr notice before delivery. Inform\ncontacts you"
                        . " are shipping on behalf of \"Vroom\".\nGATE PASS NOT REQUIRED!\n----------------------\n\"PICKUP"
                        . " HOURS:\nMonday - Friday 8 am - 4:30 pm\nSaturday & Sunday - Closed\"\n------------------------------\nDELIVERY"
                        . " HOURS:\nMonday-Sunday: 7 am-10 pm\n\nCHECK-IN UNDER VROOM WHOLESALE #5269621\n1. Must call as"
                        . " soon as possible to schedule a pickup appointment and confirm the vehicle is ready for pick up."
                        . " 24 hour notice is preferred.\n2. Absolutely NO drops/deliveries after business hours otherwise"
                        . " the customer may apply a penalty of $150.\n3. Must complete a vehicle inspection and obtain signatures"
                        . " at pick up and delivery!",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Kia",
                            "model" => "Seltos",
                            "vin" => "KNDERCAA1N7313741",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Vroom - Tennessee",
                        "state" => "TN",
                        "city" => "Cleveland",
                        "address" => "405 Airport Road Northwest",
                        "zip" => "37312",
                        "phones" => [
                            [
                                "number" => "14233107716",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "13469718772",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Denver Last Mile Hub",
                        "state" => "CO",
                        "city" => "Aurora",
                        "address" => "17500 East 32nd Avenue",
                        "zip" => "80011",
                        "phones" => [
                            [
                                "number" => "18008708933",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "13033433443",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "logistics@montway.com",
                        "phone" => "12242203712",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 900,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $900"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 900,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_35(): void
    {
        $this->createMakes("NISSAN")
            ->createStates("OK", "AL", "IL")
            ->createTimeZones("74133", "36066", "60173")
            ->assertParsing(
                35,
                [
                    "load_id" => "1741850",
                    "pickup_date" => "03/10/2023",
                    "delivery_date" => "03/11/2023",
                    "dispatch_instructions" => "Pickup and delivery hours are 9 am to 6 pm Monday - Friday and Saturday"
                        . " 9 am to 4 pm - closed Sunday\n1. Check Make/Model & VIN # of vehicle(s) – Must match your dispatch"
                        . " sheet! If different, DO NOT LOAD & call Montway first for approval!!!\n2. If you pick up any vehicle"
                        . " different than what Montway dispatched, we reserve the rights not to pay!\n3. If loading from"
                        . " Auction, Carrier responsible for calling ahead to confirm vehicle location & availability.\n4."
                        . " If loading from Auction any preexisting damages must be documented on the gate pass for each corresponding"
                        . " vehicle/VIN. You are required to retain\na copy of the gate pass with the verified preexisting"
                        . " damages. If this is not done, the carrier assumes all responsibility for any claims resulting"
                        . " from this\nrule not being followed.\n5. Driver MUST do a proper & detailed inspection at pickup"
                        . " and delivery! Must leave a copy of BOL at both locations!\n6. Absolutely NO drops/deliveries after"
                        . " business hours otherwise we will apply a penalty of $150\n7. Any issues call our logistics department"
                        . " at 224-300-5434",
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "Nissan",
                            "model" => "Murano",
                            "vin" => "5N1AZ2DJ8LN100391",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Sales Manager. Bill Knight Ford",
                        "state" => "OK",
                        "city" => "Tulsa",
                        "address" => "9607 S Memorial Dr",
                        "zip" => "74133",
                        "phones" => [
                            [
                                "number" => "14052693827",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "19182210009",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Chris Lowe. Larry Puckett",
                        "state" => "AL",
                        "city" => "Prattville",
                        "address" => "2101 Cobbs Ford Rd",
                        "zip" => "36066",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13342234481",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "logistics@montway.com",
                        "phone" => "12242203712",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 500,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $500"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 500,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_36(): void
    {
        $this->createMakes("GMC", "FORD")
            ->createStates("OH", "SD", "NV")
            ->createTimeZones("44720", "57301", "89120")
            ->assertParsing(
                36,
                [
                    "load_id" => "30818",
                    "pickup_date" => "03/11/2023",
                    "delivery_date" => "03/13/2023",
                    "dispatch_instructions" => "Authority to transport this vehicle is hereby assigned to GIG Logistics"
                        . " Inc. By accepting this agreement GIG Logistics Inc certifies that it has the proper\nlegal authority"
                        . " and insurance to carry the above-described vehicle, only on trucks owned by GIG Logistics Inc."
                        . " All invoices must be accompanied by a\nsigned delivery receipt and faxed to American Auto Shipping."
                        . " The above agreed-upon price includes any and all surcharges unless otherwise agreed to by\nboth"
                        . " [GIG Logistics Inc and American Auto Shipping. The agreement between GIG Logistics Inc and American"
                        . " Auto Shipping, as described in this\ndispatch sheet, is solely between GIG Logistics Inc and American"
                        . " Auto Shipping. GIG Logistics Inc agrees and understands that we at American Auto\nShipping explain"
                        . " to the customer that the driver will call and make an appointment to pick up and deliver the vehicles"
                        . " in advance. Our customers are told\nthat door to door means as close as the carrier can safely"
                        . " get to their door. If the carrier, at the driver's discretion, cannot safely get to the customer's\ndoor,"
                        . " that arrangements will be made to meet at a nearby parking lot. We explain to our customers that"
                        . " the driver cannot make an appointment until\nthey are done loading or unloading the last customer"
                        . " before them. We also ask and expect that drivers will promptly return calls and texts from the\ncustomers"
                        . " regarding pickup and delivery status. GIG Logistics Inc agrees and understands that customers"
                        . " can and will cancel before a pickup,\nsometimes without any notice to us (American Auto Shipping)."
                        . " Carrier agrees not to hold us liable for any fees (Dry Run Fee) or liabilities associated with\nthese"
                        . " cancellations prior to pick up and agrees to give us any bad rating or reviews. Carrier also agrees"
                        . " that if the delivery takes longer than 14 days to\ndeliver the vehicle they will pay for the customer's"
                        . " car rental fees at the amount of $35/day for up to 15 days. COD payment will be accepted as cash,\ncashiers"
                        . " check or money order on delivery. All Carrier payments will be sent within 30 calendar days once"
                        . " the BOL has been emailed to\naccounting@americanautoshipping.com. Please provide your credentials"
                        . " for Paypal, Cash App, Venmo, or ACH when you send a copy of the BOL. We\ncan also mail a check"
                        . " to the address on your invoice.\nDispatch Contract #30818 Page 1 of 1",
                    "vehicles" => [
                        [
                            "year" => "2008",
                            "make" => "GMC",
                            "model" => "Yukon XL",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                        [
                            "year" => "2008",
                            "make" => "Ford",
                            "model" => "F-350 Super Duty Crew Cab Short Bed SRW",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Matt Kiko. Kiko Auctioneer",
                        "state" => "OH",
                        "city" => "North Canton",
                        "address" => "5655 Whipple Avenue Northwest",
                        "zip" => "44720",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13303279617",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Lisa Kuapp",
                        "state" => "SD",
                        "city" => "Mitchell",
                        "address" => "41290 Rock Creek Dr",
                        "zip" => "57301",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16056561905",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "American Auto Shipping",
                        "address" => "3283 E Warm Springs Rd. , STE 100",
                        "city" => "Las Vegas",
                        "state" => "NV",
                        "zip" => "89120",
                        "phones" => [
                            [
                                "number" => "18009307417",
                            ],
                        ],
                        "email" => "dispatch@americanautoshipping.com",
                        "phone" => "17023421058",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 2125,
                        "terms" => "American Auto Shipping agrees to send payment to GIG Logistics Inc $2125"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with Company Check",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 2125,
                        "broker_payment_method_id" => 3,
                        "broker_payment_method" => [
                            "id" => 3,
                            "title" => "Comcheck",
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
    public function test_37(): void
    {
        $this->createMakes("FORD")
            ->createStates("OH", "KY", "IL")
            ->createTimeZones("44137", "42025", "60173")
            ->assertParsing(
                37,
                [
                    "load_id" => "1742700",
                    "pickup_date" => "03/11/2023",
                    "delivery_date" => "03/12/2023",
                    "dispatch_instructions" => "PLEASE COLLECT $210 BROKER FEE\nPick up working hours\nMonday 9 AM–7 PM\nTuesday"
                        . " 9 AM–6 PM\nWednesday 9 AM–6 PM\nThursday 9 AM–7 PM\nFriday 9 AM–6 PM\nSaturday 9 AM–4 PM\nSunday"
                        . " Closed",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "Ford",
                            "model" => "F-150 Crew Cab Short Bed",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Regie. Liberty Ford",
                        "state" => "OH",
                        "city" => "Maple Heights",
                        "address" => "5500 Warrensville Center Rd",
                        "zip" => "44137",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12166623673",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "James Leonard",
                        "state" => "KY",
                        "city" => "Benton",
                        "address" => "3590 Hwy 95",
                        "zip" => "42025",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12707037207",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 550,
                        "terms" => "GIG Logistics Inc agrees to pay Montway Auto Transport $210 within 5 business"
                            . " days of delivery.\n Payment will be made with Company Check",
                        "customer_payment_amount" => 760,
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
                        "broker_fee_amount" => 210,
                        "broker_fee_method_id" => 3,
                        "broker_fee_method" => [
                            "id" => 3,
                            "title" => "Comcheck",
                        ],
                        "broker_fee_days" => 5,
                        "broker_fee_begins" => "delivery",
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_38(): void
    {
        $this->createMakes("DODGE")
            ->createStates("MN", "KS", "IL")
            ->createTimeZones("55119", "67216", "60173")
            ->assertParsing(
                38,
                [
                    "load_id" => "1739252",
                    "pickup_date" => "03/10/2023",
                    "delivery_date" => "03/11/2023",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "1994",
                            "make" => "Dodge",
                            "model" => "Viper",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Elmir Name",
                        "state" => "MN",
                        "city" => "Saint Paul",
                        "address" => "1950 Margaret St",
                        "zip" => "55119",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16122908085",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Estados Unidos",
                        "state" => "KS",
                        "city" => "Wichita",
                        "address" => "1300 E 59th Ct S",
                        "zip" => "67216",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13166449169",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 450,
                        "terms" => "GIG Logistics Inc agrees to pay Montway Auto Transport $0 within 5 business"
                            . " days of delivery.\n Payment will be made with Company Check",
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
    public function test_39(): void
    {
        $this->createMakes("FORD")
            ->createStates("IL", "GA", "IL")
            ->createTimeZones("60142", "30253", "60173")
            ->assertParsing(
                39,
                [
                    "load_id" => "1742590",
                    "pickup_date" => "03/11/2023",
                    "delivery_date" => "03/13/2023",
                    "dispatch_instructions" => "Pick up Business Hours\nThursday 9 AM–9 PM\nFriday 9 AM–9 PM\nSaturday 9"
                        . " AM–7 PM\nSunday Closed\nMonday Closed\nTuesday 9 AM–9 PM\nWednesday 9 AM–9 PM\n*IMPORTANT*\nCarrier"
                        . " understands and agrees that:\n1. Carrier shall collect the uShip Code on Delivery (“C.O.D”) prior"
                        . " to releasing the Vehicle to the Consignee\n2. Carrier shall not request the uShip C.O.D. prior"
                        . " to Vehicle arriving at the designated delivery location\n3. Carrier is solely responsible for"
                        . " collecting and verifying the validity of the uShip C.O.D. and waives any and all claims against"
                        . " Montway for such\npayments, if it fails to obtain and provide the C.O.D.\n4. Carrier is not allowed"
                        . " to request any other form of payment from a uShip customer than the Code on Delivery and if it"
                        . " does, Carrier is violating\nMontway’s Dispatch Contract and uShip’s Terms and Conditions\n*If"
                        . " the order is set as Billing:*\n5. Montway will only issue payment if the Carrier has collected"
                        . " and provided the uShip C.O.D.\n6. Payment will be issued as per the terms set in the Order Information"
                        . " and after providing all documents, as agreed upon in the Dispatch Contract\nDispatch Contract"
                        . " #1742590 Page 1 of 8\n GIG Logistics Inc accepted order #1742590 electronically signed on Mar,"
                        . " 10, 2023, 3:08 p.m. C ST\nThrough Ship.C ars platform\nFrom IP : 35.191.3.102",
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "Ford",
                            "model" => "F-150",
                            "vin" => "1FTEW1EP1MKE13331",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Huntley Ford Service",
                        "state" => "IL",
                        "city" => "Huntley",
                        "address" => "13900 Automall Dr",
                        "zip" => "60142",
                        "phones" => [
                            [
                                "number" => "16786147129",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "18153755150",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Jacob Hinton",
                        "state" => "GA",
                        "city" => "McDonough",
                        "address" => "40 Harkins St",
                        "zip" => "30253",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16786147129",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 600,
                        "terms" => "GIG Logistics Inc agrees to pay Montway Auto Transport $0 within 5 business"
                            . " days of delivery.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => 600,
                        "customer_payment_method_id" => 7,
                        "customer_payment_method" => [
                            "id" => 7,
                            "title" => "Uship",
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
    public function test_40(): void
    {
        $this->createMakes("DODGE")
            ->createStates("TN", "IL", "IL")
            ->createTimeZones("37064", "61254", "60173")
            ->assertParsing(
                40,
                [
                    "load_id" => "1741009",
                    "pickup_date" => "03/11/2023",
                    "delivery_date" => "03/12/2023",
                    "dispatch_instructions" => "PLEASE COLLECT $70 BROKER FEE for MONTWAY\nPU WORKING HOURS:\nMON:FRI -"
                        . " 9am - 8pm\nSAT - 9am - 7pm\nSUN - 12–6 PM\nDEL can be any time, just call in advance.",
                    "vehicles" => [
                        [
                            "year" => "2018",
                            "make" => "Dodge",
                            "model" => "Challenger",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Derek. Franklin Dodge Chrysler Jeep Ram",
                        "state" => "TN",
                        "city" => "Franklin",
                        "address" => "1124 Murfreesboro Rd",
                        "zip" => "37064",
                        "phones" => [
                            [
                                "number" => "13099452094",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "16154951903",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Keith Kennett",
                        "state" => "IL",
                        "city" => "Geneseo",
                        "address" => "1058 Willow Drive",
                        "zip" => "61254",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13099452094",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 350,
                        "terms" => "GIG Logistics Inc agrees to pay Montway Auto Transport $70 within 5 business"
                            . " days of delivery.\n Payment will be made with Company Check",
                        "customer_payment_amount" => 420,
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
                        "broker_fee_amount" => 70,
                        "broker_fee_method_id" => 3,
                        "broker_fee_method" => [
                            "id" => 3,
                            "title" => "Comcheck",
                        ],
                        "broker_fee_days" => 5,
                        "broker_fee_begins" => "delivery",
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_41(): void
    {
        $this->createMakes("PLYMOUTH")
            ->createStates("IL", "GA", "IL")
            ->createTimeZones("60585", "30705", "60173")
            ->assertParsing(
                41,
                [
                    "load_id" => "1744136",
                    "pickup_date" => "03/13/2023",
                    "delivery_date" => "03/14/2023",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "1955",
                            "make" => "Plymouth",
                            "model" => "Belvedere",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Aimee Barranco",
                        "state" => "IL",
                        "city" => "Plainfield",
                        "address" => "13300 Allyn Street",
                        "zip" => "60585",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16302637546",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "John Viale",
                        "state" => "GA",
                        "city" => "Chatsworth",
                        "address" => "Will Update",
                        "zip" => "30705",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18139273046",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 400,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $400"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 400,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_42(): void
    {
        $this->createMakes("TESLA")
            ->createStates("IL", "PA", "IL")
            ->createTimeZones("60118", "17522", "60173")
            ->assertParsing(
                42,
                [
                    "load_id" => "1742470",
                    "pickup_date" => "03/13/2023",
                    "delivery_date" => "03/14/2023",
                    "dispatch_instructions" => "PUL Working hours:\nThursday\n8 AM–5 PM\nFriday\n8 AM–5 PM\nSaturday\nClosed\nSunday\nClosed\nMonday\n8"
                        . " AM–5 PM\nTuesday\n8 AM–5 PM\nWednesday\n8 AM–5 PM\nVehicle runs per customer. If not running,"
                        . " driver to call Montway BEFORE leaving the Auction! *\n*IMPORTANT*\nCarrier understands and agrees"
                        . " that:\n1. Carrier shall collect the uShip Code on Delivery (“C.O.D”) prior to releasing the Vehicle"
                        . " to the Consignee\n2. Carrier shall not request the uShip C.O.D. prior to Vehicle arriving at the"
                        . " designated delivery location\n3. Carrier is solely responsible for collecting and verifying the"
                        . " validity of the uShip C.O.D. and waives any and all claims against Montway for such\npayments,"
                        . " if it fails to obtain and provide the C.O.D.\n4. Carrier is not allowed to request any other form"
                        . " of payment from a uShip customer than the Code on Delivery and if it does, Carrier is violating\nMontway’s"
                        . " Dispatch Contract and uShip’s Terms and Conditions\n*If the order is set as Billing:*\n5. Montway"
                        . " will only issue payment if the Carrier has collected and provided the uShip C.O.D.\n6. Payment"
                        . " will be issued as per the terms set in the Order Information and after providing all documents,"
                        . " as agreed upon in the Dispatch Contract",
                    "vehicles" => [
                        [
                            "year" => "2018",
                            "make" => "Tesla",
                            "model" => "Model 3",
                            "vin" => "5YJ3E1EB9JF145183",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Iaa",
                        "state" => "IL",
                        "city" => "Dundee",
                        "address" => "605 Healy Rd. East Dundee, Il 60118",
                        "zip" => "60118",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17176295873",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Mike Wipf",
                        "state" => "PA",
                        "city" => "Ephrata",
                        "address" => "2458 Division Highway",
                        "zip" => "17522",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17176295873",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 500,
                        "terms" => "GIG Logistics Inc agrees to pay Montway Auto Transport $0 within 5 business"
                            . " days of delivery.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => 500,
                        "customer_payment_method_id" => 7,
                        "customer_payment_method" => [
                            "id" => 7,
                            "title" => "Uship",
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
    public function test_43(): void
    {
        $this->createMakes("LEXUS")
            ->createStates("IL", "TX", "IL")
            ->createTimeZones("60649", "75762", "60173")
            ->assertParsing(
                43,
                [
                    "load_id" => "1744387",
                    "pickup_date" => "03/13/2023",
                    "delivery_date" => "03/14/2023",
                    "dispatch_instructions" => "PLEASE COLLECT $110 BROKER FEE",
                    "vehicles" => [
                        [
                            "year" => "2015",
                            "make" => "Lexus",
                            "model" => "ES 350",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Thomas Garner / Ricky",
                        "state" => "IL",
                        "city" => "Chicago",
                        "address" => "6726 Cregier Avenue",
                        "zip" => "60649",
                        "phones" => [
                            [
                                "number" => "19033120734",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "19033123631",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Thomas Garner / Ricky",
                        "state" => "TX",
                        "city" => "Flint",
                        "address" => "18294 Stillwood Lane",
                        "zip" => "75762",
                        "phones" => [
                            [
                                "number" => "19033120734",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "19033123631",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 600,
                        "terms" => "GIG Logistics Inc agrees to pay Montway Auto Transport $110 within 5 business"
                            . " days of delivery.\n Payment will be made with Company Check",
                        "customer_payment_amount" => 710,
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
                        "broker_fee_amount" => 110,
                        "broker_fee_method_id" => 3,
                        "broker_fee_method" => [
                            "id" => 3,
                            "title" => "Comcheck",
                        ],
                        "broker_fee_days" => 5,
                        "broker_fee_begins" => "delivery",
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_44(): void
    {
        $this->createMakes("GMC")
            ->createStates("TN", "OK", "IL")
            ->createTimeZones("38133", "74868", "60173")
            ->assertParsing(
                44,
                [
                    "load_id" => "1745542",
                    "pickup_date" => "03/14/2023",
                    "delivery_date" => "",
                    "dispatch_instructions" => "Pickup/Delivery Hours Monday-Saturday 9am-6pm NO SUNDAYS\n1. Check Make/Model"
                        . " & VIN # of vehicle(s) – Must match your dispatch sheet! If different, DO NOT LOAD & call Montway"
                        . " first for approval!!!\n2. If you pick up any vehicle different than what Montway dispatched, we"
                        . " reserve the rights not to pay!\n3. If loading from Auction, Carrier responsible for calling ahead"
                        . " to confirm vehicle location & availability.\n4. If loading from Auction any preexisting damages"
                        . " must be documented on the gate pass for each corresponding vehicle/VIN. You are required to retain\na"
                        . " copy of the gate pass with the verified preexisting damages. If this is not done, the carrier"
                        . " assumes all responsibility for any claims resulting from this\nrule not being followed.\n5. Driver"
                        . " MUST do a proper & detailed inspection at pickup and delivery! Must leave a copy of BOL at both"
                        . " locations!\n6. Absolutely NO drops/deliveries after business hours otherwise we will apply a penalty"
                        . " of $150\n7. Any issues call our logistics department at 224-300-5434",
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "GMC",
                            "model" => "Canyon",
                            "vin" => "1GTG6EEN8K1201298",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Sunrise Buick Gmc At Wolfchase",
                        "state" => "TN",
                        "city" => "Bartlett",
                        "address" => "8500 Us Highway 64",
                        "zip" => "38133",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19013338000",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Tony Campbell. Seminole Automotive Group Inc",
                        "state" => "OK",
                        "city" => "Seminole",
                        "address" => "1405 N. Milt Philips Ave",
                        "zip" => "74868",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "logistics@montway.com",
                        "phone" => "12242203712",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 325,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $325"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 325,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_45(): void
    {
        $this->createMakes("ACURA")
            ->createStates("NJ", "MS", "IL")
            ->createTimeZones("08822", "38671", "60173")
            ->assertParsing(
                45,
                [
                    "load_id" => "1704675",
                    "pickup_date" => "02/18/2023",
                    "delivery_date" => "02/20/2023",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2017",
                            "make" => "Acura",
                            "model" => "TLX",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Martha Polston",
                        "state" => "NJ",
                        "city" => "Flemington",
                        "address" => "25 Horseshoe Drive",
                        "zip" => "08822",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19082845022",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Betty Albright",
                        "state" => "MS",
                        "city" => "Southaven",
                        "address" => "1454 Trafalgar Cove",
                        "zip" => "38671",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19017566291",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 750,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $750"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 750,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_46(): void
    {
        $this->createMakes("TESLA")
            ->createStates("KY", "VA", "IL")
            ->createTimeZones("40160", "22041", "60173")
            ->assertParsing(
                46,
                [
                    "load_id" => "1743999",
                    "pickup_date" => "03/14/2023",
                    "delivery_date" => "03/17/2023",
                    "dispatch_instructions" => "Pickup working hours:\nMon-Fr - 10am - 6pm\nSat - 10am - 3pm\nSunday-closed",
                    "vehicles" => [
                        [
                            "year" => "2018",
                            "make" => "Tesla",
                            "model" => "Model S",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Galen Wallace. Just 4 Fun Motorsports",
                        "state" => "KY",
                        "city" => "Radcliff",
                        "address" => "1629 S Dixie Blvd",
                        "zip" => "40160",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15022992581",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Anthony F. Jones",
                        "state" => "VA",
                        "city" => "Falls Church",
                        "address" => "3438 Diehl Court",
                        "zip" => "22041",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17037315812",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 375,
                        "terms" => "GIG Logistics Inc agrees to pay Montway Auto Transport $0 within 15 business"
                            . " days of delivery.\n Payment will be made with Company Check",
                        "customer_payment_amount" => 375,
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
    public function test_47(): void
    {
        $this->createMakes("FORD")
            ->createStates("MI", "TX", "IL")
            ->createTimeZones("49348", "77511", "60173")
            ->assertParsing(
                47,
                [
                    "load_id" => "1740017",
                    "pickup_date" => "03/15/2023",
                    "delivery_date" => "03/16/2023",
                    "dispatch_instructions" => "Pickup hours are\nMonday 9 AM–5 PM\nTuesday 9 AM–5 PM\nWednesday 9 AM–3"
                        . " PM\nThursday 9 AM–5 PM\nFriday 8 AM–4:30 PM\nSaturday Closed\nSunday Closed\n*******************\nDelivery"
                        . " hours are 9 am to 6 pm Monday - Friday and Saturday 9 am to 4 pm\n\n1. Check Make/Model & VIN"
                        . " # of vehicle(s) – Must match your dispatch sheet! If different, DO NOT LOAD & call Montway first"
                        . " for approval!!!\n2. If you pick up any vehicle different than what Montway dispatched, we reserve"
                        . " the rights not to pay!\n3. If loading from Auction, Carrier responsible for calling ahead to confirm"
                        . " vehicle location & availability.\n4. If loading from Auction any preexisting damages must be documented"
                        . " on the gate pass for each corresponding vehicle/VIN. You are required to retain\na copy of the"
                        . " gate pass with the verified preexisting damages. If this is not done, the carrier assumes all"
                        . " responsibility for any claims resulting from this\nrule not being followed.\n5. Driver MUST do"
                        . " a proper & detailed inspection at pickup and delivery! Must leave a copy of BOL at both locations!\n6."
                        . " Absolutely NO drops/deliveries after business hours otherwise we will apply a penalty of $150\n7."
                        . " Any issues call our logistics department at 224-300-5434",
                    "vehicles" => [
                        [
                            "year" => "2017",
                            "make" => "Ford",
                            "model" => "F-150 Crew Cab Short Bed",
                            "vin" => "1FTEW1EGXHFB37682",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Silvano Licari. America's Aa West Michigan",
                        "state" => "MI",
                        "city" => "Wayland",
                        "address" => "4758 Division St",
                        "zip" => "49348",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16163339285",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Stephen Dommert. Reliance Nissan",
                        "state" => "TX",
                        "city" => "Alvin",
                        "address" => "3485 Fm 528 Rd",
                        "zip" => "77511",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18328622500",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "logistics@montway.com",
                        "phone" => "12242203712",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 1050,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $1050"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 1050,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
        $this->createMakes("KIA")
            ->createStates("PA", "IN", "IL")
            ->createTimeZones("17545", "47905", "60173")
            ->assertParsing(
                48,
                [
                    "load_id" => "1745293",
                    "pickup_date" => "03/15/2023",
                    "delivery_date" => "03/16/2023",
                    "dispatch_instructions" => "Pickup hours are 6 am to 10 pm everyday\n****************************\nDelivery"
                        . " hours are 9 am to 6 pm Monday - Friday and Saturday 9 am to 4 pm CLOSED SUNDAY - NO DROPPING AFTER"
                        . " HOURS\n1. Check Make/Model & VIN # of vehicle(s) – Must match your dispatch sheet! If different,"
                        . " DO NOT LOAD & call Montway first for approval!!!\n2. If you pick up any vehicle different than"
                        . " what Montway dispatched, we reserve the rights not to pay!\n3. If loading from Auction, Carrier"
                        . " responsible for calling ahead to confirm vehicle location & availability.\n4. If loading from"
                        . " Auction any preexisting damages must be documented on the gate pass for each corresponding vehicle/VIN."
                        . " You are required to retain\na copy of the gate pass with the verified preexisting damages. If"
                        . " this is not done, the carrier assumes all responsibility for any claims resulting from this\nrule"
                        . " not being followed.\n5. Driver MUST do a proper & detailed inspection at pickup and delivery!"
                        . " Must leave a copy of BOL at both locations!\n6. Absolutely NO drops/deliveries after business"
                        . " hours otherwise we will apply a penalty of $150\n\n7. Any issues call our logistics department"
                        . " at 224-300-5434",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Kia",
                            "model" => "Sorento Hybrid",
                            "vin" => "KNDRHDLG9N5098665",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Manheim Pennsylvania",
                        "state" => "PA",
                        "city" => "Manheim",
                        "address" => "1190 Lancaster Rd",
                        "zip" => "17545",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17176653571",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Rob Jones. Bob Rohrman Kia",
                        "state" => "IN",
                        "city" => "Lafayette",
                        "address" => "801 Sagamore Pkwy S",
                        "zip" => "47905",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "logistics@montway.com",
                        "phone" => "12242203712",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 450,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $450"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 450,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_49(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("IN", "WV", "IL")
            ->createTimeZones("46217", "25177", "60173")
            ->assertParsing(
                49,
                [
                    "load_id" => "1741448",
                    "pickup_date" => "03/15/2023",
                    "delivery_date" => "03/15/2023",
                    "dispatch_instructions" => "text for the driver - Montway #1741448 - Customer requests: Bring printed"
                        . " release form(s) to pick up location. 24hr & 1 hr notice before delivery. Inform\ncontacts you"
                        . " are shipping on behalf of \"Vroom\".\nGATE PASS IS NOT REQUIRED!\n------------------\nPICKUP HOURS:\nMonday"
                        . " - Friday: 7am - 3pm\nSaturday - Sunday: Closed\n------------------\nMUST CALL DELIVERY AT LEAST"
                        . " 24 HOURS IN ADVANCE!!!\nMust identify as the “Carrier delivering on behalf of Vroom”\n1. Must"
                        . " call as soon as possible to schedule a pickup appointment and confirm the vehicle is ready for"
                        . " pick up. 24 hour notice is preferred.\n\n2. Absolutely NO drops/deliveries after business hours"
                        . " otherwise the customer may apply a penalty of $150.\n3. Must complete a vehicle inspection and"
                        . " obtain signatures at pick up and delivery!",
                    "vehicles" => [
                        [
                            "year" => "2016",
                            "make" => "Chevrolet",
                            "model" => "Traverse",
                            "vin" => "1GNKVFKD7GJ132987",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Vroom - Indianapolis Retail Ready",
                        "state" => "IN",
                        "city" => "Indianapolis",
                        "address" => "1210 Thompson Rd",
                        "zip" => "46217",
                        "phones" => [
                            [
                                "number" => "18327635427",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "13466247112",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Tray Justin Bennett",
                        "state" => "WV",
                        "city" => "Saint Albans",
                        "address" => "207 West Street",
                        "zip" => "25177",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13044003107",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "logistics@montway.com",
                        "phone" => "12242203712",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 450,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $450"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 450,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_50(): void
    {
        $this->createMakes("HONDA")
            ->createStates("MA", "CO", "IL")
            ->createTimeZones("02136", "80918", "60173")
            ->assertParsing(
                50,
                [
                    "load_id" => "1712312",
                    "pickup_date" => "02/18/2023",
                    "delivery_date" => "02/20/2023",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2017",
                            "make" => "Honda",
                            "model" => "Pilot",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Kate",
                        "state" => "MA",
                        "city" => "Hyde Park",
                        "address" => "10 Magee St",
                        "zip" => "02136",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16107249764",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Maureen / Kate",
                        "state" => "CO",
                        "city" => "Colorado Springs",
                        "address" => "7037 Shining Peak Ln",
                        "zip" => "80918",
                        "phones" => [
                            [
                                "number" => "16107249764",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "14849952658",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 1200,
                        "terms" => "GIG Logistics Inc agrees to pay Montway Auto Transport $0 within 5 business"
                            . " days of delivery.\n Payment will be made with Company Check",
                        "customer_payment_amount" => 1200,
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
    public function test_51(): void
    {
        $this->createMakes("NISSAN")
            ->createStates("DE", "GA", "IL")
            ->createTimeZones("19701", "30306", "60173")
            ->assertParsing(
                51,
                [
                    "load_id" => "1725122",
                    "pickup_date" => "02/17/2023",
                    "delivery_date" => "02/18/2023",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2014",
                            "make" => "Nissan",
                            "model" => "Versa Note",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Adejoke",
                        "state" => "DE",
                        "city" => "Bear",
                        "address" => "8 East Weald Avenue",
                        "zip" => "19701",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13022903538",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Adejoke",
                        "state" => "GA",
                        "city" => "Atlanta",
                        "address" => "675 North Highland Avenue",
                        "zip" => "30306",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13022903538",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 500,
                        "terms" => "GIG Logistics Inc agrees to pay Montway Auto Transport $0 within 15 business"
                            . " days of delivery.\n Payment will be made with Company Check",
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
        $this->createMakes("DODGE")
            ->createStates("AL", "IN", "IL")
            ->createTimeZones("35023", "46037", "60173")
            ->assertParsing(
                52,
                [
                    "load_id" => "1724650",
                    "pickup_date" => "02/17/2023",
                    "delivery_date" => "02/18/2023",
                    "dispatch_instructions" => "Pick-up working hours: Monday - Friday 8 AM - 5 PM\nTHE VEHICLE HAS A BAD"
                        . " WHEEL, THERE IS A FORKLIFT AVAILABLE AT PICKUP!!\nCUSTOMER HAS A SPARE WHEEL TO CHANGE AT DELIVERY\n*IMPORTANT*\nCarrier"
                        . " understands and agrees that:\n1. Carrier shall collect the uShip Code on Delivery (“C.O.D”) prior"
                        . " to releasing the Vehicle to the Consignee\n2. Carrier shall not request the uShip C.O.D. prior"
                        . " to Vehicle arriving at the designated delivery location\n3. Carrier is solely responsible for"
                        . " collecting and verifying the validity of the uShip C.O.D. and waives any and all claims against"
                        . " Montway for such\npayments, if it fails to obtain and provide the C.O.D.\n4. Carrier is not allowed"
                        . " to request any other form of payment from a uShip customer than the Code on Delivery and if it"
                        . " does, Carrier is violating\nMontway’s Dispatch Contract and uShip’s Terms and Conditions\n*If"
                        . " the order is set as Billing:*\n5. Montway will only issue payment if the Carrier has collected"
                        . " and provided the uShip C.O.D.\n6. Payment will be issued as per the terms set in the Order Information"
                        . " and after providing all documents, as agreed upon in the Dispatch Contract\nPick up at Copart",
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "Dodge",
                            "model" => "Challenger",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Sanjiv Neal. Copart",
                        "state" => "AL",
                        "city" => "Hueytown",
                        "address" => "3101 Davey Allison Boulevard",
                        "zip" => "35023",
                        "phones" => [
                            [
                                "number" => "12054240257",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "13175297905",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Sanjiv Neal",
                        "state" => "IN",
                        "city" => "Fishers",
                        "address" => "14075 Farmstead Drive",
                        "zip" => "46037",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13175297905",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "dispatch@montway.com",
                        "phone" => "12242209323",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 350,
                        "terms" => "GIG Logistics Inc agrees to pay Montway Auto Transport $0 within 5 business"
                            . " days of delivery.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => 350,
                        "customer_payment_method_id" => 7,
                        "customer_payment_method" => [
                            "id" => 7,
                            "title" => "Uship",
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
    public function test_53(): void
    {
        $this->createMakes("ACURA")
            ->createStates("TN", "MO", "IL")
            ->createTimeZones("38118", "64030", "60173")
            ->assertParsing(
                53,
                [
                    "load_id" => "1725946",
                    "pickup_date" => "02/20/2023",
                    "delivery_date" => "02/21/2023",
                    "dispatch_instructions" => "Montway #1725946 - Customer requests: Pickup MON-FRI 8:00 AM - 4:30 PM."
                        . " Delivery needs 24hr & 1hr notice. Do NOT collect COD.\n----------------------------------------------\n**"
                        . " LINK WITH PICTURES:\nhttps://vis.iaai.com/resizer?imageKeys=35969857~SID&width=640&height=480\n----------------------------------------------\nMONTWAY"
                        . " CAN NOT GUARANTEE THAT THE VEHICLE IS RUNNING.\nFORKLIFT IS AVAILABLE AT PICK UP BUT NOT AT DELIVERY"
                        . " !\n----------------------\nPick-up hours\nMonday-Friday: 8:00 AM - 4:30 PM\n\nSaturday - Sunday:"
                        . " closed\n\"If for any reason the driver decides not to pickup the unit from the branch\nhe should"
                        . " inform a representative at the branch of the cancelation \"\n----------------------\n** DELIVERY"
                        . " IS A PRIVATE LOCATION NEED TO CALL IN ADVANCE **\n----------------------\nDelivery customer does"
                        . " not have the right to refuse delivery once transport has been arranged. Order will be paid through"
                        . " direct deposit, do not collect\npayment at delivery!\n1. Check Make/Model & VIN # of vehicle(s)"
                        . " – Must match your dispatch sheet! If different, DO NOT LOAD & call Montway first for approval!!!\n2."
                        . " If you pick up any vehicle different than what Montway dispatched, we reserve the rights not to"
                        . " pay!\n3. If loading from Auction, Carrier responsible for calling ahead to confirm vehicle location"
                        . " & availability.\n4. If loading from Auction any preexisting damages must be documented on the"
                        . " gate pass for each corresponding vehicle/VIN. You are required to retain\na copy of the gate pass"
                        . " with the verified preexisting damages. If this is not done, the carrier assumes all responsibility"
                        . " for any claims resulting from this\nrule not being followed.\n5. Driver MUST do a proper & detailed"
                        . " inspection at pickup and delivery! Must leave a copy of BOL at both locations!\n6. Absolutely"
                        . " NO drops/deliveries after business hours otherwise we will apply a penalty of $150\n7. Any issues"
                        . " call our logistics department at 224-300-5434",
                    "vehicles" => [
                        [
                            "year" => "2013",
                            "make" => "Acura",
                            "model" => "ILX",
                            "vin" => "19VDE1F70DE011308",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Branch Manager. Iaa Memphis",
                        "state" => "TN",
                        "city" => "Memphis",
                        "address" => "5400 Getwell Road",
                        "zip" => "38118",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19017949901",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Andrey Robinson",
                        "state" => "MO",
                        "city" => "Grandview",
                        "address" => "12303 Richmond Ave",
                        "zip" => "64030",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18169157854",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Montway Auto Transport",
                        "address" => "425 N Martingale Rd, Suite 550",
                        "city" => "Schaumburg",
                        "state" => "IL",
                        "zip" => "60173",
                        "phones" => [
                            [
                                "number" => "12242205105",
                            ],
                        ],
                        "email" => "logistics@montway.com",
                        "phone" => "12242203712",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 400,
                        "terms" => "Montway Auto Transport agrees to send payment to GIG Logistics Inc $400"
                            . " (15) business days of Receiving a signed BOL.\n Payment will be made with ACH (direct deposit)",
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 400,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
}
