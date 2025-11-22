<?php

namespace App\Services\Parsers\Drivers\ShipCars;

use App\Services\Parsers\Drivers\ShipCars\Parsers\PaymentParser;
use App\Services\Parsers\Drivers\ShipCars\Parsers\PickupDeliveryContactParser;
use App\Services\Parsers\Drivers\ShipCars\Parsers\ShipperContactParser;
use App\Services\Parsers\Drivers\ShipCars\Parsers\VehiclesParser;
use App\Services\Parsers\Objects\ParserPart;
use App\Services\Parsers\Objects\ParserPartIntend;
use App\Services\Parsers\ParserAbstract;

class ShipCars extends ParserAbstract
{
    public function type(): string
    {
        return "/Ship\.C\s*ars platform/s";
    }

    public function parts(): array
    {
        return [
            ParserPart::make('load_id')
                ->setReplacementBefore(
                    [
                        ["/\s*BROKER.+/s", ""],
                        ["/.+(Dispatch Contract)/s", "$1"]
                    ]
                )
                ->setReplacementAfter(
                    [
                        ["/ \(Rev.+/", ""]
                    ]
                )
                ->single("/Dispatch Contract #(?<load_id>.+)$/i"),
            ParserPart::make('pickup_date')
                ->single("/^\s*Pickup\s+(Estimated|No Later Than)\s*(?<pickup_date>\d{2}\/\d{2}\/\d{4})/m"),
            ParserPart::make('delivery_date')
                ->single("/^\s*Delivery\s+(Estimated|Between)\s*(?<delivery_date>\d{2}\/\d{2}\/\d{4})/m"),
            ParserPart::make('dispatch_instructions')
                ->setReplacementBefore(
                    [
                        ["/.+?DISPATCH INSTRUCTIONS/s", "", 1],
                        ["/\s*Very Important, Please Read!\s*\n/s", ""],
                        ["/\s*Montway as a broker does not sanction any FMCSA.+/s", ""],
                        [
                            "/By\s+accepting\s+this\s+Order,\s+you\s+agree\s+to\s+use\s+Ship\.Cars.+?transit\s+as\s+required\s+by\s+Shipper.\s*/s",
                            ""
                        ],
                        ["/Dispatch Contract #.+?\f.+?From IP[^\n]+\n/s", ""],
                        ["/\n{2,}/", "\n"],
                        ["/ {2,}/", " "],
                        ["/^ +/m", ""],
                        ["/ +$/m", ""],
                    ]
                )
                ->single("/(?<dispatch_instructions>.+)/s"),
            ParserPart::make('vehicles')
                ->setReplacementBefore(
                    [
                        ["/.+?VEHICLE INFORMATION\s*/s", "", 1],
                        ["/\n[^\n]+?DISPATCH INSTRUCTIONS.+/s", ""],
                        ["/^ +/m", ""],
                        ["/ +$/m", ""],
                    ]
                )
                ->custom(VehiclesParser::class),
            ParserPart::make('pickup_contact')
                ->setReplacementBefore(
                    [
                        ["/.+?(Pickup from {4,})/is", "$1", 1],
                        ["/\s*VEHICLE INFORMATION.+/s", ""],
                        ["/^ +/m", ""],
                        ["/ +$/m", ""],
                    ]
                )
                ->setReplacementIntend(
                    [
                        ParserPartIntend::right("/^(?<intend>.+)Deliver to\n/m")
                            ->setClearAfter(
                                [
                                    ["/\s*Pickup from\s*/is", ""],
                                    ["/ +$/m", ""],
                                    ["/^ +/m", ""],
                                ]
                            )
                    ]
                )
                ->custom(PickupDeliveryContactParser::class),
            ParserPart::make('delivery_contact')
                ->setReplacementBefore(
                    [
                        ["/.+?(Pickup from {4,})/is", "$1", 1],
                        ["/\s*VEHICLE INFORMATION.+/s", ""],
                        ["/^ +/m", ""],
                        ["/ +$/m", ""],
                    ]
                )
                ->setReplacementIntend(
                    [
                        ParserPartIntend::left("/^(?<intend>.+)Deliver to\n/m")
                            ->setClearAfter(
                                [
                                    ["/\s*Deliver to\s*/is", ""],
                                    ["/ +$/m", ""],
                                    ["/^ +/m", ""],
                                ]
                            )
                    ]
                )
                ->custom(PickupDeliveryContactParser::class),
            ParserPart::make('shipper_contact')
                ->setReplacementBefore(
                    [
                        ["/.+?(BROKER\s+CARRIER\s*)/s", "$1", 1],
                        ["/\s*\n[^\n]+ORDER\s+INFORMATION.+/s", ""],
                        ["/ +$/m", ""],
                        ["/^ +/m", ""],
                    ]
                )
                ->setReplacementIntend(
                    [
                        ParserPartIntend::right("/^(?<intend>BROKER\s{2,})CARRIER$/m")
                            ->setClearAfter(
                                [
                                    ["/\s*BROKER\s*/s", ""],
                                    ["/ +USDOT#.+/m", ""],
                                    ["/ +$/m", ""],
                                    ["/^ +/m", ""],
                                ]
                            )
                    ]
                )
                ->custom(ShipperContactParser::class),
            ParserPart::make('payment')
                ->setReplacementBefore(
                    [
                        ["/\s*For more details about payment options policy,.+/s", ""],
                        ["/.+\s*(TIMEFRAMES {3,}PAYMENT)/s", "$1"],
                    ]
                )
                ->setReplacementIntend(
                    [
                        ParserPartIntend::left("/^(?<intend>TIMEFRAMES.+)PAYMENT/m")
                            ->setClearAfter(
                                [
                                    ["/^ +/m", ""],
                                    ["/\n{2,}.+/", ""],
                                    ["/^PAYMENT\s*/", ""],
                                    ["/\sShip Via[^\n]+\n/is", ""],
                                ]
                            )
                    ]
                )
                ->custom(PaymentParser::class),
        ];
    }
}
