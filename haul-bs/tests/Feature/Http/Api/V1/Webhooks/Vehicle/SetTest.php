<?php

namespace Tests\Feature\Http\Api\V1\Webhooks\Vehicle;

use App\Foundations\Modules\Media\Models\Media;
use App\Models\Companies\Company;
use App\Models\Customers\Customer;
use App\Models\Tags\Tag;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\Builders\Companies\CompanyBuilder;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Tags\TagBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\TestCase;

class SetTest extends TestCase
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
            [
                'vehicle_type' => 'truck',
                'id' => 100,
                'vin' => 'RR22267887JH86',
                'unit_number' => 'RR22',
                'make' => 'Ford',
                'model' => 'S serial',
                'year' => '2020',
                'color' => 'red',
                'gvwr' => 3.9,
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
            ],
            [
                'vehicle_type' => 'truck',
                'id' => 101,
                'vin' => 'RR22267887JH81',
                'unit_number' => 'RR21',
                'make' => 'Ford',
                'model' => 'S serial',
                'year' => '2020',
                'color' => null,
                'gvwr' => 88,
                'type' => 3,
                'license_plate' => 'Z5566T1',
                'temporary_plate' => null,
                'notes' => null,
                'created_at' => CarbonImmutable::now()->timestamp,
                'company' => [
                    'id' => 4,
                    'name' => 'Company name'
                ],
                'customer' => [
                    'id' => 6,
                    'first_name' => 'John 1',
                    'last_name' => 'Doe 1',
                    'phone' => '1399999991',
                    'phone_extension' => '331',
                    'phones' => null,
                    'email' => 'test1@gmail.com'
                ],
                'media' => [],
                'tags' => [],
            ],
            [
                'vehicle_type' => 'trailer',
                'id' => 102,
                'vin' => 'RR22267887JH82',
                'unit_number' => 'RR23',
                'make' => 'Ford',
                'model' => 'S serial',
                'year' => '2022',
                'color' => null,
                'gvwr' => null,
                'type' => null,
                'license_plate' => 'Z5566T3',
                'temporary_plate' => 'JK983',
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
                'media' => [],
                'tags' => [],
            ]
        ];
    }

    /** @test */
    public function success_set()
    {
        $data = $this->data;

        /** @var $tag Tag */
        $tag = $this->tagBuilder
            ->origin_id(data_get($data, '0.tags.0.id'))
            ->create();

        $this->assertNotEquals($tag->name, data_get($data, '0.tags.0.name'));
        $this->assertNotEquals($tag->color, data_get($data, '0.tags.0.color'));

        $this->assertFalse(Truck::query()->where('origin_id', data_get($data, '0.id'))->exists());
        $this->assertFalse(Truck::query()->where('origin_id', data_get($data, '1.id'))->exists());
        $this->assertFalse(Trailer::query()->where('origin_id', data_get($data, '2.id'))->exists());
        $this->assertFalse(Company::query()->where('id', data_get($data, '0.company.id'))->exists());
        $this->assertFalse(Customer::query()->where('origin_id', data_get($data, '0.customer.id'))->exists());
        $this->assertFalse(Customer::query()->where('origin_id', data_get($data, '1.customer.id'))->exists());
        $this->assertFalse(Customer::query()->where('origin_id', data_get($data, '2.customer.id'))->exists());
        $this->assertFalse(Tag::query()->where('origin_id', data_get($data, '0.tags.1.id'))->exists());
        $this->assertFalse(Media::query()->where('file_name', data_get($data, '0.media.0.file_name'))->exists());

        $this->postJson(route('api.v1.webhooks.vehicles.set'), ['data' => $data], [
            'Authorization' => config('api.webhook.token')
        ])
            ->assertJson([
                'data' => [
                    'message' => 'Set data',
                ]
            ])
        ;

        $tag->refresh();

        /** @var $company Company */
        $company = Company::query()->where('id', data_get($data, '0.company.id'))->first();
        $this->assertEquals($company->name, data_get($data, '0.company.name'));

        $this->assertEquals(1, Company::count());

        /** @var $customer Customer */
        $customer = Customer::query()->where('origin_id', data_get($data, '0.customer.id'))->first();
        $this->assertEquals($customer->first_name, data_get($data, '0.customer.first_name'));
        $this->assertEquals($customer->last_name, data_get($data, '0.customer.last_name'));
        $this->assertEquals($customer->email, data_get($data, '0.customer.email'));
        $this->assertEquals($customer->phone, data_get($data, '0.customer.phone'));
        $this->assertEquals($customer->phone_extension, data_get($data, '0.customer.phone_extension'));
        $this->assertEquals($customer->phones, []);
        $this->assertTrue($customer->from_haulk);
        $this->assertTrue($customer->type->isHaulk());

        $this->assertEquals(2, Customer::count());

        /** @var $truck Truck */
        $truck = Truck::query()->where('origin_id', data_get($data, '0.id'))->first();
        $this->assertEquals($truck->vin, data_get($data, '0.vin'));
        $this->assertEquals($truck->unit_number, data_get($data, '0.unit_number'));
        $this->assertEquals($truck->make, data_get($data, '0.make'));
        $this->assertEquals($truck->model, data_get($data, '0.model'));
        $this->assertEquals($truck->year, data_get($data, '0.year'));
        $this->assertEquals($truck->color, data_get($data, '0.color'));
        $this->assertEquals($truck->gvwr, data_get($data, '0.gvwr'));
        $this->assertEquals($truck->type, data_get($data, '0.type'));
        $this->assertEquals($truck->license_plate, data_get($data, '0.license_plate'));
        $this->assertEquals($truck->temporary_plate, data_get($data, '0.temporary_plate'));
        $this->assertEquals($truck->notes, data_get($data, '0.notes'));
        $this->assertEquals($truck->company_id, $company->id);
        $this->assertEquals($truck->customer_id, $customer->id);

        $this->assertCount(0, $truck->tags);
        $this->assertCount(1, $truck->getAttachments());
        $this->assertEquals($truck->getAttachments()[0]->origin_id, data_get($data, '0.media.0.id'));

//        $tag_1 = $truck->tags->where('origin_id', data_get($data, '0.tags.0.id'))->first();
//        $tag_2 = $truck->tags->where('origin_id', data_get($data, '0.tags.1.id'))->first();
//
//        $this->assertEquals($tag_1->name, data_get($data, '0.tags.0.name'));
//        $this->assertEquals($tag_1->color, data_get($data, '0.tags.0.color'));
//        $this->assertEquals($tag_2->name, data_get($data, '0.tags.1.name'));
//        $this->assertEquals($tag_2->color, data_get($data, '0.tags.1.color'));
//
//        $this->assertEquals($tag->name, data_get($data, '0.tags.0.name'));
//        $this->assertEquals($tag->color, data_get($data, '0.tags.0.color'));

        $this->assertTrue(Truck::query()->where('origin_id', data_get($data, '1.id'))->exists());
        $this->assertTrue(Trailer::query()->where('origin_id', data_get($data, '2.id'))->exists());

        $this->assertTrue(Customer::query()->where('origin_id', data_get($data, '0.customer.id'))->exists());
        $this->assertTrue(Customer::query()->where('origin_id', data_get($data, '1.customer.id'))->exists());
        $this->assertTrue(Customer::query()->where('origin_id', data_get($data, '2.customer.id'))->exists());
    }

    /** @test */
    public function success_set_if_exist_data()
    {
        $data = $this->data;

        $originIdTruck2 = data_get($data, '0.id') + 100;

        /** @var $customer Customer */
        $customer = $this->customerBuilder->origin_id(data_get($data, '0.customer.id'))->create();

        /** @var $company Company */
        $company = $this->companyBuilder->id(data_get($data, '0.company.id'))->create();

        $file_1 = UploadedFile::fake()->createWithContent('info.pdf', 'Some text for file');
        $file_2 = UploadedFile::fake()->createWithContent('info.pdf', 'Some text for file');

        /** @var $truck_1 Truck */
        $truck_1 = $this->truckBuilder
            ->delete()
            ->origin_id(data_get($data, '0.id'))
            ->create();
        $truck_2 = $this->truckBuilder
            ->delete()
            ->origin_id($originIdTruck2)
            ->create();
        /** @var $trailer_1 Trailer */
        $trailer_1 = $this->trailerBuilder
            ->delete()
            ->attachments($file_1, $file_2)
            ->origin_id(data_get($data, '2.id'))
            ->create();

        $this->assertFalse(Truck::query()->where('origin_id', data_get($data, '0.id'))->exists());
        $this->assertTrue(Truck::query()->withTrashed()->where('origin_id', data_get($data, '0.id'))->exists());

        $this->assertFalse(Truck::query()->where('origin_id', $originIdTruck2)->exists());
        $this->assertTrue(Truck::query()->withTrashed()->where('origin_id', $originIdTruck2)->exists());

        $this->assertFalse(Truck::query()->where('origin_id', data_get($data, '1.id'))->exists());

        $this->assertFalse(Trailer::query()->where('origin_id', data_get($data, '2.id'))->exists());
        $this->assertTrue(Trailer::query()->withTrashed()->where('origin_id', data_get($data, '2.id'))->exists());

        $this->assertNotEquals($company->name, data_get($data, '0.company.name'));

        $this->assertNotEquals($customer->first_name, data_get($data, '0.customer.first_name'));
        $this->assertNotEquals($customer->last_name, data_get($data, '0.customer.last_name'));
        $this->assertNotEquals($customer->email->getValue(), data_get($data, '0.customer.email'));
        $this->assertNotEquals($customer->phone->getValue(), data_get($data, '0.customer.phone'));

        $this->assertFalse(Customer::query()->where('origin_id', data_get($data, '1.customer.id'))->exists());
        $this->assertTrue(Customer::query()->where('origin_id', data_get($data, '2.customer.id'))->exists());

        $this->postJson(route('api.v1.webhooks.vehicles.set'), ['data' => $data], [
            'Authorization' => config('api.webhook.token')
        ])
            ->assertJson([
                'data' => [
                    'message' => 'Set data',
                ]
            ])
        ;

        $company->refresh();
        $this->assertEquals(1, Company::count());
        $this->assertEquals($company->name, data_get($data, '0.company.name'));

        $customer->refresh();
        $this->assertEquals($customer->first_name, data_get($data, '0.customer.first_name'));
        $this->assertEquals($customer->last_name, data_get($data, '0.customer.last_name'));
        $this->assertEquals($customer->email->getValue(), data_get($data, '0.customer.email'));
        $this->assertEquals($customer->phone->getValue(), data_get($data, '0.customer.phone'));

        /** @var $truck Truck */
        $truck = Truck::query()->where('origin_id', data_get($data, '0.id'))->first();
        $this->assertEquals($truck->id, $truck_1->id);
        $this->assertEquals($truck->vin, data_get($data, '0.vin'));
        $this->assertEquals($truck->unit_number, data_get($data, '0.unit_number'));
        $this->assertEquals($truck->make, data_get($data, '0.make'));
        $this->assertEquals($truck->model, data_get($data, '0.model'));
        $this->assertEquals($truck->year, data_get($data, '0.year'));
        $this->assertEquals($truck->color, data_get($data, '0.color'));
        $this->assertEquals($truck->gvwr, data_get($data, '0.gvwr'));
        $this->assertEquals($truck->type, data_get($data, '0.type'));
        $this->assertEquals($truck->license_plate, data_get($data, '0.license_plate'));
        $this->assertEquals($truck->temporary_plate, data_get($data, '0.temporary_plate'));
        $this->assertEquals($truck->notes, data_get($data, '0.notes'));

        $this->assertTrue(Truck::query()->where('origin_id', data_get($data, '1.id'))->exists());

        $this->assertFalse(Truck::query()->where('origin_id', $originIdTruck2)->exists());
        $this->assertTrue(Truck::query()->withTrashed()->where('origin_id', $originIdTruck2)->exists());

        /** @var $trailer Trailer */
        $trailer = Trailer::query()->where('origin_id', data_get($data, '2.id'))->first();

        $this->assertEquals($trailer->id, $trailer_1->id);
        $this->assertCount(0, $trailer_1->getAttachments());
    }

    /** @test */
    public function fail_empty_data()
    {
        $data = [];

        $this->postJson(route('api.v1.webhooks.vehicles.set'), ['data' => $data], [
            'Authorization' => config('api.webhook.token')
        ])
            ->assertJson([
                'data' => [
                    'message' => 'Not set data, empty',
                ]
            ])
        ;

    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        $res = $this->postJson(route('api.v1.webhooks.vehicles.set'), ['data' => $data])
        ;

        self::assertErrorMsg($res, "Wrong webhook auth-token", Response::HTTP_UNAUTHORIZED);
    }
}
