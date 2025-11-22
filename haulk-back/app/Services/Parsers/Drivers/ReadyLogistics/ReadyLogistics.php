<?php

namespace App\Services\Parsers\Drivers\ReadyLogistics;

use App\Services\Parsers\Drivers\ReadyLogistics\Parsers\DispatchInstructionParser;
use App\Services\Parsers\Drivers\ReadyLogistics\Parsers\VehiclesParser;
use App\Services\Parsers\Drivers\ReadyLogistics\Parsers\PaymentParser;
use App\Services\Parsers\Drivers\ReadyLogistics\Parsers\PickupDeliveryContactParser;
use App\Services\Parsers\Drivers\ReadyLogistics\Parsers\ShipperContactParser;
use App\Services\Parsers\Objects\ParserPart;
use App\Services\Parsers\Objects\ParserPartIntend;
use App\Services\Parsers\ParserAbstract;

class ReadyLogistics extends ParserAbstract
{
    public function type(): string
    {
        return "/Ready Logistics/i";
    }

    public function options(): array
    {
        return [
            'raw',
            'r 96'
        ];
    }

    public function parts(): array
    {
        return [
            ParserPart::make('load_id')
                ->setReplacementBefore(
                    [
                        ["/\s*Shipper Info.+/is", ""],
                        ["/^ +/m", ""]
                    ]
                )
                ->setReplacementIntend(
                    [
                        ParserPartIntend::left("/^(?<intend>.+\s{3,})Load/m")
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
                ->single("/\s*Load (?<load_id>.+)$/i"),
            ParserPart::make('pickup_date')
                ->setReplacementBefore(
                    [
                        ["/(.+Scheduled Pick-Up)/s", ""],
                        ["/(Scheduled Delivery.*)/s", ""],
                    ]
                )
                ->single("/(?<pickup_date>\d{2}\/\d{2}\/\d{4})/is"),
            ParserPart::make('delivery_date')
                ->setReplacementBefore(
                    [
                        ["/(.+Scheduled Delivery)/s", ""],
                        ["/(Load Info.*)/s", ""],
                    ]
                )
                ->single("/(?<delivery_date>\d{2}\/\d{2}\/\d{4})/is"),
            ParserPart::make('instructions')
                ->setReplacementBefore(
                    [
                        ["/(.+Transport Release Notes)/s", ""]
                    ]
                )
                ->single("/(?<instructions>.+)/s"),
//            ParserPart::make('dispatch_instructions')
//                ->setReplacementBefore(
//                    [
//                        ["/(.+Additional Info)/s", ""],
//                        ["/(Dispatch Sheet.*)/s", ""],
//                    ]
//                )
//                ->single("/(?<dispatch_instructions>.+)/s"),
            ParserPart::make('dispatch_instructions')
                ->custom(DispatchInstructionParser::class),
            ParserPart::make('vehicles')
                ->setReplacementBefore(
                    [
                        ["/(.+Vehicle Year\/Make\/Model)/s", ""],
                        ["/(Critical Notes.*)/s", ""],
                        ["/VIN/", " ?? "],
                        ["/\n/", ""],
                    ]
                )
                ->custom(VehiclesParser::class),
            ParserPart::make('pickup_contact')
                ->setReplacementBefore(
                    [
                        ["/.+?Destination Info.?\n/is", "$1", 1],
                        ["/(Dates.*)/s", ""],
                        ["/Origin/", ""],
                        ["/Destination/", ""],
                        ["/Contact Info/", ""],
                        ["/\n\n/", "---"],
                        ["/\n/", " ?? "],
                        ["/---/", "\n"]
                    ]
                )
                ->custom(PickupDeliveryContactParser::class),
            ParserPart::make('delivery_contact')
                ->setReplacementBefore(
                    [
                        ["/.+?Destination Info.?\n/is", "$1", 1],
                        ["/(Dates.*)/s", ""],
                        ["/Origin/", ""],
                        ["/Destination/", ""],
                        ["/Contact Info/", ""],
                        ["/\n\n/", "---"],
                        ["/\n/", " ?? "],
                        ["/---/", "\n"]
                    ]
                )
                ->custom(PickupDeliveryContactParser::class),
            ParserPart::make('shipper_contact')
                ->setReplacementBefore(
                    [
                        ["/(.+Shipper)/s", ""],
                        ["/(Carrier.*)/s", " "],
                        ["/Contact Info/", "\n"],
                        ["/\n\n/", "---"],
                        ["/\n/", " ?? "],
                        ["/---/", "\n"]
                    ]
                )
                ->custom(ShipperContactParser::class),
            ParserPart::make('payment')
                ->setReplacementBefore(
                    [
                        ["/\n/", " "],
                        ["/ {2,}/", " "],
                        ["/^(.*?)(?=Scheduled Delivery)/s", ""],
                        ["/(Vehicle Info.*)/s", ""],
                    ]
                )
                ->custom(PaymentParser::class),
        ];
    }
}

