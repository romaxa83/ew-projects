<?php

namespace Database\Seeders\Catalog\Solutions;

use App\Enums\Solutions\SolutionClimateZoneEnum;
use App\Enums\Solutions\SolutionIndoorEnum;
use App\Enums\Solutions\SolutionSeriesEnum;
use App\Enums\Solutions\SolutionTypeEnum;
use App\Enums\Solutions\SolutionZoneEnum;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Solutions\Series\SolutionSeries;
use App\Models\Catalog\Solutions\Solution;
use App\Models\Localization\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SolutionDemoSeeder extends Seeder
{
    private const LINE_SETS = [
        'PJX-N1-16',
        'PJX-N2-16',
        'PJX-N3-16',
        'PJX-N1-25',
        'PJX-N2-25',
        'PJX-N3-25',
        'PJX-N1-50',
        'PJX-N2-50',
        'PJX-N3-50',
        'PJX-N4-25',
        'PJX-N4-50',
    ];

    private const INDOORS = [
        'CH-SR09SPHWM-115VI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::WALL_MOUNT,
            'btu' => 9000,
            'line_sets' => [
                'PJX-N1-16',
                'PJX-N1-25',
                'PJX-N1-50'
            ]
        ],
        'CH-12SPHWM-115VI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::WALL_MOUNT,
            'btu' => 12000,
            'line_sets' => [
                'PJX-N2-16',
                'PJX-N2-25',
                'PJX-N2-50'
            ]
        ],
        'CH-09MSPHWM-230VI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::WALL_MOUNT,
            'btu' => 9000,
            'line_sets' => [
                'PJX-N1-16',
                'PJX-N1-25',
                'PJX-N1-50'
            ]
        ],
        'CH-12MSPHWM-230VI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::WALL_MOUNT,
            'btu' => 12000,
            'line_sets' => [
                'PJX-N2-16',
                'PJX-N2-25',
                'PJX-N2-50'
            ]
        ],
        'CH-18MSPHWM-230VI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::WALL_MOUNT,
            'btu' => 18000,
            'line_sets' => [
                'PJX-N2-16',
                'PJX-N2-25',
                'PJX-N2-50'
            ]
        ],
        'CH-24MSPHWM-230VI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::WALL_MOUNT,
            'btu' => 24000,
            'line_sets' => [
                'PJX-N3-16',
                'PJX-N3-25',
                'PJX-N3-50'
            ]
        ],
        'CH-30SPHWM-230VI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::WALL_MOUNT,
            'btu' => 30000,
            'line_sets' => [
                'PJX-N3-16',
                'PJX-N3-25',
                'PJX-N3-50'
            ]
        ],
        'CH-36SPHWM-230VI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::WALL_MOUNT,
            'btu' => 36000,
            'line_sets' => [
                'PJX-N3-16',
                'PJX-N3-25',
                'PJX-N3-50'
            ]
        ],
        'CH-09MSPHCT-230VI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::CEILING_CASSETTE,
            'btu' => 9000,
            'line_sets' => [
                'PJX-N1-16',
                'PJX-N1-25',
                'PJX-N1-50'
            ]
        ],
        'CH-12MSPHCT-230VI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::CEILING_CASSETTE,
            'btu' => 12000,
            'line_sets' => [
                'PJX-N2-16',
                'PJX-N2-25',
                'PJX-N2-50'
            ]
        ],
        'CH-18MSPHCT-230VI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::CEILING_CASSETTE,
            'btu' => 18000,
            'line_sets' => [
                'PJX-N2-16',
                'PJX-N2-25',
                'PJX-N2-50'
            ]
        ],
        'CH-24MSPHCT-230VI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::CEILING_CASSETTE,
            'btu' => 24000,
            'line_sets' => [
                'PJX-N3-16',
                'PJX-N3-25',
                'PJX-N3-50'
            ]
        ],
        'CH-36LCCT-230VI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::CEILING_CASSETTE,
            'btu' => 36000,
            'line_sets' => [
                'PJX-N3-16',
                'PJX-N3-25',
                'PJX-N3-50'
            ]
        ],
        'CH-48LCCT-230VI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::CEILING_CASSETTE,
            'btu' => 48000,
            'line_sets' => [
                'PJX-N3-16',
                'PJX-N3-25',
                'PJX-N3-50'
            ]
        ],
        'CH-M09DTUI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::SLIM_DUCT,
            'btu' => 9000,
            'line_sets' => [
                'PJX-N1-16',
                'PJX-N1-25',
                'PJX-N1-50'
            ]
        ],
        'CH-M12DTUI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::SLIM_DUCT,
            'btu' => 12000,
            'line_sets' => [
                'PJX-N2-16',
                'PJX-N2-25',
                'PJX-N2-50'
            ]
        ],
        'CH-M18DTUI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::SLIM_DUCT,
            'btu' => 18000,
            'line_sets' => [
                'PJX-N2-16',
                'PJX-N2-25',
                'PJX-N2-50'
            ]
        ],
        'CH-M24DTUI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::SLIM_DUCT,
            'btu' => 24000,
            'line_sets' => [
                'PJX-N3-16',
                'PJX-N3-25',
                'PJX-N3-50'
            ]
        ],
        'CH-36LCDTU/I' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::SLIM_DUCT,
            'btu' => 36000,
            'line_sets' => [
                'PJX-N3-16',
                'PJX-N3-25',
                'PJX-N3-50'
            ]
        ],
        'CH-48LCDTU/I' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::SLIM_DUCT,
            'btu' => 48000,
            'line_sets' => [
                'PJX-N3-16',
                'PJX-N3-25',
                'PJX-N3-50'
            ]
        ],
        'CH-60LCDTU/I' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::SLIM_DUCT,
            'btu' => 60000,
            'line_sets' => [
                'PJX-N4-25',
                'PJX-N4-50',
            ]
        ],
        'CH-12MSPHMC-230VI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::MINI_FLOOR_CONSOLE,
            'btu' => 12000,
            'line_sets' => [
                'PJX-N2-16',
                'PJX-N2-25',
                'PJX-N2-50'
            ]
        ],
        'CH-18MSPHFC-230VI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::UNIVERSAL_FLOOR_CEILING,
            'btu' => 18000,
            'line_sets' => [
                'PJX-N2-16',
                'PJX-N2-25',
                'PJX-N2-50'
            ]
        ],
        'CH-24MSPHFC-230VI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::UNIVERSAL_FLOOR_CEILING,
            'btu' => 24000,
            'line_sets' => [
                'PJX-N3-16',
                'PJX-N3-25',
                'PJX-N3-50'
            ]
        ],
        'CH-36LCFCI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::UNIVERSAL_FLOOR_CEILING,
            'btu' => 36000,
            'line_sets' => [
                'PJX-N3-16',
                'PJX-N3-25',
                'PJX-N3-50'
            ]
        ],
        'CH-48LCFCI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::UNIVERSAL_FLOOR_CEILING,
            'btu' => 48000,
            'line_sets' => [
                'PJX-N3-16',
                'PJX-N3-25',
                'PJX-N3-50'
            ]
        ],
        'CH-60LCFCI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'indoor_type' => SolutionIndoorEnum::UNIVERSAL_FLOOR_CEILING,
            'btu' => 60000,
            'line_sets' => [
                'PJX-N4-25',
                'PJX-N4-50',
            ]
        ],
        'CH-D09MSPHWM-230VI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA_D,
            'indoor_type' => SolutionIndoorEnum::WALL_MOUNT,
            'btu' => 9000,
            'line_sets' => [
                'PJX-N1-16',
                'PJX-N1-25',
                'PJX-N1-50'
            ]
        ],
        'CH-D12MSPHWM-230VI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA_D,
            'indoor_type' => SolutionIndoorEnum::WALL_MOUNT,
            'btu' => 12000,
            'line_sets' => [
                'PJX-N2-16',
                'PJX-N2-25',
                'PJX-N2-50'
            ]
        ],
        'CH-D18MSPHWM-230VI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA_D,
            'indoor_type' => SolutionIndoorEnum::WALL_MOUNT,
            'btu' => 18000,
            'line_sets' => [
                'PJX-N2-16',
                'PJX-N2-25',
                'PJX-N2-50'
            ]
        ],
        'CH-D24MSPHWM-230VI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA_D,
            'indoor_type' => SolutionIndoorEnum::WALL_MOUNT,
            'btu' => 24000,
            'line_sets' => [
                'PJX-N3-16',
                'PJX-N3-25',
                'PJX-N3-50'
            ]
        ],
        'СH-D30SPHWM-230VI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA_D,
            'indoor_type' => SolutionIndoorEnum::WALL_MOUNT,
            'btu' => 30000,
            'line_sets' => [
                'PJX-N3-16',
                'PJX-N3-25',
                'PJX-N3-50'
            ]
        ],
        'СH-D36SPHWM-230VI' => [
            'type' => SolutionTypeEnum::INDOOR,
            'series' => SolutionSeriesEnum::SOPHIA_D,
            'indoor_type' => SolutionIndoorEnum::WALL_MOUNT,
            'btu' => 36000,
            'line_sets' => [
                'PJX-N3-16',
                'PJX-N3-25',
                'PJX-N3-50'
            ]
        ],
    ];

    private const OUTDOORS = [
        'CH-SR09SPH-115VO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'zone' => SolutionZoneEnum::SINGLE,
            'climate_zones' => [
                SolutionClimateZoneEnum::HOT,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'btu' => 9000,
            'voltage' => 115,
            'indoors' => [
                'CH-SR09SPHWM-115VI'
            ]
        ],
        'CH-12SPH-115VO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'zone' => SolutionZoneEnum::SINGLE,
            'climate_zones' => [
                SolutionClimateZoneEnum::HOT,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'btu' => 12000,
            'voltage' => 115,
            'indoors' => [
                'CH-12SPHWM-115VI'
            ]
        ],
        'CH-09SPH-230VO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'zone' => SolutionZoneEnum::SINGLE,
            'climate_zones' => [
                SolutionClimateZoneEnum::HOT,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'btu' => 9000,
            'voltage' => 230,
            'indoors' => [
                'CH-09MSPHWM-230VI',
                'CH-D09MSPHWM-230VI',
                'CH-09MSPHCT-230VI',
                'CH-M09DTUI',
            ]
        ],
        'CH-12SPH-230VO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'zone' => SolutionZoneEnum::SINGLE,
            'climate_zones' => [
                SolutionClimateZoneEnum::HOT,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'btu' => 12000,
            'voltage' => 230,
            'indoors' => [
                'CH-12MSPHWM-230VI',
                'CH-D12MSPHWM-230VI',
                'CH-12MSPHCT-230VI',
                'CH-M12DTUI',
                'CH-12MSPHMC-230VI',
            ]
        ],
        'CH-18SPH-230VO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'zone' => SolutionZoneEnum::SINGLE,
            'climate_zones' => [
                SolutionClimateZoneEnum::HOT,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'btu' => 18000,
            'voltage' => 230,
            'indoors' => [
                'CH-18MSPHWM-230VI',
                'CH-D18MSPHWM-230VI',
                'CH-18MSPHCT-230VI',
                'CH-M18DTUI',
                'CH-18MSPHFC-230VI',
            ]
        ],
        'CH-24SPH-230VO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'zone' => SolutionZoneEnum::SINGLE,
            'climate_zones' => [
                SolutionClimateZoneEnum::HOT,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'btu' => 24000,
            'voltage' => 230,
            'indoors' => [
                'CH-24MSPHWM-230VI',
                'CH-D24MSPHWM-230VI',
                'CH-24MSPHCT-230VI',
                'CH-M24DTUI',
                'CH-24MSPHFC-230VI',
            ]
        ],
        'CH-30SPH-230VO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'zone' => SolutionZoneEnum::SINGLE,
            'climate_zones' => [
                SolutionClimateZoneEnum::HOT,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'btu' => 30000,
            'voltage' => 230,
            'indoors' => [
                'CH-30SPHWM-230VI',
                'СH-D30SPHWM-230VI',
            ]
        ],
        'CH-36SPH-230VO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'zone' => SolutionZoneEnum::SINGLE,
            'climate_zones' => [
                SolutionClimateZoneEnum::HOT,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'btu' => 36000,
            'voltage' => 230,
            'indoors' => [
                'CH-36SPHWM-230VI',
                'СH-D36SPHWM-230VI',
            ]
        ],
        'CH-HYP09SPH230VO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::HYPER,
            'zone' => SolutionZoneEnum::SINGLE,
            'climate_zones' => [
                SolutionClimateZoneEnum::COLD,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'btu' => 9000,
            'voltage' => 230,
            'indoors' => [
                'CH-09MSPHWM-230VI',
                'CH-D09MSPHWM-230VI',
                'CH-M09DTUI',
            ]
        ],
        'CH-HYP12SPH230VO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::HYPER,
            'zone' => SolutionZoneEnum::SINGLE,
            'climate_zones' => [
                SolutionClimateZoneEnum::COLD,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'btu' => 12000,
            'voltage' => 230,
            'indoors' => [
                'CH-12MSPHWM-230VI',
                'CH-D12MSPHWM-230VI',
                'CH-12MSPHCT-230VI',
                'CH-M12DTUI',
                'CH-12MSPHMC-230VI',
            ]
        ],
        'CH-HYP18SPH230VO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::HYPER,
            'zone' => SolutionZoneEnum::SINGLE,
            'climate_zones' => [
                SolutionClimateZoneEnum::COLD,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'btu' => 18000,
            'voltage' => 230,
            'indoors' => [
                'CH-18MSPHWM-230VI',
                'CH-D18MSPHWM-230VI',
                'CH-18MSPHCT-230VI',
                'CH-M18DTUI',
                'CH-18MSPHFC-230VI',
            ]
        ],
        'CH-HYP24SPH230VO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::HYPER,
            'zone' => SolutionZoneEnum::SINGLE,
            'climate_zones' => [
                SolutionClimateZoneEnum::COLD,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'btu' => 24000,
            'voltage' => 230,
            'indoors' => [
                'CH-24MSPHWM-230VI',
                'CH-D24MSPHWM-230VI',
                'CH-24MSPHCT-230VI',
                'CH-M24DTUI',
                'CH-24MSPHFC-230VI',
            ]
        ],
        'CH-24LCUO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'zone' => SolutionZoneEnum::SINGLE,
            'climate_zones' => [
                SolutionClimateZoneEnum::HOT,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'btu' => 24000,
            'voltage' => 230,
            'indoors' => [
                'CH-24MSPHCT-230VI',
                'CH-M24DTUI',
                'CH-24MSPHFC-230VI',
            ]
        ],
        'CH-36LCUO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'zone' => SolutionZoneEnum::SINGLE,
            'climate_zones' => [
                SolutionClimateZoneEnum::HOT,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'btu' => 36000,
            'voltage' => 230,
            'indoors' => [
                'CH-36LCCT-230VI',
                'CH-36LCDTU/I',
                'CH-36LCFCI',
            ]
        ],
        'CH-HYP36LCUO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::HYPER,
            'zone' => SolutionZoneEnum::SINGLE,
            'climate_zones' => [
                SolutionClimateZoneEnum::COLD,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'btu' => 36000,
            'voltage' => 230,
            'indoors' => [
                'CH-36LCCT-230VI',
                'CH-36LCDTU/I',
                'CH-36LCFCI',
            ]
        ],
        'CH-48LCUO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'zone' => SolutionZoneEnum::SINGLE,
            'climate_zones' => [
                SolutionClimateZoneEnum::HOT,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'btu' => 48000,
            'voltage' => 230,
            'indoors' => [
                'CH-48LCCT-230VI',
                'CH-48LCDTU/I',
                'CH-48LCFCI',
            ]
        ],
        'CH-60LCUO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'zone' => SolutionZoneEnum::SINGLE,
            'climate_zones' => [
                SolutionClimateZoneEnum::HOT,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'btu' => 60000,
            'voltage' => 230,
            'indoors' => [
                'CH-60LCDTU/I',
                'CH-60LCFCI',
            ]
        ],
        'CH-HYP48LCUO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::HYPER,
            'zone' => SolutionZoneEnum::SINGLE,
            'climate_zones' => [
                SolutionClimateZoneEnum::COLD,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'btu' => 48000,
            'voltage' => 230,
            'indoors' => [
                'CH-48LCCT-230VI',
                'CH-48LCDTU/I',
                'CH-48LCFCI',
            ]
        ],
        'CH-18MSPH-230VO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'zone' => SolutionZoneEnum::MULTI,
            'climate_zones' => [
                SolutionClimateZoneEnum::HOT,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'default_schemas' => [
                [
                    'count_zones' => 2,
                    'indoors' => [
                        'CH-09MSPHWM-230VI',
                        'CH-09MSPHWM-230VI'
                    ]
                ]
            ],
            'btu' => 18000,
            'voltage' => 230,
            'indoors' => [
                'CH-09MSPHWM-230VI',
                'CH-12MSPHWM-230VI',
                'CH-D09MSPHWM-230VI',
                'CH-D12MSPHWM-230VI',
                'CH-09MSPHCT-230VI',
                'CH-12MSPHCT-230VI',
                'CH-M09DTUI',
                'CH-M12DTUI',
                'CH-12MSPHMC-230VI',
            ]
        ],
        'CH-HYP19MSPH-230VO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::HYPER,
            'zone' => SolutionZoneEnum::MULTI,
            'climate_zones' => [
                SolutionClimateZoneEnum::COLD,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'default_schemas' => [
                [
                    'count_zones' => 2,
                    'indoors' => [
                        'CH-09MSPHWM-230VI',
                        'CH-09MSPHWM-230VI'
                    ]
                ]
            ],
            'btu' => 19000,
            'voltage' => 230,
            'indoors' => [
                'CH-09MSPHWM-230VI',
                'CH-12MSPHWM-230VI',
                'CH-D09MSPHWM-230VI',
                'CH-D12MSPHWM-230VI',
                'CH-09MSPHCT-230VI',
                'CH-12MSPHCT-230VI',
                'CH-M09DTUI',
                'CH-M12DTUI',
                'CH-12MSPHMC-230VI',
            ]
        ],
        'CH-28MSPH-230VO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'zone' => SolutionZoneEnum::MULTI,
            'climate_zones' => [
                SolutionClimateZoneEnum::HOT,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'default_schemas' => [
                [
                    'count_zones' => 2,
                    'indoors' => [
                        'CH-12MSPHWM-230VI',
                        'CH-12MSPHWM-230VI'
                    ]
                ],
                [
                    'count_zones' => 3,
                    'indoors' => [
                        'CH-09MSPHWM-230VI',
                        'CH-09MSPHWM-230VI',
                        'CH-12MSPHWM-230VI'
                    ]
                ],
            ],
            'btu' => 28000,
            'voltage' => 230,
            'indoors' => [
                'CH-09MSPHWM-230VI',
                'CH-12MSPHWM-230VI',
                'CH-18MSPHWM-230VI',
                'CH-D09MSPHWM-230VI',
                'CH-D12MSPHWM-230VI',
                'CH-D18MSPHWM-230VI',
                'CH-09MSPHCT-230VI',
                'CH-12MSPHCT-230VI',
                'CH-M09DTUI',
                'CH-M12DTUI',
                'CH-M18DTUI',
                'CH-12MSPHMC-230VI',
                'CH-18MSPHFC-230VI',
            ]
        ],
        'CH-HYP28MSPH-230VO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::HYPER,
            'zone' => SolutionZoneEnum::MULTI,
            'climate_zones' => [
                SolutionClimateZoneEnum::COLD,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'default_schemas' => [
                [
                    'count_zones' => 2,
                    'indoors' => [
                        'CH-12MSPHWM-230VI',
                        'CH-12MSPHWM-230VI'
                    ]
                ],
                [
                    'count_zones' => 3,
                    'indoors' => [
                        'CH-09MSPHWM-230VI',
                        'CH-09MSPHWM-230VI',
                        'CH-12MSPHWM-230VI'
                    ]
                ],
            ],
            'btu' => 28000,
            'voltage' => 230,
            'indoors' => [
                'CH-09MSPHWM-230VI',
                'CH-12MSPHWM-230VI',
                'CH-18MSPHWM-230VI',
                'CH-D09MSPHWM-230VI',
                'CH-D12MSPHWM-230VI',
                'CH-D18MSPHWM-230VI',
                'CH-09MSPHCT-230VI',
                'CH-12MSPHCT-230VI',
                'CH-M09DTUI',
                'CH-M12DTUI',
                'CH-M18DTUI',
                'CH-12MSPHMC-230VI',
                'CH-18MSPHFC-230VI',
            ]
        ],
        'CH-36MSPH-230VO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'zone' => SolutionZoneEnum::MULTI,
            'climate_zones' => [
                SolutionClimateZoneEnum::HOT,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'default_schemas' => [
                [
                    'count_zones' => 2,
                    'indoors' => [
                        'CH-12MSPHWM-230VI',
                        'CH-D24MSPHWM-230VI'
                    ]
                ],
                [
                    'count_zones' => 3,
                    'indoors' => [
                        'CH-12MSPHWM-230VI',
                        'CH-12MSPHWM-230VI',
                        'CH-12MSPHWM-230VI',
                    ]
                ],
                [
                    'count_zones' => 4,
                    'indoors' => [
                        'CH-09MSPHWM-230VI',
                        'CH-09MSPHWM-230VI',
                        'CH-12MSPHWM-230VI',
                        'CH-12MSPHWM-230VI',
                    ]
                ],
            ],
            'btu' => 36000,
            'voltage' => 230,
            'indoors' => [
                'CH-09MSPHWM-230VI',
                'CH-12MSPHWM-230VI',
                'CH-18MSPHWM-230VI',
                'CH-24MSPHWM-230VI',
                'CH-D09MSPHWM-230VI',
                'CH-D12MSPHWM-230VI',
                'CH-D18MSPHWM-230VI',
                'CH-D24MSPHWM-230VI',
                'CH-09MSPHCT-230VI',
                'CH-12MSPHCT-230VI',
                'CH-18MSPHCT-230VI',
                'CH-24MSPHCT-230VI',
                'CH-M09DTUI',
                'CH-M12DTUI',
                'CH-M18DTUI',
                'CH-M24DTUI',
                'CH-12MSPHMC-230VI',
                'CH-18MSPHFC-230VI',
                'CH-24MSPHFC-230VI',
            ]
        ],
        'CH-48MSPH-230VO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::SOPHIA,
            'zone' => SolutionZoneEnum::MULTI,
            'climate_zones' => [
                SolutionClimateZoneEnum::HOT,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'default_schemas' => [
                [
                    'count_zones' => 2,
                    'indoors' => [
                        'CH-D24MSPHWM-230VI',
                        'CH-D24MSPHWM-230VI',
                    ]
                ],
                [
                    'count_zones' => 3,
                    'indoors' => [
                        'CH-12MSPHWM-230VI',
                        'CH-12MSPHWM-230VI',
                        'CH-D24MSPHWM-230VI',
                    ]
                ],
                [
                    'count_zones' => 4,
                    'indoors' => [
                        'CH-12MSPHWM-230VI',
                        'CH-12MSPHWM-230VI',
                        'CH-12MSPHWM-230VI',
                        'CH-18MSPHFC-230VI',
                    ]
                ],
                [
                    'count_zones' => 5,
                    'indoors' => [
                        'CH-09MSPHWM-230VI',
                        'CH-09MSPHWM-230VI',
                        'CH-09MSPHWM-230VI',
                        'CH-12MSPHWM-230VI',
                        'CH-12MSPHWM-230VI',
                    ]
                ],
            ],
            'btu' => 48000,
            'voltage' => 230,
            'indoors' => [
                'CH-09MSPHWM-230VI',
                'CH-12MSPHWM-230VI',
                'CH-18MSPHWM-230VI',
                'CH-24MSPHWM-230VI',
                'CH-D09MSPHWM-230VI',
                'CH-D12MSPHWM-230VI',
                'CH-D18MSPHWM-230VI',
                'CH-D24MSPHWM-230VI',
                'CH-09MSPHCT-230VI',
                'CH-12MSPHCT-230VI',
                'CH-18MSPHCT-230VI',
                'CH-24MSPHCT-230VI',
                'CH-M09DTUI',
                'CH-M12DTUI',
                'CH-M18DTUI',
                'CH-M24DTUI',
                'CH-12MSPHMC-230VI',
                'CH-18MSPHFC-230VI',
                'CH-24MSPHFC-230VI',
            ]
        ],
        'CH-HYP36MSPH-230VO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::HYPER,
            'zone' => SolutionZoneEnum::MULTI,
            'climate_zones' => [
                SolutionClimateZoneEnum::COLD,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'default_schemas' => [
                [
                    'count_zones' => 2,
                    'indoors' => [
                        'CH-12MSPHWM-230VI',
                        'CH-D24MSPHWM-230VI'
                    ]
                ],
                [
                    'count_zones' => 3,
                    'indoors' => [
                        'CH-12MSPHWM-230VI',
                        'CH-12MSPHWM-230VI',
                        'CH-12MSPHWM-230VI',
                    ]
                ],
                [
                    'count_zones' => 4,
                    'indoors' => [
                        'CH-09MSPHWM-230VI',
                        'CH-09MSPHWM-230VI',
                        'CH-12MSPHWM-230VI',
                        'CH-12MSPHWM-230VI',
                    ]
                ],
            ],
            'btu' => 36000,
            'voltage' => 230,
            'indoors' => [
                'CH-09MSPHWM-230VI',
                'CH-12MSPHWM-230VI',
                'CH-18MSPHWM-230VI',
                'CH-24MSPHWM-230VI',
                'CH-D09MSPHWM-230VI',
                'CH-D12MSPHWM-230VI',
                'CH-D18MSPHWM-230VI',
                'CH-D24MSPHWM-230VI',
                'CH-09MSPHCT-230VI',
                'CH-12MSPHCT-230VI',
                'CH-18MSPHCT-230VI',
                'CH-24MSPHCT-230VI',
                'CH-M09DTUI',
                'CH-M12DTUI',
                'CH-M18DTUI',
                'CH-M24DTUI',
                'CH-12MSPHMC-230VI',
                'CH-18MSPHFC-230VI',
                'CH-24MSPHFC-230VI',
            ]
        ],
        'CH-HYP48MSPH-230VO' => [
            'type' => SolutionTypeEnum::OUTDOOR,
            'series' => SolutionSeriesEnum::HYPER,
            'zone' => SolutionZoneEnum::MULTI,
            'climate_zones' => [
                SolutionClimateZoneEnum::COLD,
                SolutionClimateZoneEnum::MODERATE,
            ],
            'default_schemas' => [
                [
                    'count_zones' => 2,
                    'indoors' => [
                        'CH-D24MSPHWM-230VI',
                        'CH-D24MSPHWM-230VI',
                    ]
                ],
                [
                    'count_zones' => 3,
                    'indoors' => [
                        'CH-12MSPHWM-230VI',
                        'CH-12MSPHWM-230VI',
                        'CH-D24MSPHWM-230VI',
                    ]
                ],
                [
                    'count_zones' => 4,
                    'indoors' => [
                        'CH-12MSPHWM-230VI',
                        'CH-12MSPHWM-230VI',
                        'CH-12MSPHWM-230VI',
                        'CH-18MSPHFC-230VI',
                    ]
                ],
                [
                    'count_zones' => 5,
                    'indoors' => [
                        'CH-09MSPHWM-230VI',
                        'CH-09MSPHWM-230VI',
                        'CH-09MSPHWM-230VI',
                        'CH-12MSPHWM-230VI',
                        'CH-12MSPHWM-230VI',
                    ]
                ],
            ],
            'btu' => 48000,
            'voltage' => 230,
            'indoors' => [
                'CH-09MSPHWM-230VI',
                'CH-12MSPHWM-230VI',
                'CH-18MSPHWM-230VI',
                'CH-24MSPHWM-230VI',
                'CH-D09MSPHWM-230VI',
                'CH-D12MSPHWM-230VI',
                'CH-D18MSPHWM-230VI',
                'CH-D24MSPHWM-230VI',
                'CH-09MSPHCT-230VI',
                'CH-12MSPHCT-230VI',
                'CH-18MSPHCT-230VI',
                'CH-24MSPHCT-230VI',
                'CH-M09DTUI',
                'CH-M12DTUI',
                'CH-M18DTUI',
                'CH-M24DTUI',
                'CH-12MSPHMC-230VI',
                'CH-18MSPHFC-230VI',
                'CH-24MSPHFC-230VI',
            ]
        ],
    ];

    private array $series;

    private array $lineSets = [];

    private array $indoors = [];

    public function run(): void
    {
        if (isProd() || isTesting()) {
            return;
        }
        $this->removeDemoProducts();
        $this->setSettings();
        $this->setLineSetProducts();
        $this->setIndoorsProducts();
        $this->setOutdoorProducts();
    }

    private function removeDemoProducts(): void
    {
        $products = array_merge(
            self::LINE_SETS,
            array_keys(self::INDOORS),
            array_keys(self::OUTDOORS)
        );

        Product::whereIn('title', $products)
            ->delete();
    }

    private function setSettings(): void
    {
        $this->series = SolutionSeries::all()
            ->mapWithKeys(
                fn(SolutionSeries $series) => [
                    $series->slug => $series->id
                ]
            )
            ->toArray();
    }

    private function setLineSetProducts(): void
    {
        $category = Category::inRandomOrder()
            ->first();

        foreach (self::LINE_SETS as $lineSet) {
            $product = Product::create(
                [
                    'slug' => Str::slug($lineSet),
                    'title' => $lineSet,
                    'title_metaphone' => makeSearchSlug($lineSet),
                    'category_id' => $category->id,
                ]
            );

            $this->setProductTranslates($product);

            Solution::create(
                [
                    'product_id' => $product->id,
                    'type' => SolutionTypeEnum::LINE_SET,
                ]
            );

            $product->refresh();

            $this->lineSets[$lineSet] = $product->solution->id;
        }
    }

    private function setIndoorsProducts(): void
    {
        $category = Category::inRandomOrder()
            ->first();

        foreach (self::INDOORS as $indoorName => $indoorSetting) {
            $product = Product::create(
                [
                    'slug' => Str::slug($indoorName),
                    'title' => $indoorName,
                    'title_metaphone' => makeSearchSlug($indoorName),
                    'category_id' => $category->id,
                ]
            );

            $this->setProductTranslates($product);

            $indoor = Solution::create(
                [
                    'product_id' => $product->id,
                    'type' => SolutionTypeEnum::INDOOR,
                    'series_id' => $this->series[$indoorSetting['series']],
                    'indoor_type' => $indoorSetting['indoor_type'],
                    'btu' => $indoorSetting['btu']
                ]
            );

            $lineSetIds = [];

            foreach ($indoorSetting['line_sets'] as $lineSet) {
                $lineSetIds[] = $this->lineSets[$lineSet];
            }

            $indoor
                ->children()
                ->sync($lineSetIds);

            $indoor
                ->defaultLineSets()
                ->createMany(
                    [
                        [
                            'line_set_id' => $lineSetIds[0],
                            'zone' => SolutionZoneEnum::SINGLE(),
                        ],
                        [
                            'line_set_id' => $lineSetIds[0],
                            'zone' => SolutionZoneEnum::MULTI(),
                        ],
                    ]
                );

            $this->indoors[$indoorName] = $indoor->id;
        }
    }

    private function setOutdoorProducts(): void
    {
        $category = Category::inRandomOrder()
            ->first();

        foreach (self::OUTDOORS as $outdoorName => $outdoorSetting) {
            $product = Product::create(
                [
                    'slug' => Str::slug($outdoorName),
                    'title' => $outdoorName,
                    'title_metaphone' => makeSearchSlug($outdoorName),
                    'category_id' => $category->id,
                ]
            );

            $this->setProductTranslates($product);

            $outdoor = Solution::create(
                [
                    'product_id' => $product->id,
                    'type' => SolutionTypeEnum::OUTDOOR,
                    'series_id' => $this->series[$outdoorSetting['series']],
                    'btu' => $outdoorSetting['btu'],
                    'max_btu_percent' => config('catalog.solutions.btu.max_percent'),
                    'voltage' => $outdoorSetting['voltage'],
                    'zone' => $outdoorSetting['zone'],
                ]
            );

            $indoorIds = [];

            foreach ($outdoorSetting['indoors'] as $indoor) {
                $indoorIds[] = $this->indoors[$indoor];
            }

            $outdoor->children()
                ->sync($indoorIds);

            foreach ($outdoorSetting['climate_zones'] as $zone) {
                $outdoor->climateZones()
                    ->create(
                        [
                            'climate_zone' => $zone
                        ]
                    );
            }

            if (empty($outdoorSetting['default_schemas'])) {
                continue;
            }
            $schemas = [];
            foreach ($outdoorSetting['default_schemas'] as $schema) {
                for ($i = 0; $i < $schema['count_zones']; $i++) {
                    $schemas[] = [
                        'count_zones' => $schema['count_zones'],
                        'zone' => $i + 1,
                        'indoor_id' => $this->indoors[$schema['indoors'][$i]]
                    ];
                }
            }

            $outdoor->schemas()
                ->createMany($schemas);
        }
    }

    private function setProductTranslates(Product $product): void
    {
        $product->translations()
            ->createMany(
                languages()->map(
                    fn(Language $language) => [
                        'language' => $language->slug,
                        'description' => $product->title,
                    ]
                )
            );
    }
}
