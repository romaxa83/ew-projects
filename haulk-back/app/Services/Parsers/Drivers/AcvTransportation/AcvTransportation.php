<?php

namespace App\Services\Parsers\Drivers\AcvTransportation;

use App\Services\Parsers\Drivers\AcvTransportation\Parsers\DispatchInstructionsParser;
use App\Services\Parsers\Drivers\AcvTransportation\Parsers\PaymentParser;
use App\Services\Parsers\Drivers\AcvTransportation\Parsers\PickupDeliveryContactParser;
use App\Services\Parsers\Drivers\AcvTransportation\Parsers\ShipperContactParser;
use App\Services\Parsers\Drivers\AcvTransportation\Parsers\VehiclesParser;
use App\Services\Parsers\Objects\ParserPart;
use App\Services\Parsers\ParserAbstract;
use App\Services\Parsers\PdfService;

class AcvTransportation extends ParserAbstract
{

    public function type(): string
    {
        return '/https:\/\/transport\.acvauctions\.com\/legal$/';
    }

    public function parts(): array
    {
        return [
            ParserPart::init([
                'name' => 'load_id',
                'pattern' => '/\nOrder ID: (?<load_id>\w+)\n/s',
                'type' => PdfService::PARSER_TYPE_SINGLE,
            ]),
            ParserPart::init([
                'name' => 'pickup_date',
                'pattern' => "/\s*Date: +(?<pickup_date>[0-9]{4}-[0-9]{2}-[0-9]{2})[^\n]+Date: +[0-9]{4}-[0-9]{2}-[0-9]{2}.*/is",
                'type' => PdfService::PARSER_TYPE_SINGLE,
                'replacement_before' => [
                    ["/\s*ACV Notes:.+/s", ""]
                ]
            ]),
            ParserPart::init([
                'name' => 'delivery_date',
                'pattern' => "/\s*Date: +[0-9]{4}-[0-9]{2}-[0-9]{2}[^\n]+Date: +(?<delivery_date>[0-9]{4}-[0-9]{2}-[0-9]{2}).*/is",
                'type' => PdfService::PARSER_TYPE_SINGLE,
                'replacement_before' => [
                    ["/\s*ACV Notes:.+/s", ""]
                ]
            ]),
            ParserPart::init([
                'name' => 'dispatch_instructions',
                'replacement_before' => [
                    ["/\s*ACV Notes:.+/s", ""],
                    ["/.+(Pickup +Details\s*Delivery +Details.+)/s", "$1"]
                ],
                'parser' => DispatchInstructionsParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'vehicles',
                'replacement_before' => [
                    ["/\s*Pickup +Details\s*Delivery +Details.+/s", ""],
                    ["/.+\s*(Carrier[^\n]+Vehicle.+)/s", "$1"]
                ],
                'parser' => VehiclesParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'pickup_contact',
                'replacement_before' => [
                    ["/.+?(Pickup +Details\s*Delivery +Details.+)/s", "$1"],
                    ["/\s*Date: +[0-9]{4}-[0-9]{2}-[0-9]{2}[^\n]+Date: +[0-9]{4}-[0-9]{2}-[0-9]{2}.+/s", ""]
                ],
                'parser' => PickupDeliveryContactParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'delivery_contact',
                'replacement_before' => [
                    ["/.+?(Pickup +Details\s*Delivery +Details.+)/s", "$1"],
                    ["/\s*Date: +[0-9]{4}-[0-9]{2}-[0-9]{2}[^\n]+Date: +[0-9]{4}-[0-9]{2}-[0-9]{2}.+/s", ""]
                ],
                'parser' => PickupDeliveryContactParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'shipper_contact',
                'replacement_before' => [
                    ['/^(.*?)Dispatch Sheet /s', ""],
//                    ["/Auction ID:(.*)/s", ""],
//                    ["/\n/", "??"],
                ],
                'parser' => ShipperContactParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ]),
            ParserPart::init([
                'name' => 'payment',
                'replacement_before' => [
                    ["/\s*Dispatched by *:.*/is", ""],
                    ["/.+?ACV Notes:[^\n]*\n/is", ""],
                ],
                'parser' => PaymentParser::class,
                'type' => PdfService::PARSER_TYPE_CUSTOM,
            ])
        ];
    }
}
