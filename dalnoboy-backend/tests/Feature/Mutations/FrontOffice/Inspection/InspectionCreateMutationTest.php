<?php


namespace Tests\Feature\Mutations\FrontOffice\Inspection;


use App\Enums\Inspections\InspectionModerationEntityEnum;
use App\Enums\Inspections\InspectionModerationFieldEnum;
use App\Enums\Inspections\TirePhotoType;
use App\GraphQL\Mutations\FrontOffice\Inspections\InspectionCreateMutation;
use App\Models\Dictionaries\InspectionReason;
use App\Models\Dictionaries\Problem;
use App\Models\Dictionaries\Recommendation;
use App\Models\Drivers\Driver;
use App\Models\Inspections\Inspection;
use App\Models\Inspections\InspectionTire;
use App\Models\Media\Media;
use App\Models\Tires\Tire;
use App\Models\Users\User;
use App\Models\Vehicles\Schemas\SchemaWheel;
use App\Models\Vehicles\Vehicle;
use Carbon\Carbon;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InspectionCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    private User $inspector;

    public function setUp(): void
    {
        parent::setUp();

        $this->inspector = $this->loginAsUserWithRole();
    }

    public function test_create_inspection(): void
    {
        Storage::fake(config('media-library.disk_name'));

        $vehicle = Vehicle::factory()
            ->create();

        $createdAt = Carbon::now()->addHours(-1)
            ->getTimestamp();

        Recommendation::factory()
            ->hasAttached(
                Problem::factory()
                    ->count(10),
                relationship: 'problems'
            )
            ->count(20)
            ->create();

        $inspection = [
            'vehicle_id' => $vehicle->id,
            'driver_id' => Driver::factory()
                ->create()->id,
            'inspection_reason_id' => InspectionReason::factory()
                ->create()->id,
            'unable_to_sign' => false,
            'odo' => $vehicle->odo + 10000,
            'time' => $createdAt,
            'photos' => [
                Inspection::MC_VEHICLE => File::image(Inspection::MC_VEHICLE . '.jpeg'),
                Inspection::MC_STATE_NUMBER => File::image(Inspection::MC_STATE_NUMBER . '.jpeg'),
                Inspection::MC_DATA_SHEET_1 => File::image(Inspection::MC_DATA_SHEET_1 . '.jpeg'),
                Inspection::MC_DATA_SHEET_2 => File::image(Inspection::MC_DATA_SHEET_2 . '.jpeg'),
                Inspection::MC_ODO => File::image(Inspection::MC_ODO . '.jpeg'),
                Inspection::MC_SIGN => File::image(Inspection::MC_SIGN . '.jpeg'),
            ],
            'tires' => $vehicle
                ->schemaVehicle
                ->wheels
                ->map(
                    function (SchemaWheel $wheel)
                    {
                        $recommendations = Recommendation::inRandomOrder()
                            ->limit(5)
                            ->get();
                        $problems = [];

                        foreach ($recommendations as $recommendation) {
                            $problems = array_merge(
                                $problems,
                                $recommendation->problems()
                                    ->inRandomOrder()
                                    ->limit(2)
                                    ->get()
                                    ->pluck('id')
                                    ->toArray()
                            );
                        }

                        $problems = array_values(array_unique($problems));

                        return [
                            'tire_id' => Tire::factory()
                                ->create()->id,
                            'schema_wheel_id' => $wheel->id,
                            'ogp' => 6,
                            'pressure' => 2.2,
                            'problems' => $problems,
                            'recommendations' => $recommendations
                                ->map(
                                    fn(Recommendation $recommendation) => [
                                        'recommendation_id' => $recommendation->id,
                                        'is_confirmed' => true
                                    ]
                                )
                                ->values()
                                ->toArray(),
                            'comment' => 'test comment',
                            'photos' => [
                                [
                                    'type' => TirePhotoType::MAIN(),
                                    'file_as_base_64' => 'z/Do4uXyIQ==',
                                    'file_name' => TirePhotoType::MAIN,
                                    'file_ext' => 'jpeg',
                                ],
                                [
                                    'type' => TirePhotoType::SERIAL_NUMBER(),
                                    'file_as_base_64' => 'z/Do4uXyIQ==',
                                    'file_name' => TirePhotoType::SERIAL_NUMBER,
                                    'file_ext' => 'jpeg',
                                ]
                            ]
                        ];
                    }
                )
                ->values()
                ->toArray()
        ];

        $res = $this->postGraphQlUpload(
            GraphQLQuery::upload(InspectionCreateMutation::NAME)
                ->args(
                    [
                        'inspection' => $inspection
                    ]
                )
                ->select(
                    [
                        'id',
                        'created_at',
                        'inspector' => [
                            'id',
                        ],
                        'branch' => [
                            'id'
                        ],
                        'vehicle' => [
                            'id',
                        ],
                        'is_moderated',
                        'unable_to_sign',
                        'driver' => [
                            'id'
                        ],
                        'inspection_reason' => [
                            'id',
                        ],
                        'inspection_reason_description',
                        'photos' => [
                            Inspection::MC_STATE_NUMBER => [
                                'id',
                                'url',
                                'name',
                                'file_name'
                            ],
                            Inspection::MC_VEHICLE => [
                                'id',
                                'url',
                                'name',
                                'file_name'
                            ],
                            Inspection::MC_DATA_SHEET_1 => [
                                'id',
                                'url',
                                'name',
                                'file_name'
                            ],
                            Inspection::MC_DATA_SHEET_2 => [
                                'id',
                                'url',
                                'name',
                                'file_name'
                            ],
                            Inspection::MC_ODO => [
                                'id',
                                'url',
                                'name',
                                'file_name'
                            ],
                            Inspection::MC_SIGN => [
                                'id',
                                'url',
                                'name',
                                'file_name'
                            ],
                        ],
                        'tires' => [
                            'tire' => [
                                'id',
                            ],
                            'schema_wheel' => [
                                'id',
                            ],
                            'ogp',
                            'pressure',
                            'comment',
                            'no_problems',
                            'problems' => [
                                'id',
                            ],
                            'recommendations' => [
                                'id',
                                'is_confirmed',
                                'new_tire' => [
                                    'id'
                                ]
                            ],
                            'photos' => [
                                TirePhotoType::MAIN => [
                                    'id',
                                    'url',
                                    'name',
                                    'file_name'
                                ],
                                TirePhotoType::SERIAL_NUMBER => [
                                    'id',
                                    'url',
                                    'name',
                                    'file_name'
                                ],
                            ],
                        ],
                        'moderation_fields' => [
                            'field',
                            'message',
                        ]
                    ]
                )
                ->make()
        )
//            ->dump()
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        InspectionCreateMutation::NAME => [
                            'id',
                            'created_at',
                            'inspector' => [
                                'id',
                            ],
                            'branch' => [
                                'id'
                            ],
                            'vehicle' => [
                                'id',
                            ],
                            'is_moderated',
                            'inspection_reason' => [
                                'id',
                            ],
                            'inspection_reason_description',
                            'photos' => [
                                Inspection::MC_STATE_NUMBER => [
                                    'id',
                                    'url',
                                    'name',
                                    'file_name'
                                ],
                                Inspection::MC_VEHICLE => [
                                    'id',
                                    'url',
                                    'name',
                                    'file_name'
                                ],
                                Inspection::MC_DATA_SHEET_1 => [
                                    'id',
                                    'url',
                                    'name',
                                    'file_name'
                                ],
                                Inspection::MC_DATA_SHEET_2 => [
                                    'id',
                                    'url',
                                    'name',
                                    'file_name'
                                ],
                                Inspection::MC_ODO => [
                                    'id',
                                    'url',
                                    'name',
                                    'file_name'
                                ],
                                Inspection::MC_SIGN => [
                                    'id',
                                    'url',
                                    'name',
                                    'file_name'
                                ],
                            ],
                            'tires' => [
                                '*' => [
                                    'tire' => [
                                        'id',
                                    ],
                                    'schema_wheel' => [
                                        'id',
                                    ],
                                    'ogp',
                                    'pressure',
                                    'comment',
                                    'no_problems',
                                    'problems' => [
                                        '*' => [
                                            'id'
                                        ]
                                    ],
                                    'recommendations' => [
                                        '*' => [
                                            'id',
                                            'is_confirmed',
                                            'new_tire' => [
                                                'id'
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            'moderation_fields'
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        InspectionCreateMutation::NAME => [
                            'created_at' => $createdAt,
                            'inspector' => [
                                'id' => $this->inspector->id,
                            ],
                            'branch' => [
                                'id' => $this->inspector->branch->id,
                            ],
                            'vehicle' => [
                                'id' => $vehicle->id,
                            ],
                            'is_moderated' => true,
                            'inspection_reason' => [
                                'id' => $inspection['inspection_reason_id'],
                            ],
                            'inspection_reason_description' => null,
                            'photos' => [
                                Inspection::MC_STATE_NUMBER => [
                                    'name' => Inspection::MC_STATE_NUMBER,
                                    'file_name' => Inspection::MC_STATE_NUMBER . '.jpeg'
                                ],
                                Inspection::MC_VEHICLE => [
                                    'name' => Inspection::MC_VEHICLE,
                                    'file_name' => Inspection::MC_VEHICLE . '.jpeg'
                                ],
                                Inspection::MC_DATA_SHEET_1 => [
                                    'name' => Inspection::MC_DATA_SHEET_1,
                                    'file_name' => Inspection::MC_DATA_SHEET_1 . '.jpeg'
                                ],
                                Inspection::MC_DATA_SHEET_2 => [
                                    'name' => Inspection::MC_DATA_SHEET_2,
                                    'file_name' => Inspection::MC_DATA_SHEET_2 . '.jpeg'
                                ],
                                Inspection::MC_ODO => [
                                    'name' => Inspection::MC_ODO,
                                    'file_name' => Inspection::MC_ODO . '.jpeg'
                                ],
                                Inspection::MC_SIGN => [
                                    'name' => Inspection::MC_SIGN,
                                    'file_name' => Inspection::MC_SIGN . '.jpeg'
                                ],
                            ],
                            'tires' => array_map(
                                fn(array $tire) => [
                                    'tire' => [
                                        'id' => $tire['tire_id'],
                                    ],
                                    'schema_wheel' => [
                                        'id' => $tire['schema_wheel_id'],
                                    ],
                                    'ogp' => $tire['ogp'],
                                    'pressure' => $tire['pressure'],
                                    'comment' => $tire['comment'] ?? null,
                                    'no_problems' => false,
                                    'problems' => array_map(
                                        fn(int $id) => [
                                            'id' => $id,
                                        ],
                                        $tire['problems']
                                    ),
                                    'recommendations' => array_map(
                                        fn(array $recommendation) => [
                                            'id' => $recommendation['recommendation_id'],
                                            'is_confirmed' => $recommendation['is_confirmed'],
                                            'new_tire' => null
                                        ],
                                        $tire['recommendations']
                                    )
                                ],
                                $inspection['tires']
                            ),
                            'moderation_fields' => null
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            Vehicle::class,
            [
                'id' => $vehicle->id,
                'odo' => $inspection['odo']
            ]
        );

        $this->assertDatabaseHas(
            Media::class,
            [
                'model_type' => Vehicle::class,
                'model_id' => $vehicle->id,
                'collection_name' => Vehicle::MC_VEHICLE
            ]
        );

        $this->assertDatabaseHas(
            Media::class,
            [
                'model_type' => Vehicle::class,
                'model_id' => $vehicle->id,
                'collection_name' => Vehicle::MC_STATE_NUMBER
            ]
        );

        $inspection =  Inspection::find($res->json('data.'.InspectionCreateMutation::NAME. '.id'));

        $this->assertEquals(
            $inspection->inspectionTires->first()->getFirstMedia(TirePhotoType::MAIN)->getFullUrl(),
            env('APP_URL') . "/storage/{$res->json('data.' . InspectionCreateMutation::NAME . '.tires.0.photos.main.id')}/main.jpeg"
        );
        $this->assertEquals(
            $inspection->inspectionTires->first()->getFirstMedia(TirePhotoType::SERIAL_NUMBER)->getFullUrl(),
            env('APP_URL') . "/storage/{$res->json('data.' . InspectionCreateMutation::NAME . '.tires.0.photos.serial_number.id')}/serial_number.jpeg"
        );
    }

    public function test_create_inspection_only_one_tire(): void
    {
        Storage::fake(config('media-library.disk_name'));

        $vehicle = Vehicle::factory()
            ->create();

        $createdAt = Carbon::now()->addHours(-1)
            ->getTimestamp();

        Recommendation::factory()
            ->hasAttached(
                Problem::factory()
                    ->count(10),
                relationship: 'problems'
            )
            ->count(20)
            ->create();

        $inspection = [
            'vehicle_id' => $vehicle->id,
            'driver_id' => Driver::factory()
                ->create()->id,
            'inspection_reason_id' => InspectionReason::factory()
                ->create()->id,
            'unable_to_sign' => false,
            'odo' => $vehicle->odo + 10000,
            'time' => $createdAt,
            'photos' => [
                Inspection::MC_VEHICLE => File::image(Inspection::MC_VEHICLE . '.jpeg'),
                Inspection::MC_STATE_NUMBER => File::image(Inspection::MC_STATE_NUMBER . '.jpeg'),
                Inspection::MC_DATA_SHEET_1 => File::image(Inspection::MC_DATA_SHEET_1 . '.jpeg'),
                Inspection::MC_DATA_SHEET_2 => File::image(Inspection::MC_DATA_SHEET_2 . '.jpeg'),
                Inspection::MC_ODO => File::image(Inspection::MC_ODO . '.jpeg'),
                Inspection::MC_SIGN => File::image(Inspection::MC_SIGN . '.jpeg'),
            ],
            'tires' => [
                [
                    'tire_id' => Tire::factory()->create()->id,
                    'schema_wheel_id' => $vehicle->schemaVehicle->wheels->first()->id,
                    'ogp' => 6,
                    'pressure' => 2.2,
                    'comment' => 'test comment',
                ]
            ],
        ];

        $this->postGraphQlUpload(
            GraphQLQuery::upload(InspectionCreateMutation::NAME)
                ->args(
                    [
                        'inspection' => $inspection
                    ]
                )
                ->select(
                    [
                        'id',
                        'tires' => [
                            'tire' => [
                                'id',
                            ],
                            'schema_wheel' => [
                                'id',
                            ],
                            'ogp',
                            'pressure',
                            'comment',
                            'no_problems',
                        ],
                        'moderation_fields' => [
                            'field',
                            'message',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonCount(1, 'data.'.InspectionCreateMutation::NAME.'.tires');
    }

    public function test_create_inspection_same_tires(): void
    {
        Storage::fake(config('media-library.disk_name'));

        $vehicle = Vehicle::factory()
            ->create();

        $createdAt = Carbon::now()->addHours(-1)
            ->getTimestamp();

        Recommendation::factory()
            ->hasAttached(
                Problem::factory()
                    ->count(10),
                relationship: 'problems'
            )
            ->count(20)
            ->create();

        $tire = Tire::factory()->create();

        $inspection = [
            'vehicle_id' => $vehicle->id,
            'driver_id' => Driver::factory()
                ->create()->id,
            'inspection_reason_id' => InspectionReason::factory()
                ->create()->id,
            'unable_to_sign' => false,
            'odo' => $vehicle->odo + 10000,
            'time' => $createdAt,
            'photos' => [
                Inspection::MC_VEHICLE => File::image(Inspection::MC_VEHICLE . '.jpeg'),
                Inspection::MC_STATE_NUMBER => File::image(Inspection::MC_STATE_NUMBER . '.jpeg'),
                Inspection::MC_DATA_SHEET_1 => File::image(Inspection::MC_DATA_SHEET_1 . '.jpeg'),
                Inspection::MC_DATA_SHEET_2 => File::image(Inspection::MC_DATA_SHEET_2 . '.jpeg'),
                Inspection::MC_ODO => File::image(Inspection::MC_ODO . '.jpeg'),
                Inspection::MC_SIGN => File::image(Inspection::MC_SIGN . '.jpeg'),
            ],
            'tires' => [
                [
                    'tire_id' => $tire->id,
                    'schema_wheel_id' => $vehicle->schemaVehicle->wheels->first()->id,
                    'ogp' => 1,
                    'pressure' => 2.2,
                    'comment' => 'test comment',
                ],
                [
                    'tire_id' => $tire->id,
                    'schema_wheel_id' => $vehicle->schemaVehicle->wheels->first()->id,
                    'ogp' => 1,
                    'pressure' => 2.2,
                    'comment' => 'test comment',
                ]
            ],
        ];

        $this->postGraphQlUpload(
            GraphQLQuery::upload(InspectionCreateMutation::NAME)
                ->args(
                    [
                        'inspection' => $inspection
                    ]
                )
                ->select(
                    [
                        'id',
                        'tires' => [
                            'tire' => [
                                'id',
                            ],
                            'schema_wheel' => [
                                'id',
                            ],
                            'ogp',
                            'pressure',
                            'comment',
                            'no_problems',
                        ],
                        'moderation_fields' => [
                            'field',
                            'message',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson([
                'errors' => [
                    [
                        'extensions' => [
                            'validation' => [
                                'inspection.tires.0.tire_id' => [trans('inspections.validation_messages.tire.same_tire')],
                                'inspection.tires.1.tire_id' => [trans('inspections.validation_messages.tire.same_tire')]
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function test_create_inspection_without_problems(): void
    {
        Storage::fake(config('media-library.disk_name'));

        $vehicle = Vehicle::factory()
            ->create();

        $inspection = [
            'vehicle_id' => $vehicle->id,
            'driver_id' => Driver::factory()
                ->create()->id,
            'inspection_reason_id' => InspectionReason::factory()
                ->create()->id,
            'unable_to_sign' => false,
            'odo' => $vehicle->odo + 10000,
            'time' => Carbon::now()
                ->getTimestamp(),
            'photos' => [
                Inspection::MC_VEHICLE => File::image(Inspection::MC_VEHICLE . '.jpeg'),
                Inspection::MC_STATE_NUMBER => File::image(Inspection::MC_STATE_NUMBER . '.jpeg'),
                Inspection::MC_DATA_SHEET_1 => File::image(Inspection::MC_DATA_SHEET_1 . '.jpeg'),
                Inspection::MC_DATA_SHEET_2 => File::image(Inspection::MC_DATA_SHEET_2 . '.jpeg'),
                Inspection::MC_ODO => File::image(Inspection::MC_ODO . '.jpeg'),
                Inspection::MC_SIGN => File::image(Inspection::MC_SIGN . '.jpeg'),
            ],
            'tires' => $vehicle
                ->schemaVehicle
                ->wheels
                ->map(
                    function (SchemaWheel $wheel)
                    {
                        return [
                            'tire_id' => Tire::factory()
                                ->create()->id,
                            'schema_wheel_id' => $wheel->id,
                            'ogp' => 6,
                            'pressure' => 2.2,
                        ];
                    }
                )
                ->values()
                ->toArray()
        ];

        $this->postGraphQlUpload(
            GraphQLQuery::upload(InspectionCreateMutation::NAME)
                ->args(
                    [
                        'inspection' => $inspection
                    ]
                )
                ->select(
                    [
                        'id',
                        'inspector' => [
                            'id',
                        ],
                        'vehicle' => [
                            'id',
                        ],
                        'is_moderated',
                        'unable_to_sign',
                        'driver' => [
                            'id'
                        ],
                        'inspection_reason' => [
                            'id',
                        ],
                        'inspection_reason_description',
                        'photos' => [
                            Inspection::MC_STATE_NUMBER => [
                                'id',
                                'url',
                                'name',
                                'file_name'
                            ],
                            Inspection::MC_VEHICLE => [
                                'id',
                                'url',
                                'name',
                                'file_name'
                            ],
                            Inspection::MC_DATA_SHEET_1 => [
                                'id',
                                'url',
                                'name',
                                'file_name'
                            ],
                            Inspection::MC_DATA_SHEET_2 => [
                                'id',
                                'url',
                                'name',
                                'file_name'
                            ],
                            Inspection::MC_ODO => [
                                'id',
                                'url',
                                'name',
                                'file_name'
                            ],
                            Inspection::MC_SIGN => [
                                'id',
                                'url',
                                'name',
                                'file_name'
                            ],
                        ],
                        'tires' => [
                            'tire' => [
                                'id',
                            ],
                            'schema_wheel' => [
                                'id',
                            ],
                            'ogp',
                            'pressure',
                            'comment',
                            'no_problems',
                            'problems' => [
                                'id',
                            ],
                            'recommendations' => [
                                'id',
                                'is_confirmed',
                                'new_tire' => [
                                    'id'
                                ]
                            ]
                        ],
                        'moderation_fields' => [
                            'field',
                            'message',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        InspectionCreateMutation::NAME => [
                            'id',
                            'inspector' => [
                                'id',
                            ],
                            'vehicle' => [
                                'id',
                            ],
                            'is_moderated',
                            'inspection_reason' => [
                                'id',
                            ],
                            'inspection_reason_description',
                            'photos' => [
                                Inspection::MC_STATE_NUMBER => [
                                    'id',
                                    'url',
                                    'name',
                                    'file_name'
                                ],
                                Inspection::MC_VEHICLE => [
                                    'id',
                                    'url',
                                    'name',
                                    'file_name'
                                ],
                                Inspection::MC_DATA_SHEET_1 => [
                                    'id',
                                    'url',
                                    'name',
                                    'file_name'
                                ],
                                Inspection::MC_DATA_SHEET_2 => [
                                    'id',
                                    'url',
                                    'name',
                                    'file_name'
                                ],
                                Inspection::MC_ODO => [
                                    'id',
                                    'url',
                                    'name',
                                    'file_name'
                                ],
                                Inspection::MC_SIGN => [
                                    'id',
                                    'url',
                                    'name',
                                    'file_name'
                                ],
                            ],
                            'tires' => [
                                '*' => [
                                    'tire' => [
                                        'id',
                                    ],
                                    'schema_wheel' => [
                                        'id',
                                    ],
                                    'ogp',
                                    'pressure',
                                    'comment',
                                    'no_problems',
                                    'problems',
                                    'recommendations'
                                ]
                            ],
                            'moderation_fields'
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        InspectionCreateMutation::NAME => [
                            'inspector' => [
                                'id' => $this->inspector->id,
                            ],
                            'vehicle' => [
                                'id' => $vehicle->id,
                            ],
                            'is_moderated' => true,
                            'inspection_reason' => [
                                'id' => $inspection['inspection_reason_id'],
                            ],
                            'inspection_reason_description' => null,
                            'photos' => [
                                Inspection::MC_STATE_NUMBER => [
                                    'name' => Inspection::MC_STATE_NUMBER,
                                    'file_name' => Inspection::MC_STATE_NUMBER . '.jpeg'
                                ],
                                Inspection::MC_VEHICLE => [
                                    'name' => Inspection::MC_VEHICLE,
                                    'file_name' => Inspection::MC_VEHICLE . '.jpeg'
                                ],
                                Inspection::MC_DATA_SHEET_1 => [
                                    'name' => Inspection::MC_DATA_SHEET_1,
                                    'file_name' => Inspection::MC_DATA_SHEET_1 . '.jpeg'
                                ],
                                Inspection::MC_DATA_SHEET_2 => [
                                    'name' => Inspection::MC_DATA_SHEET_2,
                                    'file_name' => Inspection::MC_DATA_SHEET_2 . '.jpeg'
                                ],
                                Inspection::MC_ODO => [
                                    'name' => Inspection::MC_ODO,
                                    'file_name' => Inspection::MC_ODO . '.jpeg'
                                ],
                                Inspection::MC_SIGN => [
                                    'name' => Inspection::MC_SIGN,
                                    'file_name' => Inspection::MC_SIGN . '.jpeg'
                                ],
                            ],
                            'tires' => array_map(
                                fn(array $tire) => [
                                    'tire' => [
                                        'id' => $tire['tire_id'],
                                    ],
                                    'schema_wheel' => [
                                        'id' => $tire['schema_wheel_id'],
                                    ],
                                    'ogp' => $tire['ogp'],
                                    'pressure' => $tire['pressure'],
                                    'comment' => $tire['comment'] ?? null,
                                    'no_problems' => true,
                                    'problems' => null,
                                    'recommendations' => null
                                ],
                                $inspection['tires']
                            ),
                            'moderation_fields' => null
                        ]
                    ]
                ]
            );
    }

    public function test_try_to_create_inspection_without_signature(): void
    {
        Storage::fake(config('media-library.disk_name'));

        $vehicle = Vehicle::factory()
            ->create();

        $inspection = [
            'vehicle_id' => $vehicle->id,
            'driver_id' => Driver::factory()
                ->create()->id,
            'inspection_reason_id' => InspectionReason::factory()
                ->create()->id,
            'unable_to_sign' => false,
            'odo' => $vehicle->odo + 10000,
            'time' => Carbon::now()
                ->getTimestamp(),
            'photos' => [
                Inspection::MC_VEHICLE => File::image(Inspection::MC_VEHICLE . '.jpeg'),
                Inspection::MC_STATE_NUMBER => File::image(Inspection::MC_STATE_NUMBER . '.jpeg'),
                Inspection::MC_DATA_SHEET_1 => File::image(Inspection::MC_DATA_SHEET_1 . '.jpeg'),
                Inspection::MC_DATA_SHEET_2 => File::image(Inspection::MC_DATA_SHEET_2 . '.jpeg'),
                Inspection::MC_ODO => File::image(Inspection::MC_ODO . '.jpeg'),
            ],
            'tires' => $vehicle
                ->schemaVehicle
                ->wheels
                ->map(
                    function (SchemaWheel $wheel)
                    {
                        return [
                            'tire_id' => Tire::factory()
                                ->create()->id,
                            'schema_wheel_id' => $wheel->id,
                            'ogp' => 6,
                            'pressure' => 2.2,
                        ];
                    }
                )
                ->values()
                ->toArray()
        ];

        $this->postGraphQlUpload(
            GraphQLQuery::upload(InspectionCreateMutation::NAME)
                ->args(
                    [
                        'inspection' => $inspection
                    ]
                )
                ->select(
                    [
                        'id'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('inspections.validation_messages.photos.sign.is_required')
                        ]
                    ]
                ]
            );
    }

    public function test_create_inspection_without_signature_is_offline(): void
    {
        Storage::fake(config('media-library.disk_name'));

        $vehicle = Vehicle::factory()
            ->create();

        $inspection = [
            'vehicle_id' => $vehicle->id,
            'driver_id' => Driver::factory()
                ->create()->id,
            'inspection_reason_id' => InspectionReason::factory()
                ->create()->id,
            'unable_to_sign' => false,
            'odo' => $vehicle->odo + 10000,
            'time' => Carbon::now()
                ->getTimestamp(),
            'is_offline' => true,
            'photos' => [
                Inspection::MC_VEHICLE => File::image(Inspection::MC_VEHICLE . '.jpeg'),
                Inspection::MC_STATE_NUMBER => File::image(Inspection::MC_STATE_NUMBER . '.jpeg'),
                Inspection::MC_DATA_SHEET_1 => File::image(Inspection::MC_DATA_SHEET_1 . '.jpeg'),
                Inspection::MC_DATA_SHEET_2 => File::image(Inspection::MC_DATA_SHEET_2 . '.jpeg'),
                Inspection::MC_ODO => File::image(Inspection::MC_ODO . '.jpeg'),
            ],
            'tires' => $vehicle
                ->schemaVehicle
                ->wheels
                ->map(
                    function (SchemaWheel $wheel)
                    {
                        return [
                            'tire_id' => Tire::factory()
                                ->create()->id,
                            'schema_wheel_id' => $wheel->id,
                            'ogp' => 6,
                            'pressure' => 2.2,
                        ];
                    }
                )
                ->values()
                ->toArray()
        ];

        $this->postGraphQlUpload(
            GraphQLQuery::upload(InspectionCreateMutation::NAME)
                ->args(
                    [
                        'inspection' => $inspection
                    ]
                )
                ->select(
                    [
                        'id',
                        'is_moderated',
                        'moderation_fields' => [
                            'entity',
                            'field',
                            'message',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        InspectionCreateMutation::NAME => [
                            'id',
                            'is_moderated',
                            'moderation_fields' => [
                                '*' => [
                                    'entity',
                                    'field',
                                    'message',
                                ]
                            ],
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        InspectionCreateMutation::NAME => [
                            'is_moderated' => false,
                            'moderation_fields' => [
                                [
                                    'entity' => InspectionModerationEntityEnum::VEHICLE,
                                    'field' => InspectionModerationFieldEnum::PHOTO_SIGN,
                                    'message' => trans('inspections.validation_messages.photos.sign.is_required')
                                ]
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_try_to_create_inspection_with_incorrect_odo(): void
    {
        Storage::fake(config('media-library.disk_name'));

        $vehicle = Vehicle::factory()
            ->create();

        $inspection = [
            'vehicle_id' => $vehicle->id,
            'driver_id' => Driver::factory()
                ->create()->id,
            'inspection_reason_id' => InspectionReason::factory()
                ->create()->id,
            'unable_to_sign' => false,
            'odo' => $vehicle->odo - 10000,
            'time' => Carbon::now()
                ->getTimestamp(),
            'is_offline' => false,
            'photos' => [
                Inspection::MC_VEHICLE => File::image(Inspection::MC_VEHICLE . '.jpeg'),
                Inspection::MC_STATE_NUMBER => File::image(Inspection::MC_STATE_NUMBER . '.jpeg'),
                Inspection::MC_DATA_SHEET_1 => File::image(Inspection::MC_DATA_SHEET_1 . '.jpeg'),
                Inspection::MC_DATA_SHEET_2 => File::image(Inspection::MC_DATA_SHEET_2 . '.jpeg'),
                Inspection::MC_ODO => File::image(Inspection::MC_ODO . '.jpeg'),
            ],
            'tires' => $vehicle
                ->schemaVehicle
                ->wheels
                ->map(
                    function (SchemaWheel $wheel)
                    {
                        return [
                            'tire_id' => Tire::factory()
                                ->create()->id,
                            'schema_wheel_id' => $wheel->id,
                            'ogp' => 6,
                            'pressure' => 2.2,
                        ];
                    }
                )
                ->values()
                ->toArray()
        ];

        $this->postGraphQlUpload(
            GraphQLQuery::upload(InspectionCreateMutation::NAME)
                ->args(
                    [
                        'inspection' => $inspection
                    ]
                )
                ->select(
                    [
                        'id',
                        'is_moderated',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('inspections.validation_messages.photos.sign.is_required')
                        ]
                    ]
                ]
            );
    }

    public function test_create_inspection_with_incorrect_odo_is_offline(): void
    {
        Storage::fake(config('media-library.disk_name'));

        $vehicle = Vehicle::factory()
            ->create();

        $inspection = [
            'vehicle_id' => $vehicle->id,
            'driver_id' => Driver::factory()
                ->create()->id,
            'inspection_reason_id' => InspectionReason::factory()
                ->create()->id,
            'unable_to_sign' => false,
            'odo' => $vehicle->odo - 10000,
            'time' => Carbon::now()
                ->getTimestamp(),
            'is_offline' => true,
            'photos' => [
                Inspection::MC_VEHICLE => File::image(Inspection::MC_VEHICLE . '.jpeg'),
                Inspection::MC_STATE_NUMBER => File::image(Inspection::MC_STATE_NUMBER . '.jpeg'),
                Inspection::MC_DATA_SHEET_1 => File::image(Inspection::MC_DATA_SHEET_1 . '.jpeg'),
                Inspection::MC_DATA_SHEET_2 => File::image(Inspection::MC_DATA_SHEET_2 . '.jpeg'),
                Inspection::MC_ODO => File::image(Inspection::MC_ODO . '.jpeg'),
            ],
            'tires' => $vehicle
                ->schemaVehicle
                ->wheels
                ->map(
                    function (SchemaWheel $wheel)
                    {
                        return [
                            'tire_id' => Tire::factory()
                                ->create()->id,
                            'schema_wheel_id' => $wheel->id,
                            'ogp' => 6,
                            'pressure' => 2.2,
                        ];
                    }
                )
                ->values()
                ->toArray()
        ];

        $this->postGraphQlUpload(
            GraphQLQuery::upload(InspectionCreateMutation::NAME)
                ->args(
                    [
                        'inspection' => $inspection
                    ]
                )
                ->select(
                    [
                        'id',
                        'is_moderated',
                        'moderation_fields' => [
                            'entity',
                            'field',
                            'message',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        InspectionCreateMutation::NAME => [
                            'id',
                            'is_moderated',
                            'moderation_fields' => [
                                '*' => [
                                    'entity',
                                    'field',
                                    'message',
                                ]
                            ],
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        InspectionCreateMutation::NAME => [
                            'is_moderated' => false,
                            'moderation_fields' => [
                                [
                                    'entity' => InspectionModerationEntityEnum::VEHICLE,
                                    'field' => InspectionModerationFieldEnum::ODO,
                                    'message' => trans('inspections.validation_messages.odo.too_small')
                                ]
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_create_inspection_for_trailer(): void
    {
        Storage::fake(config('media-library.disk_name'));

        $vehicle = Vehicle::factory()
            ->trailer()
            ->create();

        $inspection = [
            'vehicle_id' => $vehicle->id,
            'driver_id' => Driver::factory()
                ->create()->id,
            'inspection_reason_id' => InspectionReason::factory()
                ->create()->id,
            'unable_to_sign' => false,
            'odo' => $vehicle->odo - 10000,
            'time' => Carbon::now()
                ->getTimestamp(),
            'photos' => [
                Inspection::MC_VEHICLE => File::image(Inspection::MC_VEHICLE . '.jpeg'),
                Inspection::MC_STATE_NUMBER => File::image(Inspection::MC_STATE_NUMBER . '.jpeg'),
                Inspection::MC_DATA_SHEET_1 => File::image(Inspection::MC_DATA_SHEET_1 . '.jpeg'),
                Inspection::MC_DATA_SHEET_2 => File::image(Inspection::MC_DATA_SHEET_2 . '.jpeg'),
                Inspection::MC_SIGN => File::image(Inspection::MC_SIGN . '.jpeg'),
            ],
            'tires' => $vehicle
                ->schemaVehicle
                ->wheels
                ->map(
                    function (SchemaWheel $wheel)
                    {
                        return [
                            'tire_id' => Tire::factory()
                                ->create()->id,
                            'schema_wheel_id' => $wheel->id,
                            'ogp' => 6,
                            'pressure' => 2.2,
                        ];
                    }
                )
                ->values()
                ->toArray()
        ];

        $this->postGraphQlUpload(
            GraphQLQuery::upload(InspectionCreateMutation::NAME)
                ->args(
                    [
                        'inspection' => $inspection
                    ]
                )
                ->select(
                    [
                        'id',
                        'is_moderated',
                        'moderation_fields' => [
                            'field',
                            'message',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        InspectionCreateMutation::NAME => [
                            'id',
                            'is_moderated',
                            'moderation_fields'
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        InspectionCreateMutation::NAME => [
                            'is_moderated' => true,
                            'moderation_fields' => null
                        ]
                    ]
                ]
            );
    }

    public function test_create_inspection_with_incorrect_ogp(): void
    {
        Storage::fake(config('media-library.disk_name'));

        $vehicle = Vehicle::factory()
            ->trailer()
            ->create();

        $inspection = [
            'vehicle_id' => $vehicle->id,
            'driver_id' => Driver::factory()
                ->create()->id,
            'inspection_reason_id' => InspectionReason::factory()
                ->create()->id,
            'unable_to_sign' => false,
            'is_offline' => true,
            'odo' => $vehicle->odo - 10000,
            'time' => Carbon::now()
                ->getTimestamp(),
            'photos' => [
                Inspection::MC_VEHICLE => File::image(Inspection::MC_VEHICLE . '.jpeg'),
                Inspection::MC_STATE_NUMBER => File::image(Inspection::MC_STATE_NUMBER . '.jpeg'),
                Inspection::MC_DATA_SHEET_1 => File::image(Inspection::MC_DATA_SHEET_1 . '.jpeg'),
                Inspection::MC_DATA_SHEET_2 => File::image(Inspection::MC_DATA_SHEET_2 . '.jpeg'),
                Inspection::MC_SIGN => File::image(Inspection::MC_SIGN . '.jpeg'),
            ],
            'tires' => $vehicle
                ->schemaVehicle
                ->wheels
                ->map(
                    function (SchemaWheel $wheel)
                    {
                        $tire = Tire::factory()
                            ->create();
                        return [
                            'tire_id' => $tire->id,
                            'schema_wheel_id' => $wheel->id,
                            'ogp' => $tire->ogp + 1,
                            'pressure' => 2.2,
                        ];
                    }
                )
                ->values()
                ->toArray()
        ];

        $this->postGraphQlUpload(
            GraphQLQuery::upload(InspectionCreateMutation::NAME)
                ->args(
                    [
                        'inspection' => $inspection
                    ]
                )
                ->select(
                    [
                        'id',
                        'is_moderated',
                        'moderation_fields' => [
                            'field',
                            'message',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        InspectionCreateMutation::NAME => [
                            'id',
                            'is_moderated',
                            'moderation_fields' => [
                                '*' => [
                                    'field',
                                    'message',
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(
                count($inspection['tires']),
                'data.' . InspectionCreateMutation::NAME . '.moderation_fields'
            );
    }

    public function test_create_inspection_for_trailer_with_odo_photo(): void
    {
        Storage::fake(config('media-library.disk_name'));

        $vehicle = Vehicle::factory()
            ->trailer()
            ->create();

        $inspection = [
            'vehicle_id' => $vehicle->id,
            'driver_id' => Driver::factory()
                ->create()->id,
            'inspection_reason_id' => InspectionReason::factory()
                ->create()->id,
            'unable_to_sign' => false,
            'odo' => $vehicle->odo - 10000,
            'time' => Carbon::now()
                ->getTimestamp(),
            'photos' => [
                Inspection::MC_VEHICLE => File::image(Inspection::MC_VEHICLE . '.jpeg'),
                Inspection::MC_STATE_NUMBER => File::image(Inspection::MC_STATE_NUMBER . '.jpeg'),
                Inspection::MC_DATA_SHEET_1 => File::image(Inspection::MC_DATA_SHEET_1 . '.jpeg'),
                Inspection::MC_DATA_SHEET_2 => File::image(Inspection::MC_DATA_SHEET_2 . '.jpeg'),
                Inspection::MC_SIGN => File::image(Inspection::MC_SIGN . '.jpeg'),
                Inspection::MC_ODO => File::image(Inspection::MC_ODO . '.jpeg'),
            ],
            'tires' => $vehicle
                ->schemaVehicle
                ->wheels
                ->map(
                    function (SchemaWheel $wheel)
                    {
                        return [
                            'tire_id' => Tire::factory()
                                ->create()->id,
                            'schema_wheel_id' => $wheel->id,
                            'ogp' => 6,
                            'pressure' => 2.2,
                        ];
                    }
                )
                ->values()
                ->toArray()
        ];

        $this->postGraphQlUpload(
            GraphQLQuery::upload(InspectionCreateMutation::NAME)
                ->args(
                    [
                        'inspection' => $inspection
                    ]
                )
                ->select(
                    [
                        'id',
                        'photos' => [
                            Inspection::MC_ODO => [
                                'id',
                                'url',
                                'name',
                                'file_name'
                            ],
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        InspectionCreateMutation::NAME => [
                            'id',
                            'photos' => [
                                Inspection::MC_ODO => [
                                    'id',
                                    'url',
                                    'name',
                                    'file_name'
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        InspectionCreateMutation::NAME => [
                            'photos' => [
                                Inspection::MC_ODO => [
                                    'name' => Inspection::MC_ODO,
                                    'file_name' => Inspection::MC_ODO . '.jpeg'
                                ],
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_create_inspection_for_trailer_without_odometer(): void
    {
        Storage::fake(config('media-library.disk_name'));

        $vehicle = Vehicle::factory()
            ->trailer()
            ->create();

        $inspection = [
            'vehicle_id' => $vehicle->id,
            'driver_id' => Driver::factory()
                ->create()->id,
            'inspection_reason_id' => InspectionReason::factory()
                ->create()->id,
            'unable_to_sign' => false,
            'time' => Carbon::now()
                ->getTimestamp(),
            'photos' => [
                Inspection::MC_VEHICLE => File::image(Inspection::MC_VEHICLE . '.jpeg'),
                Inspection::MC_STATE_NUMBER => File::image(Inspection::MC_STATE_NUMBER . '.jpeg'),
                Inspection::MC_DATA_SHEET_1 => File::image(Inspection::MC_DATA_SHEET_1 . '.jpeg'),
                Inspection::MC_DATA_SHEET_2 => File::image(Inspection::MC_DATA_SHEET_2 . '.jpeg'),
                Inspection::MC_SIGN => File::image(Inspection::MC_SIGN . '.jpeg'),
                Inspection::MC_ODO => File::image(Inspection::MC_ODO . '.jpeg'),
            ],
            'tires' => $vehicle
                ->schemaVehicle
                ->wheels
                ->map(
                    function (SchemaWheel $wheel)
                    {
                        return [
                            'tire_id' => Tire::factory()
                                ->create()->id,
                            'schema_wheel_id' => $wheel->id,
                            'ogp' => 6,
                            'pressure' => 2.2,
                        ];
                    }
                )
                ->values()
                ->toArray()
        ];

        $this->postGraphQlUpload(
            GraphQLQuery::upload(InspectionCreateMutation::NAME)
                ->args(
                    [
                        'inspection' => $inspection
                    ]
                )
                ->select(
                    [
                        'id',
                        'odo',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        InspectionCreateMutation::NAME => [
                            'id',
                            'odo',
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        InspectionCreateMutation::NAME => [
                            'odo' => null,
                        ]
                    ]
                ]
            );
    }

    public function test_create_inspection_with_previous_inspection_photos(): void
    {
        Storage::fake(config('media-library.disk_name'));

        $previousInspection = Inspection::factory(['inspector_id' => $this->inspector->id])
            ->create();

        $vehicle = $previousInspection->vehicle;

        $createdAt = Carbon::now()->addHours(-1)
            ->getTimestamp();

        Recommendation::factory()
            ->hasAttached(
                Problem::factory()
                    ->count(10),
                relationship: 'problems'
            )
            ->count(20)
            ->create();

        $inspection = [
            'vehicle_id' => $vehicle->id,
            'driver_id' => Driver::factory()
                ->create()->id,
            'inspection_reason_id' => InspectionReason::factory()
                ->create()->id,
            'unable_to_sign' => false,
            'odo' => $vehicle->odo + 10000,
            'time' => $createdAt,
            'photos' => [
                Inspection::MC_STATE_NUMBER => File::image(Inspection::MC_STATE_NUMBER . '.jpeg'),
                Inspection::MC_ODO => File::image(Inspection::MC_ODO . '.jpeg'),
                Inspection::MC_SIGN => File::image(Inspection::MC_SIGN . '.jpeg'),
            ],
            'tires' => $vehicle
                ->schemaVehicle
                ->wheels
                ->map(
                    function (SchemaWheel $wheel)
                    {
                        return [
                            'tire_id' => Tire::factory()
                                ->create()->id,
                            'schema_wheel_id' => $wheel->id,
                            'ogp' => 6,
                            'pressure' => 2.2,
                            'problems' => null,
                            'recommendations' => null,
                            'comment' => 'test comment',
                        ];
                    }
                )
                ->values()
                ->toArray()
        ];

        $result = $this->postGraphQlUpload(
            GraphQLQuery::upload(InspectionCreateMutation::NAME)
                ->args(
                    [
                        'inspection' => $inspection
                    ]
                )
                ->select(
                    [
                        'id',
                        'created_at',
                        'inspector' => [
                            'id',
                        ],
                        'branch' => [
                            'id'
                        ],
                        'vehicle' => [
                            'id',
                        ],
                        'is_moderated',
                        'unable_to_sign',
                        'driver' => [
                            'id'
                        ],
                        'inspection_reason' => [
                            'id',
                        ],
                        'inspection_reason_description',
                        'photos' => [
                            Inspection::MC_STATE_NUMBER => [
                                'id',
                                'url',
                                'name',
                                'file_name'
                            ],
                            Inspection::MC_VEHICLE => [
                                'id',
                                'url',
                                'name',
                                'file_name'
                            ],
                            Inspection::MC_DATA_SHEET_1 => [
                                'id',
                                'url',
                                'name',
                                'file_name'
                            ],
                            Inspection::MC_DATA_SHEET_2 => [
                                'id',
                                'url',
                                'name',
                                'file_name'
                            ],
                            Inspection::MC_ODO => [
                                'id',
                                'url',
                                'name',
                                'file_name'
                            ],
                            Inspection::MC_SIGN => [
                                'id',
                                'url',
                                'name',
                                'file_name'
                            ],
                        ],
                        'tires' => [
                            'tire' => [
                                'id',
                            ],
                            'schema_wheel' => [
                                'id',
                            ],
                            'ogp',
                            'pressure',
                            'comment',
                            'no_problems',
                            'problems' => [
                                'id',
                            ],
                            'recommendations' => [
                                'id',
                                'is_confirmed',
                                'new_tire' => [
                                    'id'
                                ]
                            ]
                        ],
                        'moderation_fields' => [
                            'field',
                            'message',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        InspectionCreateMutation::NAME => [
                            'id',
                            'created_at',
                            'inspector' => [
                                'id',
                            ],
                            'branch' => [
                                'id'
                            ],
                            'vehicle' => [
                                'id',
                            ],
                            'is_moderated',
                            'inspection_reason' => [
                                'id',
                            ],
                            'inspection_reason_description',
                            'photos' => [
                                Inspection::MC_STATE_NUMBER => [
                                    'id',
                                    'url',
                                    'name',
                                    'file_name'
                                ],
                                Inspection::MC_VEHICLE => [
                                    'id',
                                    'url',
                                    'name',
                                    'file_name'
                                ],
                                Inspection::MC_DATA_SHEET_1 => [
                                    'id',
                                    'url',
                                    'name',
                                    'file_name'
                                ],
                                Inspection::MC_DATA_SHEET_2 => [
                                    'id',
                                    'url',
                                    'name',
                                    'file_name'
                                ],
                                Inspection::MC_ODO => [
                                    'id',
                                    'url',
                                    'name',
                                    'file_name'
                                ],
                                Inspection::MC_SIGN => [
                                    'id',
                                    'url',
                                    'name',
                                    'file_name'
                                ],
                            ],
                            'tires' => [
                                '*' => [
                                    'tire' => [
                                        'id',
                                    ],
                                    'schema_wheel' => [
                                        'id',
                                    ],
                                    'ogp',
                                    'pressure',
                                    'comment',
                                    'no_problems',
                                    'problems' => [
                                        '*' => [
                                            'id'
                                        ]
                                    ],
                                    'recommendations' => [
                                        '*' => [
                                            'id',
                                            'is_confirmed',
                                            'new_tire' => [
                                                'id'
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            'moderation_fields'
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        InspectionCreateMutation::NAME => [
                            'created_at' => $createdAt,
                            'inspector' => [
                                'id' => $this->inspector->id,
                            ],
                            'branch' => [
                                'id' => $this->inspector->branch->id,
                            ],
                            'vehicle' => [
                                'id' => $vehicle->id,
                            ],
                            'is_moderated' => true,
                            'inspection_reason' => [
                                'id' => $inspection['inspection_reason_id'],
                            ],
                            'inspection_reason_description' => null,
                            'photos' => [
                                Inspection::MC_STATE_NUMBER => [
                                    'name' => Inspection::MC_STATE_NUMBER,
                                    'file_name' => Inspection::MC_STATE_NUMBER . '.jpeg'
                                ],
                                Inspection::MC_VEHICLE => [
                                    'name' => Inspection::MC_VEHICLE,
                                    'file_name' => Inspection::MC_VEHICLE . '.jpeg'
                                ],
                                Inspection::MC_DATA_SHEET_1 => [
                                    'name' => Inspection::MC_DATA_SHEET_1,
                                    'file_name' => Inspection::MC_DATA_SHEET_1 . '.jpeg'
                                ],
                                Inspection::MC_DATA_SHEET_2 => [
                                    'name' => Inspection::MC_DATA_SHEET_2,
                                    'file_name' => Inspection::MC_DATA_SHEET_2 . '.jpeg'
                                ],
                                Inspection::MC_ODO => [
                                    'name' => Inspection::MC_ODO,
                                    'file_name' => Inspection::MC_ODO . '.jpeg'
                                ],
                                Inspection::MC_SIGN => [
                                    'name' => Inspection::MC_SIGN,
                                    'file_name' => Inspection::MC_SIGN . '.jpeg'
                                ],
                            ],
                            'tires' => array_map(
                                fn(array $tire) => [
                                    'tire' => [
                                        'id' => $tire['tire_id'],
                                    ],
                                    'schema_wheel' => [
                                        'id' => $tire['schema_wheel_id'],
                                    ],
                                    'ogp' => $tire['ogp'],
                                    'pressure' => $tire['pressure'],
                                    'comment' => $tire['comment'] ?? null,
                                    'no_problems' => true,
                                    'problems' => null,
                                    'recommendations' => null,
                                ],
                                $inspection['tires']
                            ),
                            'moderation_fields' => null
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            Vehicle::class,
            [
                'id' => $vehicle->id,
                'odo' => $inspection['odo']
            ]
        );

        $createdId = $result['data'][InspectionCreateMutation::NAME]['id'] ?? null;
        $this->assertDatabaseHas(
            Media::class,
            [
                'model_type' => Inspection::class,
                'model_id' => $createdId,
                'collection_name' => Inspection::MC_VEHICLE
            ]
        );

        $this->assertDatabaseHas(
            Media::class,
            [
                'model_type' => Inspection::class,
                'model_id' => $createdId,
                'collection_name' => Inspection::MC_DATA_SHEET_1
            ]
        );

        $this->assertDatabaseHas(
            Media::class,
            [
                'model_type' => Inspection::class,
                'model_id' => $createdId,
                'collection_name' => Inspection::MC_DATA_SHEET_2
            ]
        );

        $this->assertDatabaseHas(
            Media::class,
            [
                'model_type' => Vehicle::class,
                'model_id' => $vehicle->id,
                'collection_name' => Vehicle::MC_STATE_NUMBER
            ]
        );
    }

    public function test_error_create_inspection_without_vehicle_photo(): void
    {
        Storage::fake(config('media-library.disk_name'));

        $vehicle = Vehicle::factory()->create();

        $createdAt = Carbon::now()->addHours(-1)
            ->getTimestamp();

        Recommendation::factory()
            ->hasAttached(
                Problem::factory()
                    ->count(10),
                relationship: 'problems'
            )
            ->count(20)
            ->create();

        $inspection = [
            'vehicle_id' => $vehicle->id,
            'driver_id' => Driver::factory()
                ->create()->id,
            'inspection_reason_id' => InspectionReason::factory()
                ->create()->id,
            'unable_to_sign' => false,
            'odo' => $vehicle->odo + 10000,
            'time' => $createdAt,
            'photos' => [
                Inspection::MC_STATE_NUMBER => File::image(Inspection::MC_STATE_NUMBER . '.jpeg'),
                Inspection::MC_ODO => File::image(Inspection::MC_ODO . '.jpeg'),
                Inspection::MC_SIGN => File::image(Inspection::MC_SIGN . '.jpeg'),
            ],
            'tires' => $vehicle
                ->schemaVehicle
                ->wheels
                ->map(
                    function (SchemaWheel $wheel)
                    {
                        return [
                            'tire_id' => Tire::factory()
                                ->create()->id,
                            'schema_wheel_id' => $wheel->id,
                            'ogp' => 6,
                            'pressure' => 2.2,
                            'problems' => null,
                            'recommendations' => null,
                            'comment' => 'test comment',
                        ];
                    }
                )
                ->values()
                ->toArray()
        ];

        $this->postGraphQlUpload(
            GraphQLQuery::upload(InspectionCreateMutation::NAME)
                ->args(
                    [
                        'inspection' => $inspection
                    ]
                )
                ->select(
                    [
                        'id',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('inspections.validation_messages.photos.vehicle.is_required')
                        ]
                    ]
                ]
            );
    }
}
