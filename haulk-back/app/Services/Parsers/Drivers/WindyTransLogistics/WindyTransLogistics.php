<?php

namespace App\Services\Parsers\Drivers\WindyTransLogistics;

use App\Exceptions\Parser\PdfFileException;
use App\Services\Parsers\Drivers\WindyTransLogistics\Parsers\PaymentParser;
use App\Services\Parsers\Drivers\WindyTransLogistics\Parsers\PickupDeliveryContactParser;
use App\Services\Parsers\Drivers\WindyTransLogistics\Parsers\ShipperContactParser;
use App\Services\Parsers\Drivers\WindyTransLogistics\Parsers\VehiclesParser;
use App\Services\Parsers\Objects\ParserPart;
use App\Services\Parsers\ParserAbstract;

class WindyTransLogistics extends ParserAbstract
{
    public function type(): string
    {
        return "/^[^\n]+ Manifest # [0-9 ]+\n[0-9]+ Orders\(s\) \/ [0-9]+ Vehicles\(s\)/s";
    }

    /**
     * @param string $text
     * @return void
     * @throws PdfFileException
     */
    public function preCheck(string $text): void
    {
        if (!preg_match("/[1-9][0-9]*\.\s+Order # .+[1-9][0-9]*\.\s+Order # /s", $text)) {
            return;
        }
        throw new PdfFileException('two_destination_data');
    }

    public function parts(): array
    {
        return [
            ParserPart::make('load_id')
                ->setReplacementBefore(
                    [
                        ["/\u{00AD}/", "-"],
                        ["/.+?\f/s", "", 1],
                        ["/\f.+/s", "", 1]
                    ]
                )
                ->single("/Load Number: +(?<load_id>[^\n]+)\n/s"),
            ParserPart::make('pickup_date')
                ->setReplacementBefore(
                    [
                        ["/\u{00AD}/", "-"],
                        ["/.+?\f/s", "", 1],
                        ["/\f.+/s", "", 1],
                    ]
                )
                ->single("/Pickup Date:[^\n]+(?<pickup_date>[0-9]{4}-[0-9]{2}-[0-9]{2})\n/us"),
            ParserPart::make('delivery_date')
                ->setReplacementBefore(
                    [
                        ["/\u{00AD}/", "-"],
                        ["/.+?\f/s", "", 1],
                        ["/\f.+/s", "", 1]
                    ]
                )
                ->single("/Due Date:[^\n]+(?<delivery_date>[0-9]{4}-[0-9]{2}-[0-9]{2})\n/us"),
            ParserPart::make('vehicles')
                ->setReplacementBefore(
                    [
                        ["/\u{00AD}/", "-"],
                        ["/.+\nDestination:[^\n]+\n/s", "\n"],
                        ["/Freight:/s", ""],
                        ["/\nContracted.+/s", ""],
                        ["/^ +/m", ""],
                        ["/^\n/m", ""],
                    ]
                )
                ->custom(VehiclesParser::class),
            ParserPart::make('pickup_contact')
                ->setReplacementBefore(
                    [
                        ["/\u{00AD}/", "-"],
                        ["/.+\n(\s+Origin {3,}Destination)/s", "$1"],
                        ["/STI-No/", "      "],
                        ["/STI-Yes/", "       "]
                    ]
                )
                ->custom(PickupDeliveryContactParser::class),
            ParserPart::make('delivery_contact')
                ->setReplacementBefore(
                    [
                        ["/\u{00AD}/", "-"],
                        ["/.+\n(\s+Origin {3,}Destination)/s", "$1"],
                        ["/STI-No/", "      "],
                        ["/STI-Yes/", "       "]
                    ]
                )
                ->custom(PickupDeliveryContactParser::class),
            ParserPart::make('shipper_contact')
                ->setReplacementBefore(
                    [
                        ["/\u{00AD}/s", "-"],
                        ["/.+Load Confirmation:\s*/s", ""],
                        ["/\s+Carrier:\s+.+/s", ""],
                        ["/, (Fax:)/s", "\n$1"],
                        ["/\n{2,}/", "\n"],
                        ["/^ +/m", ""]
                    ]
                )
                ->custom(ShipperContactParser::class),
            ParserPart::make('payment')
                ->setReplacementBefore(
                    [
                        ["/\u{00AD}/", "-"],
                        ["/.+\s+Contracted\s+/s", ""],
                        ["/\s+Payment.+/s", ""],
                        ["/Rate:/s", ""],
                        ["/\n/", ""],
                        ["/ /", ""],
                    ]
                )
                ->custom(PaymentParser::class),
        ];
    }
}
