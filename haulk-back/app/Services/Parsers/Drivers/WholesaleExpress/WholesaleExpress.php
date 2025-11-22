<?php

namespace App\Services\Parsers\Drivers\WholesaleExpress;

use App\Services\Parsers\Drivers\WholesaleExpress\Parsers\PickupDeliveryContactParser;
use App\Services\Parsers\Drivers\WholesaleExpress\Parsers\ShipperContactParser;
use App\Services\Parsers\Drivers\WholesaleExpress\Parsers\VehiclesParser;
use App\Services\Parsers\Objects\ParserPart;
use App\Services\Parsers\ParserAbstract;
use App\Services\Parsers\PdfService;

class WholesaleExpress extends ParserAbstract
{

    public function type(): string
    {
        return "/(BILL[^\~]+OF[^\~]+LADING)([^\~]+?)(WHOLESALE EXPRESS LLC)/";
    }

    public function parts(): array
    {
        return [
            ParserPart::init([
                'name' => 'load_id',
                'pattern' => '/Load\/Order[^\~]+  ID: (?<load_id>\d+)\n/',
                'type' => PdfService::PARSER_TYPE_SINGLE,
            ]),
            ParserPart::init([
                'name' => 'pickup_date',
                'pattern' => '/38\\x1D (?<pickup_date>[\d]{2}\/[\d]{2}\/[\d]{4}) \d+:\d+/',
                'replacement_before' => [
                    ['&^[^\~]+ORIGIN\s*DESTINATION\n([^\~]+)PICKUP[^\~]+$&', '${1}'],
                ],
                'replacement_after' => null,
                'type' => PdfService::PARSER_TYPE_SINGLE,
            ]),
            ParserPart::init([
                'name' => 'delivery_date',
                'pattern' => '/\(\/\\x1D (?<delivery_date>[\d]{2}\/[\d]{2}\/[\d]{4}) \d+:\d+/',
                'replacement_before' => [
                    ['&^[^\~]+ORIGIN\s*DESTINATION\n([^\~]+)PICKUP[^\~]+$&', '${1}'],
                ],
                'replacement_after' => null,
                'type' => PdfService::PARSER_TYPE_SINGLE,
            ]),
            ParserPart::init([
                'name' => 'instructions',
                'pattern' => '/(?<instructions>.+)/',
                'replacement_before' => [
                    ["/.+COMMENTS\s*/s", ""],
                    ["/\s*NOTICE.+/s", ""],
                    ["/\n/", " "],
                    ["/ {2,}/", " "]
                ],
                'type' => PdfService::PARSER_TYPE_SINGLE,
            ]),
            ParserPart::init([
                'name' => 'vehicles',
                'replacement_before' => [
                    ["/.+INSPECTIONS\s*\n/s", ""],
                    ["/\s*SPECIAL.+/s", ""],
                    ["/^([A-Z\d]{11})( )?([\d]{0,5})(&)([\d]{0,5}\s*)/m", "$1$2$3C$5"],
                    ["/^([A-Z\d]{11})( )([A-Z\d]{6}\s*)/m", "$1$3"]
                ],
                'parser' => VehiclesParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'pickup_contact',
                'replacement_before' => [
                    ["/.+ORIGIN\s+DESTINATION\s*/s", ""],
                    ["/\s*PICKUP[^\n]+NOTES.+/s", ""]
                ],
                'parser' => PickupDeliveryContactParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM
            ]),
            ParserPart::init([
                'name' => 'delivery_contact',
                'replacement_before' => [
                    ["/.+ORIGIN\s+DESTINATION\s*/s", ""],
                    ["/\s*PICKUP[^\n]+NOTES.+/s", ""]
                ],
                'parser' => PickupDeliveryContactParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM
            ]),
            ParserPart::init([
                'name' => 'shipper_contact',
                'replacement_before' => [
                    ["/^.+(WHOLESALE EXPRESS LLC.+)/is", "$1"],
                    ["/\sCARRIER:\s.+/is", ""]
                ],
                'parser' => ShipperContactParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM
            ])
        ];
    }
}
