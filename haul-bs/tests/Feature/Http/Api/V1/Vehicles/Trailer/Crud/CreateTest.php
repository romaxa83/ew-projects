<?php

namespace Tests\Feature\Http\Api\V1\Vehicles\Trailer\Crud;

use App\Enums\Tags\TagType;
use App\Enums\Vehicles\VehicleType;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Models\Tags\Tag;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use App\Services\Events\Vehicle\VehicleEventService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Tags\TagBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;
    protected TagBuilder $tagBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->tagBuilder = resolve(TagBuilder::class);
        $this->customerBuilder = resolve(CustomerBuilder::class);

        $tag = $this->tagBuilder->type(TagType::TRUCKS_AND_TRAILER())->create();
        $customer = $this->customerBuilder->create();

        $this->data = [
            'vin' => '5675dfJGFDHG0099',
            'unit_number' => '23YY56',
            'year' => '2004',
            'make' => 'BMW',
            'model' => 'Seria S',
            'license_plate' => 'S444s',
            'owner_id' => $customer->id,
            'color' => 'black',
            'gvwr' => 20.8,
            'tags' => [$tag->id],
            'notes' => 'text'
        ];
    }

    /** @test */
    public function success_create()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $this->postJson(route('api.v1.vehicles.trailers'), $data)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'tags' => [
                        [
                            'id',
                            'name',
                            'color',
                        ]
                    ],
                    'owner' => [
                        'first_name',
                        'last_name',
                        'phone',
                        'phone_extension',
                        'email',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'vin' => data_get($data, 'vin'),
                    'unit_number' => data_get($data, 'unit_number'),
                    'license_plate' => data_get($data, 'license_plate'),
                    'temporary_plate' => null,
                    'make' => data_get($data, 'make'),
                    'model' => data_get($data, 'model'),
                    'year' => data_get($data, 'year'),
                    'notes' => data_get($data, 'notes'),
                    'color' => data_get($data, 'color'),
                    'gvwr' => data_get($data, 'gvwr'),
                    'tags' => [
                        [
                            'id' => data_get($data, 'tags.0')
                        ]
                    ],
                    'owner' => [
                        'id' => data_get($data, 'owner_id')
                    ],
                    'attachments' => [],
                    'company_name' => null
                ],
            ])
            ->assertJsonCount(0, 'data.attachments')
        ;
    }

    /** @test */
    public function success_create_check_history()
    {
        $user = $this->loginUserAsSuperAdmin();

        $customer = $this->customerBuilder->create();

        $tag = $this->tagBuilder->type(TagType::TRUCKS_AND_TRAILER())->create();

        $data = $this->data;
        $data['owner_id'] = $customer->id;
        $data['tags'][1] = $tag->id;

        $id = $this->postJson(route('api.v1.vehicles.trailers'), $data)
            ->json('data.id')
        ;

        /** @var $model Trailer */
        $model = Trailer::query()->where('id', $id)->first();
        $history = $model->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.vehicle.created');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'vehicle_type' => __('history.vehicle.trailer'),
            'user_id' => $user->id,
        ]);

        $this->assertEquals($history->details, [
            'vin' => [
                'old' => null,
                'new' => data_get($data, 'vin'),
                'type' => 'added',
            ],
            'unit_number' => [
                'old' => null,
                'new' => data_get($data, 'unit_number'),
                'type' => 'added',
            ],
            'make' => [
                'old' => null,
                'new' => data_get($data, 'make'),
                'type' => 'added',
            ],
            'model' => [
                'old' => null,
                'new' => data_get($data, 'model'),
                'type' => 'added',
            ],
            'year' => [
                'old' => null,
                'new' => data_get($data, 'year'),
                'type' => 'added',
            ],
            'license_plate' => [
                'old' => null,
                'new' => data_get($data, 'license_plate'),
                'type' => 'added',
            ],
            'notes' => [
                'old' => null,
                'new' => data_get($data, 'notes'),
                'type' => 'added',
            ],
            'color' => [
                'old' => null,
                'new' => data_get($data, 'color'),
                'type' => 'added',
            ],
            'gvwr' => [
                'old' => null,
                'new' => data_get($data, 'gvwr'),
                'type' => 'added',
            ],
            'customer_id' => [
                'old' => null,
                'new' => $customer->full_name,
                'type' => 'added',
            ],
            "tags" => [
                'old' => null,
                'new' => Tag::find(data_get($data, 'tags.0'))->name .', '. $tag->name,
                'type' => 'added',
            ]
        ]);
    }

    /** @test */
    public function success_create_with_files()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        $data[Vehicle::ATTACHMENT_FIELD_NAME] = [
            $file_1 = UploadedFile::fake()->createWithContent('info_1.pdf', 'Some text for file'),
            $file_2 = UploadedFile::fake()->createWithContent('info_2.pdf', 'Some text for file'),
        ];

        $id = $this->postJson(route('api.v1.vehicles.trailers'), $data)
            ->assertJsonStructure([
                'data' => [
                    Vehicle::ATTACHMENT_COLLECTION_NAME => [
                        [
                            'id',
                            'name',
                            'file_name',
                            'mime_type',
                            'url',
                            'size',
                            'created_at',
                        ],
                    ],
                ]
            ])
            ->assertJson([
                'data' => [
                    Vehicle::ATTACHMENT_COLLECTION_NAME => [
                        ['name' => 'info_1'],
                        ['name' => 'info_2'],
                    ],
                ],
            ])
            ->assertJsonCount(2, 'data.'.Vehicle::ATTACHMENT_COLLECTION_NAME)
            ->json('data.id')
        ;

        $model = Trailer::find($id);

        foreach ($model->getAttachments() as $media){
            /** @var $media \Spatie\MediaLibrary\MediaCollections\Models\Media */
            $this->assertEquals(
                $model->histories[0]->details['attachments.'.$media->id.'.name']['new'],
                $media->name
            );
            $this->assertEquals(
                $model->histories[0]->details['attachments.'.$media->id.'.name']['type'],
                'added'
            );
            $this->assertNull(
                $model->histories[0]->details['attachments.'.$media->id.'.name']['old']
            );
        }
    }

    /** @test */
    public function fail_create_with_files_more_file()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        $data[Vehicle::ATTACHMENT_FIELD_NAME] = [
            UploadedFile::fake()->createWithContent('info_1.pdf', 'Some text for file'),
            UploadedFile::fake()->createWithContent('info_2.pdf', 'Some text for file'),
            UploadedFile::fake()->createWithContent('info_3.pdf', 'Some text for file'),
            UploadedFile::fake()->createWithContent('info_4.pdf', 'Some text for file'),
            UploadedFile::fake()->createWithContent('info_5.pdf', 'Some text for file'),
            UploadedFile::fake()->createWithContent('info_6.pdf', 'Some text for file'),
        ];

        $res = $this->postJson(route('api.v1.vehicles.trailers'), $data)
        ;

        $this->assertValidationMsg($res,
            __('validation.max.array', ['attribute' => 'attachment files', 'max' => Vehicle::MAX_ATTACHMENTS_COUNT]),
            'attachment_files');
    }

    /** @test */
    public function success_create_only_required_field()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        unset(
            $data['color'],
            $data['gvwr'],
            $data['notes'],
            $data['tags'],
        );

        $this->postJson(route('api.v1.vehicles.trailers'), $data)
            ->assertJson([
                'data' => [
                    'color' => null,
                    'gvwr' => null,
                    'notes' => null,
                    'tags' => []
                ],
            ])
            ->assertJsonCount(0, 'data.tags')
        ;
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        $data['vin'] = null;

        $res = $this->postJson(route('api.v1.vehicles.trailers'), $data, [
            'Validate-Only' => true
        ])
        ;

        $this->assertValidationMsgWithValidateOnly($res, __('validation.required', ['attribute' => 'VIN']), 'vin');
    }

    /** @test */
    public function field_success_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $this->postJson(route('api.v1.vehicles.trailers'), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;

        $this->assertDatabaseEmpty(Trailer::TABLE);
    }

    /** @test */
    public function field_wrong_tag()
    {
        $this->loginUserAsSuperAdmin();

        $tag = $this->tagBuilder->type(TagType::CUSTOMER())->create();

        $data = $this->data;
        $data['tags'] = [$tag->id];

        $res = $this->postJson(route('api.v1.vehicles.trailers'), $data)
        ;

        $this->assertValidationMsg($res,
            __('validation.exists', ['attribute' => 'Tag']),
            'tags.0');
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.vehicles.trailers'), $data)
        ;

        $this->assertValidationMsg($res, __($msgKey, $attributes), $field);
    }

    public static function validate(): array
    {
        return [
            ['vin', null, 'validation.required', ['attribute' => 'VIN']],
            ['vin', 1111, 'validation.string', ['attribute' => 'VIN']],
            ['vin', '!&&&', 'validation.alpha_num', ['attribute' => 'VIN']],
            ['unit_number', null, 'validation.required', ['attribute' => 'Unit Number']],
            ['unit_number', 1111, 'validation.string', ['attribute' => 'Unit Number']],
            ['unit_number', '!&&&', 'validation.alpha_num', ['attribute' => 'Unit Number']],
            ['license_plate', null, 'validation.required', ['attribute' => 'License Plate']],
            ['license_plate', 1111, 'validation.string', ['attribute' => 'License Plate']],
            ['license_plate', '!&&&', 'validation.alpha_dash', ['attribute' => 'License Plate']],
            ['year', null, 'validation.required', ['attribute' => 'Year']],
            ['year', 2022, 'validation.string', ['attribute' => 'Year']],
            ['year', '20228', 'validation.max.string', ['attribute' => 'Year', 'max' => 4]],
            ['make', null, 'validation.required', ['attribute' => 'Make']],
            ['make', 1111, 'validation.string', ['attribute' => 'Make']],
            ['model', null, 'validation.required', ['attribute' => 'Model']],
            ['model', 1111, 'validation.string', ['attribute' => 'Model']],
            ['owner_id', null, 'validation.required', ['attribute' => 'owner id']],
            ['owner_id', 0, 'validation.exists', ['attribute' => 'owner id']],
            ['owner_id', 0, 'validation.exists', ['attribute' => 'owner id']],
            ['gvwr', 'addd', 'validation.numeric', ['attribute' => 'gvwr']],
            ['gvwr', 0, 'validation.min.numeric', ['attribute' => 'gvwr', 'min' => 1]],
        ];
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.vehicles.trailers'), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        $res = $this->postJson(route('api.v1.vehicles.trailers'), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
