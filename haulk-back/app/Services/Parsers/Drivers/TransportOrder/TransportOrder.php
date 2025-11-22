<?php


namespace App\Services\Parsers\Drivers\TransportOrder;


use App\Services\Parsers\Drivers\TransportOrder\Parsers\PaymentParser;
use App\Services\Parsers\Drivers\TransportOrder\Parsers\PickupDeliveryContactParser;
use App\Services\Parsers\Drivers\TransportOrder\Parsers\ShipperContactParser;
use App\Services\Parsers\Drivers\TransportOrder\Parsers\VehiclesParser;
use App\Services\Parsers\Objects\ParserPart;
use App\Services\Parsers\Objects\ParserPartIntend;
use App\Services\Parsers\ParserAbstract;
use App\Services\Parsers\PdfService;

class TransportOrder extends ParserAbstract
{

    public function type(): string
    {
        return "/^\s*TRANSPORT ORDER\s*$/im";
    }

    public function parts(): array
    {
        return [
            ParserPart::init([
                'name' => 'load_id',
                'pattern' => "/^\s*Load ID: (?<load_id>.+?)\s*$/im",
                'type' => PdfService::PARSER_TYPE_SINGLE,
            ]),
            ParserPart::init([
                'name' => 'pickup_date',
                'pattern' => "/^(?<pickup_date>\d{1,2}\/\d{1,2}\/\d{4})/is",
                'type' => PdfService::PARSER_TYPE_SINGLE,
                'replacement_before' => [
                    ["/.+\n(PICK-UP: {3,})/is", "$1"],
                    ["/\s*VIN\(s\).+/s", ""],
                ],
                'replacement_intend' => [
                    ParserPartIntend::init([
                        'pattern' => "/^(?<intend>.+)PICK-UP ON:/m",
                        'type' => ParserPartIntend::TYPE_LEFT,
                        'clear_after' => [
                            ["/PICK-UP ON:[^\n]+\n/is", ""],
                        ]
                    ])
                ]
            ]),
            ParserPart::init([
                'name' => 'delivery_date',
                'pattern' => "/^(?<delivery_date>\d{1,2}\/\d{1,2}\/\d{4})/is",
                'type' => PdfService::PARSER_TYPE_SINGLE,
                'replacement_before' => [
                    ["/.+\n(PICK-UP: {3,})/is", "$1"],
                    ["/\s*VIN\(s\).+/s", ""],
                ],
                'replacement_intend' => [
                    ParserPartIntend::init([
                        'pattern' => "/^(?<intend>.+)DROP-OFF ON:/m",
                        'type' => ParserPartIntend::TYPE_LEFT,
                        'clear_after' => [
                            ["/DROP-OFF ON:[^\n]*\n/is", ""],
                        ]
                    ])
                ]
            ]),
            ParserPart::init([
                'name' => 'dispatch_instructions',
                'pattern' => "/^(?<dispatch_instructions>.+)/is",
                'replacement_before' => [
                    ["/\s*PICK-UP: {3,}.+/is", ""],
                    ["/.+PRE-DISPATCH NOTES TO CARRIER:\s*/s", ""],
                    ["/\s*RELEASE NOTES:\s/s", " "],
                    ["/\n/", " "],
                    ["/ {2,}/", " "]
                ],
                'type' => PdfService::PARSER_TYPE_SINGLE,
            ]),
            ParserPart::init([
                'name' => 'vehicles',
                'replacement_before' => [
                    ["/.+\s*VIN\(s\)[^\n]+\n/s", ""],
                    ["/\s*PAYMENT TERMS: .+/s", ""],
                    ["/\s{4,}[^\s]+$/m", ""]
                ],
                'parser' => VehiclesParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'pickup_contact',
                'replacement_before' => [
                    ["/.+\n(PICK-UP: {3,})/is", "$1"],
                    ["/\s*VIN\(s\).+/s", ""],
                ],
                'replacement_intend' => [
                    ParserPartIntend::init([
                        'pattern' => "/^(?<intend>.+)PICK-UP ON:/m",
                        'type' => ParserPartIntend::TYPE_RIGHT,
                        'clear_after' => [
                            ["/\s*$/m", ""],
                            ["/^\s*PICK-UP:\s*\n/is", ""],
                            ["/\n\s*Contact:[^\n]*\n/is", "\n"]
                        ]
                    ])
                ],
                'parser' => PickupDeliveryContactParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'delivery_contact',
                'replacement_before' => [
                    ["/.+\n(PICK-UP: {3,})/is", "$1"],
                    ["/\s*VIN\(s\).+/s", ""],
                ],
                'replacement_intend' => [
                    ParserPartIntend::init([
                        'pattern' => "/^(?<intend>.+)DROP-OFF:/m",
                        'type' => ParserPartIntend::TYPE_LEFT
                    ]),
                    ParserPartIntend::init([
                        'pattern' => "/^(?<intend>.+)DROP-OFF ON:/m",
                        'type' => ParserPartIntend::TYPE_RIGHT,
                        'clear_after' => [
                            ["/\s*$/m", ""],
                            ["/^\s*DROP-OFF:\s*\n/is", ""],
                            ["/\n\s*Contact:[^\n]*\n/is", "\n"]
                        ]
                    ]),
                ],
                'parser' => PickupDeliveryContactParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'shipper_contact',
                'replacement_before' => [
                    ["/\s*PRE-DISPATCH NOTES TO CARRIER:.+/s", ""],
                    ["/.+(SHIPPER ARRANGING)/s", "$1"],
                ],
                'replacement_intend' => [
                    ParserPartIntend::init([
                        'pattern' => "/^(?<intend>.+)CARRIER INFORMATION:/m",
                        'type' => ParserPartIntend::TYPE_RIGHT,
                        'clear_after' => [
                            ["/\s*$/m", ""],
                            ["/^\s*SHIPPER ARRANGING TRANSPORT:\s*\n/is", ""],
                        ]
                    ]),
                ],
                'parser' => ShipperContactParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'payment',
                'replacement_before' => [
                    ["/.+\s*(PAYMENT TERMS: )/s", "$1"],
                    ["/\s+1\. .+/s", ""],
                ],
                'parser' => PaymentParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
        ];
    }
}
