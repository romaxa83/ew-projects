<?php

namespace Tests\Feature\Mutations\FrontOffice\Inspection;

use App\Enums\Inspections\TirePhotoType;
use App\GraphQL\Mutations\FrontOffice\Inspections\InspectionTirePhotoUploadMutation;
use App\Models\Inspections\Inspection;
use App\Models\Inspections\InspectionTire;
use App\Models\Users\User;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\Testing\File;

class InspectionTirePhotoUploadTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    private User $inspector;

    public const MUTATION = InspectionTirePhotoUploadMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();

        $this->inspector = $this->loginAsUserWithRole();
    }

    /** @test */
    public function success_upload_media(): void
    {
        $inspection = Inspection::factory()->create();


//        dd(
//            GraphQLQuery::upload(self::MUTATION)
//                ->args(
//                    [
//                        'inspection_tire_id' => $inspection->inspectionTires->first()->id,
//                        'photos' => [
//                            TirePhotoType::MAIN => File::image(TirePhotoType::MAIN . '.jpeg'),
//                            TirePhotoType::SERIAL_NUMBER => File::image(TirePhotoType::SERIAL_NUMBER . '.jpeg'),
//                        ]
//                    ]
//                )
//                ->select(
//                    [
//                        'id',
//                        'photos' => [
//                            TirePhotoType::MAIN => [
//                                'id',
//                                'url',
//                                'name',
//                                'file_name'
//                            ],
//                            TirePhotoType::SERIAL_NUMBER => [
//                                'id',
//                                'url',
//                                'name',
//                                'file_name'
//                            ],
//                        ],
//                    ]
//                )
//                ->make()
//        );

        $res = $this->postGraphQlUpload(
            GraphQLQuery::upload(self::MUTATION)
                ->args(
                    [
                        'inspection_tire_id' => $inspection->inspectionTires->first()->id,
                        'photos' => [
                            TirePhotoType::MAIN => File::image(TirePhotoType::MAIN . '.jpeg'),
                            TirePhotoType::SERIAL_NUMBER => File::image(TirePhotoType::SERIAL_NUMBER . '.jpeg'),
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
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
                    ]
                )
                ->make()
        )
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $inspection->inspectionTires->first()->id,
                        'photos' => [
                            InspectionTire::PHOTO_MAIN => [
                                'name' => InspectionTire::PHOTO_MAIN,
                                'file_name' => InspectionTire::PHOTO_MAIN . '.jpeg'
                            ],
                            InspectionTire::PHOTO_SERIAL_NUMBER => [
                                'name' => InspectionTire::PHOTO_SERIAL_NUMBER,
                                'file_name' => InspectionTire::PHOTO_SERIAL_NUMBER . '.jpeg'
                            ]
                        ]
                    ],
                ]
            ])
            ;

        $inspection->refresh();
        $this->assertNotEmpty($inspection->inspectionTires->first()->media);
        $this->assertEmpty($inspection->inspectionTires[1]->media);

        $this->assertEquals(
            $inspection->inspectionTires->first()->media[0]->getFullUrl(),
            env('APP_URL') . "/storage/{$res->json('data.' . self::MUTATION . '.photos.main.id')}/main.jpeg"
        );
        $this->assertEquals(
            $inspection->inspectionTires->first()->media[1]->getFullUrl(),
            env('APP_URL') . "/storage/{$res->json('data.' . self::MUTATION . '.photos.serial_number.id')}/serial_number.jpeg"
        );

        unlink(storage_path("app/public/{$res->json('data.' . self::MUTATION . '.photos.main.id')}/main.jpeg"));
        unlink(storage_path("app/public/{$res->json('data.' . self::MUTATION . '.photos.serial_number.id')}/serial_number.jpeg"));
    }

    /** @test */
    public function success_upload_new_media_for_tires(): void
    {
        $inspection = Inspection::factory()->create();

        $inspection->inspectionTires->first()
            ->addMedia(File::image(TirePhotoType::MAIN . '.jpeg'))
            ->toMediaCollection(TirePhotoType::MAIN);
        $inspection->inspectionTires->first()
            ->addMedia(File::image(TirePhotoType::SERIAL_NUMBER . '.jpeg'))
            ->toMediaCollection(TirePhotoType::SERIAL_NUMBER);

        $this->assertCount(2, $inspection->inspectionTires->first()->media);
        $this->assertEquals($inspection->inspectionTires->first()->media[0]->name, TirePhotoType::MAIN);
        $this->assertEquals($inspection->inspectionTires->first()->media[1]->name, TirePhotoType::SERIAL_NUMBER);

        $res = $this->postGraphQlUpload(
            GraphQLQuery::upload(self::MUTATION)
                ->args(
                    [
                        'inspection_tire_id' => $inspection->inspectionTires->first()->id,
                        'photos' => [
                            TirePhotoType::MAIN => File::image(TirePhotoType::MAIN . '_1.jpeg'),
                            TirePhotoType::SERIAL_NUMBER => File::image(TirePhotoType::SERIAL_NUMBER . '_1.jpeg'),
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
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
                    ]
                )
                ->make()
        )
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $inspection->inspectionTires->first()->id,
                        'photos' => [
                            TirePhotoType::MAIN => [
                                'name' => TirePhotoType::MAIN . '_1',
                                'file_name' => TirePhotoType::MAIN . '_1.jpeg'
                            ],
                            TirePhotoType::SERIAL_NUMBER => [
                                'name' => TirePhotoType::SERIAL_NUMBER . '_1',
                                'file_name' => TirePhotoType::SERIAL_NUMBER . '_1.jpeg'
                            ]
                        ]
                    ],
                ]
            ])
        ;

        $inspection->refresh();
        $this->assertNotEmpty($inspection->inspectionTires->first()->media);
        $this->assertEmpty($inspection->inspectionTires[1]->media);

        $this->assertEquals(
            $inspection->inspectionTires->first()->media[0]->getFullUrl(),
            env('APP_URL') . "/storage/{$res->json('data.' . self::MUTATION . '.photos.main.id')}/main_1.jpeg"
        );
        $this->assertEquals(
            $inspection->inspectionTires->first()->media[1]->getFullUrl(),
            env('APP_URL') . "/storage/{$res->json('data.' . self::MUTATION . '.photos.serial_number.id')}/serial_number_1.jpeg"
        );

        unlink(storage_path("app/public/{$res->json('data.' . self::MUTATION . '.photos.main.id')}/main_1.jpeg"));
        unlink(storage_path("app/public/{$res->json('data.' . self::MUTATION . '.photos.serial_number.id')}/serial_number_1.jpeg"));
    }
}
