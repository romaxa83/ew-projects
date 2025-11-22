<?php

namespace App\Services\Parsers\Drivers\SuperDispatch;

use App\Services\Parsers\Drivers\SuperDispatch\Parsers\DispatchInstructionsParser;
use App\Services\Parsers\Drivers\SuperDispatch\Parsers\PaymentParser;
use App\Services\Parsers\Drivers\SuperDispatch\Parsers\PickupDeliveryContactParser;
use App\Services\Parsers\Drivers\SuperDispatch\Parsers\ShipperContactParser;
use App\Services\Parsers\Drivers\SuperDispatch\Parsers\VehiclesParser;
use App\Services\Parsers\Objects\ParserPart;
use App\Services\Parsers\Objects\ParserPartIntend;
use App\Services\Parsers\ParserAbstract;

class SuperDispatch extends ParserAbstract
{
    public function type(): string
    {
        return "/^\s*Powered by Super Dispatch\s*$/im";
    }

    public function parts(): array
    {
        return [
            ParserPart::make('load_id')
                ->setReplacementBefore(
                    [
                        ["/\s*Carrier Information.+/is", ""],
                        ["/^ +/m", ""]
                    ]
                )
                ->setReplacementIntend(
                    [
                        ParserPartIntend::left("/^(?<intend>.+\s{3,})Order/m")
                            ->setClearAfter(
                                [
                                    ["/Dispatch Sheet/", ""],
                                    ["/\n/", " "],
                                    ["/ {2,}/", " "],
                                    ["/^ +/", ""],
                                    ["/ +$/", ""],
                                    ["/\*+$/", ""]
                                ]
                            )
                    ]
                )
                ->single("/\s*Order ID: (?<load_id>.+)$/i"),
            ParserPart::make('pickup_date')
                ->setReplacementBefore(
                    [
                        ["/.+?Carrier Pickup[^:]*:/is", ""],
                        ["/\s*Carrier Delivery[^:]*:.+/is", ""],
                    ]
                )
                ->single("/(?<pickup_date>\d{2}\/\d{2}\/\d{4})/is"),
            ParserPart::make('delivery_date')
                ->setReplacementBefore(
                    [
                        ["/\s*Ship Via.+/is", ""],
                        ["/.+?Carrier Delivery[^:]*:\s*/is", ""]
                    ]
                )
                ->single("/(?<delivery_date>\d{2}\/\d{2}\/\d{4})/is"),
            ParserPart::make('dispatch_instructions')
                ->setReplacementBefore(
                    [
                        ["/\f/s", "\n"],
                        ["/\s*CONTRACT TERMS.+/s", ""],
                        ["/.+?Delivery Information\s*/s", "", 1],
                        ["/\s*Pickup Information\s*Delivery Information\s*/s", "\n", 1],
                        ["/\s*\* \- Information was updated.+(DISPATCH INSTRUCTIONS)/s", "\n$1"],
                        ["/.+\n([^\n]+Notes:)/s", "$1", 1]
                    ]
                )
                ->custom(DispatchInstructionsParser::class),
            ParserPart::make('vehicles')
                ->setReplacementBefore(
                    [
                        ["/.+?Vehicle Information.+?\n/s", "", 1],
                        ["/.+?Total Vehicles.+?\n/s", "", 1],
                        ["/\s+Pickup Information.+/s", ""],
                        ["/^ +([0-9#]+)/m", "$1"],
                    ]
                )
                ->custom(VehiclesParser::class),
            ParserPart::make('pickup_contact')
                ->setReplacementBefore(
                    [
                        ["/.+?Delivery Information.?\n/is", "$1", 1],
                        ["/\s*Pickup Information\s*Delivery Information\s*/s", "\n", 1],
                        ["/(\s*Mobile:.+Mobile:[^\n]*)\n.*/s", "$1"]
                    ]
                )
                ->setReplacementIntend(
                    [
                        ParserPartIntend::right("/^(?<intend>Name:[^\n]+)Name:/m")
                            ->setClearAfter(
                                [
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
                        ["/.+?Delivery Information.?\n/is", "$1", 1],
                        ["/\s*Pickup Information\s*Delivery Information\s*/s", "\n", 1],
                        ["/(\s*Mobile:.+Mobile:[^\n]*)\n.*/s", "$1"]
                    ]
                )
                ->setReplacementIntend(
                    [
                        ParserPartIntend::left("/^(?<intend>Name:[^\n]+)Name:/m")
                            ->setClearAfter(
                                [
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
                        ["/\s*Carrier Information.+/is", "", 1],
                        ["/\s*Order ID.+/m", ""],
                        ["/\s*Shipper\s*/s", "\n"],
                        ["/^ +/m", ""],
                    ]
                )
                ->custom(ShipperContactParser::class),
            ParserPart::make('payment')
                ->setReplacementBefore(
                    [
                        ["/\s*?Vehicle Information.+/s", "", 1],
                        ["/.+?Order Information\s*/s", "", 1],
                    ]
                )
                ->setReplacementIntend(
                    [
                        ParserPartIntend::left("/^(?<intend>.+)Total Payment/m")
                            ->setClearAfter(
                                [
                                    ["/^ +/m", ""],
                                    ["/\s*Billing Information.+/s", ""]
                                ]
                            )
                    ]
                )
                ->custom(PaymentParser::class),
        ];
    }
}
