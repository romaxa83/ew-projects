<?php

namespace App\Services\Parsers\Drivers\WholesaleExpress2;

use App\Services\Parsers\Drivers\WholesaleExpress2\Parsers\PickupDeliveryContactParser;
use App\Services\Parsers\Drivers\WholesaleExpress2\Parsers\ShipperContactParser;
use App\Services\Parsers\Drivers\WholesaleExpress2\Parsers\VehiclesParser;
use App\Services\Parsers\Objects\ParserPart;
use App\Services\Parsers\ParserAbstract;
use App\Services\Parsers\PdfService;

class WholesaleExpress2 extends ParserAbstract
{

    public function type(): string
    {
        return '/^[^\~]+(WHOLESALE EXPRESS LLC)/';
    }

    public function parts(): array
    {
        return [
            ParserPart::init([
                'name' => 'load_id',
                'pattern' => '/(?<load_id>\w+)\s*/',
                'replacement_before' => [
                    ['/WHOLESALE[^\~]+/', ''],
                ],
                'type' => PdfService::PARSER_TYPE_SINGLE,
            ]),
            ParserPart::init([
                'name' => 'instructions',
                'pattern' => "/(?<instructions>.+)/",
                'replacement_before' => [
                    ["/.+\n(Carrier:.+)/is", "$1"],
                    ["/\nP\s+Miles\s+D.+/s", ""],
                    ["/^.+?(?=(?: {3,}|\n))/im", ""],
                    ["/\s*Special Instructions:\s*/i", ""],
                    ["/\n{2,}/i", "\n"],
                    ["/\n/i", " "],
                    ["/ {2,}/i", " "],
                    ["/^\s*(.+)\s*/", "$1"]
                ],
                'type' => PdfService::PARSER_TYPE_SINGLE,
            ]),
            ParserPart::init([
                'name' => 'vehicles',
                'replacement_before' => [
                    ["/.+Damage Survey\n/is", ""],
                    ["/\nNOTE:.+/s", ""]
                ],
                'parser' => VehiclesParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'pickup_contact',
                'replacement_before' => [
                    ["/.+\n\s*(P\s+.+)/s", "$1"],
                    ["/\n\s*Unit\s*VIN.+/is", ""]
                ],
                'parser' => PickupDeliveryContactParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM
            ]),
            ParserPart::init([
                'name' => 'delivery_contact',
                'replacement_before' => [
                    ["/.+\n\s*(P\s+.+)/s", "$1"],
                    ["/\n\s*Unit\s*VIN.+/is", ""]
                ],
                'parser' => PickupDeliveryContactParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM
            ]),
            ParserPart::init([
                'name' => 'shipper_contact',
                'replacement_before' => [
                    ["/^[0-9]+\s*(.+)\nCarrier:.+/is", "$1"],
                    ["/\nDate:\s*[0-9]{4}\-[0-9]{2}\-[0-9]{2}\s*?/is", ""],
                    ["/^\s+/m", ""]
                ],
                'parser' => ShipperContactParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM
            ]),
        ];
    }
}
