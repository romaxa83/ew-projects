<?php

namespace App\Services\Parsers\Drivers\DirectConnectLogistix;

use App\Services\Parsers\Drivers\DirectConnectLogistix\Parsers\PaymentParser;
use App\Services\Parsers\Drivers\DirectConnectLogistix\Parsers\PickupDeliveryContactParser;
use App\Services\Parsers\Drivers\DirectConnectLogistix\Parsers\ShipperContactParser;
use App\Services\Parsers\Drivers\DirectConnectLogistix\Parsers\VehicleParser;
use App\Services\Parsers\Objects\ParserPart;
use App\Services\Parsers\Objects\ParserPartIntend;
use App\Services\Parsers\ParserAbstract;
use App\Services\Parsers\PdfService;

class DirectConnectLogistix extends ParserAbstract
{
    public function type(): string
    {
        return "/^[^\~]+Carrier:[^\~]+Origin:[^\~]+Destination:[^\~]+Agreement[^\~]+operations@dclogistix.com[^\~]+$/";
    }

    public function parts(): array
    {
        return [
            ParserPart::init([
                'name' => 'load_id',
                'pattern' => '/(?<load_id>.+)/',
                'replacement_before' => [
                    ["/\s*Carrier: +.+/s", ""],
                    ["/.+\s*Order:\s*/s", ""]
                ],
                'type' => PdfService::PARSER_TYPE_SINGLE,
            ]),
            ParserPart::init([
                'name' => 'pickup_date',
                'pattern' => '/Load Date: (?<pickup_date>[0-9]{2}\/[0-9]{2}\/[0-9]{4})/',
                'replacement_before' => [
                    ["/.+\s*(Origin:.+)/s", "$1"],
                    ["/\s*Destination:.+/", ""]
                ],
                'type' => PdfService::PARSER_TYPE_SINGLE,
            ]),
            ParserPart::init([
                'name' => 'delivery_date',
                'pattern' => '/Del Date: (?<delivery_date>[0-9]{2}\/[0-9]{2}\/[0-9]{4})/',
                'replacement_before' => [
                    ["/.+\s*(Destination:.+)/s", "$1"],
                    ["/\s*Rate:.+/", ""]
                ],
                'type' => PdfService::PARSER_TYPE_SINGLE,
            ]),
            ParserPart::init([
                'name' => 'dispatch_instructions',
                'pattern' => "/(?<dispatch_instructions>.+)/",
                'replacement_before' => [
                    ["/.+(Origin:.+)/s", "$1"],
                    ["/\s*\_+\s*Rate:.+/s", ""],
                ],
                'replacement_intend' => [
                    ParserPartIntend::init([
                        'pattern' => "/^(?<intend>.+) {2}Load Date:/m",
                        'type' => ParserPartIntend::TYPE_LEFT,
                        'clear_after' => [
                            ["/Load Date:\s*[0-9]{2}\/[0-9]{2}\/[0-9]{4} *[0-9]{4}\s*[0-9]{2}\/[0-9]{2}\/[0-9]{4} *[0-9]{4}\s*/is", ""],
                            ["/\s*\_+\s*Del Date:\s*[0-9]{2}\/[0-9]{2}\/[0-9]{4} *[0-9]{4}\s*[0-9]{2}\/[0-9]{2}\/[0-9]{4} *[0-9]{4}\s*/is", "\n"],
                            ["/ +$/m", ""],
                            ["/^ +/m", ""],
                            ["/\n/s", " "],
                            ["/^\s*[0-9]+\s*$/", ""]
                        ]
                    ])
                ],
                'type' => PdfService::PARSER_TYPE_SINGLE,
            ]),
            ParserPart::init([
                'name' => 'vehicles',
                'replacement_before' => [
                    ["/\s*Origin:\s*.+/s", ""],
                    ["/.+Commodity:\s*VIN:\s*/s", ""],
                    ["/\s*\_+\s*/s", ""],
                    ["/ +$/m", ""]
                ],
                'parser' => VehicleParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'pickup_contact',
                'replacement_before' => [
                    ["/.+(Origin:\s*Name:.+)/s", "$1"],
                    ["/\s*\_*\s*Destination:.+/s", ""],
                ],
                'replacement_intend' => [
                    ParserPartIntend::init([
                        'pattern' => "/^(?<intend>.+)Name:/m",
                        'type' => ParserPartIntend::TYPE_LEFT,
                        'clear_after' => [
                            ["/^ +/m", ""]
                        ]
                    ]),
                    ParserPartIntend::init([
                        'pattern' => "/^(?<intend>.+)Load Date:/m",
                        'type' => ParserPartIntend::TYPE_RIGHT,
                        'clear_after' => [
                            ["/ +$/m", ""]
                        ]
                    ])
                ],
                'parser' => PickupDeliveryContactParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'delivery_contact',
                'replacement_before' => [
                    ["/.+(Destination:\s*Name:.+)/s", "$1"],
                    ["/\s*\_*\s*Rate:.+/s", ""],
                ],
                'replacement_intend' => [
                    ParserPartIntend::init([
                        'pattern' => "/^(?<intend>.+)Name:/m",
                        'type' => ParserPartIntend::TYPE_LEFT,
                        'clear_after' => [
                            ["/^ +/m", ""]
                        ]
                    ]),
                    ParserPartIntend::init([
                        'pattern' => "/^(?<intend>.+)Del Date:/m",
                        'type' => ParserPartIntend::TYPE_RIGHT,
                        'clear_after' => [
                            ["/ +$/m", ""]
                        ]
                    ])
                ],
                'parser' => PickupDeliveryContactParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'shipper_contact',
                'replacement_before' => [
                    ["/\s*\_+\s*Carrier:.+\s*(Send all billing.+)/s", "\n$1"],
                    ["/\s*Rate Confirmation.*$/m", ""],
                    ["/\s*Order:.+$/m", ""],
                    ["/\s*Page.+$/m", ""],
                    ["/ +$/m", ""]
                ],
                'parser' => ShipperContactParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'payment',
                'replacement_before' => [
                    ["/.+Destination:.+(Rate:.+)/is", "$1"],
                    ["/\s*\_+\s*Agreement.+/is", ""],
                ],
                'parser' => PaymentParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ])
        ];
    }

}
