<?php

namespace App\Services\Parsers\Drivers\Autosled;

use App\Services\Parsers\Drivers\Autosled\Parsers\PickupDeliveryContactParser;
use App\Services\Parsers\Drivers\Autosled\Parsers\ShipperContactParser;
use App\Services\Parsers\Drivers\Autosled\Parsers\VehiclesParser;
use App\Services\Parsers\Objects\ParserPart;
use App\Services\Parsers\Objects\ParserPartIntend;
use App\Services\Parsers\ParserAbstract;

class Autosled extends ParserAbstract
{
    public function type(): string
    {
        return "/Autosled BOL/s";
    }

    public function parts(): array
    {
        return [
            ParserPart::make('load_id')
                ->setReplacementBefore(
                    [
                        ["/.+?(Load ID:)/s", "$1", 1],
                        ["/(Load ID: .+?) {6,}\S.+/s", "$1", 1],
                        ["/Load ID: /s", ""],
                    ]
                )
                ->single("/(?<load_id>.+)/"),
            ParserPart::make('dispatch_instructions')
                ->setReplacementBefore(
                    [
                        ["/\s*\f.+/s", ""],
                        ["/.+Driver Notes\s*/s", ""],
                        ["/\n{3,}.+/s", ""],
                        ["/No notes provided by driver at pickup./s", ""],
                        ["/\n/s", " "],
                        ["/ {2,}/s", " "]
                    ]
                )
                ->single("/(?<dispatch_instructions>.+)/s"),
            ParserPart::make('vehicles')
                ->setReplacementBefore(
                    [
                        ["/\s*Origin {3,}Destination.+/s", ""],
                        ["/.+\n( *Vehicle {3,}Transporter)/s", "$1"],
                    ]
                )
                ->setReplacementIntend(
                    [
                        ParserPartIntend::right("/^(?<intend> *Vehicle {3,})Transporter/m")
                            ->setClearAfter(
                                [
                                    ["/^ *Vehicle[^\n]*\n+/s", ""],
                                    ["/\n{2,}/", "\n"],
                                    ["/^ +/m", ""],
                                    ["/ +$/m", ""]
                                ]
                            )
                    ]
                )
                ->custom(VehiclesParser::class),
            ParserPart::make('pickup_contact')
                ->setReplacementBefore(
                    [
                        ["/.+\n( *Origin {3,}Destination)/s", "$1"],
                        ["/\n *Contact Printed Name.+/s", ""],
                    ]
                )
                ->setReplacementIntend(
                    [
                        ParserPartIntend::right("/^(?<intend> *Origin {3,})Destination/m")
                            ->setClearAfter(
                                [
                                    ["/^ *Origin[^\n]*\n+/s", ""],
                                    ["/\n{2,}/", "\n"],
                                    ["/^ +/m", ""],
                                    ["/ +$/m", ""]
                                ]
                            )
                    ]
                )
                ->custom(PickupDeliveryContactParser::class),
            ParserPart::make('delivery_contact')
                ->setReplacementBefore(
                    [
                        ["/.+\n( *Origin {3,}Destination)/s", "$1"],
                        ["/\n *Contact Printed Name.+/s", ""],
                    ]
                )
                ->setReplacementIntend(
                    [
                        ParserPartIntend::left("/^(?<intend> *Origin {3,})Destination/m")
                            ->setClearAfter(
                                [
                                    ["/^ *Destination[^\n]*\n+/s", ""],
                                    ["/\n{2,}/", "\n"],
                                    ["/^ +/m", ""],
                                    ["/ +$/m", ""]
                                ]
                            )
                    ]
                )
                ->custom(PickupDeliveryContactParser::class),
            ParserPart::make('shipper_contact')
                ->setReplacementBefore(
                    [
                        ["/\n\s*Vehicle {3,}Transporter.+/s", ""],
                        ["/.+\n\s*(Shipper:)/s", "$1"],
                        ["/(Shipper: .+?) {5,}/s", "$1"],
                        ["/^Shipper: +/s", ""],
                    ]
                )
                ->custom(ShipperContactParser::class)
        ];
    }
}
