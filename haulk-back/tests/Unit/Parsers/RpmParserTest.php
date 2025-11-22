<?php

namespace Tests\Unit\Parsers;

use App\Exceptions\Parser\PdfFileException;
use Throwable;

class RpmParserTest extends BaseParserTest
{
	/**
	* @throws Throwable
	*/
	public function test_1(): void
	{
		$this->createMakes("NISSAN")
			->createStates("MS", "OK", "MI")
			->createTimeZones("39046", "73701", "48067")
			->assertParsing(
				1,
				[
					"load_id" => "31592-34880",
					"pickup_date" => "03/08/2023",
					"delivery_date" => "03/10/2023",
					"dispatch_instructions" =>  "Pickup: Fred (769) 246-7244 Greg (601) 624-8991 // DRIVERS MUST SEND PHOTO"
						. " OF OUT GATE FORM WITH STICKERS OR UPLOAD VIA APP//*RPM DRIVER APP REQUIRE// *SINGLE, OVER WHEEL"
						. " STRAPS ONLY; NO CHAINS, SADDLE, OR LASSO STRAPS* *ALL FOUR WHEELS MUST BE STRAPPED DOWN, NO EXCEPTIONS*"
						. " *NO MOVING UNITS EXCEPT TO LOAD ASSIGNED UNITS ONTO TRUCK* *MUST USE SEPARATE RPM INSPECTION REPORT"
						. " FOR PICKUP & DELIVERY* *RPM INSPECTION REPORT MUST BE DATED AT PICKUP & DELIVERY* *A COPY OF THE"
						. " RPM PICKUP INSPECTION REPORT MUST BE LEFT AT THE ORIGIN LOCATION* *DAMAGE MUST BE NOTED WITH 5"
						. " DIGIT AIAG CODES ON RPM INSPECTION REPORTS* *DAMAGES MUST BE NOTED IN PROPER AREAS ON RPM INSPECTION"
						. " REPORTS* * ALL DAMAGES MUST BE REPORTED TO ONSITE STAFF PRIOR TO MOVING FROM BAY* *MUST RECEIVE"
						. " DAMAGED UNIT FORM FROM YARD STAFF IF UNIT HAS DAMAGE* *IF THERE IS DAMAGE AT PICKUP AND/OR DELIVERY,"
						. " PHOTOS ARE REQUIRED (FAILURE TO PROVIDE *MUST RECEIVE DAMAGED UNIT FORM FROM YARD STAFF IF UNIT"
						. " HAS DAMAGE* *IF THERE IS DAMAGE AT PICKUP AND/OR DELIVERY, PHOTOS ARE REQUIRED (FAILURE TO PROVIDE"
						. " WILL RESULT IN CARRIER BEING RESPONSIBLE FOR CLAIM)* *DRIVER MUST WEAR APPROPRIATE SAFETY ATTIRE"
						. " (I.E. FREE OF LOOSE METAL, CHAINS, ETC.)\nDelivery: NO STI* *NO WEEKEND DELIVERY",
					"vehicles" => [
						[
							"vin" => "1N6AA1FB5PN109872",
							"year" => "2023",
							"make" => "Nissan",
							"model" => "Titan XD",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Nissan Canton Plant",
						"state" => "MS",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "225 Nissan Dr Canton",
						"zip" => "39046",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "STUART NISSAN",
						"state" => "OK",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "301 NO. INDEPENDENCE ENID",
						"zip" => "73701",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 700,
					],
					"pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
					"delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_2(): void
	{
		$this->createMakes("NISSAN")
			->createStates("TN", "SC", "MI")
			->createTimeZones("37167", "29501", "48067")
			->assertParsing(
				2,
				[
					"load_id" => "31504-42329",
					"pickup_date" => "02/13/2023",
					"delivery_date" => "02/15/2023",
					"dispatch_instructions" =>  "Pickup: MUST CONTACT TYLER 209 -662-2568 PRIOR TO ARRIVAL // ALL DAMAGES"
						. " MUST BE REPORTED TO ONSITE STAFF PRIOR TO MOVING FROM BAY// DRIVERS MUST CHECK OUT WITH NATIONWIDE"
						. " REPS TO VALIDATE NUMBER OF UNITS ON TRUCK VS PAPERWORK PRIOR TO EXITING THE YARD*RPM DRIVER APP"
						. " REQUIRED// 0800-1900 LOADING HOURS //MUST USE GATE 8 // UNITS WITH BAYS F-J ARE LOCATED IN THE"
						. " OUTBACK LOT// IF THERE IS DAMAGE AT PICKUP AND/OR DELIVERY, PHOTOS ARE REQUIRED (FAILURE TO PROVIDE"
						. " WILL RESULT IN CARRIER BEING RESPONSIBLE FOR CLAIM)//DRIVER MUST WEAR APPROPRIATE SAFETY ATTIRE"
						. " (I.E. FREE OF LOOSE METAL, CHAINS, ETC.// DAMAGE MUST BE NOTED WITH 5 DIGIT AIAG CODES ON RPM"
						. " INSPECTION REPORTS// NO MOVING UNITS EXCEPT TO LOAD ASSIGNED UNITS ONTO TRUCK// MUST HAVE RPM"
						. " BOL AND SMYRNA OUT GATE SHEET // *MUST USE SEPARATE RPM INSPECTION REPORT FOR PICKUP & DELIVERY//"
						. " *RPM INSPECTION REPORT MUST BE DATED AT PICKUP & DELIVERY// A COPY OF THE RPM PICKUP INSPECTION"
						. " REPORT MUST BE LEFT AT THE ORIGIN LOCATION// MUST PULL VIN STICKETS AND APPLY TO SMYRNA OUT GATE"
						. " FORM // DRIVERS MUST SEND PHOTO OF OUT GATE FORM WITH STICKERS OR UPLOAD VIA APP// MUST TURN IN"
						. " OUTGATE FORM AT GUARD SHACK UPON EXIT - EXIT SHEET MUST SHOW RPM (NOT CARRIER NAME) FAILURE TO"
						. " TURN IN RPM PAPERWORK WILL RESULT IN RATE REDUCTION//\nDelivery: NO STI* *NO WEEKEND DELIVERY",
					"vehicles" => [
						[
							"vin" => "5N1BT3CA9PC786245",
							"year" => "2023",
							"make" => "Nissan",
							"model" => "Rogue",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "WWL Vehicle Services Americas",
						"state" => "TN",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "626 Enon Springs Road East Smyrna",
						"zip" => "37167",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "FIVE STAR NISSAN FLORENCE",
						"state" => "SC",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "311 N CASHUA DRIVE FLORENCE",
						"zip" => "29501",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 400,
					],
					"pickup_time" => [
						"from" => "11:00 PM",
						"to" => "10:59 PM",
					],
					"delivery_time" => [
                        "from" => "7:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_3(): void
	{
		$this->createMakes("TOYOTA")
			->createStates("AR", "MO", "MI")
			->createTimeZones("72756", "64012", "48067")
			->assertParsing(
				3,
				[
					"load_id" => "31504-81534",
					"pickup_date" => "02/27/2023",
					"delivery_date" => "02/28/2023",
					"dispatch_instructions" =>  "Pickup: CALL 24 HOURS AND 1 HOUR BEFORE PICKUP** . Must confirm VIN before"
						. " pickup, must have release at pickup Keys and runs contact Lola or Marty at 479-986-9090 up times"
						. " Monday to Friday 7:00 to 17:00\nDelivery: Delivery # VEHICLES MUST BE CHECKED IN UNDER AS WHEELS"
						. " Vehicle must check in under as wheels, must leave paperwork in vehicle stating WHEELS. Must have"
						. " signature, stamp or picture. Damaged units must have pictures. VEHICLES MUST BE CHECKED IN UNDER"
						. " AS WHEELS",
					"vehicles" => [
						[
							"vin" => "5TFTY5F12EX008585",
							"year" => "2014",
							"make" => "Toyota",
							"model" => "Tundra",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "VSC Fire & Security,",
						"state" => "AR",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "1315 N 13th St Rogers",
						"zip" => "72756",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "ADESA Kansas City",
						"state" => "MO",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "15511 ADESA Dr. Belton",
						"zip" => "64012",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 300,
					],
					"pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
					"delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_4(): void
	{
		$this->createMakes("CHEVROLET")
			->createStates("NONE", "WI", "MI")
			->createTimeZones("NONE", "53108", "48067")
			->assertParsing(
				4,
                [
                    "load_id" => "31505-54172",
                    "pickup_date" => "01/27/2023",
                    "delivery_date" => "01/28/2023",
                    "dispatch_instructions" => "Pickup: Pickup # KEYS/DRIVES Appointment # M-F 8:00-17:00 MUST CALL 24HRS"
                        . " AHEAD & 1HR OUT TO GET RELEASE PRINTED, KEYS / DRIVES, NO DAMAGES, M-F8:00-17:00 * Pickup Contact"
                        . " Name:Janie Jones, Pickup Contact Phone #: 217-357-9125. Must confirm VIN before pickup, must have"
                        . " release at pickup\nDelivery: (262) 835-4436 Delivery # VEHICLES MUST BE CHECKED IN UNDER AS WHEELS"
                        . " Vehicle must check in under as wheels, must leave paperwork in vehicle stating WHEELS. Must have"
                        . " signature, stamp or picture. Damaged units must have pictures. VEHICLES MUST BE CHECKED IN UNDER"
                        . " AS WHEELS",
                    "vehicles" => [
                        [
                            "vin" => "1GCHC24U04E119324",
							"year" => "2004",
							"make" => "Chevrolet",
							"model" => "Silverado 2500HD",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Carson Motors Inc",
						"state" => null,
						"city" => null,
						"address" => null,
						"zip" => null,
						"phones" => null,
						"fax" => null,
					],
					"delivery_contact" => [
						"full_name" => "Manheim Milwaukee",
						"state" => "WI",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "561 27th Street Caledonia",
						"zip" => "53108",
						"phones" => [],
						"fax" => null,
						"phone" => "12628354436",
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 225,
                    ],
                    "pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
                    "delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
                ]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_5(): void
	{
		$this->createMakes("NISSAN")
			->createStates("TN", "NC", "MI")
			->createTimeZones("37167", "27909", "48067")
			->assertParsing(
				5,
				[
					"load_id" => "31507-27452",
					"pickup_date" => "01/24/2023",
					"delivery_date" => "01/25/2023",
					"dispatch_instructions" =>  "Pickup: MUST CONTACT TYLER 209 -662-2568 PRIOR TO ARRIVAL // ALL DAMAGES"
						. " MUST BE REPORTED TO ONSITE STAFF PRIOR TO MOVING FROM BAY// DRIVERS MUST CHECK OUT WITH NATIONWIDE"
						. " REPS TO VALIDATE NUMBER OF UNITS ON TRUCK VS PAPERWORK PRIOR TO EXITING THE YARD*RPM DRIVER APP"
						. " REQUIRED// 0800-1900 LOADING HOURS //MUST USE GATE 8 // UNITS WITH BAYS F-J ARE LOCATED IN THE"
						. " OUTBACK LOT// IF THERE IS DAMAGE AT PICKUP AND/OR DELIVERY, PHOTOS ARE REQUIRED (FAILURE TO PROVIDE"
						. " WILL RESULT IN CARRIER BEING RESPONSIBLE FOR CLAIM)//DRIVER MUST WEAR APPROPRIATE SAFETY ATTIRE"
						. " (I.E. FREE OF LOOSE METAL, CHAINS, ETC.// DAMAGE MUST BE NOTED WITH 5 DIGIT AIAG CODES ON RPM"
						. " INSPECTION REPORTS// NO MOVING UNITS EXCEPT TO LOAD ASSIGNED UNITS ONTO TRUCK// MUST HAVE RPM"
						. " BOL AND SMYRNA OUT GATE SHEET // *MUST USE SEPARATE RPM INSPECTION REPORT FOR PICKUP & DELIVERY//"
						. " *RPM INSPECTION REPORT MUST BE DATED AT PICKUP & DELIVERY// A COPY OF THE RPM PICKUP INSPECTION"
						. " REPORT MUST BE LEFT AT THE ORIGIN LOCATION// MUST PULL VIN STICKETS AND APPLY TO SMYRNA OUT GATE"
						. " FORM // DRIVERS MUST SEND PHOTO OF OUT GATE FORM WITH STICKERS OR UPLOAD VIA APP// MUST TURN IN"
						. " OUTGATE FORM AT GUARD SHACK UPON EXIT - EXIT SHEET MUST SHOW RPM (NOT CARRIER NAME) FAILURE TO"
						. " TURN IN RPM PAPERWORK WILL RESULT IN RATE REDUCTION//\nDelivery: (252) 338-5161 *NO STI* *NO WEEKEND"
						. " DELIVERY",
					"vehicles" => [
						[
							"vin" => "1N4AZ1BV3PC557041",
							"year" => "2023",
							"make" => "Nissan",
							"model" => "LEAF",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "WWL Vehicle Services Americas",
						"state" => "TN",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "626 Enon Springs Road East Smyrna",
						"zip" => "37167",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "NISSAN OF ELIZABETH CITY",
						"state" => "NC",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "1712 N ROAD STREET ELIZABETH CITY",
						"zip" => "27909",
						"phones" => [],
						"fax" => null,
						"phone" => "12523385161",
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 500,
					],
					"pickup_time" => null,
					"delivery_time" => [
                        "from" => "2:00 AM",
                        "to" => "11:00 AM",
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
			->createStates("TN", "TX", "MI")
			->createTimeZones("37174", "77385", "48067")
			->assertParsing(
				6,
				[
					"load_id" => "31509-46790",
					"pickup_date" => "01/24/2023",
					"delivery_date" => "01/27/2023",
					"dispatch_instructions" =>  "NO STI WITHOUT APPROVAL FROM RPM // RPM Driver App MUST be used. If RPM"
						. " App not used, carrier risks non-payment and accepts liability for any damage. RPM Hard Copy of"
						. " BOL required to enter past guard and Hard Copy of RPM Pickup Inspection MUST be left at the Origin"
						. " Location. Damage Severity Level 3 and above can NOT be transported without RPM Approval. Photos"
						. " are REQUIRED for ALL Damage. Failure to provide photos will result in Carrier being Liable for"
						. " all Damage Claims. Driver MUST wear appropriate Safety attire. Single, Over-Wheel Soft-Straps"
						. " ONLY. No Chains, Saddle, or Lasso Straps. All 4 wheels must be strapped down. No Moving units"
						. " except to load assigned units onto truck.\nPickup: HARD COPY BOL REQUIRED - RPM APP REQUIRED //"
						. " MUST UPLOAD PICTURE OF BOL BEING LEFT AT ORIGIN TO RPM APP // 24/7 LOADING HOURS // STAFF ON SITE"
						. " M-F 05:00-14:00 // Marty Hutchins 513-289-0783, Charles Elledge 629-3952710, Alisha Lewis 517-897-0604,"
						. " Brian Durkin 931-797-0157 (Nights) // ALL DAMAGES MUST BE SIGNED OFF BEFORE DEPARTURE // *SINGLE,"
						. " OVER WHEEL STRAPS ONLY; NO CHAINS, SADDLE, OR LASSO STRAPS* *NO MOVING UNITS EXCEPT TO LOAD ASSIGNED"
						. " UNITS ONTO TRUCK* *MUST USE SEPARATE RPM INSPECTION REPORT FOR PICKUP & DELIVERY* *RPM INSPECTION"
						. " REPORT MUST BE DATED AT PICKUP & DELIVERY* *A COPY OF THE RPM PICKUP INSPECTION REPORT MUST BE"
						. " LEFT AT THE ORIGIN LOCATION* *DAMAGE MUST BE NOTED WITH 5 DIGIT AIAG CODES ON RPM INSPECTION REPORTS"
						. " AND RPM APP* *DAMAGES MUST BE NOTED IN PROPER AREAS ON RPM INSPECTION REPORTS* *DAMAGE SEVERITY"
						. " LEVEL 1 & 2 MUST BE NOTATED ON RPM PICKUP INSPECTION REPORT* *DAMAGE SEVERITY LEVEL 3 & ABOVE"
						. " CANNOT BE TRANSPORTED WITHOUT APPROVAL FROM RPM* *MUST RECEIVE DAMAGED UNIT FORM FROM YARD STAFF"
						. " IF UNIT HAS DAMAGE* *IF THERE IS DAMAGE AT PICKUP AND/OR DELIVERY, PHOTOS ARE REQUIRED (FAILURE"
						. " TO PROVIDE WILL RESULT IN CARRIER BEING RESPONSIBLE FOR CLAIM)* *DRIVER MUST WEAR APPROPRIATE"
						. " SAFETY ATTIRE (I.E. FREE OF LOOSE METAL, CHAINS, ETC.)\nDelivery: NO STI* *NO WEEKEND DELIVERY",
					"vehicles" => [
						[
							"vin" => "1GYKNCRS5PZ171732",
							"year" => "2023",
							"make" => "Cadillac",
							"model" => "XT5",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "GM - Spring Hill - HL",
						"state" => "TN",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "100 Saturn Pkwy Spring Hill",
						"zip" => "37174",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "BAYWAY CADILLAC OF THE WOODLANDS",
						"state" => "TX",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "16785 INTERSTATE 45 SOUTH THE WOODLANDS",
						"zip" => "77385",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 650,
					],
					"pickup_time" => [
						"from" => "12:00 AM",
						"to" => "11:59 PM",
					],
					"delivery_time" => [
                        "from" => "7:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_7(): void
	{
		$this->expectException(PdfFileException::class);
		$this->createMakes("NONE")
			->createStates("TN", "TX", "MI")
			->createTimeZones("37174", "77385", "48067")
			->assertParsing(
				7,
				[]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_8(): void
	{
		$this->expectException(PdfFileException::class);
		$this->createMakes("NONE")
			->createStates("TN", "TX", "MI")
			->createTimeZones("37174", "77385", "48067")
			->assertParsing(
				8,
				[]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_9(): void
	{
		$this->createMakes("FORD")
			->createStates("TN", "NC", "MI")
			->createTimeZones("37122", "28677", "48067")
			->assertParsing(
				9,
				[
					"load_id" => "31513-85753",
					"pickup_date" => "01/31/2023",
					"delivery_date" => "02/06/2023",
					"dispatch_instructions" =>  "Pickup: Chris Dobson 615-773-3800 615-394-9077 Chris.Dobson@coxautoinc.com"
						. " KEYS/DRIVES-GATE PASS ATTACHED\nDelivery: Scott Wilson 7049025775 scott@blackcdjr.com",
					"vehicles" => [
						[
							"vin" => "1FTBR1C86LKB11084",
							"year" => "2020",
							"make" => "Ford",
							"model" => "Transit Cargo",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Manheim Nashville",
						"state" => "TN",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "8400 Eastgate Blvd Mt. Juliet",
						"zip" => "37122",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "Black Automotive Group",
						"state" => "NC",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "831 Salisbury Rd Statesville",
						"zip" => "28677",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 400,
					],
					"pickup_time" => [
						"from" => "11:00 AM",
						"to" => "11:00 AM",
					],
					"delivery_time" => [
                        "from" => "6:00 PM",
                        "to" => "6:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_10(): void
	{
		$this->createMakes("RAM")
			->createStates("TX", "WI", "MI")
			->createTimeZones("78045", "537186381", "48067")
			->assertParsing(
				10,
				[
					"load_id" => "31515-26044",
					"pickup_date" => "03/01/2023",
					"delivery_date" => "03/05/2023",
					"dispatch_instructions" =>  "RPM DRIVER APP REQUIRED AT PICK UP AND DELIVERY // NO STI - NO WEEKEND DELIVERY"
						. " WITHOUT APPROVAL FROM RPM // RATE SUBJECT TO CHANGE IF NOT FOLLOWED // Leaving units unattended"
						. " is strictly forbidden and will result in permanent ban from RPM along with liability for any damages"
						. " // ALL AFTER HOUR DROPS MUST BE APPROVED BY RPM - NOT THE DEALER\nPickup: HOURS MON-SAT 0600 -"
						. " 1600 // HELP ON SITE Joseph DiBella -- (734) 765-7193 // STREET ADDRESS 3210 PINTO VALLE, LAREDO,"
						. " TX, 78045 // PHYSICAL DOCUMENTS REQUIRED // all severities of damage identified on product being"
						. " moved out of any VASCOR facility must be legible and properly documented on a VASCOR outgate sheet."
						. " Also, prior to the units handling, the damage must be verified and signed for by an authorized"
						. " VASCOR representative. Failure to follow these steps will result in VASCOR denying liability for"
						. " any damages that result in a claim. // SINGLE, OVER WHEEL STRAPS ONLY; NO CHAINS, SADDLE, OR LASSO"
						. " STRAPS ALL FOUR WHEELS MUST BE STRAPPED DOWN, NO EXCEPTIONS NO MOVING UNITS EXCEPT TO LOAD ASSIGNED"
						. " UNITS ONTO TRUCK* ALL DAMAGES ON APP MUST MATCH HARD COPY LEFT AT YARD* RPM PICKUP INSPECTION"
						. " REPORT MUST BE LEFT AT THE ORIGIN LOCATION DAMAGE MUST BE NOTED WITH 5 DIGIT AIAG CODES ON RPM"
						. " INSPECTION REPORTS AND RPM DRIVER APP * DAMAGES MUST BE NOTED IN PROPER AREAS ON RPM INSPECTION"
						. " REPORTS DAMAGE PHOTOS ARE REQUIRED (FAILURE TO PROVIDE WILL RESULT IN CARRIER BEING RESPONSIBLE"
						. " FOR CLAIM) DRIVER MUST WEAR APPROPRIATE SAFETY ATTIRE (I.E. FREE OF LOOSE METAL, CHAINS, ETC.)"
						. " *IF PLASTIC FILM IS LOOSE, REMOVE FROM UNIT* IF FILM IS DAMAGED AT DELIVERY, MAKE NOTE OF DAMAGED"
						. " FILM AND MARK DAMAGES CAUSED BY FILM*FAILURE TO USE RPM APP OR RPM OEM INSPECTION DOCS WILL RESULT"
						. " IN DELAYED PAYMENT AND LIABILITY OF CLAIMS",
					"vehicles" => [
						[
							"vin" => "3C6LRVDG0PE506167",
							"year" => "2023",
							"make" => "Ram",
							"model" => "ProMaster",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Vascor Laredo Yard",
						"state" => "TX",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "3210 Pinto Valle Laredo",
						"zip" => "78045",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "RUSS DARROW CHRYSLER, JEEP, DODGE",
						"state" => "WI",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "3502 LANCASTER DR MADISON",
						"zip" => "537186381",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 1400,
					],
					"pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
					"delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_11(): void
	{
		$this->expectException(PdfFileException::class);
		$this->createMakes("NONE")
			->createStates("NONE", "NONE", "NONE")
			->createTimeZones("NONE", "NONE", "NONE")
			->assertParsing(
				11,
				[]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_12(): void
	{
		$this->expectException(PdfFileException::class);
		$this->createMakes("NONE")
			->createStates("NONE", "NONE", "NONE")
			->createTimeZones("NONE", "NONE", "NONE")
			->assertParsing(
				12,
				[]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_13(): void
	{
		$this->createMakes("CHEVROLET")
			->createStates("AR", "OK", "MI")
			->createTimeZones("72422", "74116", "48067")
			->assertParsing(
				13,
                [
                    "load_id" => "31517-43275",
                    "pickup_date" => "02/28/2023",
                    "delivery_date" => "03/01/2023",
                    "dispatch_instructions" => "Pickup: CALL 24 HOURS AND 1 HOUR BEFORE PICKUP** Must confirm VIN before"
                        . " pickup, must have release at pickup Keys and runs contact Bently Taylor at 870-857-3839 up times"
                        . " Monday to Friday 7:00 to 16:00\nDelivery: (918) 437-9044 Delivery # VEHICLES MUST BE CHECKED IN"
                        . " UNDER AS WHEELS Vehicle must check in under as wheels, must leave paperwork in vehicle stating"
                        . " WHEELS. Must have signature, stamp or picture. Damaged units must have pictures. VEHICLES MUST"
                        . " BE CHECKED IN UNDER AS WHEELS",
                    "vehicles" => [
                        [
                            "vin" => "1GCPYDEK5LZ198535",
                            "year" => "2020",
							"make" => "Chevrolet",
							"model" => "Silverado 1500",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "108 Airport Highway 980",
						"state" => "AR",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "108 Airport Highway 980 Corning",
						"zip" => "72422",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "ADESA Tulsa",
						"state" => "OK",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "16015 East Admiral Place Tulsa",
						"zip" => "74116",
						"phones" => [],
						"fax" => null,
						"phone" => "19184379044",
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 400,
                    ],
                    "pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
                    "delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
                ]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_14(): void
	{
		$this->createMakes("NISSAN")
			->createStates("TN", "SC", "MI")
			->createTimeZones("37167", "29577", "48067")
			->assertParsing(
				14,
				[
					"load_id" => "31520-94807",
					"pickup_date" => "02/20/2023",
					"delivery_date" => "02/23/2023",
					"dispatch_instructions" =>  "Pickup: DRIVERS MUST CHECK IN AND OUT WITH GUARD SHACK AND LEAVE PAPERWORK///MUST"
						. " CONTACT TYLER 209 -662-2568 PRIOR TO ARRIVAL // ALL DAMAGES MUST BE REPORTED TO ONSITE STAFF PRIOR"
						. " TO MOVING FROM BAY// DRIVERS MUST CHECK OUT WITH NATIONWIDE REPS TO VALIDATE NUMBER OF UNITS ON"
						. " TRUCK VS PAPERWORK PRIOR TO EXITING THE YARD*RPM DRIVER APP REQUIRED// 0800-1900 LOADING HOURS"
						. " //MUST USE GATE 8 // UNITS WITH BAYS F-J ARE LOCATED IN THE OUTBACK LOT// IF THERE IS DAMAGE AT"
						. " PICKUP AND/OR DELIVERY, PHOTOS ARE REQUIRED (FAILURE TO PROVIDE WILL RESULT IN CARRIER BEING RESPONSIBLE"
						. " FOR CLAIM)//DRIVER MUST WEAR APPROPRIATE SAFETY ATTIRE (I.E. FREE OF LOOSE METAL, CHAINS, ETC.//"
						. " DAMAGE MUST BE NOTED WITH 5 DIGIT AIAG CODES ON RPM INSPECTION REPORTS// NO MOVING UNITS EXCEPT"
						. " TO LOAD ASSIGNED UNITS ONTO TRUCK// MUST HAVE RPM BOL AND SMYRNA OUT GATE SHEET // *MUST USE SEPARATE"
						. " RPM INSPECTION REPORT FOR PICKUP & DELIVERY// *RPM INSPECTION REPORT MUST BE DATED AT PICKUP &"
						. " DELIVERY// A COPY OF THE RPM PICKUP INSPECTION REPORT MUST BE LEFT AT THE ORIGIN LOCATION// MUST"
						. " PULL VIN STICKETS AND APPLY TO SMYRNA OUT GATE FORM // DRIVERS MUST SEND PHOTO OF OUT GATE FORM"
						. " WITH STICKERS OR UPLOAD VIA APP// MUST TURN IN OUTGATE FORM AT GUARD SHACK UPON EXIT - EXIT SHEET"
						. " MUST SHOW RPM (NOT CARRIER NAME) FAILURE TO TURN IN RPM PAPERWORK WILL RESULT IN RATE REDUCTION//\nDelivery:"
						. " NO STI* *NO WEEKEND DELIVERY*. DropShip Address.",
					"vehicles" => [
						[
							"vin" => "5N1DR3BA6PC248319",
							"year" => "2023",
							"make" => "Nissan",
							"model" => "Pathfinder",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
						[
							"vin" => "5N1DR3BA0PC248705",
							"year" => "2023",
							"make" => "Nissan",
							"model" => "Pathfinder",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
						[
							"vin" => "5N1DR3AA3PC245069",
							"year" => "2023",
							"make" => "Nissan",
							"model" => "Pathfinder",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "WWL Vehicle Services Americas",
						"state" => "TN",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "626 Enon Springs Road East Smyrna",
						"zip" => "37167",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "ENTERPRISE HOLD MYRT",
						"state" => "SC",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "1097 JETPORT RD MYRTLE BEACH",
						"zip" => "29577",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 1000,
					],
					"pickup_time" => [
						"from" => "11:00 PM",
						"to" => "10:59 PM",
					],
					"delivery_time" => [
                        "from" => "7:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_15(): void
	{
		$this->createMakes("RAM")
			->createStates("TX", "OK", "MI")
			->createTimeZones("78045", "73762", "48067")
			->assertParsing(
				15,
				[
					"load_id" => "31522-03607",
					"pickup_date" => "02/20/2023",
					"delivery_date" => "02/22/2023",
					"dispatch_instructions" =>  "RPM DRIVER APP REQUIRED AT PICK UP AND DELIVERY // NO STI - NO WEEKEND DELIVERY"
						. " WITHOUT APPROVAL FROM RPM // RATE SUBJECT TO CHANGE IF NOT FOLLOWED // Leaving units unattended"
						. " is strictly forbidden and will result in permanent ban from RPM along with liability for any damages"
						. " // ALL AFTER HOUR DROPS MUST BE APPROVED BY RPM - NOT THE DEALER\nPickup: HOURS MON-SAT 0600 -"
						. " 1600 // HELP ON SITE Joseph DiBella -- (734) 765-7193 // STREET ADDRESS 3210 PINTO VALLE, LAREDO,"
						. " TX, 78045 // PHYSICAL DOCUMENTS REQUIRED // all severities of damage identified on product being"
						. " moved out of any VASCOR facility must be legible and properly documented on a VASCOR outgate sheet."
						. " Also, prior to the units handling, the damage must be verified and signed for by an authorized"
						. " VASCOR representative. Failure to follow these steps will result in VASCOR denying liability for"
						. " any damages that result in a claim. // SINGLE, OVER WHEEL STRAPS ONLY; NO CHAINS, SADDLE, OR LASSO"
						. " STRAPS ALL FOUR WHEELS MUST BE STRAPPED DOWN, NO EXCEPTIONS NO MOVING UNITS EXCEPT TO LOAD ASSIGNED"
						. " UNITS ONTO TRUCK* ALL DAMAGES ON APP MUST MATCH HARD COPY LEFT AT YARD* RPM PICKUP INSPECTION"
						. " REPORT MUST BE LEFT AT THE ORIGIN LOCATION DAMAGE MUST BE NOTED WITH 5 DIGIT AIAG CODES ON RPM"
						. " INSPECTION REPORTS AND RPM DRIVER APP * DAMAGES MUST BE NOTED IN PROPER AREAS ON RPM INSPECTION"
						. " REPORTS DAMAGE PHOTOS ARE REQUIRED (FAILURE TO PROVIDE WILL RESULT IN CARRIER BEING RESPONSIBLE"
						. " FOR CLAIM) DRIVER MUST WEAR APPROPRIATE SAFETY ATTIRE (I.E. FREE OF LOOSE METAL, CHAINS, ETC.)"
						. " *IF PLASTIC FILM IS LOOSE, REMOVE FROM UNIT* IF FILM IS DAMAGED AT DELIVERY, MAKE NOTE OF DAMAGED"
						. " FILM AND MARK DAMAGES CAUSED BY FILM*FAILURE TO USE RPM APP OR RPM OEM INSPECTION DOCS WILL RESULT"
						. " IN DELAYED PAYMENT AND LIABILITY OF CLAIMS",
					"vehicles" => [
						[
							"vin" => "3C6LRVDGXPE504619",
							"year" => "2023",
							"make" => "Ram",
							"model" => "ProMaster",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Vascor Laredo Yard",
						"state" => "TX",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "3210 Pinto Valle Laredo",
						"zip" => "78045",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "OEM SYSTEMS",
						"state" => "OK",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "210 W. OKLAHOMA AVENUE OKARCHE",
						"zip" => "73762",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 900,
					],
					"pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
					"delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_16(): void
	{
		$this->createMakes("JEEP")
			->createStates("OH", "MS", "MI")
			->createTimeZones("43612", "397598928", "48067")
			->assertParsing(
				16,
				[
					"load_id" => "31524-51538",
					"pickup_date" => "03/04/2023",
					"delivery_date" => "03/06/2023",
					"dispatch_instructions" =>  "RPM DRIVER APP REQUIRED AT PICK UP AND DELIVERY // NO STI - NO WEEKEND DELIVERY"
						. " WITHOUT APPROVAL FROM RPM // RATE SUBJECT TO CHANGE IF NOT FOLLOWED // Leaving units unattended"
						. " is strictly forbidden and will result in permanent ban from RPM along with liability for any damages"
						. " // ALL AFTER HOUR DROPS MUST BE APPROVED BY RPM - NOT THE DEALER\nPickup: NO SHUTTLING OF UNITS"
						. " // RPM DRIVER APP REQUIRED // PICKUP HOURS -- 24/7 // CHECK IN WITH SECURITY ON ARRIVAL // PHYSICAL"
						. " DOCS NEEDED FOR OUTGATE // MUST UPLOAD PICTURE OF PAPER COPY BOL THAT IS BEING LEFT AT ORIGIN"
						. " TO RPM DRIVER APP // RAM TRX AND HELLCAT CHALLENGER/CHARGER MODEL KEYS ARE WITH AWC PERSONNEL"
						. " - IF DRIVER CANNOT LOCATE PERSONNEL, ASK GUARD FOR ASSISTANCE. DRIVERS WILL NOT BE ABLE TO OBTAIN"
						. " KEYS ON HOLIDAYS, BETWEEN SHIFTS, AND ON WEEKENDS IF AWC IS NOT AVAILABLE // IF AN ISSUE IS DISCOVERED"
						. " WITH UNIT PREVENTING IT FROM BEING LOADED/PICKING UP, PLEASE NOTATE THE DAMAGE/REASON THIS IS"
						. " BEING LEFT BEHIND ON BOL FOR AWC TO REVIEW UPON TURN IN // MUST MATCH RPM DRIVER APP // SEV 3+"
						. " DAMAGE MUST HAVE SIGNATURES // DRIVERS MUST REPORT ISSUES (DAMAGE, NO START, ETC) TO GUARD OR"
						. " WHITE VAN WITH \"AWC\" ON THE SIDE - IF UNABLE TO LOCATE ONSITE STAFF, DRIVER MUST WRITE \"ISSUE\""
						. " ON BOL EXPLAINING WHY UNIT WAS NOT LOADED // IF A VIN IS NOT LOADED, DRIVER MUST CROSS OFF VIN"
						. " ON BOL // TRAVIS - 586-202-0513 // DRIVERS MAY NOT USE VEHICLES AS SHUTTLES // FOR AFTERHOURS"
						. " ASSISTANCE CONTACT CURTIS 734-775-5822",
					"vehicles" => [
						[
							"vin" => "1C4HJXDG0PW670851",
							"year" => "2023",
							"make" => "Jeep",
							"model" => "Wrangler Unlimited",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Stellantis - Raceway",
						"state" => "OH",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "5700 Telegraph Road Toledo",
						"zip" => "43612",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "PARKER CHRYSLER DODGE JEEP RAM",
						"state" => "MS",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "105 PARKER-MCGILL DRIVE STARKVILLE",
						"zip" => "397598928",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 600,
					],
					"pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
					"delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_17(): void
	{
		$this->createMakes("JEEP")
			->createStates("OH", "NC", "MI")
			->createTimeZones("43612", "275116023", "48067")
			->assertParsing(
				17,
				[
					"load_id" => "31525-16159",
					"pickup_date" => "02/20/2023",
					"delivery_date" => "02/20/2023",
					"dispatch_instructions" =>  "RPM DRIVER APP REQUIRED AT PICK UP AND DELIVERY // NO STI - NO WEEKEND DELIVERY"
						. " WITHOUT APPROVAL FROM RPM // RATE SUBJECT TO CHANGE IF NOT FOLLOWED // Leaving units unattended"
						. " is strictly forbidden and will result in permanent ban from RPM along with liability for any damages"
						. " // ALL AFTER HOUR DROPS MUST BE APPROVED BY RPM - NOT THE DEALER\nPickup: NO SHUTTLING OF UNITS"
						. " // RPM DRIVER APP REQUIRED // PICKUP HOURS -- 24/7 // CHECK IN WITH SECURITY ON ARRIVAL // PHYSICAL"
						. " DOCS NEEDED FOR OUTGATE // MUST UPLOAD PICTURE OF PAPER COPY BOL THAT IS BEING LEFT AT ORIGIN"
						. " TO RPM DRIVER APP // RAM TRX AND HELLCAT CHALLENGER/CHARGER MODEL KEYS ARE WITH AWC PERSONNEL"
						. " - IF DRIVER CANNOT LOCATE PERSONNEL, ASK GUARD FOR ASSISTANCE. DRIVERS WILL NOT BE ABLE TO OBTAIN"
						. " KEYS ON HOLIDAYS, BETWEEN SHIFTS, AND ON WEEKENDS IF AWC IS NOT AVAILABLE // IF AN ISSUE IS DISCOVERED"
						. " WITH UNIT PREVENTING IT FROM BEING LOADED/PICKING UP, PLEASE NOTATE THE DAMAGE/REASON THIS IS"
						. " BEING LEFT BEHIND ON BOL FOR AWC TO REVIEW UPON TURN IN // MUST MATCH RPM DRIVER APP // SEV 3+"
						. " DAMAGE MUST HAVE SIGNATURES // DRIVERS MUST REPORT ISSUES (DAMAGE, NO START, ETC) TO GUARD OR"
						. " WHITE VAN WITH \"AWC\" ON THE SIDE - IF UNABLE TO LOCATE ONSITE STAFF, DRIVER MUST WRITE \"ISSUE\""
						. " ON BOL EXPLAINING WHY UNIT WAS NOT LOADED // IF A VIN IS NOT LOADED, DRIVER MUST CROSS OFF VIN"
						. " ON BOL // TRAVIS - 586-202-0513 // DRIVERS MAY NOT USE VEHICLES AS SHUTTLES // FOR AFTERHOURS"
						. " ASSISTANCE CONTACT CURTIS 734-775-5822",
					"vehicles" => [
						[
							"vin" => "1C4JJXP68PW644843",
							"year" => "2023",
							"make" => "Jeep",
							"model" => "Wrangler Unlimited",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Stellantis - Raceway",
						"state" => "OH",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "5700 Telegraph Road Toledo",
						"zip" => "43612",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "AUTOPARK CHRYSLER JEEP",
						"state" => "NC",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "400 AUTO PARK BLVD CARY",
						"zip" => "275116023",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 500,
					],
					"pickup_time" => null,
					"delivery_time" => null,
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_18(): void
	{
		$this->createMakes("CHEVROLET")
			->createStates("IL", "WI", "MI")
			->createTimeZones("61455", "53108", "48067")
			->assertParsing(
				18,
                [
                    "load_id" => "31526-68099",
                    "pickup_date" => "01/27/2023",
                    "delivery_date" => "01/28/2023",
                    "dispatch_instructions" => "Pickup: Pickup # KEYS/DRIVES Appointment # M - F 08:00 - 17:00 MUST CALL"
                        . " 24HRS AHEAD & 1HR OUT FOR PU, KEYS / DRIVES, NO DAMAGES, M - F 08:00 - 17:00 *** PICKUP CONTACT"
                        . " NAME: Tina Turner, PICKUP CONTACT PHONE #: (309) 833-2112. MUST CONFIRM VIN BEFORE PICKUP, MUST"
                        . " HAVE RELEASE AT PICKUP\nDelivery: (262) 835-4436 Delivery # VEHICLES MUST BE CHECKED IN UNDER"
                        . " AS WHEELS Vehicle must check in under as wheels, must leave paperwork in vehicle stating WHEELS."
                        . " Must have signature, stamp or picture. Damaged units must have pictures. VEHICLES MUST BE CHECKED"
                        . " IN UNDER AS WHEELS",
                    "vehicles" => [
                        [
                            "vin" => "2GNAXTEV6L6243949",
							"year" => "2020",
							"make" => "Chevrolet",
							"model" => "Equinox",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Woodrum Ford Lincoln",
						"state" => "IL",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "3100 E Jackson Road Macomb",
						"zip" => "61455",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "Manheim Milwaukee",
						"state" => "WI",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "561 27th Street Caledonia",
						"zip" => "53108",
						"phones" => [],
						"fax" => null,
						"phone" => "12628354436",
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 225,
                    ],
                    "pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
                    "delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
                ]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_19(): void
	{
		$this->createMakes("JEEP")
			->createStates("OH", "GA", "MI")
			->createTimeZones("43612", "315139087", "48067")
			->assertParsing(
				19,
				[
					"load_id" => "31527-87699",
					"pickup_date" => "03/13/2023",
					"delivery_date" => "03/15/2023",
					"dispatch_instructions" =>  "MUST USE YARD OUTGATE FORM AND UPLOAD PICTURE TO RPM DRIVER APP IF DAMAGES"
						. " ARE PRESNT // RPM DRIVER APP REQUIRED AT PICK UP AND DELIVERY // NO STI - NO WEEKEND DELIVERY"
						. " WITHOUT APPROVAL FROM RPM // RATE SUBJECT TO CHANGE IF NOT FOLLOWED // Leaving units unattended"
						. " is strictly forbidden and will result in permanent ban from RPM along with liability for any damages"
						. " // ALL AFTER HOUR DROPS MUST BE APPROVED BY RPM - NOT THE DEALER\nPickup: NO SHUTTLING OF UNITS"
						. " // RPM DRIVER APP REQUIRED // PICKUP HOURS -- 24/7 // CHECK IN WITH SECURITY ON ARRIVAL // PHYSICAL"
						. " DOCS NEEDED FOR OUTGATE // MUST UPLOAD PICTURE OF PAPER COPY BOL THAT IS BEING LEFT AT ORIGIN"
						. " TO RPM DRIVER APP // RAM TRX AND HELLCAT CHALLENGER/CHARGER MODEL KEYS ARE WITH AWC PERSONNEL"
						. " - IF DRIVER CANNOT LOCATE PERSONNEL, ASK GUARD FOR ASSISTANCE. DRIVERS WILL NOT BE ABLE TO OBTAIN"
						. " KEYS ON HOLIDAYS, BETWEEN SHIFTS, AND ON WEEKENDS IF AWC IS NOT AVAILABLE // IF AN ISSUE IS DISCOVERED"
						. " WITH UNIT PREVENTING IT FROM BEING LOADED/PICKING UP, PLEASE NOTATE THE DAMAGE/REASON THIS IS"
						. " BEING LEFT BEHIND ON BOL FOR AWC TO REVIEW UPON TURN IN // MUST MATCH RPM DRIVER APP // SEV 3+"
						. " DAMAGE MUST HAVE SIGNATURES // DRIVERS MUST REPORT ISSUES (DAMAGE, NO START, ETC) TO GUARD OR"
						. " WHITE VAN WITH \"AWC\" ON THE SIDE - IF UNABLE TO LOCATE ONSITE STAFF, DRIVER MUST WRITE \"ISSUE\""
						. " ON BOL EXPLAINING WHY UNIT WAS NOT LOADED // IF A VIN IS NOT LOADED, DRIVER MUST CROSS OFF VIN"
						. " ON BOL // TRAVIS - 586-202-0513 // DRIVERS MAY NOT USE VEHICLES AS SHUTTLES // FOR AFTERHOURS"
						. " ASSISTANCE CONTACT CURTIS 734-775-5822",
					"vehicles" => [
						[
							"vin" => "1C4RJJBG8P8798649",
							"year" => "2023",
							"make" => "Jeep",
							"model" => "Grand Cherokee L",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Stellantis - Raceway",
						"state" => "OH",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "5700 Telegraph Road Toledo",
						"zip" => "43612",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "WOODY FOLSOM CHRYSLER DODGE JEEP",
						"state" => "GA",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "1859 GOLDEN ISLES WEST BAXLEY",
						"zip" => "315139087",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 600,
					],
					"pickup_time" => null,
					"delivery_time" => [
						"from" => "12:16 PM",
						"to" => "12:30 PM",
					],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_20(): void
	{
		$this->createMakes("RAM")
			->createStates("OH", "OK", "MI")
			->createTimeZones("43612", "740554667", "48067")
			->assertParsing(
				20,
				[
					"load_id" => "31531-50476",
					"pickup_date" => "02/11/2023",
					"delivery_date" => "02/14/2023",
					"dispatch_instructions" =>  "RPM DRIVER APP REQUIRED AT PICK UP AND DELIVERY // NO STI - NO WEEKEND DELIVERY"
						. " WITHOUT APPROVAL FROM RPM // RATE SUBJECT TO CHANGE IF NOT FOLLOWED // Leaving units unattended"
						. " is strictly forbidden and will result in permanent ban from RPM along with liability for any damages"
						. " // ALL AFTER HOUR DROPS MUST BE APPROVED BY RPM - NOT THE DEALER\nPickup: NO SHUTTLING OF UNITS"
						. " // RPM DRIVER APP REQUIRED // PICKUP HOURS -- 24/7 // CHECK IN WITH SECURITY ON ARRIVAL // PHYSICAL"
						. " DOCS NEEDED FOR OUTGATE // MUST UPLOAD PICTURE OF PAPER COPY BOL THAT IS BEING LEFT AT ORIGIN"
						. " TO RPM DRIVER APP // RAM TRX AND HELLCAT CHALLENGER/CHARGER MODEL KEYS ARE WITH AWC PERSONNEL"
						. " - IF DRIVER CANNOT LOCATE PERSONNEL, ASK GUARD FOR ASSISTANCE. DRIVERS WILL NOT BE ABLE TO OBTAIN"
						. " KEYS ON HOLIDAYS, BETWEEN SHIFTS, AND ON WEEKENDS IF AWC IS NOT AVAILABLE // IF AN ISSUE IS DISCOVERED"
						. " WITH UNIT PREVENTING IT FROM BEING LOADED/PICKING UP, PLEASE NOTATE THE DAMAGE/REASON THIS IS"
						. " BEING LEFT BEHIND ON BOL FOR AWC TO REVIEW UPON TURN IN // MUST MATCH RPM DRIVER APP // SEV 3+"
						. " DAMAGE MUST HAVE SIGNATURES // DRIVERS MUST REPORT ISSUES (DAMAGE, NO START, ETC) TO GUARD OR"
						. " WHITE VAN WITH \"AWC\" ON THE SIDE - IF UNABLE TO LOCATE ONSITE STAFF, DRIVER MUST WRITE \"ISSUE\""
						. " ON BOL EXPLAINING WHY UNIT WAS NOT LOADED // IF A VIN IS NOT LOADED, DRIVER MUST CROSS OFF VIN"
						. " ON BOL // TRAVIS - 586-202-0513 // DRIVERS MAY NOT USE VEHICLES AS SHUTTLES // FOR AFTERHOURS"
						. " ASSISTANCE CONTACT CURTIS 734-775-5822",
					"vehicles" => [
						[
							"vin" => "1C6SRFFT7PN562405",
							"year" => "2023",
							"make" => "Ram",
							"model" => "1500",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Stellantis - Raceway",
						"state" => "OH",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "5700 Telegraph Road Toledo",
						"zip" => "43612",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "JIM GLOVER CHRYSLER DODGE JEEP RAM",
						"state" => "OK",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "10505 N OWASSO EXPY OWASSO",
						"zip" => "740554667",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 700,
					],
					"pickup_time" => [
                        "from" => "2:11 PM",
                        "to" => "2:11 PM",
                    ],
					"delivery_time" => [
                        "from" => "2:11 PM",
                        "to" => "2:11 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_21(): void
	{
		$this->createMakes("JEEP", "CHRYSLER")
			->createStates("OH", "MO", "MI")
			->createTimeZones("43612", "65802", "48067")
			->assertParsing(
				21,
				[
					"load_id" => "31534-99472",
					"pickup_date" => "02/17/2023",
					"delivery_date" => "02/19/2023",
					"dispatch_instructions" =>  "RPM DRIVER APP REQUIRED AT PICK UP AND DELIVERY // NO STI - NO WEEKEND DELIVERY"
						. " WITHOUT APPROVAL FROM RPM // RATE SUBJECT TO CHANGE IF NOT FOLLOWED // Leaving units unattended"
						. " is strictly forbidden and will result in permanent ban from RPM along with liability for any damages"
						. " // ALL AFTER HOUR DROPS MUST BE APPROVED BY RPM - NOT THE DEALER\nPickup: NO SHUTTLING OF UNITS"
						. " // RPM DRIVER APP REQUIRED // PICKUP HOURS -- 24/7 // CHECK IN WITH SECURITY ON ARRIVAL // PHYSICAL"
						. " DOCS NEEDED FOR OUTGATE // MUST UPLOAD PICTURE OF PAPER COPY BOL THAT IS BEING LEFT AT ORIGIN"
						. " TO RPM DRIVER APP // RAM TRX AND HELLCAT CHALLENGER/CHARGER MODEL KEYS ARE WITH AWC PERSONNEL"
						. " - IF DRIVER CANNOT LOCATE PERSONNEL, ASK GUARD FOR ASSISTANCE. DRIVERS WILL NOT BE ABLE TO OBTAIN"
						. " KEYS ON HOLIDAYS, BETWEEN SHIFTS, AND ON WEEKENDS IF AWC IS NOT AVAILABLE // IF AN ISSUE IS DISCOVERED"
						. " WITH UNIT PREVENTING IT FROM BEING LOADED/PICKING UP, PLEASE NOTATE THE DAMAGE/REASON THIS IS"
						. " BEING LEFT BEHIND ON BOL FOR AWC TO REVIEW UPON TURN IN // MUST MATCH RPM DRIVER APP // SEV 3+"
						. " DAMAGE MUST HAVE SIGNATURES // DRIVERS MUST REPORT ISSUES (DAMAGE, NO START, ETC) TO GUARD OR"
						. " WHITE VAN WITH \"AWC\" ON THE SIDE - IF UNABLE TO LOCATE ONSITE STAFF, DRIVER MUST WRITE \"ISSUE\""
						. " ON BOL EXPLAINING WHY UNIT WAS NOT LOADED // IF A VIN IS NOT LOADED, DRIVER MUST CROSS OFF VIN"
						. " ON BOL // TRAVIS - 586-202-0513 // DRIVERS MAY NOT USE VEHICLES AS SHUTTLES // FOR AFTERHOURS"
						. " ASSISTANCE CONTACT CURTIS 734-775-5822",
					"vehicles" => [
						[
							"vin" => "1C6HJTFG9PL540002",
							"year" => "2023",
							"make" => "Jeep",
							"model" => "Gladiator",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
						[
							"vin" => "2C4RC1GG4PR520872",
							"year" => "2023",
							"make" => "Chrysler",
							"model" => "Pacifica",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
						[
							"vin" => "1C6HJTFG0PL540003",
							"year" => "2023",
							"make" => "Jeep",
							"model" => "Gladiator",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Stellantis - Raceway",
						"state" => "OH",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "5700 Telegraph Road Toledo",
						"zip" => "43612",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "NATIONAL / ALAMO PDI",
						"state" => "MO",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "5937 W. HIGHWAY EE SPRINGFIELD",
						"zip" => "65802",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 1500,
					],
					"pickup_time" => [
                        "from" => "1:33 PM",
                        "to" => "1:33 PM",
                    ],
					"delivery_time" => [
                        "from" => "1:33 PM",
                        "to" => "1:33 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_22(): void
	{
		$this->expectException(PdfFileException::class);
		$this->createMakes("NONE")
			->createStates("OH", "MO", "MI")
			->createTimeZones("43612", "65802", "48067")
			->assertParsing(
				22,
				[]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_23(): void
	{
		$this->createMakes("GMC")
			->createStates("TN", "TX", "MI")
			->createTimeZones("37174", "79762", "48067")
			->assertParsing(
				23,
				[
					"load_id" => "31538-32045",
					"pickup_date" => "03/02/2023",
					"delivery_date" => "03/06/2023",
					"dispatch_instructions" =>  "NO STI WITHOUT APPROVAL FROM RPM // RPM Driver App MUST be used. If RPM"
						. " App not used, carrier risks non-payment and accepts liability for any damage. RPM Hard Copy of"
						. " BOL required to enter past guard and Hard Copy of RPM Pickup Inspection MUST be left at the Origin"
						. " Location. Damage Severity Level 3 and above can NOT be transported without RPM Approval. Photos"
						. " are REQUIRED for ALL Damage. Failure to provide photos will result in Carrier being Liable for"
						. " all Damage Claims. Driver MUST wear appropriate Safety attire. Single, Over-Wheel Soft-Straps"
						. " ONLY. No Chains, Saddle, or Lasso Straps. All 4 wheels must be strapped down. No Moving units"
						. " except to load assigned units onto truck.\nPickup: MUST CONTACT JACK 1 HOUR PRIOR TO ARRIVAL 248-330-2677"
						. " // MUST ALSO CHECK IN AND CHECK OUT WITH RPM REPRESENTIVE ONSITE 248-330-2677 // HARD COPY BOL"
						. " REQUIRED - RPM APP REQUIRED // MUST UPLOAD PICTURE OF BOL BEING LEFT AT ORIGIN TO RPM APP // 24/7"
						. " LOADING HOURS // STAFF ON SITE M-F 05:00-14:00 // Marty Hutchins 513-289-0783, Charles Elledge"
						. " 629-3952710, Alisha Lewis 517-897-0604, Brian Durkin 931-797-0157 (Nights) // ALL DAMAGES MUST"
						. " BE SIGNED OFF BEFORE DEPARTURE // *SINGLE, OVER WHEEL STRAPS ONLY; NO CHAINS, SADDLE, OR LASSO"
						. " STRAPS* *NO MOVING UNITS EXCEPT TO LOAD ASSIGNED UNITS ONTO TRUCK* *MUST USE SEPARATE RPM INSPECTION"
						. " REPORT FOR PICKUP & DELIVERY* *RPM INSPECTION REPORT MUST BE DATED AT PICKUP & DELIVERY* *A COPY"
						. " OF THE RPM PICKUP INSPECTION REPORT MUST BE LEFT AT THE ORIGIN LOCATION* *DAMAGE MUST BE NOTED"
						. " WITH 5 DIGIT AIAG CODES ON RPM INSPECTION REPORTS AND RPM APP* *DAMAGES MUST BE NOTED IN PROPER"
						. " AREAS ON RPM INSPECTION REPORTS* *DAMAGE SEVERITY LEVEL 1 & 2 MUST BE NOTATED ON RPM PICKUP INSPECTION"
						. " REPORT* *DAMAGE SEVERITY LEVEL 3 & ABOVE CANNOT BE TRANSPORTED WITHOUT APPROVAL FROM RPM* *MUST"
						. " RECEIVE DAMAGED UNIT FORM FROM YARD STAFF IF UNIT HAS DAMAGE* *IF THERE IS DAMAGE AT PICKUP AND/OR"
						. " DELIVERY, PHOTOS ARE REQUIRED (FAILURE TO PROVIDE WILL RESULT IN CARRIER BEING RESPONSIBLE FOR"
						. " CLAIM)* *DRIVER MUST WEAR APPROPRIATE SAFETY ATTIRE (I.E. FREE OF LOOSE METAL, CHAINS, ETC.)\nDelivery:"
						. " NO STI* *NO WEEKEND DELIVERY",
					"vehicles" => [
						[
							"vin" => "1GKKNXLS8PZ177146",
							"year" => "2023",
							"make" => "GMC",
							"model" => "Acadia",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "GM - Spring Hill - HL",
						"state" => "TN",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "100 Saturn Pkwy Spring Hill",
						"zip" => "37174",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "BW ODESSA PARTNERS, LLLP",
						"state" => "TX",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "5251 E 42ND ST ODESSA",
						"zip" => "79762",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 1000,
					],
					"pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
					"delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_24(): void
	{
		$this->createMakes("GMC")
			->createStates("TN", "TX", "MI")
			->createTimeZones("37174", "79762", "48067")
			->assertParsing(
				24,
				[
					"load_id" => "31538-32045",
					"pickup_date" => "03/02/2023",
					"delivery_date" => "03/06/2023",
					"dispatch_instructions" =>  "NO STI WITHOUT APPROVAL FROM RPM // RPM Driver App MUST be used. If RPM"
						. " App not used, carrier risks non-payment and accepts liability for any damage. RPM Hard Copy of"
						. " BOL required to enter past guard and Hard Copy of RPM Pickup Inspection MUST be left at the Origin"
						. " Location. Damage Severity Level 3 and above can NOT be transported without RPM Approval. Photos"
						. " are REQUIRED for ALL Damage. Failure to provide photos will result in Carrier being Liable for"
						. " all Damage Claims. Driver MUST wear appropriate Safety attire. Single, Over-Wheel Soft-Straps"
						. " ONLY. No Chains, Saddle, or Lasso Straps. All 4 wheels must be strapped down. No Moving units"
						. " except to load assigned units onto truck.\nPickup: MUST CONTACT JACK 1 HOUR PRIOR TO ARRIVAL 248-330-2677"
						. " // MUST ALSO CHECK IN AND CHECK OUT WITH RPM REPRESENTIVE ONSITE 248-330-2677 // HARD COPY BOL"
						. " REQUIRED - RPM APP REQUIRED // MUST UPLOAD PICTURE OF BOL BEING LEFT AT ORIGIN TO RPM APP // 24/7"
						. " LOADING HOURS // STAFF ON SITE M-F 05:00-14:00 // Marty Hutchins 513-289-0783, Charles Elledge"
						. " 629-3952710, Alisha Lewis 517-897-0604, Brian Durkin 931-797-0157 (Nights) // ALL DAMAGES MUST"
						. " BE SIGNED OFF BEFORE DEPARTURE // *SINGLE, OVER WHEEL STRAPS ONLY; NO CHAINS, SADDLE, OR LASSO"
						. " STRAPS* *NO MOVING UNITS EXCEPT TO LOAD ASSIGNED UNITS ONTO TRUCK* *MUST USE SEPARATE RPM INSPECTION"
						. " REPORT FOR PICKUP & DELIVERY* *RPM INSPECTION REPORT MUST BE DATED AT PICKUP & DELIVERY* *A COPY"
						. " OF THE RPM PICKUP INSPECTION REPORT MUST BE LEFT AT THE ORIGIN LOCATION* *DAMAGE MUST BE NOTED"
						. " WITH 5 DIGIT AIAG CODES ON RPM INSPECTION REPORTS AND RPM APP* *DAMAGES MUST BE NOTED IN PROPER"
						. " AREAS ON RPM INSPECTION REPORTS* *DAMAGE SEVERITY LEVEL 1 & 2 MUST BE NOTATED ON RPM PICKUP INSPECTION"
						. " REPORT* *DAMAGE SEVERITY LEVEL 3 & ABOVE CANNOT BE TRANSPORTED WITHOUT APPROVAL FROM RPM* *MUST"
						. " RECEIVE DAMAGED UNIT FORM FROM YARD STAFF IF UNIT HAS DAMAGE* *IF THERE IS DAMAGE AT PICKUP AND/OR"
						. " DELIVERY, PHOTOS ARE REQUIRED (FAILURE TO PROVIDE WILL RESULT IN CARRIER BEING RESPONSIBLE FOR"
						. " CLAIM)* *DRIVER MUST WEAR APPROPRIATE SAFETY ATTIRE (I.E. FREE OF LOOSE METAL, CHAINS, ETC.)\nDelivery:"
						. " NO STI* *NO WEEKEND DELIVERY",
					"vehicles" => [
						[
							"vin" => "1GKKNXLS8PZ177146",
							"year" => "2023",
							"make" => "GMC",
							"model" => "Acadia",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "GM - Spring Hill - HL",
						"state" => "TN",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "100 Saturn Pkwy Spring Hill",
						"zip" => "37174",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "BW ODESSA PARTNERS, LLLP",
						"state" => "TX",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "5251 E 42ND ST ODESSA",
						"zip" => "79762",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 1000,
					],
					"pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
					"delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
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
			->createStates("TX", "NE", "MI")
			->createTimeZones("78045", "68118", "48067")
			->assertParsing(
				25,
				[
					"load_id" => "31539-87371",
					"pickup_date" => "03/15/2023",
					"delivery_date" => "03/18/2023",
					"dispatch_instructions" =>  "MUST USE YARD OUTGATE FORM AND UPLOAD PICTURE TO RPM DRIVER APP IF DAMAGES"
						. " ARE PRESNT // RPM DRIVER APP REQUIRED AT PICK UP AND DELIVERY // NO STI - NO WEEKEND DELIVERY"
						. " WITHOUT APPROVAL FROM RPM // RATE SUBJECT TO CHANGE IF NOT FOLLOWED // Leaving units unattended"
						. " is strictly forbidden and will result in permanent ban from RPM along with liability for any damages"
						. " // ALL AFTER HOUR DROPS MUST BE APPROVED BY RPM - NOT THE DEALER\nPickup: HOURS MON-SAT 0600 -"
						. " 1600 // HELP ON SITE Joseph DiBella -- (734) 765-7193 // STREET ADDRESS 3210 PINTO VALLE, LAREDO,"
						. " TX, 78045 // PHYSICAL DOCUMENTS REQUIRED // all severities of damage identified on product being"
						. " moved out of any VASCOR facility must be legible and properly documented on a VASCOR outgate sheet."
						. " Also, prior to the units handling, the damage must be verified and signed for by an authorized"
						. " VASCOR representative. Failure to follow these steps will result in VASCOR denying liability for"
						. " any damages that result in a claim. // SINGLE, OVER WHEEL STRAPS ONLY; NO CHAINS, SADDLE, OR LASSO"
						. " STRAPS ALL FOUR WHEELS MUST BE STRAPPED DOWN, NO EXCEPTIONS NO MOVING UNITS EXCEPT TO LOAD ASSIGNED"
						. " UNITS ONTO TRUCK* ALL DAMAGES ON APP MUST MATCH HARD COPY LEFT AT YARD* RPM PICKUP INSPECTION"
						. " REPORT MUST BE LEFT AT THE ORIGIN LOCATION DAMAGE MUST BE NOTED WITH 5 DIGIT AIAG CODES ON RPM"
						. " INSPECTION REPORTS AND RPM DRIVER APP * DAMAGES MUST BE NOTED IN PROPER AREAS ON RPM INSPECTION"
						. " REPORTS DAMAGE PHOTOS ARE REQUIRED (FAILURE TO PROVIDE WILL RESULT IN CARRIER BEING RESPONSIBLE"
						. " FOR CLAIM) DRIVER MUST WEAR APPROPRIATE SAFETY ATTIRE (I.E. FREE OF LOOSE METAL, CHAINS, ETC.)"
						. " *IF PLASTIC FILM IS LOOSE, REMOVE FROM UNIT* IF FILM IS DAMAGED AT DELIVERY, MAKE NOTE OF DAMAGED"
						. " FILM AND MARK DAMAGES CAUSED BY FILM*FAILURE TO USE RPM APP OR RPM OEM INSPECTION DOCS WILL RESULT"
						. " IN DELAYED PAYMENT AND LIABILITY OF CLAIMS",
					"vehicles" => [
						[
							"vin" => "3C6MRVJGXPE512180",
							"year" => "2023",
							"make" => "Ram",
							"model" => "ProMaster",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Vascor Laredo Yard",
						"state" => "TX",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "3210 Pinto Valle Laredo",
						"zip" => "78045",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "ADESA OPEN LANE",
						"state" => "NE",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "17950 BURT STREET OMAHA",
						"zip" => "68118",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 1100,
					],
					"pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
					"delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_26(): void
	{
		$this->createMakes("RAM")
			->createStates("TX", "MN", "MI")
			->createTimeZones("78045", "550755913", "48067")
			->assertParsing(
				26,
				[
					"load_id" => "31540-54344",
					"pickup_date" => "02/27/2023",
					"delivery_date" => "02/27/2023",
					"dispatch_instructions" =>  "RPM DRIVER APP REQUIRED AT PICK UP AND DELIVERY // NO STI - NO WEEKEND DELIVERY"
						. " WITHOUT APPROVAL FROM RPM // RATE SUBJECT TO CHANGE IF NOT FOLLOWED // Leaving units unattended"
						. " is strictly forbidden and will result in permanent ban from RPM along with liability for any damages"
						. " // ALL AFTER HOUR DROPS MUST BE APPROVED BY RPM - NOT THE DEALER\nPickup: HOURS MON-SAT 0600 -"
						. " 1600 // HELP ON SITE Joseph DiBella -- (734) 765-7193 // STREET ADDRESS 3210 PINTO VALLE, LAREDO,"
						. " TX, 78045 // PHYSICAL DOCUMENTS REQUIRED // all severities of damage identified on product being"
						. " moved out of any VASCOR facility must be legible and properly documented on a VASCOR outgate sheet."
						. " Also, prior to the units handling, the damage must be verified and signed for by an authorized"
						. " VASCOR representative. Failure to follow these steps will result in VASCOR denying liability for"
						. " any damages that result in a claim. // SINGLE, OVER WHEEL STRAPS ONLY; NO CHAINS, SADDLE, OR LASSO"
						. " STRAPS ALL FOUR WHEELS MUST BE STRAPPED DOWN, NO EXCEPTIONS NO MOVING UNITS EXCEPT TO LOAD ASSIGNED"
						. " UNITS ONTO TRUCK* ALL DAMAGES ON APP MUST MATCH HARD COPY LEFT AT YARD* RPM PICKUP INSPECTION"
						. " REPORT MUST BE LEFT AT THE ORIGIN LOCATION DAMAGE MUST BE NOTED WITH 5 DIGIT AIAG CODES ON RPM"
						. " INSPECTION REPORTS AND RPM DRIVER APP * DAMAGES MUST BE NOTED IN PROPER AREAS ON RPM INSPECTION"
						. " REPORTS DAMAGE PHOTOS ARE REQUIRED (FAILURE TO PROVIDE WILL RESULT IN CARRIER BEING RESPONSIBLE"
						. " FOR CLAIM) DRIVER MUST WEAR APPROPRIATE SAFETY ATTIRE (I.E. FREE OF LOOSE METAL, CHAINS, ETC.)"
						. " *IF PLASTIC FILM IS LOOSE, REMOVE FROM UNIT* IF FILM IS DAMAGED AT DELIVERY, MAKE NOTE OF DAMAGED"
						. " FILM AND MARK DAMAGES CAUSED BY FILM*FAILURE TO USE RPM APP OR RPM OEM INSPECTION DOCS WILL RESULT"
						. " IN DELAYED PAYMENT AND LIABILITY OF CLAIMS",
					"vehicles" => [
						[
							"vin" => "3C6LRVDG9PE528426",
							"year" => "2023",
							"make" => "Ram",
							"model" => "ProMaster",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Vascor Laredo Yard",
						"state" => "TX",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "3210 Pinto Valle Laredo",
						"zip" => "78045",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "FURY MOTORS INC",
						"state" => "MN",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "1000 CONCORD ST S SOUTH ST PAUL",
						"zip" => "550755913",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 1300,
					],
					"pickup_time" => null,
					"delivery_time" => null,
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_27(): void
	{
		$this->createMakes("RAM", "JEEP")
			->createStates("OH", "NC", "MI")
			->createTimeZones("43612", "286978637", "48067")
			->assertParsing(
				27,
				[
					"load_id" => "31540-91090",
					"pickup_date" => "02/17/2023",
					"delivery_date" => "02/19/2023",
					"dispatch_instructions" =>  "RPM DRIVER APP REQUIRED AT PICK UP AND DELIVERY // NO STI - NO WEEKEND DELIVERY"
						. " WITHOUT APPROVAL FROM RPM // RATE SUBJECT TO CHANGE IF NOT FOLLOWED // Leaving units unattended"
						. " is strictly forbidden and will result in permanent ban from RPM along with liability for any damages"
						. " // ALL AFTER HOUR DROPS MUST BE APPROVED BY RPM - NOT THE DEALER\nPickup: NO SHUTTLING OF UNITS"
						. " // RPM DRIVER APP REQUIRED // PICKUP HOURS -- 24/7 // CHECK IN WITH SECURITY ON ARRIVAL // PHYSICAL"
						. " DOCS NEEDED FOR OUTGATE // MUST UPLOAD PICTURE OF PAPER COPY BOL THAT IS BEING LEFT AT ORIGIN"
						. " TO RPM DRIVER APP // RAM TRX AND HELLCAT CHALLENGER/CHARGER MODEL KEYS ARE WITH AWC PERSONNEL"
						. " - IF DRIVER CANNOT LOCATE PERSONNEL, ASK GUARD FOR ASSISTANCE. DRIVERS WILL NOT BE ABLE TO OBTAIN"
						. " KEYS ON HOLIDAYS, BETWEEN SHIFTS, AND ON WEEKENDS IF AWC IS NOT AVAILABLE // IF AN ISSUE IS DISCOVERED"
						. " WITH UNIT PREVENTING IT FROM BEING LOADED/PICKING UP, PLEASE NOTATE THE DAMAGE/REASON THIS IS"
						. " BEING LEFT BEHIND ON BOL FOR AWC TO REVIEW UPON TURN IN // MUST MATCH RPM DRIVER APP // SEV 3+"
						. " DAMAGE MUST HAVE SIGNATURES // DRIVERS MUST REPORT ISSUES (DAMAGE, NO START, ETC) TO GUARD OR"
						. " WHITE VAN WITH \"AWC\" ON THE SIDE - IF UNABLE TO LOCATE ONSITE STAFF, DRIVER MUST WRITE \"ISSUE\""
						. " ON BOL EXPLAINING WHY UNIT WAS NOT LOADED // IF A VIN IS NOT LOADED, DRIVER MUST CROSS OFF VIN"
						. " ON BOL // TRAVIS - 586-202-0513 // DRIVERS MAY NOT USE VEHICLES AS SHUTTLES // FOR AFTERHOURS"
						. " ASSISTANCE CONTACT CURTIS 734-775-5822",
					"vehicles" => [
						[
							"vin" => "1C6SRFGT3PN553666",
							"year" => "2023",
							"make" => "Ram",
							"model" => "1500",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
						[
							"vin" => "1C6SRFFT0NN477774",
							"year" => "2022",
							"make" => "Ram",
							"model" => "1500",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
						[
							"vin" => "1C4GJXAN8PW635490",
							"year" => "2023",
							"make" => "Jeep",
							"model" => "Wrangler",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Stellantis - Raceway",
						"state" => "OH",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "5700 Telegraph Road Toledo",
						"zip" => "43612",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "RANDY MARION CHRYSLER DODGE JEEP R",
						"state" => "NC",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "2000 W US HIGHWAY 421 WILKESBORO",
						"zip" => "286978637",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 1500,
					],
					"pickup_time" => [
                        "from" => "1:36 PM",
                        "to" => "1:36 PM",
                    ],
					"delivery_time" => [
                        "from" => "1:36 PM",
                        "to" => "1:36 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_28(): void
	{
		$this->createMakes("FORD")
			->createStates("TN", "AL", "MI")
			->createTimeZones("37921", "35004", "48067")
			->assertParsing(
				28,
                [
                    "load_id" => "31541-97697",
                    "pickup_date" => "01/31/2023",
                    "delivery_date" => "02/01/2023",
                    "dispatch_instructions" => "Pickup: Pickup # KEYS/DRIVES Appointment # M-F 7AM 5PM MUST CALL 24HRS AHEAD"
                        . " & 1HR OUT FOR PU, KEYS / DRIVES, NO DAMAGES, M - F 07:00 - 17:00 *** Pickup Contact Name: Eric"
                        . " Jenkins, Pickup Contact Phone #: 865-540-8120. Must confirm VIN before pickup, must have release"
                        . " at pickup\nDelivery: (205) 640-1010 Delivery # VEHICLES MUST BE CHECKED IN UNDER AS WHEELS Vehicle"
                        . " must check in under as wheels, must leave paperwork in vehicle stating WHEELS. Must have signature,"
                        . " stamp or picture. Damaged units must have pictures. VEHICLES MUST BE CHECKED IN UNDER AS WHEELS",
                    "vehicles" => [
                        [
                            "vin" => "1FTFW1EF8HFB35750",
                            "year" => "2017",
							"make" => "Ford",
							"model" => "F-150",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Superior Pool Products",
						"state" => "TN",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "2815 Texas Ave Ste 118 Knoxville",
						"zip" => "37921",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "ADESA Birmingham",
						"state" => "AL",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "804 SOLLIE DR Moody",
						"zip" => "35004",
						"phones" => [],
						"fax" => null,
						"phone" => "12056401010",
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 300,
                    ],
                    "pickup_time" => [
                        "from" => "3:00 PM",
                        "to" => "3:00 PM",
                    ],
                    "delivery_time" => [
                        "from" => "3:00 PM",
                        "to" => "3:00 PM",
                    ],
                ]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_29(): void
    {
        $this->expectException(PdfFileException::class);
        $this->createMakes("NONE")
            ->createStates("TN", "TX", "MI")
            ->createTimeZones("37174", "77385", "48067")
            ->assertParsing(
                29,
                []
            );
    }

	/**
	* @throws Throwable
	*/
	public function test_30(): void
	{
		$this->createMakes("TESLA")
			->createStates("NJ", "OH", "MI")
			->createTimeZones("08034", "43219", "48067")
			->assertParsing(
				30,
				[
					"load_id" => "31543-61113",
					"pickup_date" => "01/27/2023",
					"delivery_date" => "01/31/2023",
					"dispatch_instructions" =>  "Must Use Tesla App to get Paid; No STI's; Delivery Signature Required; Note"
						. " All Damage at Pickup or Carrier will be held liable for all claims\nDelivery: Monday-Friday 8-5,"
						. " Dropbox. (614) 336-2042 ext 4 - All Drivers must use the Tesla App or $250 penalty will apply."
						. " Must deliver by 12pm on the \"Need By\" date or else $50/vin per day penalty will apply. STI is"
						. " not permitted. Vins reported as delivered before they are physically dropped on site will incur"
						. " $500 penalty. If the car is delivered to the wrong location - Carrier has 24 hours to transport"
						. " the units to correct destination or rate will be reduced. Units delivered to wrong destination"
						. " will be corrected at no cost.",
					"vehicles" => [
						[
							"vin" => "5YJ3E1EA9MF915152",
							"year" => "2021",
							"make" => "Tesla",
							"model" => "Model 3",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Bensalem Cherry Hill Cuthbert road",
						"state" => "NJ",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "1840 Old Cuthbert Rd Cherry Hill",
						"zip" => "08034",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "Tesla Columbus Easton Loop Service Center",
						"state" => "OH",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "4099 Easton Loop West Columbus",
						"zip" => "43219",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 580,
					],
					"pickup_time" => null,
					"delivery_time" => [
                        "from" => "9:45 AM",
                        "to" => "3:00 PM",
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
			->createStates("TX", "MN", "MI")
			->createTimeZones("78045", "55337", "48067")
			->assertParsing(
				31,
				[
					"load_id" => "31547-11505",
					"pickup_date" => "03/01/2023",
					"delivery_date" => "03/05/2023",
					"dispatch_instructions" =>  "RPM DRIVER APP REQUIRED AT PICK UP AND DELIVERY // NO STI - NO WEEKEND DELIVERY"
						. " WITHOUT APPROVAL FROM RPM // RATE SUBJECT TO CHANGE IF NOT FOLLOWED // Leaving units unattended"
						. " is strictly forbidden and will result in permanent ban from RPM along with liability for any damages"
						. " // ALL AFTER HOUR DROPS MUST BE APPROVED BY RPM - NOT THE DEALER\nPickup: HOURS MON-SAT 0600 -"
						. " 1600 // HELP ON SITE Joseph DiBella -- (734) 765-7193 // STREET ADDRESS 3210 PINTO VALLE, LAREDO,"
						. " TX, 78045 // PHYSICAL DOCUMENTS REQUIRED // all severities of damage identified on product being"
						. " moved out of any VASCOR facility must be legible and properly documented on a VASCOR outgate sheet."
						. " Also, prior to the units handling, the damage must be verified and signed for by an authorized"
						. " VASCOR representative. Failure to follow these steps will result in VASCOR denying liability for"
						. " any damages that result in a claim. // SINGLE, OVER WHEEL STRAPS ONLY; NO CHAINS, SADDLE, OR LASSO"
						. " STRAPS ALL FOUR WHEELS MUST BE STRAPPED DOWN, NO EXCEPTIONS NO MOVING UNITS EXCEPT TO LOAD ASSIGNED"
						. " UNITS ONTO TRUCK* ALL DAMAGES ON APP MUST MATCH HARD COPY LEFT AT YARD* RPM PICKUP INSPECTION"
						. " REPORT MUST BE LEFT AT THE ORIGIN LOCATION DAMAGE MUST BE NOTED WITH 5 DIGIT AIAG CODES ON RPM"
						. " INSPECTION REPORTS AND RPM DRIVER APP * DAMAGES MUST BE NOTED IN PROPER AREAS ON RPM INSPECTION"
						. " REPORTS DAMAGE PHOTOS ARE REQUIRED (FAILURE TO PROVIDE WILL RESULT IN CARRIER BEING RESPONSIBLE"
						. " FOR CLAIM) DRIVER MUST WEAR APPROPRIATE SAFETY ATTIRE (I.E. FREE OF LOOSE METAL, CHAINS, ETC.)"
						. " *IF PLASTIC FILM IS LOOSE, REMOVE FROM UNIT* IF FILM IS DAMAGED AT DELIVERY, MAKE NOTE OF DAMAGED"
						. " FILM AND MARK DAMAGES CAUSED BY FILM*FAILURE TO USE RPM APP OR RPM OEM INSPECTION DOCS WILL RESULT"
						. " IN DELAYED PAYMENT AND LIABILITY OF CLAIMS",
					"vehicles" => [
						[
							"vin" => "3C6LRVDG2PE506249",
							"year" => "2023",
							"make" => "Ram",
							"model" => "ProMaster",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Vascor Laredo Yard",
						"state" => "TX",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "3210 Pinto Valle Laredo",
						"zip" => "78045",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "DODGE OF BURNSVILLE INC",
						"state" => "MN",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "12101 35W SOUTH BURNSVILLE",
						"zip" => "55337",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 1400,
					],
					"pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
					"delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_32(): void
	{
		$this->createMakes("RAM")
			->createStates("OH", "NC", "MI")
			->createTimeZones("43612", "272782506", "48067")
			->assertParsing(
				32,
				[
					"load_id" => "31548-08657",
					"pickup_date" => "02/21/2023",
					"delivery_date" => "02/23/2023",
					"dispatch_instructions" =>  "RPM DRIVER APP REQUIRED AT PICK UP AND DELIVERY // NO STI - NO WEEKEND DELIVERY"
						. " WITHOUT APPROVAL FROM RPM // RATE SUBJECT TO CHANGE IF NOT FOLLOWED // Leaving units unattended"
						. " is strictly forbidden and will result in permanent ban from RPM along with liability for any damages"
						. " // ALL AFTER HOUR DROPS MUST BE APPROVED BY RPM - NOT THE DEALER\nPickup: NO SHUTTLING OF UNITS"
						. " // RPM DRIVER APP REQUIRED // PICKUP HOURS -- 24/7 // CHECK IN WITH SECURITY ON ARRIVAL // PHYSICAL"
						. " DOCS NEEDED FOR OUTGATE // MUST UPLOAD PICTURE OF PAPER COPY BOL THAT IS BEING LEFT AT ORIGIN"
						. " TO RPM DRIVER APP // RAM TRX AND HELLCAT CHALLENGER/CHARGER MODEL KEYS ARE WITH AWC PERSONNEL"
						. " - IF DRIVER CANNOT LOCATE PERSONNEL, ASK GUARD FOR ASSISTANCE. DRIVERS WILL NOT BE ABLE TO OBTAIN"
						. " KEYS ON HOLIDAYS, BETWEEN SHIFTS, AND ON WEEKENDS IF AWC IS NOT AVAILABLE // IF AN ISSUE IS DISCOVERED"
						. " WITH UNIT PREVENTING IT FROM BEING LOADED/PICKING UP, PLEASE NOTATE THE DAMAGE/REASON THIS IS"
						. " BEING LEFT BEHIND ON BOL FOR AWC TO REVIEW UPON TURN IN // MUST MATCH RPM DRIVER APP // SEV 3+"
						. " DAMAGE MUST HAVE SIGNATURES // DRIVERS MUST REPORT ISSUES (DAMAGE, NO START, ETC) TO GUARD OR"
						. " WHITE VAN WITH \"AWC\" ON THE SIDE - IF UNABLE TO LOCATE ONSITE STAFF, DRIVER MUST WRITE \"ISSUE\""
						. " ON BOL EXPLAINING WHY UNIT WAS NOT LOADED // IF A VIN IS NOT LOADED, DRIVER MUST CROSS OFF VIN"
						. " ON BOL // TRAVIS - 586-202-0513 // DRIVERS MAY NOT USE VEHICLES AS SHUTTLES // FOR AFTERHOURS"
						. " ASSISTANCE CONTACT CURTIS 734-775-5822",
					"vehicles" => [
						[
							"vin" => "1C6SRFU90PN500805",
							"year" => "2023",
							"make" => "Ram",
							"model" => "1500",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Stellantis - Raceway",
						"state" => "OH",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "5700 Telegraph Road Toledo",
						"zip" => "43612",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "HILLSBOROUGH CHRYSLER DODGE JEEP R",
						"state" => "NC",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "259 S CHURTON ST HILLSBOROUGH",
						"zip" => "272782506",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 500,
					],
					"pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
					"delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_33(): void
	{
		$this->expectException(PdfFileException::class);
		$this->createMakes("NONE")
			->createStates("OH", "NC", "MI")
			->createTimeZones("43612", "272782506", "48067")
			->assertParsing(
				33,
				[]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_34(): void
	{
		$this->createMakes("RAM")
			->createStates("TX", "MN", "MI")
			->createTimeZones("78045", "550259459", "48067")
			->assertParsing(
				34,
				[
					"load_id" => "31552-13353",
					"pickup_date" => "02/28/2023",
					"delivery_date" => "03/04/2023",
					"dispatch_instructions" =>  "RPM DRIVER APP REQUIRED AT PICK UP AND DELIVERY // NO STI - NO WEEKEND DELIVERY"
						. " WITHOUT APPROVAL FROM RPM // RATE SUBJECT TO CHANGE IF NOT FOLLOWED // Leaving units unattended"
						. " is strictly forbidden and will result in permanent ban from RPM along with liability for any damages"
						. " // ALL AFTER HOUR DROPS MUST BE APPROVED BY RPM - NOT THE DEALER\nPickup: HOURS MON-SAT 0600 -"
						. " 1600 // HELP ON SITE Joseph DiBella -- (734) 765-7193 // STREET ADDRESS 3210 PINTO VALLE, LAREDO,"
						. " TX, 78045 // PHYSICAL DOCUMENTS REQUIRED // all severities of damage identified on product being"
						. " moved out of any VASCOR facility must be legible and properly documented on a VASCOR outgate sheet."
						. " Also, prior to the units handling, the damage must be verified and signed for by an authorized"
						. " VASCOR representative. Failure to follow these steps will result in VASCOR denying liability for"
						. " any damages that result in a claim. // SINGLE, OVER WHEEL STRAPS ONLY; NO CHAINS, SADDLE, OR LASSO"
						. " STRAPS ALL FOUR WHEELS MUST BE STRAPPED DOWN, NO EXCEPTIONS NO MOVING UNITS EXCEPT TO LOAD ASSIGNED"
						. " UNITS ONTO TRUCK* ALL DAMAGES ON APP MUST MATCH HARD COPY LEFT AT YARD* RPM PICKUP INSPECTION"
						. " REPORT MUST BE LEFT AT THE ORIGIN LOCATION DAMAGE MUST BE NOTED WITH 5 DIGIT AIAG CODES ON RPM"
						. " INSPECTION REPORTS AND RPM DRIVER APP * DAMAGES MUST BE NOTED IN PROPER AREAS ON RPM INSPECTION"
						. " REPORTS DAMAGE PHOTOS ARE REQUIRED (FAILURE TO PROVIDE WILL RESULT IN CARRIER BEING RESPONSIBLE"
						. " FOR CLAIM) DRIVER MUST WEAR APPROPRIATE SAFETY ATTIRE (I.E. FREE OF LOOSE METAL, CHAINS, ETC.)"
						. " *IF PLASTIC FILM IS LOOSE, REMOVE FROM UNIT* IF FILM IS DAMAGED AT DELIVERY, MAKE NOTE OF DAMAGED"
						. " FILM AND MARK DAMAGES CAUSED BY FILM*FAILURE TO USE RPM APP OR RPM OEM INSPECTION DOCS WILL RESULT"
						. " IN DELAYED PAYMENT AND LIABILITY OF CLAIMS",
					"vehicles" => [
						[
							"vin" => "3C6MRVUGXPE545157",
							"year" => "2023",
							"make" => "Ram",
							"model" => "ProMaster",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
						[
							"vin" => "3C6LRVPG9PE543004",
							"year" => "2023",
							"make" => "Ram",
							"model" => "ProMaster",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Vascor Laredo Yard",
						"state" => "TX",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "3210 Pinto Valle Laredo",
						"zip" => "78045",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "WALDOCH CRAFTS INC",
						"state" => "MN",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "13821 LAKE DR FOREST LAKE",
						"zip" => "550259459",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 2800,
					],
					"pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
					"delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_35(): void
	{
		$this->createMakes("RAM")
			->createStates("TX", "SD", "MI")
			->createTimeZones("78045", "571082242", "48067")
			->assertParsing(
				35,
				[
					"load_id" => "31552-16626",
					"pickup_date" => "03/16/2023",
					"delivery_date" => "03/20/2023",
					"dispatch_instructions" =>  "MUST USE YARD OUTGATE FORM AND UPLOAD PICTURE TO RPM DRIVER APP IF DAMAGES"
						. " ARE PRESNT // RPM DRIVER APP REQUIRED AT PICK UP AND DELIVERY // NO STI - NO WEEKEND DELIVERY"
						. " WITHOUT APPROVAL FROM RPM // RATE SUBJECT TO CHANGE IF NOT FOLLOWED // Leaving units unattended"
						. " is strictly forbidden and will result in permanent ban from RPM along with liability for any damages"
						. " // ALL AFTER HOUR DROPS MUST BE APPROVED BY RPM - NOT THE DEALER\nPickup: HOURS MON-SAT 0600 -"
						. " 1600 // HELP ON SITE Joseph DiBella -- (734) 765-7193 // STREET ADDRESS 3210 PINTO VALLE, LAREDO,"
						. " TX, 78045 // PHYSICAL DOCUMENTS REQUIRED // all severities of damage identified on product being"
						. " moved out of any VASCOR facility must be legible and properly documented on a VASCOR outgate sheet."
						. " Also, prior to the units handling, the damage must be verified and signed for by an authorized"
						. " VASCOR representative. Failure to follow these steps will result in VASCOR denying liability for"
						. " any damages that result in a claim. // SINGLE, OVER WHEEL STRAPS ONLY; NO CHAINS, SADDLE, OR LASSO"
						. " STRAPS ALL FOUR WHEELS MUST BE STRAPPED DOWN, NO EXCEPTIONS NO MOVING UNITS EXCEPT TO LOAD ASSIGNED"
						. " UNITS ONTO TRUCK* ALL DAMAGES ON APP MUST MATCH HARD COPY LEFT AT YARD* RPM PICKUP INSPECTION"
						. " REPORT MUST BE LEFT AT THE ORIGIN LOCATION DAMAGE MUST BE NOTED WITH 5 DIGIT AIAG CODES ON RPM"
						. " INSPECTION REPORTS AND RPM DRIVER APP * DAMAGES MUST BE NOTED IN PROPER AREAS ON RPM INSPECTION"
						. " REPORTS DAMAGE PHOTOS ARE REQUIRED (FAILURE TO PROVIDE WILL RESULT IN CARRIER BEING RESPONSIBLE"
						. " FOR CLAIM) DRIVER MUST WEAR APPROPRIATE SAFETY ATTIRE (I.E. FREE OF LOOSE METAL, CHAINS, ETC.)"
						. " *IF PLASTIC FILM IS LOOSE, REMOVE FROM UNIT* IF FILM IS DAMAGED AT DELIVERY, MAKE NOTE OF DAMAGED"
						. " FILM AND MARK DAMAGES CAUSED BY FILM*FAILURE TO USE RPM APP OR RPM OEM INSPECTION DOCS WILL RESULT"
						. " IN DELAYED PAYMENT AND LIABILITY OF CLAIMS",
					"vehicles" => [
						[
							"vin" => "3C6LRVBG7PE540755",
							"year" => "2023",
							"make" => "Ram",
							"model" => "ProMaster",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Vascor Laredo Yard",
						"state" => "TX",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "3210 Pinto Valle Laredo",
						"zip" => "78045",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "BILLION CHRYSLER JEEP DODGE RAM FI",
						"state" => "SD",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "5910 S LOUISE AVE SIOUX FALLS",
						"zip" => "571082242",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 1300,
					],
					"pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
					"delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_36(): void
	{
		$this->createMakes("RAM")
			->createStates("OH", "MS", "MI")
			->createTimeZones("43612", "388665703", "48067")
			->assertParsing(
				36,
				[
					"load_id" => "31552-34032",
					"pickup_date" => "03/04/2023",
					"delivery_date" => "03/06/2023",
					"dispatch_instructions" =>  "RPM DRIVER APP REQUIRED AT PICK UP AND DELIVERY // NO STI - NO WEEKEND DELIVERY"
						. " WITHOUT APPROVAL FROM RPM // RATE SUBJECT TO CHANGE IF NOT FOLLOWED // Leaving units unattended"
						. " is strictly forbidden and will result in permanent ban from RPM along with liability for any damages"
						. " // ALL AFTER HOUR DROPS MUST BE APPROVED BY RPM - NOT THE DEALER\nPickup: NO SHUTTLING OF UNITS"
						. " // RPM DRIVER APP REQUIRED // PICKUP HOURS -- 24/7 // CHECK IN WITH SECURITY ON ARRIVAL // PHYSICAL"
						. " DOCS NEEDED FOR OUTGATE // MUST UPLOAD PICTURE OF PAPER COPY BOL THAT IS BEING LEFT AT ORIGIN"
						. " TO RPM DRIVER APP // RAM TRX AND HELLCAT CHALLENGER/CHARGER MODEL KEYS ARE WITH AWC PERSONNEL"
						. " - IF DRIVER CANNOT LOCATE PERSONNEL, ASK GUARD FOR ASSISTANCE. DRIVERS WILL NOT BE ABLE TO OBTAIN"
						. " KEYS ON HOLIDAYS, BETWEEN SHIFTS, AND ON WEEKENDS IF AWC IS NOT AVAILABLE // IF AN ISSUE IS DISCOVERED"
						. " WITH UNIT PREVENTING IT FROM BEING LOADED/PICKING UP, PLEASE NOTATE THE DAMAGE/REASON THIS IS"
						. " BEING LEFT BEHIND ON BOL FOR AWC TO REVIEW UPON TURN IN // MUST MATCH RPM DRIVER APP // SEV 3+"
						. " DAMAGE MUST HAVE SIGNATURES // DRIVERS MUST REPORT ISSUES (DAMAGE, NO START, ETC) TO GUARD OR"
						. " WHITE VAN WITH \"AWC\" ON THE SIDE - IF UNABLE TO LOCATE ONSITE STAFF, DRIVER MUST WRITE \"ISSUE\""
						. " ON BOL EXPLAINING WHY UNIT WAS NOT LOADED // IF A VIN IS NOT LOADED, DRIVER MUST CROSS OFF VIN"
						. " ON BOL // TRAVIS - 586-202-0513 // DRIVERS MAY NOT USE VEHICLES AS SHUTTLES // FOR AFTERHOURS"
						. " ASSISTANCE CONTACT CURTIS 734-775-5822",
					"vehicles" => [
						[
							"vin" => "1C6SRFJT2PN589891",
							"year" => "2023",
							"make" => "Ram",
							"model" => "1500",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
						[
							"vin" => "1C6SRFHT9PN582765",
							"year" => "2023",
							"make" => "Ram",
							"model" => "1500",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Stellantis - Raceway",
						"state" => "OH",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "5700 Telegraph Road Toledo",
						"zip" => "43612",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "CARLOCK CHRYSLER DODGE JEEP RAM",
						"state" => "MS",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "966 CROSS CREEK DRIVE SALTILLO",
						"zip" => "388665703",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 1200,
					],
					"pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
					"delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_37(): void
	{
		$this->createMakes("RAM")
			->createStates("OH", "MS", "MI")
			->createTimeZones("43612", "388665703", "48067")
			->assertParsing(
				37,
				[
					"load_id" => "31552-34032",
					"pickup_date" => "03/04/2023",
					"delivery_date" => "03/06/2023",
					"dispatch_instructions" =>  "RPM DRIVER APP REQUIRED AT PICK UP AND DELIVERY // NO STI - NO WEEKEND DELIVERY"
						. " WITHOUT APPROVAL FROM RPM // RATE SUBJECT TO CHANGE IF NOT FOLLOWED // Leaving units unattended"
						. " is strictly forbidden and will result in permanent ban from RPM along with liability for any damages"
						. " // ALL AFTER HOUR DROPS MUST BE APPROVED BY RPM - NOT THE DEALER\nPickup: NO SHUTTLING OF UNITS"
						. " // RPM DRIVER APP REQUIRED // PICKUP HOURS -- 24/7 // CHECK IN WITH SECURITY ON ARRIVAL // PHYSICAL"
						. " DOCS NEEDED FOR OUTGATE // MUST UPLOAD PICTURE OF PAPER COPY BOL THAT IS BEING LEFT AT ORIGIN"
						. " TO RPM DRIVER APP // RAM TRX AND HELLCAT CHALLENGER/CHARGER MODEL KEYS ARE WITH AWC PERSONNEL"
						. " - IF DRIVER CANNOT LOCATE PERSONNEL, ASK GUARD FOR ASSISTANCE. DRIVERS WILL NOT BE ABLE TO OBTAIN"
						. " KEYS ON HOLIDAYS, BETWEEN SHIFTS, AND ON WEEKENDS IF AWC IS NOT AVAILABLE // IF AN ISSUE IS DISCOVERED"
						. " WITH UNIT PREVENTING IT FROM BEING LOADED/PICKING UP, PLEASE NOTATE THE DAMAGE/REASON THIS IS"
						. " BEING LEFT BEHIND ON BOL FOR AWC TO REVIEW UPON TURN IN // MUST MATCH RPM DRIVER APP // SEV 3+"
						. " DAMAGE MUST HAVE SIGNATURES // DRIVERS MUST REPORT ISSUES (DAMAGE, NO START, ETC) TO GUARD OR"
						. " WHITE VAN WITH \"AWC\" ON THE SIDE - IF UNABLE TO LOCATE ONSITE STAFF, DRIVER MUST WRITE \"ISSUE\""
						. " ON BOL EXPLAINING WHY UNIT WAS NOT LOADED // IF A VIN IS NOT LOADED, DRIVER MUST CROSS OFF VIN"
						. " ON BOL // TRAVIS - 586-202-0513 // DRIVERS MAY NOT USE VEHICLES AS SHUTTLES // FOR AFTERHOURS"
						. " ASSISTANCE CONTACT CURTIS 734-775-5822",
					"vehicles" => [
						[
							"vin" => "1C6SRFJT2PN589891",
							"year" => "2023",
							"make" => "Ram",
							"model" => "1500",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
						[
							"vin" => "1C6SRFHT9PN582765",
							"year" => "2023",
							"make" => "Ram",
							"model" => "1500",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Stellantis - Raceway",
						"state" => "OH",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "5700 Telegraph Road Toledo",
						"zip" => "43612",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "CARLOCK CHRYSLER DODGE JEEP RAM",
						"state" => "MS",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "966 CROSS CREEK DRIVE SALTILLO",
						"zip" => "388665703",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 1200,
					],
					"pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
					"delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_38(): void
	{
		$this->createMakes("INFINITI", "NISSAN")
			->createStates("NJ", "NC", "MI")
			->createTimeZones("8505", "28227", "48067")
			->assertParsing(
				38,
				[
					"load_id" => "31552-54604",
					"pickup_date" => "02/06/2023",
					"delivery_date" => "02/08/2023",
					"dispatch_instructions" => "Pickup: auction to store\nDelivery: (704) 535-7791 auction to store",
					"vehicles" => [
						[
							"vin" => "5N1DL0MM1KC569412",
							"year" => "2019",
							"make" => "INFINITI",
							"model" => "QX60",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
						[
							"vin" => "5N1DR2MM7KC650327",
							"year" => "2019",
							"make" => "Nissan",
							"model" => "Pathfinder",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Manheim New Jersey",
						"state" => "NJ",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "730 New Jersey 68 Bordentown",
						"zip" => "8505",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "7106 CARMAX - CHARLOTTE",
						"state" => "NC",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "7700 KREFELD DR. CHARLOTTE",
						"zip" => "28227",
						"phones" => [],
						"fax" => null,
						"phone" => "17045357791",
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 896,
					],
					"pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
					"delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
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
			->createStates("OH", "NC", "MI")
			->createTimeZones("43612", "275348348", "48067")
			->assertParsing(
				39,
				[
					"load_id" => "31553-96896",
					"pickup_date" => "02/11/2023",
					"delivery_date" => "02/13/2023",
					"dispatch_instructions" =>  "RPM DRIVER APP REQUIRED AT PICK UP AND DELIVERY // NO STI - NO WEEKEND DELIVERY"
						. " WITHOUT APPROVAL FROM RPM // RATE SUBJECT TO CHANGE IF NOT FOLLOWED // Leaving units unattended"
						. " is strictly forbidden and will result in permanent ban from RPM along with liability for any damages"
						. " // ALL AFTER HOUR DROPS MUST BE APPROVED BY RPM - NOT THE DEALER\nPickup: NO SHUTTLING OF UNITS"
						. " // RPM DRIVER APP REQUIRED // PICKUP HOURS -- 24/7 // CHECK IN WITH SECURITY ON ARRIVAL // PHYSICAL"
						. " DOCS NEEDED FOR OUTGATE // MUST UPLOAD PICTURE OF PAPER COPY BOL THAT IS BEING LEFT AT ORIGIN"
						. " TO RPM DRIVER APP // RAM TRX AND HELLCAT CHALLENGER/CHARGER MODEL KEYS ARE WITH AWC PERSONNEL"
						. " - IF DRIVER CANNOT LOCATE PERSONNEL, ASK GUARD FOR ASSISTANCE. DRIVERS WILL NOT BE ABLE TO OBTAIN"
						. " KEYS ON HOLIDAYS, BETWEEN SHIFTS, AND ON WEEKENDS IF AWC IS NOT AVAILABLE // IF AN ISSUE IS DISCOVERED"
						. " WITH UNIT PREVENTING IT FROM BEING LOADED/PICKING UP, PLEASE NOTATE THE DAMAGE/REASON THIS IS"
						. " BEING LEFT BEHIND ON BOL FOR AWC TO REVIEW UPON TURN IN // MUST MATCH RPM DRIVER APP // SEV 3+"
						. " DAMAGE MUST HAVE SIGNATURES // DRIVERS MUST REPORT ISSUES (DAMAGE, NO START, ETC) TO GUARD OR"
						. " WHITE VAN WITH \"AWC\" ON THE SIDE - IF UNABLE TO LOCATE ONSITE STAFF, DRIVER MUST WRITE \"ISSUE\""
						. " ON BOL EXPLAINING WHY UNIT WAS NOT LOADED // IF A VIN IS NOT LOADED, DRIVER MUST CROSS OFF VIN"
						. " ON BOL // TRAVIS - 586-202-0513 // DRIVERS MAY NOT USE VEHICLES AS SHUTTLES // FOR AFTERHOURS"
						. " ASSISTANCE CONTACT CURTIS 734-775-5822",
					"vehicles" => [
						[
							"vin" => "1C6SRFRT0PN562497",
							"year" => "2023",
							"make" => "Ram",
							"model" => "1500",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Stellantis - Raceway",
						"state" => "OH",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "5700 Telegraph Road Toledo",
						"zip" => "43612",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "CLASSIC CHRYSLER JEEP DODGE RAM",
						"state" => "NC",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "306 N OAK FOREST RD GOLDSBORO",
						"zip" => "275348348",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 600,
					],
					"pickup_time" => [
                        "from" => "2:15 PM",
                        "to" => "2:15 PM",
                    ],
					"delivery_time" => [
                        "from" => "2:15 PM",
                        "to" => "2:15 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_40(): void
	{
		$this->createMakes("TESLA")
			->createStates("OH", "VA", "MI")
			->createTimeZones("44124", "23060", "48067")
			->assertParsing(
				40,
				[
					"load_id" => "31557-53659",
					"pickup_date" => "01/25/2023",
					"delivery_date" => "01/28/2023",
					"dispatch_instructions" =>  "Must Use Tesla App to get Paid; No STI's; Delivery Signature Required; Note"
						. " All Damage at Pickup or Carrier will be held liable for all claims\nPickup: (440) 461-1016 ? Denise"
						. " Monday-Saturday 8-6, Sunday 12-6. Dropbox. Call Denise at (440) 461-1016 ext. 4 when you are on"
						. " the way to confirm loading/unloading. All Drivers must use the Tesla App or $250 penalty will"
						. " apply. Must deliver by 12pm on the \"Need By\" date or else $50/vin per day penalty will apply."
						. " STI is not permitted. Vins reported as delivered before they are physically dropped on site will"
						. " incur $500 penalty. If the car is delivered to the wrong location - Carrier has 24 hours to transport"
						. " the units to correct destination or rate will be reduced. Units delivered to wrong destination"
						. " will be corrected at no cost.",
					"vehicles" => [
						[
							"vin" => "5YJSA1E54NF494131",
							"year" => "2022",
							"make" => "Tesla",
							"model" => "Model S",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Tesla Cleveland-Lyndhurst",
						"state" => "OH",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "5180 Mayfield Road Lyndhurst",
						"zip" => "44124",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "NA-US-VA-Richmond-Glen Allen",
						"state" => "VA",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "9850 W Broad St Glen Allen",
						"zip" => "23060",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 550,
					],
					"pickup_time" => [
                        "from" => "8:00 AM",
                        "to" => "5:00 PM",
                    ],
					"delivery_time" => [
                        "from" => "9:45 AM",
                        "to" => "3:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_41(): void
	{
		$this->expectException(PdfFileException::class);
		$this->createMakes("NONE")
			->createStates("OH", "VA", "MI")
			->createTimeZones("44124", "23060", "48067")
			->assertParsing(
				41,
				[]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_42(): void
	{
		$this->createMakes("RAM")
			->createStates("TX", "MO", "MI")
			->createTimeZones("78045", "630113002", "48067")
			->assertParsing(
				42,
				[
					"load_id" => "31566-35708",
					"pickup_date" => "02/07/2023",
					"delivery_date" => "02/10/2023",
					"dispatch_instructions" =>  "RPM DRIVER APP REQUIRED AT PICK UP AND DELIVERY // NO STI - NO WEEKEND DELIVERY"
						. " WITHOUT APPROVAL FROM RPM // RATE SUBJECT TO CHANGE IF NOT FOLLOWED // Leaving units unattended"
						. " is strictly forbidden and will result in permanent ban from RPM along with liability for any damages"
						. " // ALL AFTER HOUR DROPS MUST BE APPROVED BY RPM - NOT THE DEALER\nPickup: HOURS MON-SAT 0600 -"
						. " 1600 // HELP ON SITE Joseph DiBella -- (734) 765-7193 // STREET ADDRESS 3210 PINTO VALLE, LAREDO,"
						. " TX, 78045 // PHYSICAL DOCUMENTS REQUIRED // all severities of damage identified on product being"
						. " moved out of any VASCOR facility must be legible and properly documented on a VASCOR outgate sheet."
						. " Also, prior to the units handling, the damage must be verified and signed for by an authorized"
						. " VASCOR representative. Failure to follow these steps will result in VASCOR denying liability for"
						. " any damages that result in a claim. // SINGLE, OVER WHEEL STRAPS ONLY; NO CHAINS, SADDLE, OR LASSO"
						. " STRAPS ALL FOUR WHEELS MUST BE STRAPPED DOWN, NO EXCEPTIONS NO MOVING UNITS EXCEPT TO LOAD ASSIGNED"
						. " UNITS ONTO TRUCK* ALL DAMAGES ON APP MUST MATCH HARD COPY LEFT AT YARD* RPM PICKUP INSPECTION"
						. " REPORT MUST BE LEFT AT THE ORIGIN LOCATION DAMAGE MUST BE NOTED WITH 5 DIGIT AIAG CODES ON RPM"
						. " INSPECTION REPORTS AND RPM DRIVER APP * DAMAGES MUST BE NOTED IN PROPER AREAS ON RPM INSPECTION"
						. " REPORTS DAMAGE PHOTOS ARE REQUIRED (FAILURE TO PROVIDE WILL RESULT IN CARRIER BEING RESPONSIBLE"
						. " FOR CLAIM) DRIVER MUST WEAR APPROPRIATE SAFETY ATTIRE (I.E. FREE OF LOOSE METAL, CHAINS, ETC.)"
						. " *IF PLASTIC FILM IS LOOSE, REMOVE FROM UNIT* IF FILM IS DAMAGED AT DELIVERY, MAKE NOTE OF DAMAGED"
						. " FILM AND MARK DAMAGES CAUSED BY FILM*FAILURE TO USE RPM APP OR RPM OEM INSPECTION DOCS WILL RESULT"
						. " IN DELAYED PAYMENT AND LIABILITY OF CLAIMS",
					"vehicles" => [
						[
							"vin" => "3C6LRVDG8PE530815",
							"year" => "2023",
							"make" => "Ram",
							"model" => "ProMaster",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Vascor Laredo Yard",
						"state" => "TX",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "3210 Pinto Valle Laredo",
						"zip" => "78045",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "DAVID TAYLOR ELLISVILLE CHRYSLER D",
						"state" => "MO",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "15502 MANCHESTER RD ELLISVILLE",
						"zip" => "630113002",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 1100,
					],
					"pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
					"delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_43(): void
	{
		$this->createMakes("GMC")
			->createStates("TX", "TX", "MI")
			->createTimeZones("76132", "77845", "48067")
			->assertParsing(
				43,
				[
					"load_id" => "31569-25047",
					"pickup_date" => "03/01/2023",
					"delivery_date" => "03/01/2023",
					"dispatch_instructions" =>  "Pickup: STORE TO STORE - MUST PICK & DELIVER 2/28 - CUSTOMER TRANSFER -"
						. " SUPERHOT - Must confirm VIN at pickup. CarMax BOL required for all store to store transfers; Name"
						. " of Carrier: must say RPM or RPM Broker in upper left corner. Single, over the wheel soft straps"
						. " only. No chain, saddle or lasso straps allowed.\nDelivery: STORE TO STORE - MUST PICK & DELIVER"
						. " 2/28 - CUSTOMER TRANSFER - SUPERHOT - STI not allowed. Driver must submit pic of signed Carmax"
						. " BOL to RPM within 24 hrs. of delivery for payment.",
					"vehicles" => [
						[
							"vin" => "2GKALPEK4H6322792",
							"year" => "2017",
							"make" => "GMC",
							"model" => "Terrain",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "7181 CARMAX - HULEN MALL",
						"state" => "TX",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "4700 RIVER RANCH BOULEVARD Fort Worth",
						"zip" => "76132",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "6177 CARMAX - COLLEGE STATION",
						"state" => "TX",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "1320 Pavilion Ave College Station",
						"zip" => "77845",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 250,
					],
					"pickup_time" => [
                        "from" => "8:00 AM",
                        "to" => "7:30 PM",
                    ],
					"delivery_time" => null,
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_44(): void
	{
		$this->createMakes("NISSAN")
			->createStates("MS", "OK", "MI")
			->createTimeZones("39046", "73149", "48067")
			->assertParsing(
				44,
				[
					"load_id" => "31571-44087",
					"pickup_date" => "02/14/2023",
					"delivery_date" => "02/16/2023",
					"dispatch_instructions" =>  "Pickup: Fred (769) 246-7244 Greg (601) 624-8991 // DRIVERS MUST SEND PHOTO"
						. " OF OUT GATE FORM WITH STICKERS OR UPLOAD VIA APP//*RPM DRIVER APP REQUIRE// *SINGLE, OVER WHEEL"
						. " STRAPS ONLY; NO CHAINS, SADDLE, OR LASSO STRAPS* *ALL FOUR WHEELS MUST BE STRAPPED DOWN, NO EXCEPTIONS*"
						. " *NO MOVING UNITS EXCEPT TO LOAD ASSIGNED UNITS ONTO TRUCK* *MUST USE SEPARATE RPM INSPECTION REPORT"
						. " FOR PICKUP & DELIVERY* *RPM INSPECTION REPORT MUST BE DATED AT PICKUP & DELIVERY* *A COPY OF THE"
						. " RPM PICKUP INSPECTION REPORT MUST BE LEFT AT THE ORIGIN LOCATION* *DAMAGE MUST BE NOTED WITH 5"
						. " DIGIT AIAG CODES ON RPM INSPECTION REPORTS* *DAMAGES MUST BE NOTED IN PROPER AREAS ON RPM INSPECTION"
						. " REPORTS* * ALL DAMAGES MUST BE REPORTED TO ONSITE STAFF PRIOR TO MOVING FROM BAY* *MUST RECEIVE"
						. " DAMAGED UNIT FORM FROM YARD STAFF IF UNIT HAS DAMAGE* *IF THERE IS DAMAGE AT PICKUP AND/OR DELIVERY,"
						. " PHOTOS ARE REQUIRED (FAILURE TO PROVIDE *MUST RECEIVE DAMAGED UNIT FORM FROM YARD STAFF IF UNIT"
						. " HAS DAMAGE* *IF THERE IS DAMAGE AT PICKUP AND/OR DELIVERY, PHOTOS ARE REQUIRED (FAILURE TO PROVIDE"
						. " WILL RESULT IN CARRIER BEING RESPONSIBLE FOR CLAIM)* *DRIVER MUST WEAR APPROPRIATE SAFETY ATTIRE"
						. " (I.E. FREE OF LOOSE METAL, CHAINS, ETC.)\nDelivery: NO STI* *NO WEEKEND DELIVERY",
					"vehicles" => [
						[
							"vin" => "1N4BL4CVXPN358675",
							"year" => "2023",
							"make" => "Nissan",
							"model" => "Altima",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Nissan Canton Plant",
						"state" => "MS",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "225 Nissan Dr Canton",
						"zip" => "39046",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "ORR NISSAN CENTRAL",
						"state" => "OK",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "200 E Interstate 240 Service Rd Oklahoma City",
						"zip" => "73149",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 500,
					],
					"pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
					"delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_45(): void
	{
		$this->expectException(PdfFileException::class);
		$this->createMakes("NONE")
			->createStates("MS", "OK", "MI")
			->createTimeZones("39046", "73149", "48067")
			->assertParsing(
				45,
				[]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_46(): void
	{
		$this->createMakes("NISSAN")
			->createStates("TN", "SC", "MI")
			->createTimeZones("37167", "29842", "48067")
			->assertParsing(
				46,
				[
					"load_id" => "31577-73307",
					"pickup_date" => "01/24/2023",
					"delivery_date" => "01/27/2023",
					"dispatch_instructions" =>  "Pickup: MUST CONTACT TYLER 209 -662-2568 PRIOR TO ARRIVAL // ALL DAMAGES"
						. " MUST BE REPORTED TO ONSITE STAFF PRIOR TO MOVING FROM BAY// DRIVERS MUST CHECK OUT WITH NATIONWIDE"
						. " REPS TO VALIDATE NUMBER OF UNITS ON TRUCK VS PAPERWORK PRIOR TO EXITING THE YARD*RPM DRIVER APP"
						. " REQUIRED// 0800-1900 LOADING HOURS //MUST USE GATE 8 // UNITS WITH BAYS F-J ARE LOCATED IN THE"
						. " OUTBACK LOT// IF THERE IS DAMAGE AT PICKUP AND/OR DELIVERY, PHOTOS ARE REQUIRED (FAILURE TO PROVIDE"
						. " WILL RESULT IN CARRIER BEING RESPONSIBLE FOR CLAIM)//DRIVER MUST WEAR APPROPRIATE SAFETY ATTIRE"
						. " (I.E. FREE OF LOOSE METAL, CHAINS, ETC.// DAMAGE MUST BE NOTED WITH 5 DIGIT AIAG CODES ON RPM"
						. " INSPECTION REPORTS// NO MOVING UNITS EXCEPT TO LOAD ASSIGNED UNITS ONTO TRUCK// MUST HAVE RPM"
						. " BOL AND SMYRNA OUT GATE SHEET // *MUST USE SEPARATE RPM INSPECTION REPORT FOR PICKUP & DELIVERY//"
						. " *RPM INSPECTION REPORT MUST BE DATED AT PICKUP & DELIVERY// A COPY OF THE RPM PICKUP INSPECTION"
						. " REPORT MUST BE LEFT AT THE ORIGIN LOCATION// MUST PULL VIN STICKETS AND APPLY TO SMYRNA OUT GATE"
						. " FORM // DRIVERS MUST SEND PHOTO OF OUT GATE FORM WITH STICKERS OR UPLOAD VIA APP// MUST TURN IN"
						. " OUTGATE FORM AT GUARD SHACK UPON EXIT - EXIT SHEET MUST SHOW RPM (NOT CARRIER NAME) FAILURE TO"
						. " TURN IN RPM PAPERWORK WILL RESULT IN RATE REDUCTION//\nDelivery: NO STI* *NO WEEKEND DELIVERY",
					"vehicles" => [
						[
							"vin" => "5N1BT3CAXPC743890",
							"year" => "2023",
							"make" => "Nissan",
							"model" => "Rogue",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
						[
							"vin" => "5N1DR3CA0PC238867",
							"year" => "2023",
							"make" => "Nissan",
							"model" => "Pathfinder",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "WWL Vehicle Services Americas",
						"state" => "TN",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "626 Enon Springs Road East Smyrna",
						"zip" => "37167",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "Bob Richard's Nissan",
						"state" => "SC",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "5590 Jefferson Davis Highway Beech Island",
						"zip" => "29842",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 600,
					],
					"pickup_time" => [
						"from" => "11:00 PM",
						"to" => "10:59 PM",
					],
					"delivery_time" => [
                        "from" => "7:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_47(): void
	{
		$this->createMakes("RAM")
			->createStates("TX", "IA", "MI")
			->createTimeZones("78045", "50436", "48067")
			->assertParsing(
				47,
				[
					"load_id" => "31578-03752",
					"pickup_date" => "02/27/2023",
					"delivery_date" => "02/28/2023",
					"dispatch_instructions" =>  "RPM DRIVER APP REQUIRED AT PICK UP AND DELIVERY // NO STI - NO WEEKEND DELIVERY"
						. " WITHOUT APPROVAL FROM RPM // RATE SUBJECT TO CHANGE IF NOT FOLLOWED // Leaving units unattended"
						. " is strictly forbidden and will result in permanent ban from RPM along with liability for any damages"
						. " // ALL AFTER HOUR DROPS MUST BE APPROVED BY RPM - NOT THE DEALER\nPickup: HOURS MON-SAT 0600 -"
						. " 1600 // HELP ON SITE Joseph DiBella -- (734) 765-7193 // STREET ADDRESS 3210 PINTO VALLE, LAREDO,"
						. " TX, 78045 // PHYSICAL DOCUMENTS REQUIRED // all severities of damage identified on product being"
						. " moved out of any VASCOR facility must be legible and properly documented on a VASCOR outgate sheet."
						. " Also, prior to the units handling, the damage must be verified and signed for by an authorized"
						. " VASCOR representative. Failure to follow these steps will result in VASCOR denying liability for"
						. " any damages that result in a claim. // SINGLE, OVER WHEEL STRAPS ONLY; NO CHAINS, SADDLE, OR LASSO"
						. " STRAPS ALL FOUR WHEELS MUST BE STRAPPED DOWN, NO EXCEPTIONS NO MOVING UNITS EXCEPT TO LOAD ASSIGNED"
						. " UNITS ONTO TRUCK* ALL DAMAGES ON APP MUST MATCH HARD COPY LEFT AT YARD* RPM PICKUP INSPECTION"
						. " REPORT MUST BE LEFT AT THE ORIGIN LOCATION DAMAGE MUST BE NOTED WITH 5 DIGIT AIAG CODES ON RPM"
						. " INSPECTION REPORTS AND RPM DRIVER APP * DAMAGES MUST BE NOTED IN PROPER AREAS ON RPM INSPECTION"
						. " REPORTS DAMAGE PHOTOS ARE REQUIRED (FAILURE TO PROVIDE WILL RESULT IN CARRIER BEING RESPONSIBLE"
						. " FOR CLAIM) DRIVER MUST WEAR APPROPRIATE SAFETY ATTIRE (I.E. FREE OF LOOSE METAL, CHAINS, ETC.)"
						. " *IF PLASTIC FILM IS LOOSE, REMOVE FROM UNIT* IF FILM IS DAMAGED AT DELIVERY, MAKE NOTE OF DAMAGED"
						. " FILM AND MARK DAMAGES CAUSED BY FILM*FAILURE TO USE RPM APP OR RPM OEM INSPECTION DOCS WILL RESULT"
						. " IN DELAYED PAYMENT AND LIABILITY OF CLAIMS\nDelivery: MUST CALL CHRIS (641-585-6370) 24 HOURS"
						. " PRIOR TO DELIVERY - THERE MAY BE A DELAY AT THE TIME OF UNLOADING IF THERE IS NO CALL AHEAD //"
						. " DELIVERY HOURS: MON-FRI 0700-1330 // NO WEEKENDS // NO STI // UPON ARRIVAL, HEAD TO GATE 3 //"
						. " MUST HAVE HARD COPY OF BOL WITH ALL VINS //DO NOT DROP UNITS OUTSIDE OF GATE",
					"vehicles" => [
						[
							"vin" => "3C6MRVJG1PE532009",
							"year" => "2023",
							"make" => "Ram",
							"model" => "ProMaster",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Vascor Laredo Yard",
						"state" => "TX",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "3210 Pinto Valle Laredo",
						"zip" => "78045",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "WINNEBAGO INDUSTRIES",
						"state" => "IA",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "605 WEST CRYSTAL LAKE ROAD FOREST CITY",
						"zip" => "50436",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 1200,
					],
					"pickup_time" => null,
					"delivery_time" => [
                        "from" => "4:44 PM",
                        "to" => "4:44 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_48(): void
	{
		$this->createMakes("NISSAN")
			->createStates("MS", "OK", "MI")
			->createTimeZones("39046", "74012", "48067")
			->assertParsing(
				48,
				[
					"load_id" => "31582-70097",
					"pickup_date" => "02/14/2023",
					"delivery_date" => "02/16/2023",
					"dispatch_instructions" =>  "Pickup: Fred (769) 246-7244 Greg (601) 624-8991 // DRIVERS MUST SEND PHOTO"
						. " OF OUT GATE FORM WITH STICKERS OR UPLOAD VIA APP//*RPM DRIVER APP REQUIRE// *SINGLE, OVER WHEEL"
						. " STRAPS ONLY; NO CHAINS, SADDLE, OR LASSO STRAPS* *ALL FOUR WHEELS MUST BE STRAPPED DOWN, NO EXCEPTIONS*"
						. " *NO MOVING UNITS EXCEPT TO LOAD ASSIGNED UNITS ONTO TRUCK* *MUST USE SEPARATE RPM INSPECTION REPORT"
						. " FOR PICKUP & DELIVERY* *RPM INSPECTION REPORT MUST BE DATED AT PICKUP & DELIVERY* *A COPY OF THE"
						. " RPM PICKUP INSPECTION REPORT MUST BE LEFT AT THE ORIGIN LOCATION* *DAMAGE MUST BE NOTED WITH 5"
						. " DIGIT AIAG CODES ON RPM INSPECTION REPORTS* *DAMAGES MUST BE NOTED IN PROPER AREAS ON RPM INSPECTION"
						. " REPORTS* * ALL DAMAGES MUST BE REPORTED TO ONSITE STAFF PRIOR TO MOVING FROM BAY* *MUST RECEIVE"
						. " DAMAGED UNIT FORM FROM YARD STAFF IF UNIT HAS DAMAGE* *IF THERE IS DAMAGE AT PICKUP AND/OR DELIVERY,"
						. " PHOTOS ARE REQUIRED (FAILURE TO PROVIDE *MUST RECEIVE DAMAGED UNIT FORM FROM YARD STAFF IF UNIT"
						. " HAS DAMAGE* *IF THERE IS DAMAGE AT PICKUP AND/OR DELIVERY, PHOTOS ARE REQUIRED (FAILURE TO PROVIDE"
						. " WILL RESULT IN CARRIER BEING RESPONSIBLE FOR CLAIM)* *DRIVER MUST WEAR APPROPRIATE SAFETY ATTIRE"
						. " (I.E. FREE OF LOOSE METAL, CHAINS, ETC.)\nDelivery: NO STI* *NO WEEKEND DELIVERY",
					"vehicles" => [
						[
							"vin" => "1N6ED1EK0PN631996",
							"year" => "2023",
							"make" => "Nissan",
							"model" => "Frontier",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Nissan Canton Plant",
						"state" => "MS",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "225 Nissan Dr Canton",
						"zip" => "39046",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "NELSON NISSAN",
						"state" => "OK",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "800 W QUEENS ST BROKEN ARROW",
						"zip" => "74012",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 500,
					],
					"pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
					"delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_49(): void
	{
		$this->createMakes("GMC")
			->createStates("AR", "MO", "MI")
			->createTimeZones("72756", "64012", "48067")
			->assertParsing(
				49,
				[
					"load_id" => "31585-83225",
					"pickup_date" => "02/27/2023",
					"delivery_date" => "02/28/2023",
					"dispatch_instructions" =>  "Pickup: CALL 24 HOURS AND 1 HOUR BEFORE PICKUP** . Must confirm VIN before"
						. " pickup, must have release at pickup Keys and runs contact Lola or Marty at 479-986-9090 up times"
						. " Monday to Friday 7:00 to 17:00\nDelivery: Delivery # VEHICLES MUST BE CHECKED IN UNDER AS WHEELS"
						. " Vehicle must check in under as wheels, must leave paperwork in vehicle stating WHEELS. Must have"
						. " signature, stamp or picture. Damaged units must have pictures. VEHICLES MUST BE CHECKED IN UNDER"
						. " AS WHEELS",
					"vehicles" => [
						[
							"vin" => "1GTR1TEC9FZ159855",
							"year" => "2015",
							"make" => "GMC",
							"model" => "Sierra 1500",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "VSC Fire & Security,",
						"state" => "AR",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "1315 N 13th St Rogers",
						"zip" => "72756",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "ADESA Kansas City",
						"state" => "MO",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "15511 ADESA Dr. Belton",
						"zip" => "64012",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 300,
					],
					"pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
					"delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_50(): void
	{
		$this->createMakes("NISSAN")
			->createStates("TN", "NC", "MI")
			->createTimeZones("37167", "28054", "48067")
			->assertParsing(
				50,
				[
					"load_id" => "31586-60634",
					"pickup_date" => "02/27/2023",
					"delivery_date" => "03/02/2023",
					"dispatch_instructions" =>  "Pickup: (615) 355-8214 MUST CONTACT Andrew 510-401-7730 OR TYLER 209) 662-2568"
						. " PRIOR TO ARRIVAL // ALL DAMAGES MUST BE REPORTED TO ONSITE STAFF PRIOR TO MOVING FROM BAY// DRIVERS"
						. " MUST CHECK OUT WITH NATIONWIDE REPS TO VALIDATE NUMBER OF UNITS ON TRUCK VS PAPERWORK PRIOR TO"
						. " EXITING THE YARD*RPM DRIVER APP REQUIRED// 0800-1900 LOADING HOURS //MUST USE GATE 8 // UNITS"
						. " WITH BAYS F-J ARE LOCATED IN THE OUTBACK LOT// IF THERE IS DAMAGE AT PICKUP AND/OR DELIVERY, PHOTOS"
						. " ARE REQUIRED (FAILURE TO PROVIDE WILL RESULT IN CARRIER BEING RESPONSIBLE FOR CLAIM)//DRIVER MUST"
						. " WEAR APPROPRIATE SAFETY ATTIRE (I.E. FREE OF LOOSE METAL, CHAINS, ETC.// DAMAGE MUST BE NOTED"
						. " WITH 5 DIGIT AIAG CODES ON RPM INSPECTION REPORTS// NO MOVING UNITS EXCEPT TO LOAD ASSIGNED UNITS"
						. " ONTO TRUCK// MUST HAVE RPM BOL AND SMYRNA OUT GATE SHEET // *MUST USE SEPARATE RPM INSPECTION"
						. " REPORT FOR PICKUP & DELIVERY// *RPM INSPECTION REPORT MUST BE DATED AT PICKUP & DELIVERY// A COPY"
						. " OF THE RPM PICKUP INSPECTION REPORT MUST BE LEFT AT THE ORIGIN LOCATION// MUST PULL VIN STICKETS"
						. " AND APPLY TO SMYRNA OUT GATE FORM // DRIVERS MUST SEND PHOTO OF OUT GATE FORM WITH STICKERS OR"
						. " UPLOAD VIA APP// MUST TURN IN OUTGATE FORM AT GUARD SHACK UPON EXIT - EXIT SHEET MUST SHOW RPM"
						. " (NOT CARRIER NAME) FAILURE TO TURN IN RPM PAPERWORK WILL RESULT IN RATE REDUCTION//\nDelivery:"
						. " NO STI* *NO WEEKEND DELIVERY",
					"vehicles" => [
						[
							"vin" => "5N1DR3BA3PC250013",
							"year" => "2023",
							"make" => "Nissan",
							"model" => "Pathfinder",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Nissan Smyrna Plant",
						"state" => "TN",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "610 Enon Springs Rd E GATE 8 Smyrna",
						"zip" => "37167",
						"phones" => [],
						"fax" => null,
						"phone" => "16153558214",
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "GASTONIA NISSAN",
						"state" => "NC",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "2275 E FRANKLIN BLVD GASTONIA",
						"zip" => "28054",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 625,
					],
					"pickup_time" => [
						"from" => "11:00 PM",
						"to" => "10:59 PM",
					],
					"delivery_time" => [
                        "from" => "7:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_51(): void
	{
		$this->createMakes("RAM")
			->createStates("TX", "MO", "MI")
			->createTimeZones("78045", "63471", "48067")
			->assertParsing(
				51,
				[
					"load_id" => "31588-54199",
					"pickup_date" => "02/07/2023",
					"delivery_date" => "02/10/2023",
					"dispatch_instructions" =>  "RPM DRIVER APP REQUIRED AT PICK UP AND DELIVERY // NO STI - NO WEEKEND DELIVERY"
						. " WITHOUT APPROVAL FROM RPM // RATE SUBJECT TO CHANGE IF NOT FOLLOWED // Leaving units unattended"
						. " is strictly forbidden and will result in permanent ban from RPM along with liability for any damages"
						. " // ALL AFTER HOUR DROPS MUST BE APPROVED BY RPM - NOT THE DEALER\nPickup: HOURS MON-SAT 0600 -"
						. " 1600 // HELP ON SITE Joseph DiBella -- (734) 765-7193 // STREET ADDRESS 3210 PINTO VALLE, LAREDO,"
						. " TX, 78045 // PHYSICAL DOCUMENTS REQUIRED // all severities of damage identified on product being"
						. " moved out of any VASCOR facility must be legible and properly documented on a VASCOR outgate sheet."
						. " Also, prior to the units handling, the damage must be verified and signed for by an authorized"
						. " VASCOR representative. Failure to follow these steps will result in VASCOR denying liability for"
						. " any damages that result in a claim. // SINGLE, OVER WHEEL STRAPS ONLY; NO CHAINS, SADDLE, OR LASSO"
						. " STRAPS ALL FOUR WHEELS MUST BE STRAPPED DOWN, NO EXCEPTIONS NO MOVING UNITS EXCEPT TO LOAD ASSIGNED"
						. " UNITS ONTO TRUCK* ALL DAMAGES ON APP MUST MATCH HARD COPY LEFT AT YARD* RPM PICKUP INSPECTION"
						. " REPORT MUST BE LEFT AT THE ORIGIN LOCATION DAMAGE MUST BE NOTED WITH 5 DIGIT AIAG CODES ON RPM"
						. " INSPECTION REPORTS AND RPM DRIVER APP * DAMAGES MUST BE NOTED IN PROPER AREAS ON RPM INSPECTION"
						. " REPORTS DAMAGE PHOTOS ARE REQUIRED (FAILURE TO PROVIDE WILL RESULT IN CARRIER BEING RESPONSIBLE"
						. " FOR CLAIM) DRIVER MUST WEAR APPROPRIATE SAFETY ATTIRE (I.E. FREE OF LOOSE METAL, CHAINS, ETC.)"
						. " *IF PLASTIC FILM IS LOOSE, REMOVE FROM UNIT* IF FILM IS DAMAGED AT DELIVERY, MAKE NOTE OF DAMAGED"
						. " FILM AND MARK DAMAGES CAUSED BY FILM*FAILURE TO USE RPM APP OR RPM OEM INSPECTION DOCS WILL RESULT"
						. " IN DELAYED PAYMENT AND LIABILITY OF CLAIMS\nDelivery: m-f 7-330, STI accepted, dropbox, with paperwork"
						. " 2173163060 Scott",
					"vehicles" => [
						[
							"vin" => "3C6MRVXG4PE528267",
							"year" => "2023",
							"make" => "Ram",
							"model" => "ProMaster",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Vascor Laredo Yard",
						"state" => "TX",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "3210 Pinto Valle Laredo",
						"zip" => "78045",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "KNAPHEIDE MANUFACTURING CO",
						"state" => "MO",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "8665 COUNTY ROAD 346 TAYLOR",
						"zip" => "63471",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 1100,
					],
					"pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
					"delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_52(): void
    {
        $this->expectException(PdfFileException::class);
        $this->createMakes("NONE")
            ->createStates("TN", "TX", "MI")
            ->createTimeZones("37174", "77385", "48067")
            ->assertParsing(
                52,
                []
            );
    }

	/**
	* @throws Throwable
	*/
	public function test_53(): void
	{
		$this->createMakes("NISSAN")
			->createStates("TN", "SC", "MI")
			->createTimeZones("37167", "29910", "48067")
			->assertParsing(
				53,
				[
					"load_id" => "31590-26240",
					"pickup_date" => "02/14/2023",
					"delivery_date" => "02/16/2023",
					"dispatch_instructions" =>  "Pickup: MUST CONTACT TYLER 209 -662-2568 PRIOR TO ARRIVAL // ALL DAMAGES"
						. " MUST BE REPORTED TO ONSITE STAFF PRIOR TO MOVING FROM BAY// DRIVERS MUST CHECK OUT WITH NATIONWIDE"
						. " REPS TO VALIDATE NUMBER OF UNITS ON TRUCK VS PAPERWORK PRIOR TO EXITING THE YARD*RPM DRIVER APP"
						. " REQUIRED// 0800-1900 LOADING HOURS //MUST USE GATE 8 // UNITS WITH BAYS F-J ARE LOCATED IN THE"
						. " OUTBACK LOT// IF THERE IS DAMAGE AT PICKUP AND/OR DELIVERY, PHOTOS ARE REQUIRED (FAILURE TO PROVIDE"
						. " WILL RESULT IN CARRIER BEING RESPONSIBLE FOR CLAIM)//DRIVER MUST WEAR APPROPRIATE SAFETY ATTIRE"
						. " (I.E. FREE OF LOOSE METAL, CHAINS, ETC.// DAMAGE MUST BE NOTED WITH 5 DIGIT AIAG CODES ON RPM"
						. " INSPECTION REPORTS// NO MOVING UNITS EXCEPT TO LOAD ASSIGNED UNITS ONTO TRUCK// MUST HAVE RPM"
						. " BOL AND SMYRNA OUT GATE SHEET // *MUST USE SEPARATE RPM INSPECTION REPORT FOR PICKUP & DELIVERY//"
						. " *RPM INSPECTION REPORT MUST BE DATED AT PICKUP & DELIVERY// A COPY OF THE RPM PICKUP INSPECTION"
						. " REPORT MUST BE LEFT AT THE ORIGIN LOCATION// MUST PULL VIN STICKETS AND APPLY TO SMYRNA OUT GATE"
						. " FORM // DRIVERS MUST SEND PHOTO OF OUT GATE FORM WITH STICKERS OR UPLOAD VIA APP// MUST TURN IN"
						. " OUTGATE FORM AT GUARD SHACK UPON EXIT - EXIT SHEET MUST SHOW RPM (NOT CARRIER NAME) FAILURE TO"
						. " TURN IN RPM PAPERWORK WILL RESULT IN RATE REDUCTION//\nDelivery: NO STI* *NO WEEKEND DELIVERY",
					"vehicles" => [
						[
							"vin" => "5N1AZ2CS5PC117547",
							"year" => "2023",
							"make" => "Nissan",
							"model" => "Murano",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
						[
							"vin" => "1N4CZ1CV1PC557871",
							"year" => "2023",
							"make" => "Nissan",
							"model" => "LEAF",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "WWL Vehicle Services Americas",
						"state" => "TN",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "626 Enon Springs Road East Smyrna",
						"zip" => "37167",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "VADEN NISSAN/HILTON HEAD",
						"state" => "SC",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "200 Fording Island Rd Bluffton",
						"zip" => "29910",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 750,
					],
					"pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
					"delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
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
			->createStates("TX", "OK", "MI")
			->createTimeZones("78045", "73159", "48067")
			->assertParsing(
				54,
				[
					"load_id" => "31590-59988",
					"pickup_date" => "02/20/2023",
					"delivery_date" => "02/22/2023",
					"dispatch_instructions" =>  "RPM DRIVER APP REQUIRED AT PICK UP AND DELIVERY // NO STI - NO WEEKEND DELIVERY"
						. " WITHOUT APPROVAL FROM RPM // RATE SUBJECT TO CHANGE IF NOT FOLLOWED // Leaving units unattended"
						. " is strictly forbidden and will result in permanent ban from RPM along with liability for any damages"
						. " // ALL AFTER HOUR DROPS MUST BE APPROVED BY RPM - NOT THE DEALER\nPickup: HOURS MON-SAT 0600 -"
						. " 1600 // HELP ON SITE Joseph DiBella -- (734) 765-7193 // STREET ADDRESS 3210 PINTO VALLE, LAREDO,"
						. " TX, 78045 // PHYSICAL DOCUMENTS REQUIRED // all severities of damage identified on product being"
						. " moved out of any VASCOR facility must be legible and properly documented on a VASCOR outgate sheet."
						. " Also, prior to the units handling, the damage must be verified and signed for by an authorized"
						. " VASCOR representative. Failure to follow these steps will result in VASCOR denying liability for"
						. " any damages that result in a claim. // SINGLE, OVER WHEEL STRAPS ONLY; NO CHAINS, SADDLE, OR LASSO"
						. " STRAPS ALL FOUR WHEELS MUST BE STRAPPED DOWN, NO EXCEPTIONS NO MOVING UNITS EXCEPT TO LOAD ASSIGNED"
						. " UNITS ONTO TRUCK* ALL DAMAGES ON APP MUST MATCH HARD COPY LEFT AT YARD* RPM PICKUP INSPECTION"
						. " REPORT MUST BE LEFT AT THE ORIGIN LOCATION DAMAGE MUST BE NOTED WITH 5 DIGIT AIAG CODES ON RPM"
						. " INSPECTION REPORTS AND RPM DRIVER APP * DAMAGES MUST BE NOTED IN PROPER AREAS ON RPM INSPECTION"
						. " REPORTS DAMAGE PHOTOS ARE REQUIRED (FAILURE TO PROVIDE WILL RESULT IN CARRIER BEING RESPONSIBLE"
						. " FOR CLAIM) DRIVER MUST WEAR APPROPRIATE SAFETY ATTIRE (I.E. FREE OF LOOSE METAL, CHAINS, ETC.)"
						. " *IF PLASTIC FILM IS LOOSE, REMOVE FROM UNIT* IF FILM IS DAMAGED AT DELIVERY, MAKE NOTE OF DAMAGED"
						. " FILM AND MARK DAMAGES CAUSED BY FILM*FAILURE TO USE RPM APP OR RPM OEM INSPECTION DOCS WILL RESULT"
						. " IN DELAYED PAYMENT AND LIABILITY OF CLAIMS",
					"vehicles" => [
						[
							"vin" => "3C6LRVDG2PE541244",
							"year" => "2023",
							"make" => "Ram",
							"model" => "ProMaster",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Vascor Laredo Yard",
						"state" => "TX",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "3210 Pinto Valle Laredo",
						"zip" => "78045",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "ALAMO RENT-A-CAR",
						"state" => "OK",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "5201 S. MERIDIAN QT2 OKLAHOMA CITY",
						"zip" => "73159",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 800,
					],
					"pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
					"delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_55(): void
	{
		$this->createMakes("VOLVO")
			->createStates("NONE", "WI", "MI")
			->createTimeZones("NONE", "53108", "48067")
			->assertParsing(
				55,
                [
                    "load_id" => "31591-80384",
                    "pickup_date" => "01/27/2023",
                    "delivery_date" => "01/28/2023",
                    "dispatch_instructions" => "Pickup: Pickup # KEYS/DRIVES Appointment # .PU HOURS MON-FRI 9AM-5PM, **"
                        . " CALL 24 HRS AHEAD** Tom Arnet 563-320-2168 Must confirm VIN before pickup, must have release at"
                        . " pickup..PU HOURS MON-FRI 9AM-5PM, KEYS/DRIVES\nDelivery: (262) 835-4436 Delivery # VEHICLES MUST"
                        . " BE CHECKED IN UNDER AS WHEELS Vehicle must check in under as wheels, must leave paperwork in vehicle"
                        . " stating WHEELS. Must have signature, stamp or picture. Damaged units must have pictures. VEHICLES"
                        . " MUST BE CHECKED IN UNDER AS WHEELS",
                    "vehicles" => [
                        [
                            "vin" => "YV4AC2HK7M2590699",
                            "year" => "2021",
							"make" => "Volvo",
							"model" => "XC40",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Hiland Toyota",
						"state" => null,
						"city" => null,
						"address" => null,
						"zip" => null,
						"phones" => null,
						"fax" => null,
					],
					"delivery_contact" => [
						"full_name" => "Manheim Milwaukee",
						"state" => "WI",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "561 27th Street Caledonia",
						"zip" => "53108",
						"phones" => [],
						"fax" => null,
						"phone" => "12628354436",
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
                        "state_id" => $this->states[self::SHIPPER_KEY],
                        "timezone" => $this->timezones[self::SHIPPER_KEY],
                    ],
                    "payment" => [
                        "total_carrier_amount" => 175,
                    ],
                    "pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
                    "delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
                ]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_56(): void
	{
		$this->createMakes("DODGE")
			->createStates("OH", "OK", "MI")
			->createTimeZones("43612", "741335514", "48067")
			->assertParsing(
				56,
				[
					"load_id" => "31596-59338",
					"pickup_date" => "02/11/2023",
					"delivery_date" => "02/14/2023",
					"dispatch_instructions" =>  "RPM DRIVER APP REQUIRED AT PICK UP AND DELIVERY // NO STI - NO WEEKEND DELIVERY"
						. " WITHOUT APPROVAL FROM RPM // RATE SUBJECT TO CHANGE IF NOT FOLLOWED // Leaving units unattended"
						. " is strictly forbidden and will result in permanent ban from RPM along with liability for any damages"
						. " // ALL AFTER HOUR DROPS MUST BE APPROVED BY RPM - NOT THE DEALER\nPickup: NO SHUTTLING OF UNITS"
						. " // RPM DRIVER APP REQUIRED // PICKUP HOURS -- 24/7 // CHECK IN WITH SECURITY ON ARRIVAL // PHYSICAL"
						. " DOCS NEEDED FOR OUTGATE // MUST UPLOAD PICTURE OF PAPER COPY BOL THAT IS BEING LEFT AT ORIGIN"
						. " TO RPM DRIVER APP // RAM TRX AND HELLCAT CHALLENGER/CHARGER MODEL KEYS ARE WITH AWC PERSONNEL"
						. " - IF DRIVER CANNOT LOCATE PERSONNEL, ASK GUARD FOR ASSISTANCE. DRIVERS WILL NOT BE ABLE TO OBTAIN"
						. " KEYS ON HOLIDAYS, BETWEEN SHIFTS, AND ON WEEKENDS IF AWC IS NOT AVAILABLE // IF AN ISSUE IS DISCOVERED"
						. " WITH UNIT PREVENTING IT FROM BEING LOADED/PICKING UP, PLEASE NOTATE THE DAMAGE/REASON THIS IS"
						. " BEING LEFT BEHIND ON BOL FOR AWC TO REVIEW UPON TURN IN // MUST MATCH RPM DRIVER APP // SEV 3+"
						. " DAMAGE MUST HAVE SIGNATURES // DRIVERS MUST REPORT ISSUES (DAMAGE, NO START, ETC) TO GUARD OR"
						. " WHITE VAN WITH \"AWC\" ON THE SIDE - IF UNABLE TO LOCATE ONSITE STAFF, DRIVER MUST WRITE \"ISSUE\""
						. " ON BOL EXPLAINING WHY UNIT WAS NOT LOADED // IF A VIN IS NOT LOADED, DRIVER MUST CROSS OFF VIN"
						. " ON BOL // TRAVIS - 586-202-0513 // DRIVERS MAY NOT USE VEHICLES AS SHUTTLES // FOR AFTERHOURS"
						. " ASSISTANCE CONTACT CURTIS 734-775-5822",
					"vehicles" => [
						[
							"vin" => "1C4RDJAG3PC568331",
							"year" => "2023",
							"make" => "Dodge",
							"model" => "Durango",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "Stellantis - Raceway",
						"state" => "OH",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "5700 Telegraph Road Toledo",
						"zip" => "43612",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "SOUTH POINTE CHRYSLER JEEP DODGE",
						"state" => "OK",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "9240 S. MEMORIAL DRIVE TULSA",
						"zip" => "741335514",
						"phones" => null,
						"fax" => null,
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 600,
					],
					"pickup_time" => [
                        "from" => "1:57 PM",
                        "to" => "1:57 PM",
                    ],
					"delivery_time" => [
                        "from" => "1:57 PM",
                        "to" => "1:57 PM",
                    ],
				]
			);
	}

	/**
	* @throws Throwable
	*/
	public function test_57(): void
	{
		$this->createMakes("KIA")
			->createStates("VA", "TN", "MI")
			->createTimeZones("23060", "37934", "48067")
			->assertParsing(
				57,
				[
					"load_id" => "31598-57930",
					"pickup_date" => "01/26/2023",
					"delivery_date" => "01/28/2023",
					"dispatch_instructions" =>  "Requires Carmax BoL/Inspection for payment; No STI, Delivery Signature Required;"
						. " Straps only! No Chains!\nPickup: (804) 346-2277 Must confirm VIN at pickup. CarMax BOL required"
						. " for all store to store transfers; Name of Carrier: must say RPM or RPM Broker in upper left corner."
						. " Single, over the wheel soft straps only. No chain, saddle or lasso straps allowed.\nDelivery:"
						. " (865) 218-7800 STI not allowed. Driver must submit pic of signed Carmax BOL to RPM within 24 hrs."
						. " of delivery for payment.",
					"vehicles" => [
						[
							"vin" => "5XYKUDA12BG030349",
							"year" => "2011",
							"make" => "Kia",
							"model" => "Sorento",
							"inop" => false,
							"enclosed" => false,
							"type_id" => null,
						],
					],
					"pickup_contact" => [
						"full_name" => "7101 CARMAX - RICHMOND",
						"state" => "VA",
						"city" => $this->cities[self::PICKUP_KEY],
						"address" => "11090 W. BROAD ST. Glen Allen",
						"zip" => "23060",
						"phones" => [],
						"fax" => null,
						"phone" => "18043462277",
						"state_id" => $this->states[self::PICKUP_KEY],
						"timezone" => $this->timezones[self::PICKUP_KEY],
					],
					"delivery_contact" => [
						"full_name" => "7241 CARMAX - KNOXVILLE",
						"state" => "TN",
						"city" => $this->cities[self::DELIVERY_KEY],
						"address" => "11225 PARKSIDE DRIVE Knoxville",
						"zip" => "37934",
						"phones" => [],
						"fax" => null,
						"phone" => "18652187800",
						"state_id" => $this->states[self::DELIVERY_KEY],
						"timezone" => $this->timezones[self::DELIVERY_KEY],
					],
					"shipper_contact" => [
						"full_name" => "Klod Kasmi. RPM",
						"address" => "301 W 4th St #200",
						"city" => "Royal Oak",
						"state" => "MI",
						"zip" => "48067",
						"phones" => [],
						"email" => null,
						"phone" => "12482688710",
						"state_id" => $this->states[self::SHIPPER_KEY],
						"timezone" => $this->timezones[self::SHIPPER_KEY],
					],
					"payment" => [
						"total_carrier_amount" => 525,
					],
					"pickup_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
					"delivery_time" => [
                        "from" => "9:00 AM",
                        "to" => "4:00 PM",
                    ],
				]
			);
	}
}
