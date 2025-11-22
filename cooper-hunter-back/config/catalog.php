<?php

use App\Enums\Solutions\SolutionIndoorEnum;
use App\Enums\Solutions\SolutionTypeEnum;
use App\Enums\Solutions\SolutionZoneEnum;

return [
    'features' => [
        'display_in_web_count' => 3,
    ],

    'categories' => [
        'main_count' => 4,
    ],

    'solutions' => [
        'btu' => [
            'lists' => [
                'all' => $all = [
                    6000,
                    9000,
                    12000,
                    18000,
                    19000,
                    24000,
                    28000,
                    30000,
                    36000,
                    48000,
                    55000,
                    60000,
                ],
                SolutionTypeEnum::OUTDOOR => [
                    SolutionZoneEnum::SINGLE => $all,
//                    [
//                        6000,
//                        9000,
//                        12000,
//                        18000,
//                        24000,
//                        30000,
//                        36000,
//                        48000,
//                        60000,
//                    ],
                    SolutionZoneEnum::MULTI => $all,
//                    [
//                        18000,
//                        19000,
//                        28000,
//                        36000,
//                        48000,
//                    ]
                ],
                SolutionTypeEnum::INDOOR => [
                    SolutionZoneEnum::SINGLE => [
                        SolutionIndoorEnum::AIR_HANDLER_UNIT => $all,
                        SolutionIndoorEnum::WALL_MOUNT => $all,
//                        [
//                            9000,
//                            12000,
//                            18000,
//                            24000,
//                            30000,
//                            36000,
//                        ],
                        SolutionIndoorEnum::CEILING_CASSETTE => $all,
//                        [
//                            9000,
//                            12000,
//                            18000,
//                            24000,
//                        ],
                        SolutionIndoorEnum::SLIM_DUCT => $all,
//                        [
//                            9000,
//                            12000,
//                            18000,
//                            24000,
//                        ],
                        SolutionIndoorEnum::UNIVERSAL_FLOOR_CEILING => $all,
//                        [
//                            18000,
//                            24000,
//                        ],
                        SolutionIndoorEnum::MINI_FLOOR_CONSOLE => $all,
//                        [
//                            9000,
//                            12000,
//                        ],
                    ],
                    SolutionZoneEnum::MULTI => [
                        SolutionIndoorEnum::AIR_HANDLER_UNIT => $all,
                        SolutionIndoorEnum::WALL_MOUNT => $all,
//                        [
//                            9000,
//                            12000,
//                            18000,
//                            24000,
//                            30000,
//                            36000,
//                        ],
                        SolutionIndoorEnum::CEILING_CASSETTE => $all,
//                        [
//                            9000,
//                            12000,
//                            18000,
//                            24000,
//                            36000,
//                            48000,
//                            60000,
//                        ],
                        SolutionIndoorEnum::SLIM_DUCT => $all,
//                        [
//                            9000,
//                            12000,
//                            18000,
//                            24000,
//                            36000,
//                            48000,
//                            60000,
//                        ],
                        SolutionIndoorEnum::UNIVERSAL_FLOOR_CEILING => $all,
//                        [
//                            18000,
//                            24000,
//                        ],
                        SolutionIndoorEnum::MINI_FLOOR_CONSOLE => $all,
//                        [
//                            9000,
//                            12000,
//                        ],
                    ],
                ]
            ],
            'max_percent' => 30
        ],
        'voltage' => [
            'list' => [
                115,
                230,
            ],
            'default' => 230
        ]
    ]
];
