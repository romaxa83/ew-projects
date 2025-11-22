<?php

namespace Tests\Feature\Http\Api\V1\Webhooks\Vehicle;

use App\Models\Companies\Company;
use App\Models\Customers\Customer;
use App\Models\Tags\Tag;
use App\Models\Vehicles\Truck;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\Builders\Companies\CompanyBuilder;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Tags\TagBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\TestCase;

class CreateOrUpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected TrailerBuilder $trailerBuilder;
    protected TruckBuilder $truckBuilder;
    protected TagBuilder $tagBuilder;
    protected CustomerBuilder $customerBuilder;
    protected CompanyBuilder $companyBuilder;

    protected array $data;

    public function setUp(): void
    {
        parent::setUp();

        $this->trailerBuilder = resolve(TrailerBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->tagBuilder = resolve(TagBuilder::class);
        $this->customerBuilder = resolve(CustomerBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);

        $this->data = [
            'vehicle_type' => 'truck',
            'id' => 100,
            'vin' => 'RR22267887JH86',
            'unit_number' => 'RR22',
            'make' => 'Ford',
            'model' => 'S serial',
            'year' => '2002',
            'color' => 'red',
            'gvwr' => 10.8,
            'type' => 3,
            'license_plate' => 'Z5566TT',
            'temporary_plate' => 'JK98',
            'notes' => 'text',
            'created_at' => CarbonImmutable::now()->timestamp,
            'company' => [
                'id' => 4,
                'name' => 'Company name'
            ],
            'customer' => [
                'id' => 5,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '1399999999',
                'phone_extension' => '333',
                'phones' => null,
                'email' => 'test@gmail.com'
            ],
            'tags' => [
                [
                    'id' => 1,
                    'name' => 'tag 1',
                    'color' => '#389E0D',
                ],
                [
                    'id' => 2,
                    'name' => 'tag 2',
                    'color' => '#389EEE',
                ]
            ],
            'media' => [
                [
                    "id" => 6,
                    "model_type" => "App\Models\Vehicles\Truck",
                    "model_id" => 100,
                    "name" => "pomeranskij-shpic-v-trave",
                    "file_name" => "8d42e7a0c36d889a37a88b8fcc80743b.jpg",
                    "mime_type" => "image/jpeg",
                    "disk" => "s3",
                    "size" => 131657,
                    "manipulations" => [],
                    "custom_properties" => [
                        "generated_conversions" => [
                            "sm" => true
                        ]
                    ],
                    "responsive_images" => [],
                    "order_column" => 5488
                ]
            ]
        ];
    }

    /** @test */
    public function success_create_truck()
    {
        $data = ['data' => $this->data];

        $this->assertFalse(Truck::query()->where('origin_id', data_get($data, 'data.id'))->exists());
        $this->assertFalse(Company::query()->where('id', data_get($data, 'data.company.id'))->exists());
        $this->assertFalse(Customer::query()->where('origin_id', data_get($data, 'data.customer.id'))->exists());
        $this->assertFalse(Tag::query()->where('origin_id', data_get($data, 'data.tags.0.id'))->exists());
        $this->assertFalse(Tag::query()->where('origin_id', data_get($data, 'data.tags.1.id'))->exists());
        $this->assertFalse(Media::query()->where('id', data_get($data, 'data.media.0.id'))->exists());

        $this->postJson(route('api.v1.webhooks.vehicles.sync'), $data, [
            'Authorization' => config('api.webhook.token')
        ])
            ->assertJson([
                'data' => [
                    'message' => 'Sync vehicle',
                ]
            ])
        ;

        /** @var $company Company */
        $company = Company::query()->where('id', data_get($data, 'data.company.id'))->first();
        $this->assertEquals($company->name, data_get($data, 'data.company.name'));
        $this->assertEquals(1, Company::count());

        /** @var $customer Customer */
        $customer = Customer::query()->where('origin_id', data_get($data, 'data.customer.id'))->first();
        $this->assertEquals($customer->first_name, data_get($data, 'data.customer.first_name'));
        $this->assertEquals($customer->last_name, data_get($data, 'data.customer.last_name'));
        $this->assertEquals($customer->email, data_get($data, 'data.customer.email'));
        $this->assertEquals($customer->phone, data_get($data, 'data.customer.phone'));
        $this->assertEquals($customer->phone_extension, data_get($data, 'data.customer.phone_extension'));
        $this->assertEquals($customer->phones, []);
        $this->assertTrue($customer->type->isHaulk());

        $this->assertEquals(1, Customer::count());

        /** @var $truck Truck */
        $truck = Truck::query()->where('origin_id', data_get($data, 'data.id'))->first();
        $this->assertEquals($truck->vin, data_get($data, 'data.vin'));
        $this->assertEquals($truck->unit_number, data_get($data, 'data.unit_number'));
        $this->assertEquals($truck->make, data_get($data, 'data.make'));
        $this->assertEquals($truck->model, data_get($data, 'data.model'));
        $this->assertEquals($truck->year, data_get($data, 'data.year'));
        $this->assertEquals($truck->color, data_get($data, 'data.color'));
        $this->assertEquals($truck->gvwr, data_get($data, 'data.gvwr'));
        $this->assertEquals($truck->type, data_get($data, 'data.type'));
        $this->assertEquals($truck->license_plate, data_get($data, 'data.license_plate'));
        $this->assertEquals($truck->temporary_plate, data_get($data, 'data.temporary_plate'));
        $this->assertEquals($truck->notes, data_get($data, 'data.notes'));
        $this->assertEquals($truck->company_id, $company->id);
        $this->assertEquals($truck->customer_id, $customer->id);

        $this->assertCount(0, $truck->tags);

//        $tag_1 = Tag::query()->where('origin_id', data_get($data, 'data.tags.0.id'))->first();
//        $this->assertEquals($tag_1->name, data_get($data, 'data.tags.0.name'));
//        $this->assertEquals($tag_1->color, data_get($data, 'data.tags.0.color'));
//
//        $tag_2 = Tag::query()->where('origin_id', data_get($data, 'data.tags.1.id'))->first();
//        $this->assertEquals($tag_2->name, data_get($data, 'data.tags.1.name'));
//        $this->assertEquals($tag_2->color, data_get($data, 'data.tags.1.color'));

        $this->assertCount(1, $truck->getAttachments());
        $this->assertEquals($truck->getAttachments()[0]->origin_id, data_get($data, 'data.media.0.id'));
    }

    /** @test */
    public function success_not_create_customer_if_exists_by_email()
    {
        $data = ['data' => $this->data];

        /** @var $customer Customer */
        $customer = $this->customerBuilder
            ->email($data['data']['customer']['email'])
            ->phone($data['data']['customer']['phone'])
            ->phones([['number' => '11111111', 'extension' => null]])
            ->create();

        $this->assertEquals(1, count($customer->phones));

        $this->postJson(route('api.v1.webhooks.vehicles.sync'), $data, [
            'Authorization' => config('api.webhook.token')
        ])
            ->assertJson([
                'data' => [
                    'message' => 'Sync vehicle',
                ]
            ])
        ;

        /** @var $customer Customer */
        $customer->refresh();


        $this->assertEquals(1, Customer::count());
        $this->assertEquals(1, count($customer->phones));

        /** @var $truck Truck */
        $truck = Truck::query()->where('origin_id', data_get($data, 'data.id'))->first();
        $this->assertEquals($truck->customer_id, $customer->id);
    }

    /** @test */
    public function success_not_create_customer_if_exists_by_email_and_phone()
    {
        $data = ['data' => $this->data];
        $data['data']['customer']['phone'] = '1111111111';

        /** @var $customer Customer */
        $customer = $this->customerBuilder
            ->email($data['data']['customer']['email'])
            ->phones([
                [
                    'number' => '1111111112',
                    'extension' => null
                ]
            ])
            ->create();

        $this->assertNotEquals($customer->phone->getValue(), $data['data']['customer']['phone']);
        $this->assertEquals(1, count($customer->phones));

        $this->postJson(route('api.v1.webhooks.vehicles.sync'), $data, [
            'Authorization' => config('api.webhook.token')
        ])
            ->assertJson([
                'data' => [
                    'message' => 'Sync vehicle',
                ]
            ])
        ;

        /** @var $customer Customer */
        $customer->refresh();

        $this->assertNotEquals($customer->phone->getValue(), $data['data']['customer']['phone']);
        $this->assertEquals(2, count($customer->phones));

        $this->assertEquals(1, Customer::count());

        /** @var $truck Truck */
        $truck = Truck::query()->where('origin_id', data_get($data, 'data.id'))->first();
        $this->assertEquals($truck->customer_id, $customer->id);
    }

    /** @test */
    public function success_not_create_customer_if_exists_by_email_and_phones()
    {
        $data = ['data' => $this->data];
        $data['data']['customer']['phone'] = '1111111111';

        /** @var $customer Customer */
        $customer = $this->customerBuilder
            ->email($data['data']['customer']['email'])
            ->phones([
                [
                    'number' => '1111111111',
                    'extension' => null
                ]
            ])
            ->create();

        $this->assertNotEquals($customer->phone->getValue(), $data['data']['customer']['phone']);
        $this->assertEquals(1, count($customer->phones));

        $this->postJson(route('api.v1.webhooks.vehicles.sync'), $data, [
            'Authorization' => config('api.webhook.token')
        ])
            ->assertJson([
                'data' => [
                    'message' => 'Sync vehicle',
                ]
            ])
        ;

        /** @var $customer Customer */
        $customer->refresh();

        $this->assertNotEquals($customer->phone->getValue(), $data['data']['customer']['phone']);
        $this->assertEquals(1, count($customer->phones));

        $this->assertEquals(1, Customer::count());

        /** @var $truck Truck */
        $truck = Truck::query()->where('origin_id', data_get($data, 'data.id'))->first();
        $this->assertEquals($truck->customer_id, $customer->id);
    }

    /** @test */
    public function success_not_create_customer_if_exists_by_email_and_phones_as_null()
    {
        $data = ['data' => $this->data];
        $data['data']['customer']['phone'] = '1111111111';

        /** @var $customer Customer */
        $customer = $this->customerBuilder
            ->email($data['data']['customer']['email'])
            ->phones(null)
            ->create();

        $this->assertNotEquals($customer->phone->getValue(), $data['data']['customer']['phone']);
        $this->assertNull($customer->phones);

        $this->postJson(route('api.v1.webhooks.vehicles.sync'), $data, [
            'Authorization' => config('api.webhook.token')
        ])
            ->assertJson([
                'data' => [
                    'message' => 'Sync vehicle',
                ]
            ])
        ;

        /** @var $customer Customer */
        $customer->refresh();

        $this->assertNotEquals($customer->phone->getValue(), $data['data']['customer']['phone']);
        $this->assertEquals(1, count($customer->phones));

        $this->assertEquals(1, Customer::count());

        /** @var $truck Truck */
        $truck = Truck::query()->where('origin_id', data_get($data, 'data.id'))->first();
        $this->assertEquals($truck->customer_id, $customer->id);
    }

    /** @test */
    public function success_update_truck()
    {
        $data = ['data' => $this->data];

        /** @var $customer Customer */
        $customer = $this->customerBuilder->origin_id(data_get($data, 'data.customer.id'))->create();

        $this->assertNotEquals($customer->first_name, data_get($data, 'data.customer.first_name'));
        $this->assertNotEquals($customer->last_name, data_get($data, 'data.customer.last_name'));
        $this->assertNotEquals($customer->email->getValue(), data_get($data, 'data.customer.email'));
        $this->assertNotEquals($customer->phone->getValue(), data_get($data, 'data.customer.phone'));
        $this->assertNotEquals($customer->phone_extension, data_get($data, 'data.customer.phone_extension'));

        /** @var $company Company */
        $company = $this->companyBuilder->id(data_get($data, 'data.company.id'))->create();

        $this->assertNotEquals($company->name, data_get($data, 'data.company.name'));

        /** @var $tag Tag */
        $tag = $this->tagBuilder->origin_id(data_get($data, 'data.tags.0.id'))->create();

        $this->assertNotEquals($tag->name, data_get($data, 'data.tags.0.name'));
        $this->assertNotEquals($tag->color, data_get($data, 'data.tags.0.color'));

        $file_1 = UploadedFile::fake()->createWithContent('info.pdf', 'Some text for file');
        $file_2 = UploadedFile::fake()->createWithContent('info.pdf', 'Some text for file');

        /** @var $truck Truck */
        $truck = $this->truckBuilder->origin_id(data_get($data, 'data.id'))
            ->attachments($file_1, $file_2)
            ->company($company)
            ->customer($customer)
            ->tags($tag)
            ->create();

        $this->assertNotEquals($truck->vin, data_get($data, 'data.vin'));
        $this->assertNotEquals($truck->unit_number, data_get($data, 'data.unit_number'));
        $this->assertNotEquals($truck->make, data_get($data, 'data.make'));
        $this->assertNotEquals($truck->model, data_get($data, 'data.model'));
        $this->assertNotEquals($truck->year, data_get($data, 'data.year'));
        $this->assertNotEquals($truck->color, data_get($data, 'data.color'));
        $this->assertNotEquals($truck->gvwr, data_get($data, 'data.gvwr'));
        $this->assertNotEquals($truck->type, data_get($data, 'data.type'));
        $this->assertNotEquals($truck->license_plate, data_get($data, 'data.license_plate'));
        $this->assertNotEquals($truck->temporary_plate, data_get($data, 'data.temporary_plate'));
        $this->assertNotEquals($truck->notes, data_get($data, 'data.notes'));

        $this->postJson(route('api.v1.webhooks.vehicles.sync'), $data, [
            'Authorization' => config('api.webhook.token')
        ])
            ->assertJson([
                'data' => [
                    'message' => 'Sync vehicle',
                ]
            ])
        ;

        $company->refresh();
        $customer->refresh();
        $tag->refresh();
        $truck->refresh();

        $this->assertEquals($customer->first_name, data_get($data, 'data.customer.first_name'));
        $this->assertEquals($customer->last_name, data_get($data, 'data.customer.last_name'));
        $this->assertEquals($customer->email->getValue(), data_get($data, 'data.customer.email'));
        $this->assertEquals($customer->phone->getValue(), data_get($data, 'data.customer.phone'));
        $this->assertEquals($customer->phone_extension, data_get($data, 'data.customer.phone_extension'));
        $this->assertTrue($customer->from_haulk);

        $this->assertEquals($company->name, data_get($data, 'data.company.name'));

        $this->assertEquals($truck->vin, data_get($data, 'data.vin'));
        $this->assertEquals($truck->unit_number, data_get($data, 'data.unit_number'));
        $this->assertEquals($truck->make, data_get($data, 'data.make'));
        $this->assertEquals($truck->model, data_get($data, 'data.model'));
        $this->assertEquals($truck->year, data_get($data, 'data.year'));
        $this->assertEquals($truck->color, data_get($data, 'data.color'));
        $this->assertEquals($truck->gvwr, data_get($data, 'data.gvwr'));
        $this->assertEquals($truck->type, data_get($data, 'data.type'));
        $this->assertEquals($truck->license_plate, data_get($data, 'data.license_plate'));
        $this->assertEquals($truck->temporary_plate, data_get($data, 'data.temporary_plate'));
        $this->assertEquals($truck->notes, data_get($data, 'data.notes'));

        $this->assertCount(1, $truck->tags);
        $this->assertCount(1, $truck->getAttachments());
    }

    /** @test */
    public function fail_empty_data()
    {
        $this->postJson(route('api.v1.webhooks.vehicles.sync'), [], [
            'Authorization' => config('api.webhook.token')
        ])
            ->assertJsonStructure([
                'errors' => [
                    [
                        'source' => [
                            'parameter'
                        ],
                        'title',
                        'status'
                    ]
                ]
            ])
            ->assertStatus(422)
        ;

    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        $res = $this->postJson(route('api.v1.webhooks.vehicles.sync'), ['data' => $data])
        ;

        self::assertErrorMsg($res, "Wrong webhook auth-token", Response::HTTP_UNAUTHORIZED);
    }
}
