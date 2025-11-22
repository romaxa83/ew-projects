<?php

namespace App\Services\Parsers\Drivers\CentralDispatch;

use App\Services\Parsers\Drivers\CentralDispatch\Parsers\DeliveryContactParser;
use App\Services\Parsers\Drivers\CentralDispatch\Parsers\DeliveryDateParser;
use App\Services\Parsers\Drivers\CentralDispatch\Parsers\DispatchInstructionsParser;
use App\Services\Parsers\Drivers\CentralDispatch\Parsers\LoadParser;
use App\Services\Parsers\Drivers\CentralDispatch\Parsers\PaymentParser;
use App\Services\Parsers\Drivers\CentralDispatch\Parsers\PaymentParserV2;
use App\Services\Parsers\Drivers\CentralDispatch\Parsers\PickupContactParser;
use App\Services\Parsers\Drivers\CentralDispatch\Parsers\PickupDateParser;
use App\Services\Parsers\Drivers\CentralDispatch\Parsers\PickupDeliveryContactParser;
use App\Services\Parsers\Drivers\CentralDispatch\Parsers\ShipperContactParser;
use App\Services\Parsers\Drivers\CentralDispatch\Parsers\ShipperContactParserV2;
use App\Services\Parsers\Drivers\CentralDispatch\Parsers\VehiclesParser;
use App\Services\Parsers\Drivers\CentralDispatch\Parsers\VehiclesParserV2;
use App\Services\Parsers\Objects\ParserPart;
use App\Services\Parsers\ParserAbstract;
use App\Services\Parsers\PdfService;

class CentralDispatch extends ParserAbstract
{

    public function type(): string
    {
//        return "/Carrier[^\`]*?Order Information[^\`]*?Vehicle Information[^\`]*?Delivery Information[^\`]*?/";
        return "/Karona Hauling Inc\s+7900 WHITCOMB ST UNIT\s+I\s+Merrillville, IN 46410/";
    }

    public function parts(): array
    {
        return [
            ParserPart::init([
                'name' => 'load_id',
                'parser' => LoadParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
                'id' => 1,
                'group_id' => 1,
//                'name' => 'load_id',
//                'pattern' => '/(?<load_id>.+)/s',
//                'replacement_before' => [
//                    ["/\s*.+(Order ID:.+)/is", "$1"],
//                    ["/(\s*Carrier)?\s*Information.+/is", ""],
//                    ["/\s*Carrier:.+/is", ""],
//                    ["/\s{4,}.+$/m", ""],
//                    ["/\n{2,}/", "\n"],
//                    ["/\n/", " "],
//                    ["/ {2,}/", " "],
//                    ["/Order ID:\s*/", ""]
//                ],
//                'type' => PdfService::PARSER_TYPE_SINGLE,
            ]),
            ParserPart::init([
                'id' => 5,
                'group_id' => 1,
                'name' => 'pickup_date',
                'parser' => PickupDateParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
//                'pattern' => '/(Pickup Estimated|Pickup No Earlier Than|Pickup No Later Than|Pickup Exactly): (?<pickup_date>[\d\/:]+)/',
//                'replacement_before' =>
//                    [
//                        '/.+Order\s+Information/s' => '',
//                    ]
//                ,
//                'type' => PdfService::PARSER_TYPE_SINGLE,
            ]),
            ParserPart::init([
                'id' => 6,
                'group_id' => 1,
                'name' => 'delivery_date',
                'parser' => DeliveryDateParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
//                'pattern' => '/(Delivery Estimated|Delivery No Later Than|Delivery Exactly): (?<delivery_date>[\d\/:]+)/',
//                'replacement_before' =>
//                    [
//                        '/.+Order\s+Information/s' => '',
//                    ]
//                ,
//                'type' => PdfService::PARSER_TYPE_SINGLE,
            ]),
            ParserPart::init([
                'id' => 7,
                'group_id' => 1,
                'name' => 'dispatch_instructions',
                'parser' => DispatchInstructionsParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
//                'pattern' => '/^(?<dispatch_instructions>[^~]+)$/m',
//                'replacement_before' =>
//                    [
//                        ["/.+?DISPATCH INSTRUCTIONS\s+/s", ""],
//                        ["/\s*(ADDITIONAL TERMS|CONTRACT TERMS|\n\s*PLEASE GIVE).+/is", ""],
//                        ["/^[^a-z0-9]+$/im", ""],
//                        ["/\n/is", " "],
//                        ["/ {2,}/", " "],
//                        ["/\s/", " "]
//                    ]
//                ,
//                'type' => PdfService::PARSER_TYPE_SINGLE,
            ]),
            ParserPart::init([
                'name' => 'vehicles',
                'parser' => VehiclesParserV2::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'pickup_contact',
                'parser' => PickupContactParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'delivery_contact',
                'parser' => DeliveryContactParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'shipper_contact',
                'parser' => ShipperContactParserV2::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'payment',
                'parser' => PaymentParserV2::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
//            ParserPart::init([
//                'name' => 'payment',
//                'replacement_before' => [
//                    ["/.*Price Listed:[^\n]+\n(.+)\s+Vehicle Information.+/is", "$1"],
//                    ["/s *heet/is", "sheet"],
//                    ["/C *ash/s", "Cash"],
//                    ["/Cas *h/s", "Cash"],
//                ],
//                'parser' => PaymentParser::class,
//                'type' => PdfService::PARSER_TYPE_CUSTOM
//            ])
        ];
    }

}
