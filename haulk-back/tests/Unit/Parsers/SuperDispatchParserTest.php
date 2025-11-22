<?php

namespace Tests\Unit\Parsers;

use Throwable;

class SuperDispatchParserTest extends BaseParserTest
{
    /**
     * @throws Throwable
     */
    public function test_1(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("NY", "MO", "MO")
            ->createTimeZones("10956", "63128", "63049")
            ->assertParsing(
                1,
                [
                    "load_id" => "015736S",
                    "pickup_date" => "02/15/2023",
                    "delivery_date" => "02/16/2023",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "Toyota",
                            "model" => "Avalon",
                            "vin" => "4T1BZ1FB6KU015736",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Leaseland Auto Inc. & Golden Motors",
                        "state" => "NY",
                        "city" => "New City",
                        "address" => "200 E Eckerson Rd",
                        "zip" => "10956",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Weiss Toyota of South County",
                        "state" => "MO",
                        "city" => "St. Louis",
                        "address" => "11771 Tesson Ferry Rd.",
                        "zip" => "63128",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "RC Logistics LLC",
                        "address" => "2931 High Ridge Blvd, Suite A",
                        "city" => "High Ridge",
                        "state" => "MO",
                        "zip" => "63049",
                        "phones" => [],
                        "email" => "gatepasses@rclogisticsgroup.com",
                        "phone" => "18336023456",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 750,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 750,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 15,
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
        $this->createMakes("HYUNDAI")
            ->createStates("IL", "MO", "MO")
            ->createTimeZones("60435", "63139", "63139")
            ->assertParsing(
                2,
                [
                    "load_id" => "029918",
                    "pickup_date" => "10/12/2022",
                    "delivery_date" => "10/13/2022",
                    "dispatch_instructions" => "Pickup: BACKLOT GATE CODE: 9P2392 PLEASE MOVE VEHICLE TO PICKED UP ON SUPER"
                        . " DISPATCH WHEN YOU HAVE IT. THIS NOTIFIES MY OFFICE TO HAVE A CHECK READY\nDelivery: 1. In order"
                        . " for a check to be ready upon delivery, you agree to update status to PICKED UP Status within 24"
                        . " hours in advance. 2. If the Carrier did not update to PICKED UP Status as soon as the vehicle"
                        . " is picked up, you agree to drop off the vehicle and send an invoice to accounting@joekusedcars.com."
                        . " We can mail you a check or direct deposit payment to you. 3. Delivery time are Mondays to Saturdays"
                        . " 9am-6pm Only. For after hours and Sundays delivery, please contact accounting@joekusedcars.com"
                        . " in advance to setup ACH.\n1. In order for a check to be ready upon delivery, you agree to update"
                        . " status to PICKED UP Status within 24 hours in advance. 2. If the Carrier did not update to PICKED"
                        . " UP Status as soon as the vehicle is picked up, you agree to drop off the vehicle and send an invoice"
                        . " to accounting@joekusedcars.com. We can mail you a check or direct deposit payment to you. 3. Delivery"
                        . " time are Mondays to Saturdays 9am-6pm Only. For after hours and Sundays delivery, please contact"
                        . " accounting@joekusedcars.com in advance to setup ACH.",
                    "vehicles" => [
                        [
                            "year" => "2007",
                            "make" => "Hyundai",
                            "model" => "SANTA FE",
                            "vin" => "5NMSH13EX7H029918",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "bill chignoli. Chignoli Auto Sales",
                        "state" => "IL",
                        "city" => "Joliet",
                        "address" => "1850 Essington Road",
                        "zip" => "60435",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18156932878",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Jason Pool. Joe K Used Cars",
                        "state" => "MO",
                        "city" => "Saint Louis",
                        "address" => "6741 Manchester Avenue",
                        "zip" => "63139",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13142808920",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Joe-K Used Cars",
                        "address" => "6741 Manchester Ave",
                        "city" => "St. Louis",
                        "state" => "MO",
                        "zip" => "63139",
                        "phones" => [],
                        "email" => "jpoolstl@gmail.com",
                        "phone" => "13142808920",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 250,
                        "terms" => null,
                        "customer_payment_amount" => 250,
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
    public function test_3(): void
    {
        $this->createMakes("CADILLAC")
            ->createStates("IL", "NE", "NONE")
            ->createTimeZones("60443", "68005", "NONE")
            ->assertParsing(
                3,
                [
                    "load_id" => "10196",
                    "pickup_date" => "03/07/2023",
                    "delivery_date" => "03/08/2023",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "Cadillac",
                            "model" => "XT5",
                            "vin" => "1GYKNDRS4LZ142030",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Manheim Chicago",
                        "state" => "IL",
                        "city" => "Matteson",
                        "address" => "20401 Cox Ave",
                        "zip" => "60443",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Tyler Schilling. Beardmore Chevy",
                        "state" => "NE",
                        "city" => "Bellevue",
                        "address" => "418 Fort Crook Rd N",
                        "zip" => "68005",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14027089254",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Beardmore Chevrolet",
                        "address" => "418 Fort Crook Rd N",
                        "city" => null,
                        "state" => null,
                        "zip" => null,
                        "phones" => [],
                        "email" => "bgrissom@thinkbeardmore.com",
                        "phone" => "16023213344",
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
    public function test_4(): void
    {
        $this->createMakes("JEEP")
            ->createStates("AR", "LA", "AL")
            ->createTimeZones("72117", "70460", "35950")
            ->assertParsing(
                4,
                [
                    "load_id" => "1080690203",
                    "pickup_date" => "03/07/2023",
                    "delivery_date" => "03/08/2023",
                    "dispatch_instructions" => "Pickup: Pickup Instructions**** *Gate passes should be attached to the Super"
                        . " Dispatch load. If the gate passes are missing from the load, please request these via TEXT at"
                        . " (256) 613-9186 and provide your order # and email address. *If you have trouble picking up a vehicle,"
                        . " please TEXT your order number and a description of the problem to (256) 613-9186. ****ADESA Little"
                        . " Rock Gate Hours**** Monday 6 a.m. - 8 p.m Tuesday 6 a.m. - 8 p.m Wednesday 6 a.m. - 8 p.m Thursday"
                        . " 6 a.m. - 8 p.m Friday 6 a.m. - 8 p.m Saturday 8 a.m. - 8 p.m Sunday 8 a.m. - 8 p.m paid by eCheck."
                        . " Can’t find the email? Search\nDelivery: VEHICLE DROP-OFF**** 1.) REMOVE the previous AUCTION STICKERS"
                        . " off of vehicle(s)! This step is CRITICAL and PAYMENT WILL NOT BE ISSUED if this is not done. 2.)"
                        . " WRITE dealer number “5495446” on window. 3.) Take photos of: dash and door jam VIN plates & new"
                        . " auction stickers. 4.) DON’T leave vehicle in the parking lot! Check into auction. 5.) Reread steps"
                        . " 1 - 4 please! Note: Having trouble taking photos on the Super app? You can text the dispatcher"
                        . " delivery photos to (256) 613-9186. There is no reason to also text us the photos if you successful"
                        . " snapped them within Super. Payment will be delayed if we receive no photos. ****PAYMENT**** *ECHECK"
                        . " (no limit): All loads without an alternative payment request via text by the carrier will *always*"
                        . " be for emails from app@echecks.com. If you still don’t see it - PLEASE check your JUNK/SPAM before"
                        . " contacting us! *SUPERPAY (no limit): For Super Dispatch users only. Loads with this payment method"
                        . " are LOCKED into SuperPay once the load enters PICKED-UP status. If you book these loads and haven’t"
                        . " setup SuperPay, you will need to setup SuperPay to receive payment. *VENMO & PAYPAL (no limit):"
                        . " Text us at (256) 613-9186 requesting Venmo or PayPal and provide your email address / username"
                        . " to send payment to. If you send us the wrong email address or username, note that the money is"
                        . " gone and we cannot get it back - we will NOT issue the money again. *ZELLE ($600 limit): Carriers"
                        . " who want this payment method are required to SEND US A REQUEST THROUGH THE ZELLE APP following"
                        . " the guidelines below. Zelle payments are limited to $600. 1.) Send Zelle payment request to: dispatch@bringyourownbids.com"
                        . " OR (256) 613-9186 2.) Type the Super Dispatch LOAD ID and type each vehicle’s LAST 6 OF VIN in"
                        . " the comments. VINs will be verified and typos will delay payment. *We don't pay via CashApp or"
                        . " cash.",
                    "vehicles" => [
                        [
                            "year" => "2018",
                            "make" => "Jeep",
                            "model" => "Wrangler Unlimited",
                            "vin" => "1C4HJXFGXJW108069",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Arbitrations. ADESA Little Rock",
                        "state" => "AR",
                        "city" => "North Little Rock",
                        "address" => "8700 US-70",
                        "zip" => "72117",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Manheim New Orleans",
                        "state" => "LA",
                        "city" => "Slidell",
                        "address" => "61077 Saint Tammany Ave.",
                        "zip" => "70460",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "M&J Automotive LLC",
                        "address" => "1424 Medlock Road",
                        "city" => "Albertville",
                        "state" => "AL",
                        "zip" => "35950",
                        "phones" => [],
                        "email" => "dispatch@bringyourownbids.com",
                        "phone" => "12566139186",
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
    public function test_5(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("NJ", "NC", "NJ")
            ->createTimeZones("08016", "28037", "08016")
            ->assertParsing(
                5,
                [
                    "load_id" => "112865-C",
                    "pickup_date" => "02/08/2023",
                    "delivery_date" => "02/09/2023",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2012",
                            "make" => "Chevrolet",
                            "model" => "Malibu",
                            "vin" => "1G1ZB5E08CF115012",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "1998",
                            "make" => "Chevrolet",
                            "model" => "Corvette",
                            "vin" => "1G1YY22G6W5115496",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "John Wise. McCollisters Burlington 8AM-5PM",
                        "state" => "NJ",
                        "city" => "Burlington",
                        "address" => "8 Terri Lane",
                        "zip" => "08016",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16102462134",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Nicholas Semeraro. Name: Nicholas Semeraro",
                        "state" => "NC",
                        "city" => "Denver",
                        "address" => "N 908 FALLONDALE COURT APT. 101",
                        "zip" => "28037",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15163179950",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "McCollister's Brokerage Service Inc",
                        "address" => "8 Terri Lane",
                        "city" => "BURLINGTON",
                        "state" => "NJ",
                        "zip" => "08016",
                        "phones" => [],
                        "email" => "maldispatch@mccollisters.com",
                        "phone" => "16096997010",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 850,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 850,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 10,
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
        $this->createMakes("CADILLAC")
            ->createStates("OH", "TN", "NONE")
            ->createTimeZones("45804", "37203", "NONE")
            ->assertParsing(
                6,
                [
                    "load_id" => "1211",
                    "pickup_date" => "02/16/2023",
                    "delivery_date" => "02/17/2023",
                    "dispatch_instructions" => "Pickup: CARS are 20 ft long Funeral Cars *2250# lockbox PICTURES MUST INCLUDE"
                        . " A PICTURE OF THE VIN NUMBER. PLEASE CALL THE CUSTOMER AS SOON AS YOU RECEIVE THIS ORDER. THIS"
                        . " IS THE FUNERAL BUSINESS AND COMES WITH A GREAT DEAL OF PROFESSIONALISM. PLEASE GIVE THE DELIVERY"
                        . " LOCATION A DAY'S NOTICE AND BE SOMEWHAT AWARE THAT THEY DO NOT WANT A TRUCK WITH NEW CARS TO PULL"
                        . " UP DURING THE FUNERAL. THANK YOU IN ADVANCE FOR YOUR COOPERATION. PLEASE MARK SD AS DELIVERED"
                        . " AND INVOICED FOR DATING PURPOSES.\nDelivery: PLEASE CALL THE CUSTOMER AS SOON AS YOU RECEIVE THIS"
                        . " ORDER. THIS IS THE FUNERAL BUSINESS AND COMES WITH A GREAT DEAL OF PROFESSIONALISM. PLEASE GIVE"
                        . " THE DELIVERY LOCATION A DAY'S NOTICE AND BE SOMEWHAT AWARE THAT THEY DO NOT WANT A TRUCK WITH"
                        . " NEW CARS TO PULL UP DURING THE FUNERAL. THANK YOU IN ADVANCE FOR YOUR COOPERATION. PLEASE MARK"
                        . " SD AS DELIVERED AND INVOICED FOR DATING PURPOSES.\nPLEASE CALL THE CUSTOMER AS SOON AS YOU RECEIVE"
                        . " THIS ORDER. THIS IS THE FUNERAL BUSINESS AND COMES WITH A GREAT DEAL OF PROFESSIONALISM. PLEASE"
                        . " GIVE THE DELIVERY LOCATION A DAY'S NOTICE AND BE SOMEWHAT AWARE THAT THEY DO NOT WANT A TRUCK"
                        . " WITH NEW CARS TO PULL UP DURING THE FUNERAL. THANK YOU IN ADVANCE FOR YOUR COOPERATION. PLEASE"
                        . " MARK SD AS DELIVERED AND INVOICED FOR DATING PURPOSES.",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Cadillac",
                            "model" => "Hearse 20 ft long",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                        [
                            "year" => "2022",
                            "make" => "Cadillac",
                            "model" => "Hearse 20 ft long",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Marcy Martindale. S & S Superior Coach",
                        "state" => "OH",
                        "city" => "Lima",
                        "address" => "2550 Central Point Parkway",
                        "zip" => "45804",
                        "phones" => [
                            [
                                "number" => "18883247895",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "14196747381",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Randy Gardner. AMBULANCE & COACH SALES, LLC",
                        "state" => "TN",
                        "city" => "Nashvlle",
                        "address" => "1214 Jo Johnston Avenue",
                        "zip" => "37203",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14794593641",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "McCullough Transportation Services, LLC",
                        "address" => "7199 Havemann Rd",
                        "city" => null,
                        "state" => null,
                        "zip" => null,
                        "phones" => [],
                        "email" => "curt@curtmccullough.com",
                        "phone" => "14193052878",
                    ],
                    "payment" => [
                        "total_carrier_amount" => 1225,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 1225,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 10,
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
        $this->createMakes("RAM")
            ->createStates("NH", "TX", "NH")
            ->createTimeZones("03106", "78006", "03874")
            ->assertParsing(
                7,
                [
                    "load_id" => "122939",
                    "pickup_date" => "12/10/2022",
                    "delivery_date" => "12/10/2022",
                    "dispatch_instructions" => "Delivery: Please call with a 1 hour notice before pick up Avalible Tuesday"
                        . " 2:00-5:30 or Wednesday 8:30-11:00 or 2:00-5:30.\nPick up hours are from 9AM-4PM Monday to Saturday"
                        . " at Photo Booth",
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "Ram",
                            "model" => "ProMaster Cargo",
                            "vin" => "3C6TRVDG6LE100954",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Amy Tower. merchants automotive group",
                        "state" => "NH",
                        "city" => "Hooksett",
                        "address" => "1278 Hooksett Road",
                        "zip" => "03106",
                        "phones" => [
                            [
                                "number" => "16035067352",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "19802750201",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Richard Snyder. Silver King Lighting",
                        "state" => "TX",
                        "city" => "Boerne",
                        "address" => "41205 IH-10 West Building D",
                        "zip" => "78006",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18303704876",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Baier Automotive Logistics",
                        "address" => "8 Chase Park Road",
                        "city" => "SEABROOK",
                        "state" => "NH",
                        "zip" => "03874",
                        "phones" => [],
                        "email" => "cars@baierauto.com",
                        "phone" => "16034907309",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 1900,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 1900,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 30,
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
        $this->createMakes("FORD")
            ->createStates("NC", "TX", "NH")
            ->createTimeZones("28625", "77007", "03874")
            ->assertParsing(
                8,
                [
                    "load_id" => "126828",
                    "pickup_date" => "01/18/2023",
                    "delivery_date" => "01/24/2023",
                    "dispatch_instructions" => "Pickup: Please call at least 24 hours prior to pick up/delivery. Unit is"
                        . " ready and located at lot 122 as of 1/18/23\nDelivery: Spoke to Leo Mercdo- Delivery location hours:"
                        . " Mon. - Sat. 7am - 7pm / Sun. 9am - 5pm Please call delivery location an hour prior to arrival",
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "Ford",
                            "model" => "Transit Passenger Van",
                            "vin" => "1FBAX2C89MKA11171",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Bethany Church. Manheim - Statesville",
                        "state" => "NC",
                        "city" => "Statesville",
                        "address" => "145 Auction Ln",
                        "zip" => "28625",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17049296340",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Office. Firestone - Houston",
                        "state" => "TX",
                        "city" => "Houston",
                        "address" => "1502 Washington Ave",
                        "zip" => "77007",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17132241733",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Baier Automotive Logistics",
                        "address" => "8 Chase Park Road",
                        "city" => "SEABROOK",
                        "state" => "NH",
                        "zip" => "03874",
                        "phones" => [],
                        "email" => "cars@baierauto.com",
                        "phone" => "16034907309",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 1000,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 1000,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 30,
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
        $this->createMakes("FORD")
            ->createStates("TX", "LA", "TX")
            ->createTimeZones("77037", "71201", "77084")
            ->assertParsing(
                9,
                [
                    "load_id" => "128500",
                    "pickup_date" => "02/27/2023",
                    "delivery_date" => "02/28/2023",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2014",
                            "make" => "Ford",
                            "model" => "Fusion",
                            "vin" => "3FA6P0HD8ER128500",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Autonation Auto Auction",
                        "state" => "TX",
                        "city" => "Houston",
                        "address" => "608 W Mitchell Rd",
                        "zip" => "77037",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Abram Calhoun. Drive Now Cars & Trucks",
                        "state" => "LA",
                        "city" => "Monroe",
                        "address" => "2610 Washington St",
                        "zip" => "71201",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12815063224",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "All Area Trucking CO LLC",
                        "address" => "1400 Broadfield Blvd Suite E200",
                        "city" => "Houston",
                        "state" => "TX",
                        "zip" => "77084",
                        "phones" => [],
                        "email" => "garland.aatruckingco@gmail.com",
                        "phone" => "18003202640",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 200,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 200,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 5,
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
        $this->createMakes("TOYOTA")
            ->createStates("CA", "CA", "MN")
            ->createTimeZones("95954", "92123", "55912")
            ->assertParsing(
                10,
                [
                    "load_id" => "12935",
                    "pickup_date" => "02/22/2023",
                    "delivery_date" => "02/23/2023",
                    "dispatch_instructions" => "Pickup: DRIVER MUST CALL AHEAD - CALL 24 HOURS IN ADVANCE & ONE HOUR IN"
                        . " ADVANCE!!!! ******CUSTOMER NOT ALWAYS AT LOCATION AND MUST MEET YOU FOR DELIVERY!!!!!!****** Driver"
                        . " will be met M-F Weekday business hours Residence\nDelivery: DRIVER MUST CALL AHEAD - CALL 24 HOURS"
                        . " IN ADVANCE & ONE HOUR IN ADVANCE!!!! ******CUSTOMER NOT ALWAYS AT LOCATION AND MUST MEET YOU FOR"
                        . " DELIVERY!!!!!!****** Driver will be met M-F Weekday business hours",
                    "vehicles" => [
                        [
                            "year" => "2016",
                            "make" => "Toyota",
                            "model" => "Tacoma",
                            "vin" => "5TFRX5GN0GX052036",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "JEANNE BURCHAM. ECI MAGALIA",
                        "state" => "CA",
                        "city" => "Magalia",
                        "address" => "14767 Woodbow Ct",
                        "zip" => "95954",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15305379521",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Greg Bell. ECI SAN DIEGO",
                        "state" => "CA",
                        "city" => "San Diego",
                        "address" => "8306 Century Park Ct",
                        "zip" => "92123",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17605356028",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Mottinger Auto Transport LLC",
                        "address" => "56288 205th St",
                        "city" => "Austin",
                        "state" => "MN",
                        "zip" => "55912",
                        "phones" => [],
                        "email" => "mottruck@gmail.com",
                        "phone" => "13202677051",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 850,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 850,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 15,
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
        $this->createMakes("CHEVROLET")
            ->createStates("IL", "NC", "MN")
            ->createTimeZones("60148", "28270", "55912")
            ->assertParsing(
                11,
                [
                    "load_id" => "13076",
                    "pickup_date" => "03/06/2023",
                    "delivery_date" => "03/07/2023",
                    "dispatch_instructions" => "Pickup: Keys in gas cap\nDelivery: DRIVER MUST CALL AHEAD - CALL 24 HOURS"
                        . " IN ADVANCE & ONE HOUR IN ADVANCE!!!! ******CUSTOMER NOT ALWAYS AT LOCATION AND MUST MEET YOU FOR"
                        . " DELIVERY!!!!!!****** Driver will be met M-F Weekday business hours",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Chevrolet",
                            "model" => "Colorado",
                            "vin" => "1GCGTCEN4N1242478",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Sara Woodruff. USIC Lombard",
                        "state" => "IL",
                        "city" => "Lombard",
                        "address" => "860 Oak Creek Dr",
                        "zip" => "60148",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16306756650",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Casey Hamrick. USIC Charlotte",
                        "state" => "NC",
                        "city" => "Charlotte",
                        "address" => "9123 Monroe Rd Ste 150",
                        "zip" => "28270",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13366551470",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Mottinger Auto Transport LLC",
                        "address" => "56288 205th St",
                        "city" => "Austin",
                        "state" => "MN",
                        "zip" => "55912",
                        "phones" => [],
                        "email" => "mottruck@gmail.com",
                        "phone" => "13202677051",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 600,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 600,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 15,
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
        $this->createMakes("NISSAN")
            ->createStates("OH", "NY", "TX")
            ->createTimeZones("44053", "14218", "75320")
            ->assertParsing(
                12,
                [
                    "load_id" => "18599467",
                    "pickup_date" => "03/07/2023",
                    "delivery_date" => "03/08/2023",
                    "dispatch_instructions" => "Delivery: Stock No 35597306\nDeliver on behalf of IAA Transport, Buyer ID:"
                        . " 468414",
                    "vehicles" => [
                        [
                            "year" => "2012",
                            "make" => "NISSAN",
                            "model" => "ARMADA",
                            "vin" => "5N1AA0NC3CN621479",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Branch Manager. IAA Cleveland",
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
                        "full_name" => "Branch Manager. FELICIAN MASUMBUKO",
                        "state" => "NY",
                        "city" => "LACKAWANNA",
                        "address" => "1347 ELECTRIC AVE",
                        "zip" => "14218",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17164006465",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "CT Services",
                        "address" => "PO Box 204714",
                        "city" => "Dallas",
                        "state" => "TX",
                        "zip" => "75320",
                        "phones" => [],
                        "email" => "jmoore@ctservices.com",
                        "phone" => "12483519550",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 430,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 430,
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
        $this->createMakes("FORD")
            ->createStates("AR", "TN", "WY")
            ->createTimeZones("72012", "37122", "82801")
            ->assertParsing(
                13,
                [
                    "load_id" => "1954",
                    "pickup_date" => "02/15/2023",
                    "delivery_date" => "02/16/2023",
                    "dispatch_instructions" => "Pickup: Transport has to come inside the front office to get the keys and"
                        . " a non-voided gate pass in order to get the vehicle from lot.\nDelivery: DELIVERY INSTRUCTIONS:"
                        . " CHECK VEHICLE UNDER DEALER # 5425514 DON FRANKLIN AUTO. LEAVE KEYS IN VEHICLE\nPICKUP AVAIL M-F"
                        . " ONLY",
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "Ford",
                            "model" => "Mustang",
                            "vin" => "1FA6P8CFXK5147409",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Central Arkansas Auto Auction",
                        "state" => "AR",
                        "city" => "Beebe",
                        "address" => "205 Foster Dr",
                        "zip" => "72012",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15018826447",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Manheim Nashville. Manheim Nashville",
                        "state" => "TN",
                        "city" => "Mount Juliet",
                        "address" => "8400 Eastgate Blvd",
                        "zip" => "37122",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16157733800",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Sumner Solutions LLC",
                        "address" => "30 N Gould ST STE 31885",
                        "city" => "Sheridan",
                        "state" => "WY",
                        "zip" => "82801",
                        "phones" => [],
                        "email" => "dispatch@sumnerautogroup.com",
                        "phone" => "13073176809",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 300,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 300,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 5,
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
    public function test_14(): void
    {
        $this->createMakes("AUDI")
            ->createStates("GA", "TX", "CA")
            ->createTimeZones("30291", "78154", "92807")
            ->assertParsing(
                14,
                [
                    "load_id" => "201927",
                    "pickup_date" => "02/25/2023",
                    "delivery_date" => "02/27/2023",
                    "dispatch_instructions" => "vehicle is at the dealership for a trade to Audi North Park VIN PD006243"
                        . " Be sure to call ahead to the contacts and ensure they are available to meet carrier. At pick up"
                        . " carrier MUST FILL IN A COMPLETE BOL - Bill of lading using Super Dispatch APP. Failure to use"
                        . " the app will result in paperwork and payment delays. ALL CARS must have photo of KEY(S) and number"
                        . " of keys provided at pickup. Proof of delivery signatures along with photos through the app required."
                        . " NO LATE NIGHT or SUPER EARLY morning drops offs permitted, if the locations are closed no car"
                        . " and key drops are allowed. Person on delivery must be present to sign and inspect car delivery."
                        . " =========================================================================== Payment after delivery"
                        . " from Easy Car Shipping, offered by either an ECHECK- electronic check payment that would be emailed"
                        . " to carrier or Mailed Company Check to carrier. Options picked after delivery, invoice submit with"
                        . " BOL, year 2023 W9. ($10 fee for Electronic payment) **********Attention Trucker for a pick up"
                        . " or delivery to Audi North Park to avoid a possible ticket : drive past the service dept and make"
                        . " a right. There is plenty of room to park and load/unload.",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "Audi",
                            "model" => "Q7",
                            "vin" => "WA1LXBF72PD006243",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Mourir Zgara / Manager. Audi South Atlanta",
                        "state" => "GA",
                        "city" => "Union City",
                        "address" => "4332 Jonesboro Road",
                        "zip" => "30291",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14044094965",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Camden Steele. Audi North Park -(CS)",
                        "state" => "TX",
                        "city" => "Selma",
                        "address" => "15670 IH 35 N",
                        "zip" => "78154",
                        "phones" => [
                            [
                                "number" => "12109606000",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "13253749897",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Easy Car Shipping",
                        "address" => "1250 N. Lakeview Ave Unit D",
                        "city" => "Anaheim",
                        "state" => "CA",
                        "zip" => "92807",
                        "phones" => [],
                        "email" => "dispatch@easycarshipping.com",
                        "phone" => "17148884072",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 650,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 650,
                        "broker_payment_method_id" => 9,
                        "broker_payment_method" => [
                            "id" => 9,
                            "title" => "QuickPay",
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
    public function test_15(): void
    {
        $this->createMakes("FORD")
            ->createStates("TN", "MN", "NONE")
            ->createTimeZones("37343", "56156", "NONE")
            ->assertParsing(
                15,
                [
                    "load_id" => "22787",
                    "pickup_date" => "03/04/2023",
                    "delivery_date" => "03/06/2023",
                    "dispatch_instructions" => "BY ACCEPTING THIS FREIGHT ORDER, YOU; THE CARRIER. AGREE TO ALL THE TERMS"
                        . " AND CONDITIONS STATED IN THE FOLLOWING CONTRACT SIGNED OR NOT FAXED OR NOT!!!!! E-MAIL US A CERTIFICATE"
                        . " HOLDER FROM YOUR INSURANCE COMPANY WITH OUR COMPANY NAME ON IT AND THE ATTACHED CONTRACT TO CARFREIGHT1@GMAIL.COM"
                        . " ******DRIVER PLEASE INSPECT VEHICLE(S) THOROUGHLY BEFORE LOADING****** **CARRIER must inspect"
                        . " all cars carefully. All new cars must be inspected very carefully.** ------} IT IS CARRIER'S RESPONSIBILITY"
                        . " TO CALL THE DEALER AHEAD AT LEAST 24 HOURS TO MAKE SURE FREIGHT IS READY(SPECIALLY IF IT IS A"
                        . " WEEKEND PICK UP, DRIVER MUST CALL TO MAKE SURE HE/SHE CAN PICK UP ON SAT/SUN) IN ADDITION IT IS"
                        . " CARRIER RESPONSIBILITY TO MAKE SURE UNIT(S) ARE CLEAN AND READY FOR A PROPER INSPECTION IN THE"
                        . " EVENT UNITS ARE NOT CLEAN DRIVER MUST CALL CAR FREIGHT SHIPPING INC IMMEDIATELY BEFORE LOADING"
                        . " **** FAILURE TO DO SO, WILL RESULT IN YOUR FINANCIAL RESPONSIBILITY ***** PLEASE CHECK AND CONTACT"
                        . " US BEFORE LOADING, FOR: - ANY SCRATCHES, DENTS OR DAMAGES (*MUST NOTIFY CAR FREIGHT SHIPPING INC"
                        . " IMMEDIATELY BEFORE LOADING & MUST BE WRITTEN IN A B.O.L. WITH PICK UP SIGNATURE AND PRINTED NAME)"
                        . " ***CARRIER IS FINANCIALLY RESPONSIBLE FOR ANY DAMAGES NOT REPORTED TO CAR FREIGHT SHIPPING INC."
                        . " PRIOR TO LOADING(EVEN IF IT'S ON THE BILL OF LADING WITH SIGNATURES) ANY INQUIRY INTO DAMAGES"
                        . " WILL SLOW DOWN CARRIER PAYMENT UNTIL MATTER IS RESOLVED!!!!!!!!! -LOW PRO HIGH END UNITS ARE SUBJECT"
                        . " TO - DRIVER MUST CAREFULLY INSPECT THE FRONT BUMPER WITH SPECIAL ATTENTION TO THE LOWER PORTION"
                        . " WHERE THE FRONT SPOILER IS LOCATED, PRIOR TO LOADING. REPORT ANY DAMAGES TO CAR FREIGHT SHIPPING"
                        . " INC. IMMEDIATELY PRIOR TO LOADING. - MATS, BOOKS, TWO SETS OF KEYS SHOULD COME WITH EVERY UNIT.(NOTE"
                        . " ON BILL OF LADING ANY MISSING ITEMS AND NOTIFY CAR FREIGHT SHIPPING INC PRIOR TO LOADING) - MILEAGE"
                        . " NOT TO EXCEED 50 PER UNIT (CARRIER WILL BE CHARGED UP TO $2.50 PER EVERY MILE EXCEEDED IF NOT"
                        . " ADVISED TO US BEFORE LOADING) ---} CARRIER must inspect mileage; any car(s) with more than 50"
                        . " miles must be reported to Car Freight Shipping Inc. immediately before loading. Same with cars"
                        . " showing any damage. **CARRIER must verify that VEHICLE(s) have two keys per VEHICLE, mats and"
                        . " books. Failure to do so WILL affect CARRIER payment, CARRIER IS RESPONSIBLE IN FULL FOR ANY DAMAGES"
                        . " THAT WERE NOT NOTIFIED TO CAR FREIGHT SHIPPING INC.** **Any damage that may occur by CARRIER during"
                        . " loading, transportation or unloading will result in a deduction of CARRIER payment. Any inquire"
                        . " for damage will slow down CARRIER payment until matter is resolved.*** **CARRIER WILL BE RESPONSIBLE"
                        . " FOR ANY DAMAGES THAT CAR FREIGHT SHIPPING INC WAS NOT NOTIFIED ABOUT, EVEN IF IT'S ON THE BILL"
                        . " OF LADING AT PICK UP**ALL UNITS MUST BE INSPECTED AT THE ROOF ESPECIALLY VANS, TRUCKS AND SUV'S,(TEXT"
                        . " PICTURES OF VEHICLES PRIOR TO LOADING INCLUDING ROOF and RIMS, TO 954-234-3468) ANY DAMAGES MUST"
                        . " BE REPORTED TO CAR FREIGHT SHIPPING INC. IMMEDIATELY PRIOR TO LOADING!!!!! NOTE IF THE SPARE TIRE"
                        . " IS MISSING, CARRIER WILL BE HELD FINANCIALLY RESPONSIBLE FOR ANY DAMAGES NOT REPORTED TO CAR FREIGHT"
                        . " SHIPPING INC. EVEN IF IT'S ON THE B.O.L. WITH SIGNATURES!!!!! - VIN NUMBERS MUST MATCH. ---} ***CARRIER"
                        . " MUST PROVIDE A COPY OF SIGNED B.O.L. WITH PRINTED NAME(VIA TEXT(954-234-3468) OR E-MAIL CARFREIGHT1@GMAIL.COM)"
                        . " AT TIME OF PICK UP, BEFORE LEAVING DEALER*** *****ANY VEHICLE(S) DELIVERED AFTER HOURS OR DURING"
                        . " THE WEEKEND WILL BE STI(SUBJECT TO INSPECTION) BY THE PERSON IN CHARGE OF INSPECTING THE VEHICLE(S)"
                        . " AT DELIVERY***** RECEIVING DEALER HAS 24 HOURS AFTER DELIVERY TO SUMMIT DAMAGE CLAIM EVEN WITH"
                        . " SIGNATURE ON B.O.L., AFTER UNIT(S) ARE CLEANED AND RE-INSPECTED FULLY IN DETAIL. ***CARRIER MUST"
                        . " LEAVE A COPY OF SIGNED B.O.L. AND PRINTED NAME WITH BOTH THE PICK UP & DELIVERY LOCATIONS*** ****"
                        . " FAILURE TO COMPLY WITH THE STATED TERMS AND CONDITIONS LISTED ON DISPATCH INSTRUCTIONS, WILL RESULT"
                        . " IN YOUR FINANCIAL RESPONSIBILITY ***** -} DURING TRANSIT IF A DELAY OCCURS FOR ANY REASON, CAR"
                        . " FREIGHT SHIPPING INC. MUST BE NOTIFIED OF THE NATURE OF THE DELAY AND THE EXPECTED LENGTH OF THE"
                        . " DELAY. DELAYS SHOULD BE HANDLED AS FOLLOWS; YOU THE CARRIER WILL CALL THE CUSTOMER FIRST TO NOTIFY"
                        . " THEM OF THE DELAY THEN A SECOND CALL(EMAIL OR TEXT) WILL BE MADE TO CAR FREIGHT SHIPPING INC."
                        . " TO NOTIFY STATUS OF DELAY PLUS CUSTOMER FEEDBACK. (By accepting this load you \"The Carrier\""
                        . " accept all terms and conditions of the contract signed or NOT - faxed or NOT.) ------}NO PAYMENT"
                        . " WILL BE ISSUED UNLESS WE HAVE RECEIVED PHOTOS OF VEHICLE(S), SIGNED CONTRACT, CERTIFICATE HOLDER"
                        . " FROM INSURANCE AND SIGNED BILL OF LADING WITH PRINTED NAMES(BOTH FROM PICK AS WELL AS DELIVERY)ALSO"
                        . " REQUIRED IS A VOIDED CHECK AND YOUR W-9 FORM: BY E-MAIL: CARFREIGHT1@GMAIL.COM OR E-FAX TO : 888-470-4604"
                        . " NO PAYMENT WILL BE ISSUED UNLESS WE HAVE RECEIVED REQUIRED DOCUMENTS AND PHOTOS. PAYMENTS ON ANY/ALL"
                        . " ORDERS OWED TO CARRIER WILL BE HELD UNTIL PENDING DAMAGE IS RESOLVED AND PAID FOR, THANK YOU FOR"
                        . " YOUR COMPLIANCE.********* UPON DELIVERY FAX OR E-MAIL YOUR B.O.L. AND YOUR INVOICE ALONG WITH"
                        . " A VOIDED CHECK AND WE WILL PROCESS YOUR PAYMENT WITHIN THE NEXT 5 BUSINESS DAYS FROM RECEIVABLE"
                        . " DOCUMENTS. (***IF USING FAX, AFTER FAXING, A CALL TO CONFIRM WE RECEIVED THE FAX IS RECOMMENDED***)WE"
                        . " NEED THE PICTURES OF THE VEHICLE(S) AS IT WAS PRIOR TO LOADING, ALONG WITH YOUR INVOICE, BILL"
                        . " OF LADING AND VOIDED CHECK IN ORDER TO PROCESS YOUR PAYMENT. PLEASE CONTACT BOTH LOCATIONS IN"
                        . " ADVANCE FOR ARRANGEMENTS. ------}FOR AN EMERGENCY BEFORE/AFTER HOURS OR DURING WEEKENDS PLEASE"
                        . " CALL/TEXT/E-MAIL 954-234-3468 / CARFREIGHT1@GMAIL.COM PLEASE BE ADVISED THAT FOR ANY PAYMENT MATTERS"
                        . " YOU MUST SEND AN E-MAIL TO CARFREIGHT1@GMAIL.COM Diego & Maria are Dispatch! any inquiries regarding"
                        . " payment arrangements to dispatch WILL NOT be answered, thank you for your compliance.",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Ford",
                            "model" => "Mustang",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                        [
                            "year" => "2022",
                            "make" => "Ford",
                            "model" => "Mustang",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                        [
                            "year" => "2022",
                            "make" => "Ford",
                            "model" => "Mustang",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Todd Dyer. Marshal Mize Ford M-F 9am-8pm Sat 9-6 Sun CLOSED",
                        "state" => "TN",
                        "city" => "Hixson",
                        "address" => "5348 Hwy 153",
                        "zip" => "37343",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14238752023",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Joel Herman. Herman Motor Company",
                        "state" => "MN",
                        "city" => "Luverne",
                        "address" => "624 S Kniss Ave.",
                        "zip" => "56156",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15072834427",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Car Freight Shipping INC",
                        "address" => "1700 North University Dr. Ste 303",
                        "city" => null,
                        "state" => null,
                        "zip" => null,
                        "phones" => [],
                        "email" => "carfreight1@gmail.com",
                        "phone" => "19544958975",
                    ],
                    "payment" => [
                        "total_carrier_amount" => 2000,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 2000,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
    public function test_16(): void
    {
        $this->createMakes("CHEVROLET")
            ->createStates("TX", "GA", "NONE")
            ->createTimeZones("77061", "31909", "NONE")
            ->assertParsing(
                16,
                [
                    "load_id" => "255475-son",
                    "pickup_date" => "09/25/2022",
                    "delivery_date" => "09/25/2022",
                    "dispatch_instructions" => "Pickup: please mark all damages on BOL\nDelivery: dealer does not pay. please"
                        . " text to coordinate payment with 815-272-7247 on delivery",
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "Chevrolet",
                            "model" => "Suburban",
                            "vin" => "1GNSKHKC4LR255475",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Marc Heinrich. ABG Houston Hobby",
                        "state" => "TX",
                        "city" => "Houston",
                        "address" => "7701 MONROE BLVD",
                        "zip" => "77061",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17138753560",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "SONS CHEVROLET. SONS CHEVROLET",
                        "state" => "GA",
                        "city" => "Columbus",
                        "address" => "3615 Manchester Expy",
                        "zip" => "31909",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17068909440",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Motorsports of Chicago",
                        "address" => "348 W 10th Ave",
                        "city" => null,
                        "state" => null,
                        "zip" => null,
                        "phones" => [],
                        "email" => "admin@motorsportsofchicago.com",
                        "phone" => "18152727247",
                    ],
                    "payment" => [
                        "total_carrier_amount" => 600,
                        "terms" => null,
                        "customer_payment_amount" => 600,
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
        $this->createMakes("FORD")
            ->createStates("IN", "MA", "OH")
            ->createTimeZones("46515", "01201", "44512")
            ->assertParsing(
                17,
                [
                    "load_id" => "29559578",
                    "pickup_date" => "02/21/2023",
                    "delivery_date" => "02/22/2023",
                    "dispatch_instructions" => "PUT ON EACH DISPATCH: Specs: Length 290\" x Width 74\" x Height 83\" 7700"
                        . " pounds Pickup Hrs: 6am-2pm Mon- fri arrange a day in advance - Delivery Hrs: 7am-3:30pm can arrange"
                        . " after hours Carrier must text 413-344-3510 estimated delivery time. They will remotely open the"
                        . " gate and the carrier will drop the trucks and leave keys and paperwork inside the trucks.",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Ford",
                            "model" => "F-550 Cab & Chassis",
                            "vin" => "1FDUF5HT0NDA23835",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2022",
                            "make" => "Ford",
                            "model" => "F-550 Cab & Chassis",
                            "vin" => "1FDUF5HT2NDA23836",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Laura/John. Name: Laura/John",
                        "state" => "IN",
                        "city" => "Elkhart",
                        "address" => "520 Country Road 15",
                        "zip" => "46515",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15745322079",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Jon/Mike. Name: Jon/Mike",
                        "state" => "MA",
                        "city" => "Pittsfield",
                        "address" => "10 Betnr Industrial Drive",
                        "zip" => "01201",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14134437359",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Easy Auto Ship",
                        "address" => "860 Boardman Canfield Rd Ste 200",
                        "city" => "Boardman",
                        "state" => "OH",
                        "zip" => "44512",
                        "phones" => [],
                        "email" => "dispatch@easyautoship.net",
                        "phone" => "13302940071",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 1400,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 1400,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 5,
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
        $this->createMakes("MERCEDES-BENZ")
            ->createStates("MO", "AR", "NONE")
            ->createTimeZones("64161", "72114", "NONE")
            ->assertParsing(
                18,
                [
                    "load_id" => "29787026 VFLEET",
                    "pickup_date" => "03/08/2023",
                    "delivery_date" => "03/09/2023",
                    "dispatch_instructions" => "Pickup: ATTN****** CALL PICK UP & DELIVERY contacts 1 hr before arriving."
                        . " Please inspect all units for damage and cleanliness. If any units are not clean or damaged please"
                        . " let Guardian know PRIOR to pick up MUST TAKE 6 PICTURES ON PICK UP AND DELIVERY including the"
                        . " ODOMETER and VIN# on Super Dispatch. Bill of Lading, invoice, and pictures are required for payment."
                        . " **If this is not completed, there will be a 30-day delay for payment\nDelivery: ATTN****** CALL"
                        . " PICK UP & DELIVERY contacts 1 hr before arriving. Please inspect all units for damage and cleanliness."
                        . " If any units are not clean or damaged please let Guardian know PRIOR to pick up MUST TAKE 6 PICTURES"
                        . " ON PICK UP AND DELIVERY including the ODOMETER and VIN# on Super Dispatch. Bill of Lading, invoice,"
                        . " and pictures are required for payment. **If this is not completed, there will be a 30-day delay"
                        . " for payment\nATTN****** CALL PICK UP & DELIVERY contacts 1 hr before arriving. Please inspect"
                        . " all units for damage and cleanliness. If any units are not clean or damaged please let Guardian"
                        . " know PRIOR to pick up MUST TAKE 6 PICTURES ON PICK UP AND DELIVERY including the ODOMETER and"
                        . " VIN# on Super Dispatch. Bill of Lading, invoice, and pictures are required for payment. **If this"
                        . " is not completed, there will be a 30-day delay for payment",
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "Mercedes-Benz",
                            "model" => "Sprinter",
                            "vin" => "W1W40CHY2MT055832",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Aaron Strickbine. Manheim - Kansas City",
                        "state" => "MO",
                        "city" => "Kansas City",
                        "address" => "8751 NE Parvin Rd",
                        "zip" => "64161",
                        "phones" => [
                            [
                                "number" => "18164592620",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "17347525253",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Dwayne Thompson. Freedom Forever -North Little Rock",
                        "state" => "AR",
                        "city" => "North Little Rock",
                        "address" => "1305 N Hills Blvd Suite 118",
                        "zip" => "72114",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14699923236",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Guardian Auto Transport VFLEET",
                        "address" => "275 12th St. Wheeling",
                        "city" => null,
                        "state" => null,
                        "zip" => null,
                        "phones" => [],
                        "email" => "dispatch@guardianautotransport.com",
                        "phone" => "18477480303",
                    ],
                    "payment" => [
                        "total_carrier_amount" => 550,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 550,
                        "broker_payment_method_id" => 9,
                        "broker_payment_method" => [
                            "id" => 9,
                            "title" => "QuickPay",
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
        $this->createMakes("HONDA")
            ->createStates("IL", "NC", "NY")
            ->createTimeZones("60666", "28079", "13850")
            ->assertParsing(
                19,
                [
                    "load_id" => "3877",
                    "pickup_date" => "03/07/2023",
                    "delivery_date" => "03/08/2023",
                    "dispatch_instructions" => "Delivery: MARK ALL DAMAGE AND NUMBER OF KEYS ON GATE PASS",
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "Honda",
                            "model" => "Civic",
                            "vin" => "2HGFC2F80MH543877",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Xavier Atkins. ABG CHICAGO OHARE",
                        "state" => "IL",
                        "city" => "CHICAGO",
                        "address" => "10000 BESSIE COLEMAN DRIVE",
                        "zip" => "60666",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17733550932",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "GREGG. HONDA OF INDIAN TRAIL",
                        "state" => "NC",
                        "city" => "Indian Trail",
                        "address" => "4918 US 74",
                        "zip" => "28079",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15616285245",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Shank Bros Auto Logistics",
                        "address" => "2305 Vestal RD",
                        "city" => "Vestal",
                        "state" => "NY",
                        "zip" => "13850",
                        "phones" => [],
                        "email" => "john@shankbros.com",
                        "phone" => "16077277346",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 600,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 600,
                        "broker_payment_method_id" => 9,
                        "broker_payment_method" => [
                            "id" => 9,
                            "title" => "QuickPay",
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
        $this->createMakes("FORD")
            ->createStates("AR", "FL", "NONE")
            ->createTimeZones("72032", "33144", "NONE")
            ->assertParsing(
                20,
                [
                    "load_id" => "44178",
                    "pickup_date" => "03/07/2023",
                    "delivery_date" => "03/09/2023",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Ford",
                            "model" => "F-250 Super Duty Pickup",
                            "vin" => "1FT7W2BT1NED77902",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Auction. Manheim Little Rock",
                        "state" => "AR",
                        "city" => "Conway",
                        "address" => "282 US Hwy 64 East",
                        "zip" => "72032",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15015651790",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "NESTOR MENA. FORD MIDWAY MALL, INC",
                        "state" => "FL",
                        "city" => "MIAMI",
                        "address" => "8155 W FLAGLER ST",
                        "zip" => "33144",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "30526630002292",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "TurnTime Transport",
                        "address" => "2525 W Carefree Hwy Suite 106",
                        "city" => null,
                        "state" => null,
                        "zip" => null,
                        "phones" => [],
                        "email" => "sean@turntimetransport.com",
                        "phone" => "14804206912",
                    ],
                    "payment" => [
                        "total_carrier_amount" => 700,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 700,
                        "broker_payment_method_id" => 9,
                        "broker_payment_method" => [
                            "id" => 9,
                            "title" => "QuickPay",
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
        $this->createMakes("RAM", "BMW")
            ->createStates("PA", "TX", "FL")
            ->createTimeZones("17545", "75035", "33076")
            ->assertParsing(
                21,
                [
                    "load_id" => "5817",
                    "pickup_date" => "03/11/2023",
                    "delivery_date" => "03/13/2023",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Ram",
                            "model" => "1500",
                            "vin" => "1C6SRFU93NN216064",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2020",
                            "make" => "BMW",
                            "model" => "X4",
                            "vin" => "5UX2V1C04LLE68135",
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
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "FRISCO CHRYSLER DODGE JEEP RAM, INC",
                        "state" => "TX",
                        "city" => "Frisco",
                        "address" => "9640 STATE HIGHWAY 121",
                        "zip" => "75035",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "First Class Auto Transport",
                        "address" => "5944 Coral Ridge Drive #116",
                        "city" => "Coral Springs",
                        "state" => "FL",
                        "zip" => "33076",
                        "phones" => [],
                        "email" => "nicole@firstclassautotransporters.com",
                        "phone" => "195485739709548573973",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 1625,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 1625,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 10,
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
        $this->createMakes("AUDI", "NISSAN")
            ->createStates("PA", "OH", "OH")
            ->createTimeZones("17545", "44310", "44131")
            ->assertParsing(
                22,
                [
                    "load_id" => "623857",
                    "pickup_date" => "03/10/2023",
                    "delivery_date" => "03/11/2023",
                    "dispatch_instructions" => "Payment : Cashapp/Zelle/ACH in 5 business days Status : Should be marked"
                        . " \"Delivered\" in Super Dispatch post delivery or expect a delay in processing the payment on this"
                        . " order.",
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "Audi",
                            "model" => "A6",
                            "vin" => "WAUL2AF29KN118525",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2019",
                            "make" => "Nissan",
                            "model" => "Altima",
                            "vin" => "1N4BL4FW6KC250630",
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
                        "address" => "1190 LANCASTER ROAD",
                        "zip" => "17545",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Jordon Chudzik. North Coast Mitsubishi",
                        "state" => "OH",
                        "city" => "Akron",
                        "address" => "1875 Brittain Rd",
                        "zip" => "44310",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12165260906",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "NWay Express LLC",
                        "address" => "5755 GRANGER RD STE 910",
                        "city" => "INDEPENDENCE",
                        "state" => "OH",
                        "zip" => "44131",
                        "phones" => [],
                        "email" => "vlakh@nwaylogistics.com",
                        "phone" => "12162179166",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 500,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 500,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 5,
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
        $this->createMakes("JEEP")
            ->createStates("GA", "PA", "NONE")
            ->createTimeZones("30028", "17545", "NONE")
            ->assertParsing(
                23,
                [
                    "load_id" => "6797499",
                    "pickup_date" => "02/10/2023",
                    "delivery_date" => "02/11/2023",
                    "dispatch_instructions" => "Delivery: DELIVER TO THE ADCOCK OFFICE, DO NOT DELIVER TO THE AUTO AUCTION."
                        . " HOURS ARE 7:00 AM - 5:00 PM. AFTER HOURS DELIVERY AVAILABLE. After hours drop: Park units in front"
                        . " of the office and place the keys and paperwork in the lock box attached to the building where"
                        . " the fence meets the wall\nPICKUP:Please call pick up location prior to arrival and confirm the"
                        . " vehicle is ready for pick up. Please mark vehicle as picked up using SuperDispatch through your"
                        . " carrier portal or on the driver app. WEBSITE BELOWdashboard.mysuperdispatch.com If you are unable"
                        . " to update on the app or carrier portal please CALL or EMAIL updates: Adcock Direct @267-665-0313"
                        . " TRANSPORT@ADCOCKDIRECT.COMDELIVERY:Please get signature at destination. Do not collect payment"
                        . " from customer. Please mark vehicle as delivered using SuperDispatch through your carrier portal."
                        . " WEBSITE BELOWdashboard.mysuperdispatch.com If you are unable to update on the app or carrier portal"
                        . " please CALL or EMAIL updates: Adcock Direct @ 267-665-0313 TRANSPORT@ADCOCKDIRECT.COMPAYMENT:"
                        . " Please send invoice and BOL to TRANSPORT@ADCOCKDIRECT.COM PAYMENT IS SENT VIA COMPANY CHECK WITHIN"
                        . " 30 DAYS OF RECEIVING THE BOLACCOUNTING: Please contact accounting@adcocktransport.com or 717-879-0069"
                        . " for any accounting questions/issues.",
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "JEEP",
                            "model" => "GLADIATOR",
                            "vin" => "1C6HJTFG6ML556766",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Derek Jackson Automotive Group, LLC",
                        "state" => "GA",
                        "city" => "Cumming",
                        "address" => "5855 GA-400",
                        "zip" => "30028",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17706520949",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "ADCOCK BROTHERS INC.",
                        "state" => "PA",
                        "city" => "MANHEIM",
                        "address" => "14 ANTHONY DRIVE",
                        "zip" => "17545",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17176643600",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Adcock Direct",
                        "address" => "14 ANTHONY DR",
                        "city" => null,
                        "state" => null,
                        "zip" => null,
                        "phones" => [],
                        "email" => "transport@adcockdirect.com",
                        "phone" => "12676650313",
                    ],
                    "payment" => [
                        "total_carrier_amount" => 450,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 450,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 30,
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
        $this->createMakes("RAM")
            ->createStates("NC", "PA", "NONE")
            ->createTimeZones("28205", "17315", "NONE")
            ->assertParsing(
                24,
                [
                    "load_id" => "6816589",
                    "pickup_date" => "03/09/2023",
                    "delivery_date" => "03/10/2023",
                    "dispatch_instructions" => "PICK UP INSTRUCTIONS: MUST NOTE ANY DAMAGE ON GATE PASS AND BOL WITH PICTURES."
                        . " Please call pick up location to confirm that the vehicle is ready for pick up. DELIVERY INSTRUCTIONS:"
                        . " Please get a signature if possible. If you have to STI, please take pictures. PAYMENT: Please"
                        . " send invoice and BOL to accounting@adcocktransport.com ACH AND QUICK PAY OPTIONS AVAILABLE. ACCOUNTING:"
                        . " Contact Joe for any accounting questions/issues. Joe - 717 879 0065 (jaldrich@adcocktransport.com)"
                        . " Please mark vehicle as picked up using SuperDispatch through your carrier portal or on the driver"
                        . " app. WEBSITE BELOW dashboard.mysuperdispatch.com If you are unable to update on the app or carrier"
                        . " portal please CALL or EMAIL updates: 267-767-6745 OR TRANSPORT@ADCOCKTRANSPORT.COM DELIVERY: Please"
                        . " get signature at destination. Do not collect payment from customer. Please mark vehicle as delivered"
                        . " using SuperDispatch through your carrier portal. WEBSITE BELOW dashboard.mysuperdispatch.com PAYMENT:"
                        . " Please send invoice and BOL to TRANSPORT@ADCOCKDIRECT.COM PAYMENT IS SENT VIA COMPANY CHECK WITHIN"
                        . " 30 DAYS OF RECEIVING THE BOL ACCOUNTING: Please contact accounting@adcocktransport.com or 717-879-0069"
                        . " for any accounting questions/issues.",
                    "vehicles" => [
                        [
                            "year" => "2015",
                            "make" => "RAM",
                            "model" => "2500",
                            "vin" => "3C6UR5CJ3FG635461",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "QUEEN CITY AUTO SALES",
                        "state" => "NC",
                        "city" => "CHARLOTTE",
                        "address" => "3765 E Independence Blvd",
                        "zip" => "28205",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17047737795",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "THORNTON AUTOMOTIVE DOVER",
                        "state" => "PA",
                        "city" => "DOVER",
                        "address" => "3885 CARLISLE RD",
                        "zip" => "17315",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17173220371",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Adcock Direct",
                        "address" => "14 ANTHONY DR",
                        "city" => null,
                        "state" => null,
                        "zip" => null,
                        "phones" => [],
                        "email" => "transport@adcockdirect.com",
                        "phone" => "12676650313",
                    ],
                    "payment" => [
                        "total_carrier_amount" => 450,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 450,
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
        $this->createMakes("CHRYSLER", "RAM")
            ->createStates("TX", "TN", "SC")
            ->createTimeZones("77061", "37066", "29418")
            ->assertParsing(
                25,
                [
                    "load_id" => "76297",
                    "pickup_date" => "03/10/2023",
                    "delivery_date" => "03/11/2023",
                    "dispatch_instructions" => "Driver must have gate passes",
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "Chrysler",
                            "model" => "Pacifica",
                            "vin" => "2C4RC1BG7LR285272",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2020",
                            "make" => "Ram",
                            "model" => "1500",
                            "vin" => "1C6SRFFT8LN242424",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Guardhouse. Manheim Texas Hobby",
                        "state" => "TX",
                        "city" => "Houston",
                        "address" => "8215 Kopman",
                        "zip" => "77061",
                        "phones" => [
                            [
                                "number" => "17136498233",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "17136406290",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Troy Hedgecoth. Miracle Chrysler Dodge Jeep Ram",
                        "state" => "TN",
                        "city" => "Gallatin",
                        "address" => "1290 Nashville Pike",
                        "zip" => "37066",
                        "phones" => [
                            [
                                "number" => "16154522792",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "16154388279",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "LMR Auto Transport Brokerage",
                        "address" => "4395 Amsterdam St",
                        "city" => "North Charleston",
                        "state" => "SC",
                        "zip" => "29418",
                        "phones" => [],
                        "email" => "dispatch@lmratb.com",
                        "phone" => "18436144200",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 1125,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 1125,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 5,
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
    public function test_26(): void
    {
        $this->createMakes("AUDI", "BMW")
            ->createStates("FL", "FL", "SC")
            ->createTimeZones("33316", "32751", "29936")
            ->assertParsing(
                26,
                [
                    "load_id" => "7890",
                    "pickup_date" => "02/17/2023",
                    "delivery_date" => "02/17/2023",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2017",
                            "make" => "Audi",
                            "model" => "Q5",
                            "vin" => "WA1L2AFP7HA017315",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2020",
                            "make" => "BMW",
                            "model" => "5 Series",
                            "vin" => "WBAJR7C04LCD13084",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Giff Hummel. Mercedes-Benz of Fort Lauderdale",
                        "state" => "FL",
                        "city" => "Fort Lauderdale",
                        "address" => "2411 Federal Hwy",
                        "zip" => "33316",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19549490623",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Ben Ellis. Porsche Orlando",
                        "state" => "FL",
                        "city" => "Maitland",
                        "address" => "9590 South Hwy 17-92",
                        "zip" => "32751",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14072620800",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "T&J Auto Transport LLC",
                        "address" => "P.O. Box 1017",
                        "city" => "Ridgeland",
                        "state" => "SC",
                        "zip" => "29936",
                        "phones" => [],
                        "email" => "tracey@tjautotransport.com",
                        "phone" => "18437576750",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 468,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 468,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 10,
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
        $this->createMakes("LAND")
            ->createStates("PA", "MI", "NY")
            ->createTimeZones("17520", "48168", "13850")
            ->assertParsing(
                27,
                [
                    "load_id" => "7965",
                    "pickup_date" => "03/01/2023",
                    "delivery_date" => "03/02/2023",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "Land",
                            "model" => "Rover range rover",
                            "vin" => null,
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "sales. Mercedes-Benz Of Lancaster",
                        "state" => "PA",
                        "city" => "East Petersburg",
                        "address" => "5100 Main St",
                        "zip" => "17520",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17175692100",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Erik Zolis. Erik Zolis",
                        "state" => "MI",
                        "city" => "Northville",
                        "address" => "18125 laurel springs ct",
                        "zip" => "48168",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Shank Bros Auto Logistics",
                        "address" => "2305 Vestal RD",
                        "city" => "Vestal",
                        "state" => "NY",
                        "zip" => "13850",
                        "phones" => [],
                        "email" => "john@shankbros.com",
                        "phone" => "16077277346",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 550,
                        "terms" => null,
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
    public function test_28(): void
    {
        $this->createMakes("CHRYSLER")
            ->createStates("NC", "PA", "NY")
            ->createTimeZones("28625", "17545", "13850")
            ->assertParsing(
                28,
                [
                    "load_id" => "8702",
                    "pickup_date" => "03/04/2023",
                    "delivery_date" => "03/05/2023",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2017",
                            "make" => "Chrysler",
                            "model" => "Pacifica",
                            "vin" => "2C4RC1DG2HR708702",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Manheim Statesville. Manheim Statesville",
                        "state" => "NC",
                        "city" => "Statesville",
                        "address" => "145 Auction Ln",
                        "zip" => "28625",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17048761111",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "MR DENT. MR DENT",
                        "state" => "PA",
                        "city" => "Manheim",
                        "address" => "971 Park Hill Drive",
                        "zip" => "17545",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17174134308",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Shank Bros Auto Logistics",
                        "address" => "2305 Vestal RD",
                        "city" => "Vestal",
                        "state" => "NY",
                        "zip" => "13850",
                        "phones" => [],
                        "email" => "john@shankbros.com",
                        "phone" => "16077277346",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 350,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 350,
                        "broker_payment_method_id" => 9,
                        "broker_payment_method" => [
                            "id" => 9,
                            "title" => "QuickPay",
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
        $this->createMakes("KIA")
            ->createStates("IL", "MI", "NONE")
            ->createTimeZones("60169", "48911", "NONE")
            ->assertParsing(
                29,
                [
                    "load_id" => "89156",
                    "pickup_date" => "03/07/2023",
                    "delivery_date" => "03/07/2023",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "Kia",
                            "model" => "Niro",
                            "vin" => "KNDCB3LC5L5442956",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Adesa Chicago",
                        "state" => "IL",
                        "city" => "Hoffman Estates",
                        "address" => "2785 Beverly Rd",
                        "zip" => "60169",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18475512151",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Gaston Meacham or Claudia. Safeway-Lansing",
                        "state" => "MI",
                        "city" => "Lansing",
                        "address" => "715 E Miiller Rd",
                        "zip" => "48911",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15178270090",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "RW HAULERS LLC",
                        "address" => "300 WEST AVE",
                        "city" => null,
                        "state" => null,
                        "zip" => null,
                        "phones" => [],
                        "email" => "mtompkins@rightway.com",
                        "phone" => "12342317398",
                    ],
                    "payment" => [
                        "total_carrier_amount" => 300,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 300,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 10,
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
        $this->createMakes("GMC")
            ->createStates("FL", "VA", "NONE")
            ->createTimeZones("32124", "23009", "NONE")
            ->assertParsing(
                30,
                [
                    "load_id" => "RF102794",
                    "pickup_date" => "02/21/2023",
                    "delivery_date" => "02/21/2023",
                    "dispatch_instructions" => "-VERIFY VINS PRIOR TO LOADING; if VINs do not match, contact BARCO immediately."
                        . " -Must do a thorough inspection including taking photos prior to loading & after unloading (including"
                        . " ROOF). -Trucks must be hauled, CANNOT be driven. -Load/vehicles CANNOT BE SUBLET/BROKERED to another"
                        . " carrier. Trucks may only be transported by above named carrier. -Contact DELIVERY persons at least"
                        . " 1 hour prior to drop of vehicle(s) unless advised otherwise. -Should you be delayed, contact delivery"
                        . " persons and Barco immediately. -Unresolved transport damage MAY be subject to legal action being"
                        . " taken against carrier. You can request the form to get paid through ACH by emailing achrequests@barcotrucks.com"
                        . " Please make sure to send signed BOL and invoice to dispatch@barcotrucks.com Payment: Processed"
                        . " 5 Busin. days after receiving signed BOL Truck Valuations: RAM 1500 - $52775.00, RAM 2500 - $63369.00"
                        . " , RAM 3500 - $67411.00",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "GMC",
                            "model" => "Sierra 2500HD",
                            "vin" => "1GT19PEYXPF138234",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Commercial Accounts. Manheim Daytona Beach",
                        "state" => "FL",
                        "city" => "Daytona Beach",
                        "address" => "1305 Indian Lake Rd",
                        "zip" => "32124",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13862552500",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Carter Weedon. Prodigy Telecom, LLC",
                        "state" => "VA",
                        "city" => "Aylett",
                        "address" => "779 Anne Lane",
                        "zip" => "23009",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18046879490",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Barco Rent-A-Truck",
                        "address" => "717 S 5600 W",
                        "city" => null,
                        "state" => null,
                        "zip" => null,
                        "phones" => [],
                        "email" => "dispatch@barcotrucks.com",
                        "phone" => "18014190407",
                    ],
                    "payment" => [
                        "total_carrier_amount" => 500,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 500,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 5,
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
    public function test_31(): void
    {
        $this->createMakes("RAM")
            ->createStates("AZ", "UT", "NONE")
            ->createTimeZones("85297", "84104", "NONE")
            ->assertParsing(
                31,
                [
                    "load_id" => "sw102890",
                    "pickup_date" => "03/02/2023",
                    "delivery_date" => "03/04/2023",
                    "dispatch_instructions" => "-VERIFY VINS PRIOR TO LOADING; if VINs do not match, contact BARCO immediately."
                        . " -Must do a thorough inspection including taking photos prior to loading & after unloading (including"
                        . " ROOF). -Trucks must be hauled, CANNOT be driven. -Load/vehicles CANNOT BE SUBLET/BROKERED to another"
                        . " carrier. Trucks may only be transported by above named carrier. -Contact DELIVERY persons at least"
                        . " 1 hour prior to drop of vehicle(s) unless advised otherwise. -Should you be delayed, contact delivery"
                        . " persons and Barco immediately. -Unresolved transport damage MAY be subject to legal action being"
                        . " taken against carrier. You can request the form to get paid through ACH by emailing achrequests@barcotrucks.com"
                        . " Please make sure to send signed BOL and invoice to dispatch@barcotrucks.com Payment: Processed"
                        . " 5 Busin. days after receiving signed BOL Truck Valuations: RAM 1500 - $52775.00, RAM 2500 - $63369.00"
                        . " , RAM 3500 - $67411.00",
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "Ram",
                            "model" => "Ram Pickup 1500 Pickup",
                            "vin" => "1C6SRFJT9MN816683",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Dewitt. Bill Luke",
                        "state" => "AZ",
                        "city" => "Gilbert",
                        "address" => "1358 E Motorplex Loop Building 1",
                        "zip" => "85297",
                        "phones" => [
                            [
                                "number" => "14805304018",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "15202650350",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Jake, Nate, Justin, Mike. Barco Rent-A-Truck",
                        "state" => "UT",
                        "city" => "Salt Lake City",
                        "address" => "717 South 5600 West",
                        "zip" => "84104",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18014190407",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Barco Rent-A-Truck",
                        "address" => "717 S 5600 W",
                        "city" => null,
                        "state" => null,
                        "zip" => null,
                        "phones" => [],
                        "email" => "dispatch@barcotrucks.com",
                        "phone" => "18014190407",
                    ],
                    "payment" => [
                        "total_carrier_amount" => 700,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 700,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 5,
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
        $this->createMakes("FORD", "HYUNDAI")
            ->createStates("TN", "GA", "NONE")
            ->createTimeZones("37138", "31602", "NONE")
            ->assertParsing(
                32,
                [
                    "load_id" => "CM2301816",
                    "pickup_date" => "02/28/2023",
                    "delivery_date" => "03/01/2023",
                    "dispatch_instructions" => "Pickup: Please give 24hr notice before arriving.\nDelivery: Driver must"
                        . " call in advance before pick up and delivery. Drop keys in drop box, and have a manager sign BOL.",
                    "vehicles" => [
                        [
                            "year" => "2015",
                            "make" => "Ford",
                            "model" => "Escape",
                            "vin" => "1FMCU0JXXFUB77679",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2013",
                            "make" => "Ford",
                            "model" => "Focus",
                            "vin" => "1FADP3F24DL230612",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2012",
                            "make" => "Hyundai",
                            "model" => "Genesis",
                            "vin" => "KMHGC4DD5CU154884",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Core Scruggs. Nashville, I Finance",
                        "state" => "TN",
                        "city" => "Old Hickory",
                        "address" => "631 Burnett Rd",
                        "zip" => "37138",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19012385803",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Justin Moseley. Valdosta GA Car-Mart",
                        "state" => "GA",
                        "city" => "Valdosta",
                        "address" => "3270 N. Valdosta Rd.",
                        "zip" => "31602",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12292530370",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "KBECKS TRANSPORT",
                        "address" => "362 Daine Drive",
                        "city" => null,
                        "state" => null,
                        "zip" => null,
                        "phones" => [],
                        "email" => "kbeckstransport@gmail.com",
                        "phone" => "16145809442",
                    ],
                    "payment" => [
                        "total_carrier_amount" => 1100,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 1100,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
        $this->createMakes("GMC", "TOYOTA", "HYUNDAI", "JEEP")
            ->createStates("MN", "MO", "TX")
            ->createTimeZones("55369", "64055", "77070")
            ->assertParsing(
                33,
                [
                    "load_id" => "EF-4723D",
                    "pickup_date" => "02/27/2023",
                    "delivery_date" => "02/28/2023",
                    "dispatch_instructions" => "Pickup: Carrier MUST contact Pick up and Delivery Locations for HOURS of"
                        . " OPERATION and CONFIRM THAT THE VEHICLE IS READY FOR TRANSPORT. NO DRY RUN FEES WILL BE PAID. Carrier"
                        . " MUST use the SUPER DISPATCH APP for all Expedited Freight loads. Carrier MUST take PICTURES (ALL"
                        . " ANGLES, including VIN NUMBER) of vehicle at Pick up and again at Delivery with keys If carrier"
                        . " does not pick up on the assigned pick up date, and there is no communication, the dispatch will"
                        . " be canceled and reassigned to another carrier.\nDelivery: Closed Sunday, Mon.- Thur. 11am-7 pm,"
                        . " Friday 10 am-8pm, Saturday 9am-6pm",
                    "vehicles" => [
                        [
                            "year" => "2018",
                            "make" => "GMC",
                            "model" => "Terrain",
                            "vin" => "3GKALMEV9JL360447",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2016",
                            "make" => "Toyota",
                            "model" => "Camry",
                            "vin" => "4T1BF1FK4GU173654",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2014",
                            "make" => "GMC",
                            "model" => "Terrain",
                            "vin" => "2GKFLWEK8E6282440",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2016",
                            "make" => "Hyundai",
                            "model" => "VELOSTER",
                            "vin" => "KMHTC6AE7GU283013",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2014",
                            "make" => "Jeep",
                            "model" => "Cherokee",
                            "vin" => "1C4PJLCB9EW249217",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Manager. Manheim Minneapolis",
                        "state" => "MN",
                        "city" => "Osseo",
                        "address" => "8001 Jefferson Hwy",
                        "zip" => "55369",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17633155600",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "manager. 16681 Kansas City RC",
                        "state" => "MO",
                        "city" => "Independence",
                        "address" => "3151 S Noland Rd",
                        "zip" => "64055",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18168597589",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Expedited Freight LLC",
                        "address" => "12110 silver creek dr",
                        "city" => "Houston",
                        "state" => "TX",
                        "zip" => "77070",
                        "phones" => [],
                        "email" => "expeditedfreightco@gmail.com",
                        "phone" => "13252802601",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 1650,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 1650,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 20,
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
        $this->createMakes("FORD")
            ->createStates("OK", "AR", "MO")
            ->createTimeZones("74701", "72032", "64105")
            ->assertParsing(
                34,
                [
                    "load_id" => "F954331",
                    "pickup_date" => "03/06/2023",
                    "delivery_date" => "03/08/2023",
                    "dispatch_instructions" => "Pickup: Gate release code: 33467Y. Pickup instructions: ***Please call ahead"
                        . " for smooth pickup*** Vehicles will not be released if no call or advanced notice before arrival"
                        . " is given\nDelivery: Drop off instructions:\nDry run fees (DRF) will only be paid if the vehicle"
                        . " availability has been confirmed by the carrier along with the customer's name who provided confirmation."
                        . " UNATTENDED DROPS It is MANDATORY that we receive pictures for ALL unattended deliveries including"
                        . " pictures of the vehicle location, pictures of all 4 angles of car (front, back, and both sides)"
                        . " and pictures of key location.",
                    "vehicles" => [
                        [
                            "year" => "2012",
                            "make" => "Ford",
                            "model" => "Expedition el",
                            "vin" => "1FMJK1K52CEF12746",
                            "color" => "White",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Dylan Dixon. Car-Mart",
                        "state" => "OK",
                        "city" => "Durant",
                        "address" => "376 Bryan Dr,",
                        "zip" => "74701",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14792082647",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Daniel Burge. Larry Walters Auto Sales",
                        "state" => "AR",
                        "city" => "Conway",
                        "address" => "10 Ranchette Rd",
                        "zip" => "72032",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15014726207",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "BacklotCars Inc",
                        "address" => "1100 Main St #1500",
                        "city" => "Kansas City",
                        "state" => "MO",
                        "zip" => "64105",
                        "phones" => [],
                        "email" => "transport@backlotcars.com",
                        "phone" => "18162988222",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 340,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 340,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
        $this->createMakes("BUICK")
            ->createStates("OH", "NC", "FL")
            ->createTimeZones("45807", "28214", "33928")
            ->assertParsing(
                35,
                [
                    "load_id" => "FP30202010",
                    "pickup_date" => "02/06/2023",
                    "delivery_date" => "02/08/2023",
                    "dispatch_instructions" => "Delivery: M-F: 7a-3p\nFor questions, contact Molly at 239-301-7087 Molly.Sutphen@hertz.com",
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "Buick",
                            "model" => "Encore",
                            "vin" => "KL4CJASB4KB934470",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Ally Financial",
                        "state" => "OH",
                        "city" => "Lima",
                        "address" => "2200 N Cable Rd",
                        "zip" => "45807",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Aleyda Conklin. HERTZ CHARLOTTE AP DS (01880-50)",
                        "state" => "NC",
                        "city" => "CHARLOTTE",
                        "address" => "6515 RACKHAM DR BLDG B",
                        "zip" => "28214",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17043591219",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "The Hertz Corporation",
                        "address" => "8501 Williams Rd",
                        "city" => "Estero",
                        "state" => "FL",
                        "zip" => "33928",
                        "phones" => [],
                        "email" => "dispatch@hertz.com",
                        "phone" => "12393017027",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 950,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 950,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
        $this->createMakes("GMC")
            ->createStates("MS", "IL", "MO")
            ->createTimeZones("38804", "61842", "64105")
            ->assertParsing(
                36,
                [
                    "load_id" => "J045204",
                    "pickup_date" => "03/06/2023",
                    "delivery_date" => "03/07/2023",
                    "dispatch_instructions" => "Pickup: Gate release code: 4H432K. Pickup instructions:\nDelivery: Drop"
                        . " off instructions: IF DROPPING AFTER HOURS PLEASE LEAVE KEYS UNDERNEATH DRIVER SEAT\nDry run fees"
                        . " (DRF) will only be paid if the vehicle availability has been confirmed by the carrier along with"
                        . " the customer's name who provided confirmation. UNATTENDED DROPS It is MANDATORY that we receive"
                        . " pictures for ALL unattended deliveries including pictures of the vehicle location, pictures of"
                        . " all 4 angles of car (front, back, and both sides) and pictures of key location.",
                    "vehicles" => [
                        [
                            "year" => "2017",
                            "make" => "Gmc",
                            "model" => "Acadia",
                            "vin" => "1GKKNMLS5HZ253633",
                            "color" => "White",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Barnes Crossing Volkswagen",
                        "state" => "MS",
                        "city" => "Tupelo",
                        "address" => "3973 N Gloster St",
                        "zip" => "38804",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Mike Noe. Country Motors",
                        "state" => "IL",
                        "city" => "Farmer City",
                        "address" => "101 Summer Dr.",
                        "zip" => "61842",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12175305094",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "BacklotCars Inc",
                        "address" => "1100 Main St #1500",
                        "city" => "Kansas City",
                        "state" => "MO",
                        "zip" => "64105",
                        "phones" => [],
                        "email" => "transport@backlotcars.com",
                        "phone" => "18162988222",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 461,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 461,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
        $this->createMakes("CHEVROLET")
            ->createStates("MA", "IL", "NONE")
            ->createTimeZones("02128", "61081", "NONE")
            ->assertParsing(
                37,
                [
                    "load_id" => "K30958",
                    "pickup_date" => "03/09/2023",
                    "delivery_date" => "03/10/2023",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "Chevrolet",
                            "model" => "Malibu",
                            "vin" => "1G1ZD5STXLF094611",
                            "color" => "Gray",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Dan Hunt. Hertz Boston",
                        "state" => "MA",
                        "city" => "Boston",
                        "address" => "450 William F McClellan Hwy",
                        "zip" => "02128",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16179137320",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Trisha Renji. 11 Kunes Sterling CDJR",
                        "state" => "IL",
                        "city" => "Sterling",
                        "address" => "3200 East Lincolnway",
                        "zip" => "61081",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12627452020",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Kunes Logistics",
                        "address" => "1114 Ann Street, Suite 2",
                        "city" => null,
                        "state" => null,
                        "zip" => null,
                        "phones" => [],
                        "email" => "jim.miletta@kunes.com",
                        "phone" => "12625813641",
                    ],
                    "payment" => [
                        "total_carrier_amount" => 975,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 975,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 5,
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
    public function test_38(): void
    {
        $this->createMakes("RAM")
            ->createStates("TX", "NY", "CO")
            ->createTimeZones("77388", "13039", "81082")
            ->assertParsing(
                38,
                [
                    "load_id" => "K7039",
                    "pickup_date" => "02/27/2023",
                    "delivery_date" => "03/02/2023",
                    "dispatch_instructions" => "Pickup: MON-FRI 9:30-5 MUST CALL AHEAD TO CONFIRM HOURS AND ETA.\nDelivery:"
                        . " MON-FRI 7-4:30. MUST CALL AHEAD TO CONFIRM HOURS AND ETA.\nMUST USE SUPER DISPATCH APP AND TAKE"
                        . " PICS AT PICK UP AND DELIVERY. MUST RECEIVE SIGNED BOL AT DELIVERY FOR PAYMENT TO BE ISSUED. \"STI\""
                        . " WILL NOT BE ACCEPTABLE FOR ANY LOADS. NO NIGHT DROP WITHOUT PRIOR APPROVAL FROM KEYCO LOGISTICS."
                        . " PAYMENT BY ACH OR MAIL.",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "Ram",
                            "model" => "2500",
                            "vin" => "3C6UR5DJ6PG542983",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2023",
                            "make" => "Ram",
                            "model" => "2500",
                            "vin" => "3C6UR5DJ1PG542969",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "JOEL NOYOLA. AutoNation CDJR of Spring",
                        "state" => "TX",
                        "city" => "Spring",
                        "address" => "21027 I-45 North",
                        "zip" => "77388",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18327646539",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "ART MCMANUS. RESA OF CICERO",
                        "state" => "NY",
                        "city" => "Cicero",
                        "address" => "6268 ROUTE 31",
                        "zip" => "13039",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13154028566",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Keyco Logistics LLC",
                        "address" => "PO BOX 820",
                        "city" => "TRINIDAD",
                        "state" => "CO",
                        "zip" => "81082",
                        "phones" => [],
                        "email" => "zach@keycologistics.com",
                        "phone" => "17198463566",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 2450,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 2450,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 5,
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
        $this->createMakes("RAM")
            ->createStates("TX", "CA", "NONE")
            ->createTimeZones("75093", "92707", "NONE")
            ->assertParsing(
                39,
                [
                    "load_id" => "KT2035",
                    "pickup_date" => "03/06/2023",
                    "delivery_date" => "03/08/2023",
                    "dispatch_instructions" => "Pickup: PICKUP HOURS M-F 8AM TO 5PM. IF AFTER 5PM YOU MUST GET APPROVAL"
                        . " FROM GREG. MUST TAKE PICTURES ON PICKUP AND ON DELIVERY MUST HAVE SIGNATURES ON PICKUP ANDO ON"
                        . " DELIVERY\nDelivery: MUST DELIVER BETWEEN 8AM TO 4:30PM M-F ONLY NO WEEKEND DELIVERIES NO AFTER"
                        . " HOUR DELIVERIES NO NIGHT DROPS",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Ram",
                            "model" => "ProMaster",
                            "vin" => "3C6LRVDG2NE128222",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2022",
                            "make" => "Ram",
                            "model" => "ProMaster",
                            "vin" => "3C6LRVDG0NE135153",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "GREG HAMBY. HUFFINES DODGE-GREG",
                        "state" => "TX",
                        "city" => "Plano",
                        "address" => "4500 PLANO PARKWAY DR",
                        "zip" => "75093",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14693606805",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "DAVID WADE. HOSPICE SOURCE - SANTA ANA",
                        "state" => "CA",
                        "city" => "Santa Ana",
                        "address" => "115 E ALTON AVE",
                        "zip" => "92707",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17145041903",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Kathy's Transport",
                        "address" => "2722 Covington Drive",
                        "city" => null,
                        "state" => null,
                        "zip" => null,
                        "phones" => [],
                        "email" => "kathystransport@yahoo.com",
                        "phone" => "97236535739032297645",
                    ],
                    "payment" => [
                        "total_carrier_amount" => 3000,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 3000,
                        "broker_payment_method_id" => 9,
                        "broker_payment_method" => [
                            "id" => 9,
                            "title" => "QuickPay",
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
        $this->createMakes("ALFA")
            ->createStates("FL", "MO", "MO")
            ->createTimeZones("32505", "65714", "65714")
            ->assertParsing(
                40,
                [
                    "load_id" => "MDRN139",
                    "pickup_date" => "02/27/2023",
                    "delivery_date" => "02/28/2023",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Alfa",
                            "model" => "Romeo Stelvio",
                            "vin" => "ZASPAKEV9N7D47537",
                            "color" => "Gray",
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Alfa Romeo",
                        "state" => "FL",
                        "city" => "Pensacola",
                        "address" => "5600 Pensacola Blvd",
                        "zip" => "32505",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Don Hunsaker. Modern Motorcars",
                        "state" => "MO",
                        "city" => "Nixa",
                        "address" => "1856 N. Deffer Dr.",
                        "zip" => "65714",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14178488298",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Hunsaker Enterprises LLC dba Modern Motorcars",
                        "address" => "1856 N DEFFER DRIVE",
                        "city" => "NIXA",
                        "state" => "MO",
                        "zip" => "65714",
                        "phones" => [],
                        "email" => "don@modernmotorcars.com",
                        "phone" => "14178488298",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 700,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 700,
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
    public function test_41(): void
    {
        $this->createMakes("FORD")
            ->createStates("KY", "TX", "NONE")
            ->createTimeZones("40222", "77507", "NONE")
            ->assertParsing(
                41,
                [
                    "load_id" => "NEF35580",
                    "pickup_date" => "02/25/2023",
                    "delivery_date" => "02/27/2023",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Ford",
                            "model" => "F-250 Super Duty Picku",
                            "vin" => "1FDBF2B67NEF35580",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "DAVID BABB. OXMOOR FORD DEALERSHIP",
                        "state" => "KY",
                        "city" => "Lyndon",
                        "address" => "100 OXMOOR LN",
                        "zip" => "40222",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "15023793384",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Zach Parnell. 4-HORN POWER & HVAC LLC",
                        "state" => "TX",
                        "city" => "Pasadena",
                        "address" => "8003 RED BLUFF RD",
                        "zip" => "77507",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13188202516",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Interactive Dealer Network",
                        "address" => "1122 Marshall street Suite A",
                        "city" => null,
                        "state" => null,
                        "zip" => null,
                        "phones" => [],
                        "email" => "zach@dealir.com",
                        "phone" => "13188202516",
                    ],
                    "payment" => [
                        "total_carrier_amount" => 700,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 700,
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
        $this->createMakes("RAM")
            ->createStates("TN", "TX", "TX")
            ->createTimeZones("38116", "77471", "75071")
            ->assertParsing(
                42,
                [
                    "load_id" => "ODT-15046",
                    "pickup_date" => "02/28/2023",
                    "delivery_date" => "03/01/2023",
                    "dispatch_instructions" => "Delivery: MUST call to make arrangements for delivery. Absolutely NO deliveries"
                        . " after 7pm. Client MUST sign for the vehicle.",
                    "vehicles" => [
                        [
                            "year" => "2017",
                            "make" => "Ram",
                            "model" => "ProMaster City",
                            "vin" => "ZFBERFBB9H6E64254",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Cynthia. Enterprise",
                        "state" => "TN",
                        "city" => "Memphis",
                        "address" => "3655 Airways Blvd.",
                        "zip" => "38116",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19013482784",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Chase Moehlman. Name: Chase Moehlman",
                        "state" => "TX",
                        "city" => "Rosenberg",
                        "address" => "2619 Diamond River Dr",
                        "zip" => "77471",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12343040938",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "ODT, LLC",
                        "address" => "P.O. Box 6641",
                        "city" => "McKinney",
                        "state" => "TX",
                        "zip" => "75071",
                        "phones" => [],
                        "email" => "trish@odtransport.com",
                        "phone" => "19728331602",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 700,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 700,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 10,
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
        $this->createMakes("RAM")
            ->createStates("KS", "GA", "OH")
            ->createTimeZones("66211", "31743", "43064")
            ->assertParsing(
                43,
                [
                    "load_id" => "QN-00897",
                    "pickup_date" => "03/01/2023",
                    "delivery_date" => "03/02/2023",
                    "dispatch_instructions" => "Pickup: pickup confirmed - M-F 9a-3p (by appointment only) REVISIONS Feb"
                        . " 28, 2023 at 2:31 PM Vehicle Field Old Val Make RAM Model 1500, B Feb 28, 2023 at 2:31 PM Veh Year"
                        . " 202 VIN 1C6 Type Pic Lot Number G73 Feb 28, 2023 at 2:31 PM Veh Field Old Make Model Year VIN"
                        . " Type Lot Number\nDelivery: delivery confirmed - M-F 8AM to 4PM Deleted Updated by Shipper ue New"
                        . " Value ig Horn Crew Cab icle Deleted Updated by Shipper 2 SRFFT2NN466842 kup 423 icle Added Updated"
                        . " by Shipper Value New Value RAM 1500, Big Horn Crew Cab 2022 1C6SRFFT2NN466842 Pickup G73423",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "RAM",
                            "model" => "1500, Big Horn Crew Cab",
                            "vin" => "1C6SRFFT2NN466842",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Tammy Evans, Marcus Brennon. P-Building – Black & Veatch",
                        "state" => "KS",
                        "city" => "Overland Park",
                        "address" => "11401 Lamar Avenue",
                        "zip" => "66211",
                        "phones" => [
                            [
                                "number" => "19134584983",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "19134584546",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "KENNETH FISHER, null. Construction Site",
                        "state" => "GA",
                        "city" => "DESOTO",
                        "address" => "543 DAN GREEN ROAD GA",
                        "zip" => "31743",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "13048154455",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Flexco",
                        "address" => "9200 Memorial Dr.",
                        "city" => "Plain City",
                        "state" => "OH",
                        "zip" => "43064",
                        "phones" => [],
                        "email" => "transport@flxfleet.com",
                        "phone" => "16143895771",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 1050,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 1050,
                        "broker_payment_method_id" => 9,
                        "broker_payment_method" => [
                            "id" => 9,
                            "title" => "QuickPay",
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
    public function test_44(): void
    {
        $this->createMakes("FORD")
            ->createStates("NJ", "OH", "UT")
            ->createTimeZones("07068", "43123", "84047")
            ->assertParsing(
                44,
                [
                    "load_id" => "RCOL22205",
                    "pickup_date" => "02/24/2023",
                    "delivery_date" => "02/25/2023",
                    "dispatch_instructions" => "Pickup: Mon-Fri 8am-4:30pm\nDelivery: GATE PASS WILL BE AT THE GAURD SHACK"
                        . " UNDER YOUR COMPANY NAME, PLEASE CHECK IN UNDER ACCT 4999028",
                    "vehicles" => [
                        [
                            "year" => "2021",
                            "make" => "Ford",
                            "model" => "F-150",
                            "vin" => "1FTFW1E50MFB96124",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Anthony Talamo. Dish Wireless L.L.C.",
                        "state" => "NJ",
                        "city" => "Roseland",
                        "address" => "3 ADP Blvd",
                        "zip" => "07068",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19732891123",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Manheim Ohio Drop",
                        "state" => "OH",
                        "city" => "Grove City",
                        "address" => "3905 JACKSON PIKE",
                        "zip" => "43123",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Flex Fleet Rental",
                        "address" => "6975 S. Union Park Center, Suite 500",
                        "city" => "Salt Lake City",
                        "state" => "UT",
                        "zip" => "84047",
                        "phones" => [],
                        "email" => "transportation@flexfleetrental.com",
                        "phone" => "18017028216",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 530,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 530,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 10,
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
    public function test_45(): void
    {
        $this->createMakes("TOYOTA")
            ->createStates("AL", "OH", "CT")
            ->createTimeZones("35022", "43460", "06116")
            ->assertParsing(
                45,
                [
                    "load_id" => "S31559",
                    "pickup_date" => "02/22/2023",
                    "delivery_date" => "02/23/2023",
                    "dispatch_instructions" => "Pickup: M-F 8am to 5pm. NO NIGHT OR WEEKEND DELIVERIES! Handicap Accessible"
                        . " Unit\nDelivery: HOURS: Monday-Friday 8am to 5pm. After hours drops okay, MUST PUT KEYS IN DROP"
                        . " BOX located at the front door. After hours drops are subject to inspection. Handicap Accessible"
                        . " Vehicle.",
                    "vehicles" => [
                        [
                            "year" => "2011",
                            "make" => "Toyota",
                            "model" => "Sienna",
                            "vin" => "5TDXK3DC4BS056134",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Robert Lunsford. Birmingham Mobility Works",
                        "state" => "AL",
                        "city" => "Bessemer",
                        "address" => "3747 Pine Lane SE",
                        "zip" => "35022",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12054268261",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Joshua Huner. Toledo Mobility Works",
                        "state" => "OH",
                        "city" => "Rossford",
                        "address" => "9675 S Compass Dr.",
                        "zip" => "43460",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14194764890",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Mobility Works",
                        "address" => "104 Pitkin St",
                        "city" => "East Hartford",
                        "state" => "CT",
                        "zip" => "06116",
                        "phones" => [],
                        "email" => "transportation.dept@mobilityworks.com",
                        "phone" => "16035525721",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 700,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 700,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 10,
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
        $this->createMakes("MERCEDES-BENZ")
            ->createStates("TX", "OH", "MO")
            ->createTimeZones("76442", "45177", "64133")
            ->assertParsing(
                46,
                [
                    "load_id" => "SD-2171",
                    "pickup_date" => "01/11/2023",
                    "delivery_date" => "01/12/2023",
                    "dispatch_instructions" => "READ TERMS AND CONDITIONS. SIGNATURE REQUIRED - NO NIGHT DROPS - CALL BEFORE"
                        . " ARRIVAL - NO DRY RUN FEES CONTACT DISPATCHER WITH ANY ISSUES",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Mercedes-Benz",
                            "model" => "Sprinter 170 WB",
                            "vin" => "W1Y4ECVYXNT095980",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2022",
                            "make" => "Mercedes-Benz",
                            "model" => "Sprinter 170 WB",
                            "vin" => "W1Y4ECVY1NT097696",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "SERVS",
                        "state" => "TX",
                        "city" => "Comanche",
                        "address" => "309 FM 3381",
                        "zip" => "76442",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18663562236",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Bush Specialty Vehicles",
                        "state" => "OH",
                        "city" => "Wilmington",
                        "address" => "80 Park Drive",
                        "zip" => "45177",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19373825502",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Nations Auto Transport LLC",
                        "address" => "12032 E 46th Ter",
                        "city" => "Kansas City",
                        "state" => "MO",
                        "zip" => "64133",
                        "phones" => [],
                        "email" => "support@shipnat.com",
                        "phone" => "18162956883",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 2800,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 2800,
                        "broker_payment_method_id" => 9,
                        "broker_payment_method" => [
                            "id" => 9,
                            "title" => "QuickPay",
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
        $this->createMakes("TOYOTA")
            ->createStates("IL", "MD", "MD")
            ->createTimeZones("60525", "21133", "21784")
            ->assertParsing(
                47,
                [
                    "load_id" => "TA-287",
                    "pickup_date" => "03/01/2023",
                    "delivery_date" => "03/02/2023",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "Toyota",
                            "model" => "Sequoia",
                            "vin" => "7SVAAABA2PX007651",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Mostafa Arta. Continental Toyota",
                        "state" => "IL",
                        "city" => "La Grange",
                        "address" => "6701 South La Grange Road",
                        "zip" => "60525",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17083156243",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Andrey. Andrey",
                        "state" => "MD",
                        "city" => "Randallstown",
                        "address" => "9325 Liberty rd",
                        "zip" => "21133",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12027090502",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Rosental Auto Group",
                        "address" => "1551 West old liberty rd",
                        "city" => "Sykesville",
                        "state" => "MD",
                        "zip" => "21784",
                        "phones" => [],
                        "email" => "roman@trustauto.com",
                        "phone" => "14437636554",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 600,
                        "terms" => null,
                        "customer_payment_amount" => 600,
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
    public function test_48(): void
    {
        $this->createMakes("RIVIAN")
            ->createStates("IL", "FL", "KY")
            ->createTimeZones("61761", "32824", "40504")
            ->assertParsing(
                48,
                [
                    "load_id" => "TR-9810-36045-1",
                    "pickup_date" => "12/21/2022",
                    "delivery_date" => "12/23/2022",
                    "dispatch_instructions" => "Delivery: Check in under : LEASE PLAN DEALER # 4993412\nFOR AMAZON UNITS"
                        . " PLEASE FOLLOW THE INSTRUCTIONS AT PICKUP AND DELIVERY SO THERE'S NO DELAY IN PAYMENT. CALL AHEAN"
                        . " TO SCHEDULE PICK UPS AT LEAST 24 HOURS BEFORE SCHEDULED PICK UP. MUST CALL AHEAD TO SCHEDULE PICK"
                        . " UP AND DELIVER BEFORE YOU SEND A DRIVER. WE DO NOT PAY DRY RUNS. IF THERE'S AN UNFORSEEABLE ISSUES"
                        . " WITH A UNIT THEN OUR MGMT WILL CONTACT YOU TO DISCUSS AND APPROVE ANY CHARGES. ON PICK UP TAKE"
                        . " A MINUMUM OF 6 PICTURES OF THE OUTSIDE , ONE OF THE DASH SHOWING MILEAGE, FIRE EXTINGUSHER AND"
                        . " DOLLY. IF THERE IS AN ISSUE WITH ANY VEHICLE THEN PLEASE NOTIFY US BEFORE LEAVING THE LOT. WE"
                        . " ARE ON THE PHONE ALL DAY SO PLEASE MAKE SURE TO LEAVE A MESSAGE OR SEND AN EMAIL. REPEAT THE PICTURES"
                        . " AT DELIVERY. THIS WILL TOTAL UP TO 9 PICTURES ONCE THE CHECKLIST IS COMPLETED AND SIGNED PLEASE"
                        . " ATTACHED AT THE BOTTOM OR YOUR ORDER USING THE ATTACH DOC FEATURE. DELIVERY LOCATIONS REQUIRE"
                        . " 24 HOURS AND 1 HOUR NOTICE. THESE INSUTRUCTIONS ARE TO HELP OUR CARRIERS CLEAR UP ANY KIND OF"
                        . " OBSTACLES BEFORE THEY HAPPEN. OUR OFFICE IS CLOSED AT NIGHT AND ON THE WEEKENDS. WE CHECK OUR"
                        . " MESSAGES AT THOSE HOURS BUT GENERALLY ITS HARD TO CLEAR ANY ISSUES AFTER HOURS. THANK YOU AND"
                        . " WE LOOK FORWARD TO WORKING WITH YOU IN THE FUTURE. MAKE SURE TO SEND YOUR INVOICES THRU SUPERDISPATC."
                        . " WHEN PAYMENT IS ISSUED WE WILL MARK THE ORDER AS PAID ON SUPERDISPATCH. IF YOU HAVE ANY QUESTIONS"
                        . " PLEASE REACH OUT TO AP@TOTALAUTOMOVERS.COM AND NOT OUR DISPATCH LINE.",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "RIVIAN",
                            "model" => "ELECTRIC DELIVERY VEHICLE",
                            "vin" => "7FCEHDB77NN004180",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Rivian LLC",
                        "state" => "IL",
                        "city" => "Normal",
                        "address" => "2450 Electric Ave",
                        "zip" => "61761",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Check in under : LEASE PLAN. DEALER # 4993412 MANHEIM CENTRAL FLORIDA",
                        "state" => "FL",
                        "city" => "Orlando",
                        "address" => "1240 W. Landstreet Road",
                        "zip" => "32824",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Total Auto Brokerage Inc Order Dispatch Sheet ID: TR-9810-36045-1",
                        "address" => "790 Westland Dr",
                        "city" => "Lexington",
                        "state" => "KY",
                        "zip" => "40504",
                        "phones" => [],
                        "email" => "dispatch@totalautomovers.com",
                        "phone" => "18596672379",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 2100,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 2100,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 15,
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
    public function test_49(): void
    {
        $this->createMakes("RIVIAN")
            ->createStates("IL", "NV", "KY")
            ->createTimeZones("61761", "89052", "40504")
            ->assertParsing(
                49,
                [
                    "load_id" => "TR-9810-36666-1",
                    "pickup_date" => "12/08/2022",
                    "delivery_date" => "12/11/2022",
                    "dispatch_instructions" => "FOR AMAZON UNITS PLEASE FOLLOW THE INSTRUCTIONS AT PICKUP AND DELIVERY SO"
                        . " THERE'S NO DELAY IN PAYMENT. CALL AHEAN TO SCHEDULE PICK UPS AT LEAST 24 HOURS BEFORE SCHEDULED"
                        . " PICK UP. MUST CALL AHEAD TO SCHEDULE PICK UP AND DELIVER BEFORE YOU SEND A DRIVER. WE DO NOT PAY"
                        . " DRY RUNS. IF THERE'S AN UNFORSEEABLE ISSUES WITH A UNIT THEN OUR MGMT WILL CONTACT YOU TO DISCUSS"
                        . " AND APPROVE ANY CHARGES. ON PICK UP TAKE A MINUMUM OF 6 PICTURES OF THE OUTSIDE , ONE OF THE DASH"
                        . " SHOWING MILEAGE, FIRE EXTINGUSHER AND DOLLY. IF THERE IS AN ISSUE WITH ANY VEHICLE THEN PLEASE"
                        . " NOTIFY US BEFORE LEAVING THE LOT. WE ARE ON THE PHONE ALL DAY SO PLEASE MAKE SURE TO LEAVE A MESSAGE"
                        . " OR SEND AN EMAIL. REPEAT THE PICTURES AT DELIVERY. THIS WILL TOTAL UP TO 9 PICTURES ONCE THE CHECKLIST"
                        . " IS COMPLETED AND SIGNED PLEASE ATTACHED AT THE BOTTOM OR YOUR ORDER USING THE ATTACH DOC FEATURE."
                        . " DELIVERY LOCATIONS REQUIRE 24 HOURS AND 1 HOUR NOTICE. THESE INSUTRUCTIONS ARE TO HELP OUR CARRIERS"
                        . " CLEAR UP ANY KIND OF OBSTACLES BEFORE THEY HAPPEN. OUR OFFICE IS CLOSED AT NIGHT AND ON THE WEEKENDS."
                        . " WE CHECK OUR MESSAGES AT THOSE HOURS BUT GENERALLY ITS HARD TO CLEAR ANY ISSUES AFTER HOURS. THANK"
                        . " YOU AND WE LOOK FORWARD TO WORKING WITH YOU IN THE FUTURE. MAKE SURE TO SEND YOUR INVOICES THRU"
                        . " SUPERDISPATC. WHEN PAYMENT IS ISSUED WE WILL MARK THE ORDER AS PAID ON SUPERDISPATCH. IF YOU HAVE"
                        . " ANY QUESTIONS PLEASE REACH OUT TO AP@TOTALAUTOMOVERS.COM AND NOT OUR DISPATCH LINE.",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "RIVIAN",
                            "model" => "ELECTRIC DELIVERY VEHICLE",
                            "vin" => "7FCEHDB7XNN004013",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Rivian LLC",
                        "state" => "IL",
                        "city" => "Normal",
                        "address" => "2450 Electric Ave",
                        "zip" => "61761",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Alex Vyborg. SDCS SDLA COURIER SE",
                        "state" => "NV",
                        "city" => "Henderson",
                        "address" => "11500 BERMUDA RD",
                        "zip" => "89052",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14243901676",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Total Auto Brokerage Inc Order Dispatch Sheet ID: TR-9810-36666-1",
                        "address" => "790 Westland Dr",
                        "city" => "Lexington",
                        "state" => "KY",
                        "zip" => "40504",
                        "phones" => [],
                        "email" => "dispatch@totalautomovers.com",
                        "phone" => "18596672379",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 3400,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 3400,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 15,
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
        $this->createMakes("RIVIAN")
            ->createStates("IL", "TX", "KY")
            ->createTimeZones("61761", "76033", "40504")
            ->assertParsing(
                50,
                [
                    "load_id" => "TR-9810-38566-3",
                    "pickup_date" => "01/10/2023",
                    "delivery_date" => "01/12/2023",
                    "dispatch_instructions" => "FOR AMAZON UNITS PLEASE FOLLOW THE INSTRUCTIONS AT PICKUP AND DELIVERY SO"
                        . " THERE'S NO DELAY IN PAYMENT. CALL AHEAN TO SCHEDULE PICK UPS AT LEAST 24 HOURS BEFORE SCHEDULED"
                        . " PICK UP. MUST CALL AHEAD TO SCHEDULE PICK UP AND DELIVER BEFORE YOU SEND A DRIVER. WE DO NOT PAY"
                        . " DRY RUNS. IF THERE'S AN UNFORSEEABLE ISSUES WITH A UNIT THEN OUR MGMT WILL CONTACT YOU TO DISCUSS"
                        . " AND APPROVE ANY CHARGES. ON PICK UP TAKE A MINUMUM OF 6 PICTURES OF THE OUTSIDE , ONE OF THE DASH"
                        . " SHOWING MILEAGE, FIRE EXTINGUSHER AND DOLLY. IF THERE IS AN ISSUE WITH ANY VEHICLE THEN PLEASE"
                        . " NOTIFY US BEFORE LEAVING THE LOT. WE ARE ON THE PHONE ALL DAY SO PLEASE MAKE SURE TO LEAVE A MESSAGE"
                        . " OR SEND AN EMAIL. REPEAT THE PICTURES AT DELIVERY. THIS WILL TOTAL UP TO 9 PICTURES ONCE THE CHECKLIST"
                        . " IS COMPLETED AND SIGNED PLEASE ATTACHED AT THE BOTTOM OR YOUR ORDER USING THE ATTACH DOC FEATURE."
                        . " DELIVERY LOCATIONS REQUIRE 24 HOURS AND 1 HOUR NOTICE. THESE INSUTRUCTIONS ARE TO HELP OUR CARRIERS"
                        . " CLEAR UP ANY KIND OF OBSTACLES BEFORE THEY HAPPEN. OUR OFFICE IS CLOSED AT NIGHT AND ON THE WEEKENDS."
                        . " WE CHECK OUR MESSAGES AT THOSE HOURS BUT GENERALLY ITS HARD TO CLEAR ANY ISSUES AFTER HOURS. THANK"
                        . " YOU AND WE LOOK FORWARD TO WORKING WITH YOU IN THE FUTURE. MAKE SURE TO SEND YOUR INVOICES THRU"
                        . " SUPERDISPATC. WHEN PAYMENT IS ISSUED WE WILL MARK THE ORDER AS PAID ON SUPERDISPATCH. IF YOU HAVE"
                        . " ANY QUESTIONS PLEASE REACH OUT TO AP@TOTALAUTOMOVERS.COM AND NOT OUR DISPATCH LINE.",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "RIVIAN",
                            "model" => "ELECTRIC DELIVERY VEHICLE",
                            "vin" => "7FCEHDB7XNN005050",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Rivian LLC",
                        "state" => "IL",
                        "city" => "Normal",
                        "address" => "2450 Electric Ave",
                        "zip" => "61761",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "PINNACLE DALLAS, TX",
                        "state" => "TX",
                        "city" => "Cleburne",
                        "address" => "3625 B NORTH MAIN ST",
                        "zip" => "76033",
                        "phones" => null,
                        "fax" => null,
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "Total Auto Brokerage Inc Order Dispatch Sheet ID: TR-9810-38566-3",
                        "address" => "790 Westland Dr",
                        "city" => "Lexington",
                        "state" => "KY",
                        "zip" => "40504",
                        "phones" => [],
                        "email" => "dispatch@totalautomovers.com",
                        "phone" => "18596672379",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 1600,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 1600,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 15,
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
        $this->createMakes("FORD")
            ->createStates("PA", "TN", "TX")
            ->createTimeZones("18657", "37174", "78704")
            ->assertParsing(
                51,
                [
                    "load_id" => "VIP - H4451397(#1 - #2)",
                    "pickup_date" => "03/06/2023",
                    "delivery_date" => "03/07/2023",
                    "dispatch_instructions" => "Pickup: PU M-F 7:30-2:30; call ahead 24 hrs ahead to make an appt DEL M-F"
                        . " 8-4; call ahead 24 hrs to make an appt *no mods or upfit, High Roof Extended Van 148\" WB\nDelivery:"
                        . " PU M-F 7:30-2:30; call ahead 24 hrs ahead to make an appt DEL M-F 8-4; call ahead 24 hrs to make"
                        . " an appt *no mods or upfit, High Roof Extended Van 148\" WB\nThis order requires you be on file"
                        . " with uShip Logistics LLC. To expedite and secure fast dispatch, please ensure you have your UL"
                        . " Carrier Packet: https://bit.ly/30Hnikx PAYMENT INSTRUCTIONS & REQUIREMENTS (2 DAY ACH!) ***DO"
                        . " NOT SHOW PRICE TO ANYONE AT PICK UP OR DELIVERY!! NO EXCEPTIONS, IF YOU DO, YOU ARE VOIDING FULL"
                        . " PAYMENT, WE WILL NOT BE PAYING YOU FOR THIS SHIPMENT, PLEASE BE ADVISED!*** 1) PLEASE provide"
                        . " enough notification for both pickup and delivery contacts. Make appointments! IF POSSIBLE, GIVE"
                        . " 24 HR AND 1 HR NOTICE ON BOTH ENDS!! NO EXCEPTIONS!!! WE DO NOT PAY DRY RUN FEES!!! CALL 512-200-1066"
                        . " BEFORE DOING ANYTHING OUT OF THE CONTRACT! (IF VEHICLE IS INOP, CALL 512-200-1066 BEFORE LOADING!!!!!)"
                        . " 2) Please take Clean pictures on all sides (oustide) and Inside at pick up AND delivery for insurance"
                        . " purposes. NO EXCEPTIONS! NO AFTER-HOUR PICK UP OR DROP-OFFS WITHOUT USHIP LOGISTICS APPROVAL!"
                        . " CALL 512-200-1066 3) Please obtain signatures on both pickup and delivery on a conditions report/BOL."
                        . " NO EXCEPTIONS!! 4) SEND: pictures & BOL & voided check/bank info to: Accounting@ushiplogistics.com"
                        . " FOR PAYMENT QUESTIONS: CALL (512) 537-3136 *FAILURE to do these requirements will result in a"
                        . " delay in payment to you! Please be advised!!! **IF there were any damages to this shipment, uShip"
                        . " Logistics reserves the right to withhold or modify payment to driver/carrier for 30 business days"
                        . " or more until the claim is resolved and depending on case by case. Damage claim repair estimates"
                        . " must come from a uShip Logistics approved collision center prior to proceeding** **IF there is"
                        . " evidence of sub-contracting or double brokering is grounds for uShip Logistics to modify or withhold"
                        . " payment to the assigned carrier! **IF the driver and or equipment is not operating under the authority"
                        . " to the assigned Carriers MC#, uShip Logistics has the right to modify or withhold payment!!**"
                        . " ****ANY DELAYS OR ANYTHING OUT OF THE CONTRACT, CALL OR EMAIL US, NO MATTER WHAT!!! WE NEED TO"
                        . " KNOW FIRST BEFORE ANYONE ELSE!! NO EXCEPTIONS!! WITHOUT CALLING OR NOTICIFYING US, WE WILL BE"
                        . " DEMANDING DISCOUNTS FOR ANYTHING OUTSIDE OF THE CONTRACT WITHOUT NOTICE/ CONFIRMATION FROM USHIP"
                        . " LOGISTICS!! PLEASE BE ADVISED!!!",
                    "vehicles" => [
                        [
                            "year" => "2023",
                            "make" => "Ford",
                            "model" => "Transit - HR Ext Van",
                            "vin" => "1FTBW3XG0PKA02308",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2023",
                            "make" => "Ford",
                            "model" => "Transit - HR Ext Van",
                            "vin" => "1FTBW3XG4PKA02344",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Tim Rogers. Premier Select Sires, Inc.",
                        "state" => "PA",
                        "city" => "Tunkhannock",
                        "address" => "1 Stony Mountain Rd",
                        "zip" => "18657",
                        "phones" => [
                            [
                                "number" => "15708363168",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "15703353576",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Tim Barnes. Southeast Select Sires",
                        "state" => "TN",
                        "city" => "Spring Hill",
                        "address" => "3789 N Old Port Royal Rd,",
                        "zip" => "37174",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "16157149919",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "USHIP LOGISTICS LLC H4451397(#1 - #2)",
                        "address" => "205 East Riverside Dr., Suite A",
                        "city" => "Austin",
                        "state" => "TX",
                        "zip" => "78704",
                        "phones" => [],
                        "email" => "dispatch@ushiplogistics.com",
                        "phone" => "17078465143",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 1750,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 1750,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 5,
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
        $this->createMakes("FORD")
            ->createStates("IL", "CA", "TX")
            ->createTimeZones("60050", "92879", "78704")
            ->assertParsing(
                52,
                [
                    "load_id" => "VIP - H4451437(#1 - #2)",
                    "pickup_date" => "03/03/2023",
                    "delivery_date" => "03/06/2023",
                    "dispatch_instructions" => "Pickup: PU M-F 8-5; call 24 hrs ahead to make an appt. DEL M-F 7-4; call"
                        . " 24 hrs ahead to make an appt. *no mods or upfit,\nDelivery: PU M-F 8-5; call 24 hrs ahead to make"
                        . " an appt. DEL M-F 7-4; call 24 hrs ahead to make an appt. *no mods or upfit,\nThis order requires"
                        . " you be on file with uShip Logistics LLC. To expedite and secure fast dispatch, please ensure you"
                        . " have your UL Carrier Packet: https://bit.ly/30Hnikx PAYMENT INSTRUCTIONS & REQUIREMENTS (2 DAY"
                        . " ACH!) ***DO NOT SHOW PRICE TO ANYONE AT PICK UP OR DELIVERY!! NO EXCEPTIONS, IF YOU DO, YOU ARE"
                        . " VOIDING FULL PAYMENT, WE WILL NOT BE PAYING YOU FOR THIS SHIPMENT, PLEASE BE ADVISED!*** 1) PLEASE"
                        . " provide enough notification for both pickup and delivery contacts. Make appointments! IF POSSIBLE,"
                        . " GIVE 24 HR AND 1 HR NOTICE ON BOTH ENDS!! NO EXCEPTIONS!!! WE DO NOT PAY DRY RUN FEES!!! CALL"
                        . " 512-200-1066 BEFORE DOING ANYTHING OUT OF THE CONTRACT! (IF VEHICLE IS INOP, CALL 512-200-1066"
                        . " BEFORE LOADING!!!!!) 2) Please take Clean pictures on all sides (oustide) and Inside at pick up"
                        . " AND delivery for insurance purposes. NO EXCEPTIONS! NO AFTER-HOUR PICK UP OR DROP-OFFS WITHOUT"
                        . " USHIP LOGISTICS APPROVAL! CALL 512-200-1066 3) Please obtain signatures on both pickup and delivery"
                        . " on a conditions report/BOL. NO EXCEPTIONS!! 4) SEND: pictures & BOL & voided check/bank info to:"
                        . " Accounting@ushiplogistics.com FOR PAYMENT QUESTIONS: CALL (512) 537-3136 *FAILURE to do these"
                        . " requirements will result in a delay in payment to you! Please be advised!!! **IF there were any"
                        . " damages to this shipment, uShip Logistics reserves the right to withhold or modify payment to"
                        . " driver/carrier for 30 business days or more until the claim is resolved and depending on case"
                        . " by case. Damage claim repair estimates must come from a uShip Logistics approved collision center"
                        . " prior to proceeding** **IF there is evidence of sub-contracting or double brokering is grounds"
                        . " for uShip Logistics to modify or withhold payment to the assigned carrier! **IF the driver and"
                        . " or equipment is not operating under the authority to the assigned Carriers MC#, uShip Logistics"
                        . " has the right to modify or withhold payment!!** ****ANY DELAYS OR ANYTHING OUT OF THE CONTRACT,"
                        . " CALL OR EMAIL US, NO MATTER WHAT!!! WE NEED TO KNOW FIRST BEFORE ANYONE ELSE!! NO EXCEPTIONS!!"
                        . " WITHOUT CALLING OR NOTICIFYING US, WE WILL BE DEMANDING DISCOUNTS FOR ANYTHING OUTSIDE OF THE"
                        . " CONTRACT WITHOUT NOTICE/ CONFIRMATION FROM USHIP LOGISTICS!! PLEASE BE ADVISED!!!",
                    "vehicles" => [
                        [
                            "year" => "2018",
                            "make" => "Ford",
                            "model" => "F-150",
                            "vin" => "1FTEX1C5XJFC55641",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2018",
                            "make" => "Ford",
                            "model" => "F-150",
                            "vin" => "1FTEX1C58JFC55640",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Diane Weyde, Tina. HR Green, Inc",
                        "state" => "IL",
                        "city" => "McHenry",
                        "address" => "1391 Corporate Dr, Suite 203",
                        "zip" => "60050",
                        "phones" => [
                            [
                                "number" => "18157598258",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "18157598365",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Liza , Shelby. HR Green, Inc",
                        "state" => "CA",
                        "city" => "Corona",
                        "address" => "1260 Corona Pointe Ct #305",
                        "zip" => "92879",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "19514753602",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "USHIP LOGISTICS LLC H4451437(#1 - #2)",
                        "address" => "205 East Riverside Dr., Suite A",
                        "city" => "Austin",
                        "state" => "TX",
                        "zip" => "78704",
                        "phones" => [],
                        "email" => "dispatch@ushiplogistics.com",
                        "phone" => "17078465143",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 3000,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 3000,
                        "broker_payment_method_id" => null,
                        "broker_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "broker_payment_days" => 5,
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
        $this->createMakes("NISSAN")
            ->createStates("TN", "TN", "TX")
            ->createTimeZones("38358", "37923", "78704")
            ->assertParsing(
                53,
                [
                    "load_id" => "VIP - H4451610(#1 - #2)",
                    "pickup_date" => "03/08/2023",
                    "delivery_date" => "03/09/2023",
                    "dispatch_instructions" => "Pickup: PU M-F 9-5; call 24 hrs ahead to make an appt DEL M-F 8:30-5; call"
                        . " ahead to make an appt *no mods or upfit,\nDelivery: PU M-F 9-5; call 24 hrs ahead to make an appt"
                        . " DEL M-F 8:30-5; call ahead to make an appt *no mods or upfit,\nThis order requires you be on file"
                        . " with uShip Logistics LLC. To expedite and secure fast dispatch, please ensure you have your UL"
                        . " Carrier Packet: https://bit.ly/30Hnikx PAYMENT INSTRUCTIONS & REQUIREMENTS (2 DAY ACH!) ***DO"
                        . " NOT SHOW PRICE TO ANYONE AT PICK UP OR DELIVERY!! NO EXCEPTIONS, IF YOU DO, YOU ARE VOIDING FULL"
                        . " PAYMENT, WE WILL NOT BE PAYING YOU FOR THIS SHIPMENT, PLEASE BE ADVISED!*** 1) PLEASE provide"
                        . " enough notification for both pickup and delivery contacts. Make appointments! IF POSSIBLE, GIVE"
                        . " 24 HR AND 1 HR NOTICE ON BOTH ENDS!! NO EXCEPTIONS!!! WE DO NOT PAY DRY RUN FEES!!! CALL 512-200-1066"
                        . " BEFORE DOING ANYTHING OUT OF THE CONTRACT! (IF VEHICLE IS INOP, CALL 512-200-1066 BEFORE LOADING!!!!!)"
                        . " 2) Please take Clean pictures on all sides (oustide) and Inside at pick up AND delivery for insurance"
                        . " purposes. NO EXCEPTIONS! NO AFTER-HOUR PICK UP OR DROP-OFFS WITHOUT USHIP LOGISTICS APPROVAL!"
                        . " CALL 512-200-1066 3) Please obtain signatures on both pickup and delivery on a conditions report/BOL."
                        . " NO EXCEPTIONS!! 4) SEND: pictures & BOL & voided check/bank info to: Accounting@ushiplogistics.com"
                        . " FOR PAYMENT QUESTIONS: CALL (512) 537-3136 *FAILURE to do these requirements will result in a"
                        . " delay in payment to you! Please be advised!!! **IF there were any damages to this shipment, uShip"
                        . " Logistics reserves the right to withhold or modify payment to driver/carrier for 30 business days"
                        . " or more until the claim is resolved and depending on case by case. Damage claim repair estimates"
                        . " must come from a uShip Logistics approved collision center prior to proceeding** **IF there is"
                        . " evidence of sub-contracting or double brokering is grounds for uShip Logistics to modify or withhold"
                        . " payment to the assigned carrier! **IF the driver and or equipment is not operating under the authority"
                        . " to the assigned Carriers MC#, uShip Logistics has the right to modify or withhold payment!!**"
                        . " ****ANY DELAYS OR ANYTHING OUT OF THE CONTRACT, CALL OR EMAIL US, NO MATTER WHAT!!! WE NEED TO"
                        . " KNOW FIRST BEFORE ANYONE ELSE!! NO EXCEPTIONS!! WITHOUT CALLING OR NOTICIFYING US, WE WILL BE"
                        . " DEMANDING DISCOUNTS FOR ANYTHING OUTSIDE OF THE CONTRACT WITHOUT NOTICE/ CONFIRMATION FROM USHIP"
                        . " LOGISTICS!! PLEASE BE ADVISED!!!",
                    "vehicles" => [
                        [
                            "year" => "2022",
                            "make" => "Nissan",
                            "model" => "Kicks",
                            "vin" => "3N1CP5BV8NL493776",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                        [
                            "year" => "2020",
                            "make" => "Nissan",
                            "model" => "Kicks",
                            "vin" => "3N1CP5BV1LL477352",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Shelly Akins. Camelot of Rural West TN",
                        "state" => "TN",
                        "city" => "Milan",
                        "address" => "12925 S 1st St",
                        "zip" => "38358",
                        "phones" => [
                            [
                                "number" => "17316869383",
                            ],
                        ],
                        "fax" => null,
                        "phone" => "17316950333",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Angel Wood. Camelot of East TN",
                        "state" => "TN",
                        "city" => "Knoxville",
                        "address" => "311 Directors Dr NW",
                        "zip" => "37923",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "14237364826",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "USHIP LOGISTICS LLC H4451610(#1 - #2)",
                        "address" => "205 East Riverside Dr., Suite A",
                        "city" => "Austin",
                        "state" => "TX",
                        "zip" => "78704",
                        "phones" => [],
                        "email" => "dispatch@ushiplogistics.com",
                        "phone" => "17078465143",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 800,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 800,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
        $this->createMakes("HYUNDAI")
            ->createStates("PA", "OH", "MI")
            ->createTimeZones("19153", "43615", "48150")
            ->assertParsing(
                54,
                [
                    "load_id" => "VS891994",
                    "pickup_date" => "03/01/2023",
                    "delivery_date" => "03/02/2023",
                    "dispatch_instructions" => "PAYMENT WILL BE MADE TO CARRIER ONLY ONCE VERILY HAS RECEIVED ALL OF THE"
                        . " BELOW INFO VIA EMAIL TO AP@VERILYTRANSPORT.COM: Voided check,Load ID,BOL,Invoice, Carrier MC number."
                        . " NO EXCEPTIONS",
                    "vehicles" => [
                        [
                            "year" => "2019",
                            "make" => "Hyundai",
                            "model" => "KONA",
                            "vin" => "KM8K5CA50KU265174",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "Doron Howard. Hertz Philadelphia",
                        "state" => "PA",
                        "city" => "Philadelphia",
                        "address" => "8201 Bartram Avenue",
                        "zip" => "19153",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12154922902",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Denny Gordon. Taylor Kia Toledo",
                        "state" => "OH",
                        "city" => "Toledo",
                        "address" => "6300 W Central Ave",
                        "zip" => "43615",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "12484590136",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "VERILY SOLUTIONS",
                        "address" => "36133 Schoolcraft St.",
                        "city" => "Livonia",
                        "state" => "MI",
                        "zip" => "48150",
                        "phones" => [],
                        "email" => "ap@verilytransport.com",
                        "phone" => "13134951578",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 325,
                        "terms" => null,
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
    public function test_55(): void
    {
        $this->createMakes("JEEP")
            ->createStates("IL", "ND", "MI")
            ->createTimeZones("61364", "58504", "48150")
            ->assertParsing(
                55,
                [
                    "load_id" => "VS892124",
                    "pickup_date" => "03/11/2023",
                    "delivery_date" => "03/13/2023",
                    "dispatch_instructions" => null,
                    "vehicles" => [
                        [
                            "year" => "2020",
                            "make" => "Jeep",
                            "model" => "Wrangler Unlimited",
                            "vin" => "1C4HJXFNXLW292799",
                            "color" => null,
                            "inop" => false,
                            "enclosed" => false,
                            "type_id" => null,
                        ],
                    ],
                    "pickup_contact" => [
                        "full_name" => "John Rowe. Bill Walsh Streator",
                        "state" => "IL",
                        "city" => "Streator",
                        "address" => "2330 N. Bloomington St",
                        "zip" => "61364",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "18156734333",
                        "state_id" => $this->states[self::PICKUP_KEY],
                        "timezone" => $this->timezones[self::PICKUP_KEY],
                    ],
                    "delivery_contact" => [
                        "full_name" => "Brock Keifel. Brock Keifel",
                        "state" => "ND",
                        "city" => "Lincoln",
                        "address" => "2870 Butler St",
                        "zip" => "58504",
                        "phones" => [],
                        "fax" => null,
                        "phone" => "17012262811",
                        "state_id" => $this->states[self::DELIVERY_KEY],
                        "timezone" => $this->timezones[self::DELIVERY_KEY],
                    ],
                    "shipper_contact" => [
                        "full_name" => "VERILY SOLUTIONS",
                        "address" => "36133 Schoolcraft St.",
                        "city" => "Livonia",
                        "state" => "MI",
                        "zip" => "48150",
                        "phones" => [],
                        "email" => "ap@verilytransport.com",
                        "phone" => "13134951578",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 1200,
                        "terms" => null,
                        "customer_payment_amount" => null,
                        "customer_payment_method_id" => null,
                        "customer_payment_method" => [
                            "id" => null,
                            "title" => null,
                        ],
                        "customer_payment_location" => null,
                        "broker_payment_amount" => 1200,
                        "broker_payment_method_id" => 5,
                        "broker_payment_method" => [
                            "id" => 5,
                            "title" => "ACH",
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
