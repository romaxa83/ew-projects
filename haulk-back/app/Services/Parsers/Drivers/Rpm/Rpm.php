<?php

namespace App\Services\Parsers\Drivers\Rpm;

use App\Exceptions\Parser\PdfFileException;
use App\Services\Parsers\Drivers\Rpm\Parsers\DispatchInstructionsParser;
use App\Services\Parsers\Drivers\Rpm\Parsers\PaymentParser;
use App\Services\Parsers\Drivers\Rpm\Parsers\PickupDeliveryContactParser;
use App\Services\Parsers\Drivers\Rpm\Parsers\PickupDeliveryDateParser;
use App\Services\Parsers\Drivers\Rpm\Parsers\ShipperContactParser;
use App\Services\Parsers\Drivers\Rpm\Parsers\VehiclesParser;
use App\Services\Parsers\Objects\ParserPart;
use App\Services\Parsers\Objects\ParserPartIntend;
use App\Services\Parsers\ParserAbstract;

class Rpm extends ParserAbstract
{
    public function type(): string
    {
        return "/(\n| )RPM(\n| )/s";
    }

    /**
     * @param string $text
     * @return void
     * @throws PdfFileException
     */
    public function preCheck(string $text): void
    {
        if (!preg_match(
            "/\nDelivery\n.+?\nDelivery\n/s",
            $text
        )) {
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
                        ["/Rate Confirmation/", ""],
                        ["/Page/", ""],
                        ["/\n[A-Z][a-z]{2} [0-9]{1,2}, [0-9]{4}\n?/s", ""],
                        ["/\n{3,}.+/s", ""],
                        ["/\n/", " "],
                        ["/ {2,}/", " "]
                    ]
                )
                ->single("/Shipment ID (?<load_id>.+)$/i"),
            ParserPart::make('pickup_date')
                ->setReplacementBefore(
                    [
                        ["/.+\s*?Route {3,}Pickup[^\n]+\n/s", "", 1],
                        ["/\n {3,}Delivery.+/s", ""],
                    ]
                )
                ->custom(PickupDeliveryDateParser::class),
            ParserPart::make('delivery_date')
                ->setReplacementBefore(
                    [
                        ["/.+?\n {3,}Delivery[^\n]+\n/s", "", 1],
                        ["/\s*\n\s*Items {2,}.+/s", "", 1],
                    ]
                )
                ->custom(PickupDeliveryDateParser::class),
            ParserPart::make('dispatch_instructions')
                ->setReplacementBefore(
                    [
                        ["/\s*TERMS AND CONDITIONS.+/s", "", 1],
                        ["/\s*Items {2,}.+\n\s*(Notes {2,})/s", "\n\n$1"],
                        ["/\s*Items {2,}.+/s", ""],//If order doesn't have "Notes" block
                        ["/.+\n([^\n]*?Route {3,}Pickup[^\n]+\n)/s", "$1", 1],
                    ]
                )
                ->setReplacementIntend(
                    [
                        ParserPartIntend::left("/^(?<intend>\s*Route {3,}Pickup {2,}| {3,}Delivery {2,})/m")
                            ->setClearAfter(
                                [
                                    ["/^ +/m", ""],
                                    ["/^[^\n]+\n[^\n]+\n/s", ""]
                                ]
                            )
                    ]
                )
                ->custom(DispatchInstructionsParser::class),
            ParserPart::make('vehicles')
                ->setReplacementBefore(
                    [
                        ["/.+?\n\s*Items/s", "", 1],
                        ["/\s*Total \- [0-9]+ items.+/s", ""],
                        ["/\f[^\n]+\n[^\n]+\n[^\n]+\n/s", ""],
                        ["/^ +/m", ""],
                    ]
                )
                ->custom(VehiclesParser::class),
            ParserPart::make('pickup_contact')
                ->setReplacementBefore(
                    [
                        ["/.+\n([^\n]*?Route {3,}Pickup[^\n]+\n)/s", "$1", 1],
                        ["/\n {3,}Delivery.+/s", "", 1],
                    ]
                )
                ->setReplacementIntend(
                    [
                        ParserPartIntend::left("/^(?<intend>\s*Route {3,}Pickup {2,})/m")
                            ->setClearAfter(
                                [
                                    ["/^ +/m", ""],
                                    ["/^([^\n]+\n[^\n]+\n[^\n]*).+/s", "$1"]
                                ]
                            )
                    ]
                )
                ->custom(PickupDeliveryContactParser::class),
            ParserPart::make('delivery_contact')
                ->setReplacementBefore(
                    [
                        ["/.+\n( {3,}Delivery {2,})/s", "$1"],
                        ["/\n\s*Items.+/s", ""]
                    ]
                )
                ->setReplacementIntend(
                    [
                        ParserPartIntend::left("/^(?<intend> {3,}Delivery {2,})/m")
                            ->setClearAfter(
                                [
                                    ["/^ +/m", ""],
                                    ["/^([^\n]+\n[^\n]+\n[^\n]*).+/s", "$1"]
                                ]
                            )
                    ]
                )
                ->custom(PickupDeliveryContactParser::class),
            ParserPart::make('shipper_contact')
                ->setReplacementBefore(
                    [
                        ["/(.+?)\n\s*Route {2,}.+/s", "$1", 1],
                        ["/^ +/m", ""],
                        ["/.+\n([^\n]+\n[^\n]+)$/s", "$1"]
                    ]
                )
                ->custom(ShipperContactParser::class),
            ParserPart::make('payment')
                ->setReplacementBefore(
                    [
                        ["/\s*TERMS AND CONDITIONS.+/s", ""],
                        ["/.+?\n\s*(Rate {2,})/s", "$1"],
                        ["/\s*Notes.+/s", ""],
                        ["/.+\s*Total +[$]/s", ""],
                        ["/,/s", ""],
                    ]
                )
                ->custom(PaymentParser::class),
        ];
    }
}
