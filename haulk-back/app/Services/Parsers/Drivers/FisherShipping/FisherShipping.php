<?php


namespace App\Services\Parsers\Drivers\FisherShipping;


use App\Services\Parsers\Drivers\FisherShipping\Parsers\PaymentParser;
use App\Services\Parsers\Drivers\FisherShipping\Parsers\PickupDeliveryContactParser;
use App\Services\Parsers\Drivers\FisherShipping\Parsers\ShipperContactParser;
use App\Services\Parsers\Drivers\FisherShipping\Parsers\VehicleParser;
use App\Services\Parsers\Objects\ParserPart;
use App\Services\Parsers\ParserAbstract;
use App\Services\Parsers\PdfService;

class FisherShipping extends ParserAbstract
{

    public function type(): string
    {
        return "/^[^\~]*(Fisher Shipping Company -- Transport Order Form)|(www.fishershipping.com)[^\~]+/";
    }

    public function parts(): array
    {
        return[
            ParserPart::init([
                'name' => 'load_id',
                'pattern' => "/Transport Order Number\n\s*(?<load_id>\d+)/",
                'type' => PdfService::PARSER_TYPE_SINGLE,
            ]),
            ParserPart::init([
                'name' => 'pickup_date',
                'pattern' => "/Pickup Date:\s*(?<pickup_date>[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2})/m",
                'replacement_before' => [
                    ["/.+?\n(\s*Transport Order Number.+?)Print\n\s*Go.+/is", "$1"]
                ],
                'type' => PdfService::PARSER_TYPE_SINGLE,
            ]),
            ParserPart::init([
                'name' => 'delivery_date',
                'pattern' => "/Delivery Date:\s*(?<delivery_date>[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2})/m",
                'replacement_before' => [
                    ["/.+?\n(\s*Transport Order Number.+?)Print\n\s*Go.+/is", "$1"]
                ],
                'type' => PdfService::PARSER_TYPE_SINGLE,
            ]),
            ParserPart::init([
                'name' => 'dispatch_instructions',
                'pattern' => "/(?<dispatch_instructions>.+)/",
                'replacement_before' => [
                    ["/.*?\n*(\s*Transport Order Number.+?)Print\n\s*Go.+/is", "$1"],
                    ["/.+?\n\s*Special Instructions\s*/s", ""],
                    ["/\s*Payment Information\s*.+/s", ""],
                    ["/[^\n]*please sign.+/is", ""],
                    ["/[0-9]{2} *\/ *[0-9]{2} *\/ *[0-9]{4}/", ""],
                    ["/^ +/m", ""],
                    ["/ +$/m", ""],
                    ["/\n{2,}/s", "\n"],
                    ["/\n/s", " "],
                    ["/ {2,}/", " "]
                ],
                'type' => PdfService::PARSER_TYPE_SINGLE,
            ]),
            ParserPart::init([
                'name' => 'vehicles',
                'replacement_before' => [
                    ["/.*?\n*(\s*Transport Order Number.+?)Print\n\s*Go.+/is", "$1"],
                    ["/\s*Pickup Information.+/s", ""],
                    ["/.+Number of vehicles[^\n]+\n/s", ""],
                    ["/^[^\n]+\n/", ""],
                    ["/^ +/m", ""],
                    ["/ +$/m", ""],
                    ["/^([a-z0-9]+\s{3,}+).+(\s{3,}[0-9]{4})/im", "$1$2"]
                ],
                'parser' => VehicleParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'pickup_contact',
                'replacement_before' => [
                    ["/.*?\n*(\s*Transport Order Number.+?)Print\n\s*Go.+/is", "$1"],
                    ["/.+Pickup Information.+Pickup *Date[^\n]+\n/s", ""],
                    ["/\s*Delivery Information.+/s", ""],
                    ["/Pickup Location/s", "Location"],
                    ["/\s*\([^\n]+\n/is", "\n"],
                    ["/^ +/m", ""],
                    ["/ +$/m", ""],
                ],
                'parser' => PickupDeliveryContactParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'delivery_contact',
                'replacement_before' => [
                    ["/.*?\n*(\s*Transport Order Number.+?)Print\n\s*Go.+/is", "$1"],
                    ["/.+Delivery Information.+Delivery *Date[^\n]+\n/s", ""],
                    ["/\n\s*Other\s*\n.+/s", ""],
                    ["/Delivery Location/s", "Location"],
                    ["/\s*\([^\n]+\n/is", "\n"],
                    ["/^ +/m", ""],
                    ["/ +$/m", ""],
                ],
                'parser' => PickupDeliveryContactParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'shipper_contact',
                'replacement_before' => [
                    ["/(.*\f|^)(.+Transportation Terms & Conditions.+)/s", "$2"],
                    ["/(.+?)(\f.+|$)/s", "$1"],
                    ["/Transportation Terms & Condition.+?(SEND INVOICES TO)/s", "$1"],
                    ["/(.+?)\s*Doc ID: [a-z0-9]+.+/s", "$1"],
                    ["/www\.[^\n]+\n/", ""],
                    ["/ MC#.+/m", ""],
                    ["/^ +/m", ""],
                    ["/ +$/m", ""],
                    ["/\n{2,}/s", "\n"],

                ],
                'parser' => ShipperContactParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'payment',
                'replacement_before' => [
                    ["/.+Payment Terms\s*/s", ""],
                    ["/\s*Doc ID: [a-z0-9]+.+/s", "$1"]
                ],
                'parser' => PaymentParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
        ];
    }
}
