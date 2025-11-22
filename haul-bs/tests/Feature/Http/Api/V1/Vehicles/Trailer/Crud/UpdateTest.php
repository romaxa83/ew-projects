<?php

namespace Tests\Feature\Http\Api\V1\Vehicles\Trailer\Crud;

use App\Enums\Tags\TagType;
use App\Enums\Vehicles\VehicleType;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Models\Tags\Tag;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Tags\TagBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;
    protected TagBuilder $tagBuilder;
    protected TrailerBuilder $trailerBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->tagBuilder = resolve(TagBuilder::class);
        $this->customerBuilder = resolve(CustomerBuilder::class);
        $this->trailerBuilder = resolve(TrailerBuilder::class);

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
            'gvwr' => 20.9,
            'tags' => [$tag->id],
            'notes' => 'text'
        ];
    }

    /** @test */
    public function success_update()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $tag = $this->tagBuilder->create();
        /** @var $model Trailer */
        $model = $this->trailerBuilder->tags($tag)->create();

        $this->assertNotEquals($model->vin, data_get($data, 'vin'));
        $this->assertNotEquals($model->unit_number, data_get($data, 'unit_number'));
        $this->assertNotEquals($model->year, data_get($data, 'year'));
        $this->assertNotEquals($model->make, data_get($data, 'make'));
        $this->assertNotEquals($model->model, data_get($data, 'model'));
        $this->assertNotEquals($model->license_plate, data_get($data, 'license_plate'));
        $this->assertNotEquals($model->customer_id, data_get($data, 'owner_id'));
        $this->assertNotEquals($model->color, data_get($data, 'color'));
        $this->assertNotEquals($model->gvwr, data_get($data, 'gvwr'));
        $this->assertNotEquals($model->notes, data_get($data, 'notes'));
        $this->assertNotEquals($model->tags[0]->id, data_get($data, 'tags.0'));

        $this->postJson(route('api.v1.vehicles.trailers.update', ['id' => $model->id]), $data)
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
                    'attachments' => []
                ],
            ])
            ->assertJsonCount(0, 'data.attachments')
        ;
    }

    /** @test */
    public function success_update_check_history()
    {
        $user = $this->loginUserAsSuperAdmin();

        $tag = $this->tagBuilder->create();
        /** @var $model Trailer */
        $model = $this->trailerBuilder->tags($tag)->create();
        $old = clone $model;

        $data = $this->data;
        $data['vin'] = $model->vin;
        $data['unit_number'] = $model->unit_number;
        $data['year'] = $model->year;
        $data['make'] = $model->make;
        $data['model'] = $model->model;
        $data['license_plate'] = $model->license_plate;
        $data['owner_id'] = $model->customer_id;
        $data['notes'] = $model->notes;
        $data['gvwr'] = $model->gvwr;

        $id = $this->postJson(route('api.v1.vehicles.trailers.update', ['id' => $model->id]), $data)
            ->json('data.id')
        ;

        /** @var $model Trailer */
        $model = Trailer::query()->where('id', $id)->first();
        $history = $model->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.vehicle.updated');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'vehicle_type' => __('history.vehicle.trailer'),
            'user_id' => $user->id,
        ]);


        $this->assertEquals($history->details['color'], [
            'old' => $old->color,
            'new' => data_get($data, 'color'),
            'type' => 'updated',
        ]);
        $this->assertEquals($history->details['tags'], [
            "old" => $tag->name,
            "new" => Tag::find(data_get($data, 'tags.0'))->name,
            "type" => "updated"
        ]);
    }

    /** @test */
    public function success_update_file()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $file_1 = UploadedFile::fake()->createWithContent('info_1.pdf', 'Some text for file');
        $file_2 = UploadedFile::fake()->createWithContent('info_2.pdf', 'Some text for file');

        /** @var $model Trailer */
        $model = $this->trailerBuilder->attachments($file_2)->create();

        $data = $this->data;
        $data[Vehicle::ATTACHMENT_FIELD_NAME] = [$file_1];

        $this->assertCount(1, $model->getAttachments());

        $this->postJson(route('api.v1.vehicles.trailers.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    Vehicle::ATTACHMENT_COLLECTION_NAME => [
                        ['name' => 'info_2'],
                        ['name' => 'info_1'],
                    ],
                ],
            ])
            ->assertJsonCount(2, 'data.'.Vehicle::ATTACHMENT_COLLECTION_NAME)
        ;

        $model->refresh();

        $media = $model->media->where('name', 'info_1')->first();

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

    /** @test */
    public function fail_more_more_file()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $file_1 = UploadedFile::fake()->createWithContent('info_1.pdf', 'Some text for file');
        $file_2 = UploadedFile::fake()->createWithContent('info_2.pdf', 'Some text for file');
        $file_3 = UploadedFile::fake()->createWithContent('info_3.pdf', 'Some text for file');
        $file_4 = UploadedFile::fake()->createWithContent('info_4.pdf', 'Some text for file');
        $file_5 = UploadedFile::fake()->createWithContent('info_5.pdf', 'Some text for file');
        $file_6 = UploadedFile::fake()->createWithContent('info_6.pdf', 'Some text for file');

        /** @var $model Trailer */
        $model = $this->trailerBuilder->attachments($file_1, $file_2, $file_3, $file_4)->create();

        $data = $this->data;
        $data[Vehicle::ATTACHMENT_FIELD_NAME] = [$file_5, $file_6];

        $res = $this->postJson(route('api.v1.vehicles.trailers.update', ['id' => $model->id]), $data)
        ;

        $this->assertValidationMsg($res,
            __('validation.max.array', ['attribute' => 'attachment files', 'max' => 1]),
            'attachment_files');
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.vehicles.trailers.update', ['id' => 0]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.vehicles.trailer.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Trailer */
        $model = $this->trailerBuilder->create();

        $data['vin'] = null;

        $res = $this->postJson(route('api.v1.vehicles.trailers.update', ['id' => $model->id]), $data, [
            'Validate-Only' => true
        ])
        ;

        $this->assertValidationMsgWithValidateOnly($res, __('validation.required', ['attribute' => 'VIN']), 'vin');
    }

    /** @test */
    public function field_success_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Trailer */
        $model = $this->trailerBuilder->create();

        $data = $this->data;

        $this->postJson(route('api.v1.vehicles.trailers.update', ['id' => $model->id]), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Trailer */
        $model = $this->trailerBuilder->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.vehicles.trailers.update', ['id' => $model->id]), $data)
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

        /** @var $model Trailer */
        $model = $this->trailerBuilder->create();

        $res = $this->postJson(route('api.v1.vehicles.trailers.update', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        /** @var $model Trailer */
        $model = $this->trailerBuilder->create();

        $res = $this->postJson(route('api.v1.vehicles.trailers.update', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
