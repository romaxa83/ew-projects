<?php

namespace App\Services\Parsers\Drivers\BeaconShippingLogistics;

use App\Services\Parsers\Drivers\BeaconShippingLogistics\Parsers\DispatchInstructionsParser;
use App\Services\Parsers\Drivers\BeaconShippingLogistics\Parsers\PaymentParser;
use App\Services\Parsers\Drivers\BeaconShippingLogistics\Parsers\PickupDeliveryContactParser;
use App\Services\Parsers\Drivers\BeaconShippingLogistics\Parsers\ShipperContactParser;
use App\Services\Parsers\Drivers\BeaconShippingLogistics\Parsers\VehiclesParser;
use App\Services\Parsers\Objects\ParserPart;
use App\Services\Parsers\ParserAbstract;
use App\Services\Parsers\PdfService;

class BeaconShippingLogistics extends ParserAbstract
{

    public function type(): string
    {
        return "/^Beacon Shipping Logistics[^\~]+$/";
    }

    public function parts(): array
    {
        return [
            ParserPart::init([
                'name' => 'load_id',
                'pattern' => '/(?<load_id>.+)/',
                'replacement_before' => [
                    ["/\s*Dispatched  to:.+/is", ""],
                    ["/\s*Carrier Pay.+/is", ""],
                    ["/.+Order ID:\s*/s", ""],
                    ["/\s*$/m", ""]
                ],
                'type' => PdfService::PARSER_TYPE_SINGLE,
            ]),
            ParserPart::init([
                'name' => 'dispatch_instructions',
                'replacement_before' => [
                    ["/.+DISPACTCH INFO\s*/s", ""],
                    ["/\s*VEHICLE\(S\) INFORMATION.+/s", ""],
                    ["/^ +/m", ""],
                    ["/\n+$/s", ""]
                ],
                'parser' => DispatchInstructionsParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'vehicles',
                'replacement_before' => [
                    ["/.+VEHICLE\(S\) INFORMATION\s*/s", ""],
                    ["/\s*TERMS OF SERVICE.+/s", ""],
                    ["/^ +/m", ""],
                    ["/^Specs.*$/im", ""],
                    ["/ +$/m", ""],
                    ["/^V[0-9]+$/m", ""],
                    ["/\n{2,}/s", "\n"],
                    ["/\n$/s", ""]
                ],
                'parser' => VehiclesParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'pickup_contact',
                'replacement_before' => [
                    ["/\s*DELIVERY INFORMATION.+/s", ""],
                    ["/.+(PICKUP INFORMATION.+)/s", "$1"]
                ],
                'parser' => PickupDeliveryContactParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'delivery_contact',
                'replacement_before' => [
                    ["/.+(DELIVERY INFORMATION.+)/s", "$1"],
                    ["/\s*\f.+/s", ""]
                ],
                'parser' => PickupDeliveryContactParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'shipper_contact',
                'replacement_before' => [
                    ["/\s*DISPACTCH INFO.+/s", ""],
                    ["/\s*MC #.+BEACON SHIPPING CONTACTS.+(\nContact.+)/s", "$1"],
                    ["/^ +/m", ""],
                ],
                'parser' => ShipperContactParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'payment',
                'replacement_before' => [
                    ["/.+Carrier Pay:\s/s", ""],
                    ["/\s*Dispatched\s+to:.+/s", ""]
                ],
                'parser' => PaymentParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ])
        ];
    }
}
