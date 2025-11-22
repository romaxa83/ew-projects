<?php

namespace Tests\Feature\Http\Api\OneC\Warranty;

use App\Enums\Projects\Systems\WarrantyStatus;
use App\Enums\Warranties\WarrantyType;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Commercial\CommercialProject;
use App\Models\Locations\State;
use App\Models\Warranty\Deleted\WarrantyAddressDeleted;
use App\Models\Warranty\Deleted\WarrantyRegistrationDeleted;
use App\Models\Warranty\Deleted\WarrantyRegistrationUnitPivotDeleted;
use App\Models\Warranty\WarrantyInfo\WarrantyAddress;
use App\Models\Warranty\WarrantyRegistration;
use App\Models\Warranty\WarrantyRegistrationUnitPivot;
use App\Notifications\Warranty\WarrantyStatusChangedNotification;
use App\Traits\SimpleHasher;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\Notifications\FakeNotifications;

class WarrantyRegistrationControllerTest extends TestCase
{
    use DatabaseTransactions;
    use FakeNotifications;
    use WithFaker;

    /** @test */
    public function index(): void
    {
        $this->loginAsModerator();

        $warranty = $this->makeWarranty(WarrantyStatus::PENDING());

        $this->getJson(route('1c.warranty.index'))
            ->assertOk()
            ->assertJson([
                "data" => [
                    [
                        "id" => $warranty->id,
                        "technician_verified" => $warranty->technician_verified,
                        "warranty_status" => $warranty->warranty_status->value,
                        "type" => $warranty->type,
                        "notice" => $warranty->notice,
                        "created_at" => $warranty->created_at->timestamp,
                        "technician" => [
                            'id' => $warranty->member->id,
                            'email' => (string)$warranty->member->email,
                            'phone' => (string)$warranty->member->phone,
                            'guid' => $warranty->member->guid,
                        ],
                        "units" => [
                            [
                                'product_id' => $warranty->units[0]->id,
                            ]
                        ],
                        "system" => [
                            'id' => $warranty->system->id,
                            'warranty_status' => $warranty->system->warranty_status,
                            'name' => $warranty->system->name,
                            'description' => $warranty->system->description,
                            'created_at' => $warranty->system->created_at->timestamp,
                            "project" => [
                                'id' => $warranty->system->project->id,
                                'name' => $warranty->system->project->name,
                                'created_at' => $warranty->system->project->created_at->timestamp
                            ]
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function index_some_models(): void
    {
        $this->loginAsModerator();

        $this->makeWarranty(WarrantyStatus::PENDING());
        $this->makeWarranty(WarrantyStatus::PENDING());
        $this->makeWarranty(WarrantyStatus::PENDING());

        $this->getJson(route('1c.warranty.index'))
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function some_models_with_deleted(): void
    {
        $this->loginAsModerator();

        $warranty_1 = $this->makeWarranty(WarrantyStatus::PENDING());
        $warranty_2 =  $this->makeWarrantyDeleted(WarrantyStatus::DELETE());

        $this->getJson(route('1c.warranty.index'))
            ->assertOk()
            ->assertJson([
                'data' => [
                    ['id' => $warranty_1->id],
                    ['id' => $warranty_2->id],
                ]
            ])
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function index_some_models_with_deleted_filter_by_status(): void
    {
        $this->loginAsModerator();

        $warranty_1 = $this->makeWarranty(WarrantyStatus::PENDING());
        $warranty_2 = $this->makeWarranty(WarrantyStatus::DENIED());
        $warranty_3 =  $this->makeWarrantyDeleted(WarrantyStatus::DELETE());

        $this->getJson(route('1c.warranty.index', ['warranty_status' => 'denied']))
            ->assertOk()
            ->assertJson([
                'data' => [
                    ['id' => $warranty_2->id],
                ]
            ])
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function index_some_models_with_deleted_filter_by_status_delete(): void
    {
        $this->loginAsModerator();

        $warranty_1 = $this->makeWarranty(WarrantyStatus::PENDING());
        $warranty_2 = $this->makeWarranty(WarrantyStatus::DENIED());
        $warranty_3 =  $this->makeWarrantyDeleted(WarrantyStatus::DELETE());
        $warranty_4 =  $this->makeWarrantyDeleted(WarrantyStatus::DELETE());

        $this->getJson(route('1c.warranty.index', ['warranty_status' => 'delete']))
            ->assertOk()
            ->assertJson([
                'data' => [
                    ['id' => $warranty_3->id],
                    ['id' => $warranty_4->id],
                ]
            ])
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function index_some_models_with_deleted_filter_per_page(): void
    {
        $this->loginAsModerator();

        $warranty_1 = $this->makeWarranty(WarrantyStatus::PENDING());
        $warranty_2 = $this->makeWarranty(WarrantyStatus::DELETE());
        $warranty_3 =  $this->makeWarrantyDeleted(WarrantyStatus::DELETE());
        $warranty_4 =  $this->makeWarrantyDeleted(WarrantyStatus::DELETE());

        $this->getJson(route('1c.warranty.index', ['per_page' => 2]))
            ->assertOk()
            ->assertJson([
                'data' => [
                    ['id' => $warranty_1->id],
                    ['id' => $warranty_2->id],
                ]
            ])
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function index_some_models_with_deleted_filter_per_page_and_two_page(): void
    {
        $this->loginAsModerator();

        $warranty_1 = $this->makeWarranty(WarrantyStatus::PENDING());
        $warranty_2 = $this->makeWarranty(WarrantyStatus::DELETE());
        $warranty_3 =  $this->makeWarrantyDeleted(WarrantyStatus::DELETE());
        $warranty_4 =  $this->makeWarrantyDeleted(WarrantyStatus::DELETE());

        $this->getJson(route('1c.warranty.index', ['per_page' => 2, 'page' => 2]))
            ->assertOk()
            ->assertJson([
                'data' => [
                    ['id' => $warranty_3->id],
                    ['id' => $warranty_4->id],
                ]
            ])
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function index_some_models_with_deleted_filter_per_page_and_three_page(): void
    {
        $this->loginAsModerator();

        $warranty_1 = $this->makeWarranty(WarrantyStatus::PENDING());
        $warranty_2 = $this->makeWarranty(WarrantyStatus::DELETE());
        $warranty_3 =  $this->makeWarrantyDeleted(WarrantyStatus::DELETE());
        $warranty_4 =  $this->makeWarrantyDeleted(WarrantyStatus::DELETE());
        $warranty_5 =  $this->makeWarrantyDeleted(WarrantyStatus::DELETE());

        $this->getJson(route('1c.warranty.index', ['per_page' => 2, 'page' => 3]))
            ->assertOk()
            ->assertJson([
                'data' => [
                    ['id' => $warranty_5->id],
                ]
            ])
            ->assertJsonCount(1, 'data');
    }

    public function test_list_pending_warranties(): void
    {
        $this->loginAsModerator();

        WarrantyRegistration::factory()
            ->times(3)
            ->voided()
            ->create();

        $this->makeWarranty(WarrantyStatus::PENDING());

        $this->getJson(route('1c.warranty.pending'))
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    protected function makeWarranty(WarrantyStatus $status): WarrantyRegistration
    {
        $warranty = WarrantyRegistration::factory()
            ->status($status)
            ->create();

        WarrantyAddress::factory()->create([
            'warranty_id' => $warranty->id
        ]);

        $product = Product::factory()
            ->has(
                ProductSerialNumber::factory()->state(['serial_number' => $serial = $this->faker->postcode]),
                'serialNumbers'
            )
            ->create();

        WarrantyRegistrationUnitPivot::query()
            ->insert(
                [
                    'warranty_registration_id' => $warranty->id,
                    'product_id' => $product->id,
                    'serial_number' => $serial
                ]
            );

        return $warranty;
    }

    protected function makeWarrantyDeleted(WarrantyStatus $status): WarrantyRegistrationDeleted
    {
        $warranty = WarrantyRegistrationDeleted::factory()
            ->status($status)
            ->create();

        WarrantyAddressDeleted::factory()->create([
            'warranty_id' => $warranty->id
        ]);

        $product = Product::factory()
            ->has(
                ProductSerialNumber::factory()->state(['serial_number' => $serial = $this->faker->postcode]),
                'serialNumbers'
            )
            ->create();

        WarrantyRegistrationUnitPivotDeleted::query()
            ->insert(
                [
                    'warranty_registration_deleted_id' => $warranty->id,
                    'product_id' => $product->id,
                    'serial_number' => $serial
                ]
            );

        return $warranty;
    }

    public function test_process_warranty(): void
    {
        Notification::fake();

        $this->loginAsModerator();

        $warranty = $this->makeWarranty(WarrantyStatus::PENDING());

        $unit = $warranty->unitsPivot->first();

        $warranty->system->units()->sync(
            [
                $serialNumbers = [
                    'product_id' => $unit->product_id,
                    'serial_number' => $unit->serial_number
                ]
            ]
        );

        $serialNumbers['product_guid'] = $unit->product->guid;
        unset($serialNumbers['product_id']);

        self::assertTrue($warranty->warranty_status->isPending());
        self::assertTrue($warranty->system->warranty_status->notRegistered());

        $this->postJson(
            route(
                '1c.warranty.process',
                [
                    'warranty' => $warranty->id,
                    'notice' => 'Some notice about the warranty',
                    'warranty_status' => WarrantyStatus::ON_WARRANTY,
                    'serial_numbers' => [$serialNumbers]
                ]
            )
        )
            ->assertOk();

        $warranty->refresh();

        self::assertTrue($warranty->warranty_status->onWarranty());
        self::assertTrue($warranty->system->warranty_status->onWarranty());

        $this->assertNotificationSentTo(
            $warranty->user_info->email,
            WarrantyStatusChangedNotification::class
        );
    }

    public function test_process_warranty_with_user_mistaken_serial(): void
    {
        $this->loginAsModerator();

        $warranty = $this->makeWarranty(WarrantyStatus::PENDING());

        $unit = $warranty->unitsPivot->first();

        $warranty->system->units()->sync(
            [
                $serialNumbers = [
                    'product_id' => $unit->product_id,
                    'serial_number' => $newSN = 'changed_serial_number'
                ]
            ]
        );

        $serialNumbers['product_guid'] = $unit->product->guid;
        unset($serialNumbers['product_id']);

        self::assertTrue($warranty->warranty_status->isPending());
        self::assertTrue($warranty->system->warranty_status->notRegistered());

        $this->assertDatabaseMissing(
            ProductSerialNumber::TABLE,
            [
                'serial_number' => $newSN
            ]
        );

        $this->postJson(
            route(
                '1c.warranty.process',
                [
                    'warranty' => $warranty->id,
                    'warranty_status' => WarrantyStatus::ON_WARRANTY,
                    'serial_numbers' => [$serialNumbers]
                ]
            )
        )
            ->assertOk();

        $this->assertDatabaseHas(
            ProductSerialNumber::TABLE,
            [
                'serial_number' => $newSN
            ]
        );

        $warranty->refresh();

        self::assertTrue($warranty->warranty_status->onWarranty());
        self::assertTrue($warranty->system->warranty_status->onWarranty());
    }

    public function test_process_warranty_with_one_product_but_many_serial_numbers(): void
    {
        $this->loginAsModerator();
        $warranty = WarrantyRegistration::factory()->pending()->create();
        WarrantyAddress::factory()->create([
            'warranty_id' => $warranty->id
        ]);

        $product = Product::factory()
            ->has(
                ProductSerialNumber::factory()->times(5),
                'serialNumbers'
            )
            ->create();

        $serials = $product->serialNumbers->toArray();

        foreach ($serials as &$serial) {
            unset($serial['product_id']);

            $serial['product_guid'] = $product->guid;
        }

        unset($serial);

        self::assertTrue($warranty->warranty_status->isPending());

        $this->assertDatabaseCount(WarrantyRegistrationUnitPivot::TABLE, 0);

        $this->postJson(
            route(
                '1c.warranty.process',
                [
                    'warranty' => $warranty->id,
                    'warranty_status' => WarrantyStatus::ON_WARRANTY,
                    'serial_numbers' => $serials
                ]
            )
        )
            ->assertOk();

        $this->assertDatabaseCount(WarrantyRegistrationUnitPivot::TABLE, 5);

        $warranty->refresh();

        self::assertTrue($warranty->warranty_status->onWarranty());
    }

    /** @test */
    public function process_delete_warranty(): void
    {
        $this->loginAsModerator();

        $project = CommercialProject::factory()->create();
        /** @var $warranty WarrantyRegistration */
        $warranty = $this->makeWarranty(WarrantyStatus::PENDING());
        $warranty->update(['commercial_project_id' => $project->id]);
        $warranty->load(['address', 'unitsPivot']);

        $unit = $warranty->unitsPivot->first();

        $warranty->system->units()->sync(
            [
                $serialNumbers = [
                    'product_id' => $unit->product_id,
                    'serial_number' => $unit->serial_number
                ]
            ]
        );

        $serialNumbers['product_guid'] = $unit->product->guid;
        unset($serialNumbers['product_id']);

        $warrantyID = $warranty->id;

        self::assertTrue($warranty->warranty_status->isPending());

        $this->postJson(
            route(
                '1c.warranty.process',
                [
                    'warranty' => $warranty->id,
                    'notice' => 'Some notice about the warranty',
                    'warranty_status' => WarrantyStatus::DELETE,
                    'serial_numbers' => [$serialNumbers]
                ]
            )
        );

        $warrantyDeleted = WarrantyRegistrationDeleted::query()->where('id', $warrantyID)->first();
        $this->assertEquals($warrantyDeleted->warranty_status, WarrantyStatus::DELETE);
        $this->assertEquals($warrantyDeleted->type, $warranty->type);
        $this->assertEquals($warrantyDeleted->notice, $warranty->notice);
        $this->assertEquals($warrantyDeleted->member_type, $warranty->member_type);
        $this->assertEquals($warrantyDeleted->member_id, $warranty->member_id);
        $this->assertEquals($warrantyDeleted->system_id, $warranty->system_id);
        $this->assertEquals($warrantyDeleted->commercial_project_id, $warranty->commercial_project_id);
        $this->assertEquals($warrantyDeleted->user_info, $warranty->user_info);
        $this->assertEquals($warrantyDeleted->product_info, $warranty->product_info);

        $this->assertTrue($warrantyDeleted->address instanceof WarrantyAddressDeleted);
        $this->assertEquals($warrantyDeleted->address->country_id, $warranty->address->country_id);
        $this->assertEquals($warrantyDeleted->address->state_id, $warranty->address->state_id);
        $this->assertEquals($warrantyDeleted->address->city, $warranty->address->city);
        $this->assertEquals($warrantyDeleted->address->street, $warranty->address->street);
        $this->assertEquals($warrantyDeleted->address->zip, $warranty->address->zip);

        foreach ($warrantyDeleted->unitsPivot as $unitPivot){
            $this->assertTrue($unitPivot instanceof WarrantyRegistrationUnitPivotDeleted);
            $this->assertEquals(
                $unitPivot->product_id,
                $warranty->unitsPivot->where('warranty_registration_id', $unitPivot->warranty_registration_deleted_id)->first()->product_id
            );
            $this->assertEquals(
                $unitPivot->serial_number,
                $warranty->unitsPivot->where('warranty_registration_id', $unitPivot->warranty_registration_deleted_id)->first()->serial_number
            );
        }

        self::assertNull(WarrantyRegistration::query()->where('id', $warrantyID)->first());
    }

    /** @test */
    public function update_warranty_all_fields(): void
    {
        Notification::fake();

        $this->loginAsModerator();

        $warranty = $this->makeWarranty(WarrantyStatus::PENDING());

        $unit = $warranty->unitsPivot->first();

        $warranty->system->units()->sync(
            [
                $serialNumbers = [
                    'product_id' => $unit->product_id,
                    'serial_number' => $unit->serial_number
                ]
            ]
        );

        $serialNumbers['product_guid'] = $unit->product->guid;
        unset($serialNumbers['product_id']);

        self::assertTrue($warranty->warranty_status->isPending());
        self::assertTrue($warranty->system->warranty_status->notRegistered());
        $state = State::query()->where('short_name', 'MI')->first();

        $data = [
            'warranty' => $warranty->id,
            'notice' => $warranty->id,
            'warranty_status' => WarrantyStatus::ON_WARRANTY,
            'type' => WarrantyType::COMMERCIAL,
            'serial_numbers' => [$serialNumbers],
            'user' => [
                'email' => $this->faker->safeEmail,
                'first_name' => $this->faker->name,
                'last_name' => $this->faker->name,
                'company_name' => $this->faker->company,
                'company_address' => $this->faker->streetAddress,
            ],
            'product' => [
                'purchase_date' => CarbonImmutable::now()->addDays(5)->format('Y-m-d'),
                'purchase_place' => $this->faker->name,
                'installation_date' => CarbonImmutable::now()->addDays(5)->format('Y-m-d'),
                'installation_license_number' => $this->faker->creditCardNumber,
            ],
            'address' => [
                'state' => 'MI',
                'city' => $this->faker->city,
                'street' => $this->faker->streetName,
                'zip' => $this->faker->postcode,
            ]
        ];

        $this->assertNotEquals($warranty->type, data_get($data, 'type'));

        $this->assertNotEquals($warranty->user_info->email, data_get($data, 'user.email'));
        $this->assertNotEquals($warranty->user_info->first_name, data_get($data, 'user.first_name'));
        $this->assertNotEquals($warranty->user_info->last_name, data_get($data, 'user.last_name'));
        $this->assertNotEquals($warranty->user_info->company_name, data_get($data, 'user.company_name'));
        $this->assertNotEquals($warranty->user_info->company_address, data_get($data, 'user.company_address'));

        $this->assertNotEquals($warranty->product_info->purchase_date, data_get($data, 'product.purchase_date'));
        $this->assertNotEquals($warranty->product_info->purchase_place, data_get($data, 'product.purchase_place'));
        $this->assertNotEquals($warranty->product_info->installation_date, data_get($data, 'product.installation_date'));
        $this->assertNotEquals($warranty->product_info->installer_license_number, data_get($data, 'product.installation_license_number'));

        $this->assertNotEquals($warranty->address->state_id, $state->id);
        $this->assertNotEquals($warranty->address->city, data_get($data, 'address.city'));
        $this->assertNotEquals($warranty->address->street, data_get($data, 'address.street'));
        $this->assertNotEquals($warranty->address->zip, data_get($data, 'address.zip'));

        $this->postJson(route('1c.warranty.process', $data))
            ->assertOk();

        $warranty->refresh();

        self::assertTrue($warranty->warranty_status->onWarranty());
        self::assertTrue($warranty->system->warranty_status->onWarranty());

        $this->assertEquals($warranty->type, data_get($data, 'type'));

        $this->assertEquals($warranty->user_info->email, data_get($data, 'user.email'));
        $this->assertEquals($warranty->user_info->first_name, data_get($data, 'user.first_name'));
        $this->assertEquals($warranty->user_info->last_name, data_get($data, 'user.last_name'));
        $this->assertEquals($warranty->user_info->company_name, data_get($data, 'user.company_name'));
        $this->assertEquals($warranty->user_info->company_address, data_get($data, 'user.company_address'));

        $this->assertEquals($warranty->product_info->purchase_date, data_get($data, 'product.purchase_date'));
        $this->assertEquals($warranty->product_info->purchase_place, data_get($data, 'product.purchase_place'));
        $this->assertEquals($warranty->product_info->installation_date, data_get($data, 'product.installation_date'));
        $this->assertEquals($warranty->product_info->installer_license_number, data_get($data, 'product.installation_license_number'));

        $this->assertEquals($warranty->address->state_id, $state->id);
        $this->assertEquals($warranty->address->city, data_get($data, 'address.city'));
        $this->assertEquals($warranty->address->street, data_get($data, 'address.street'));
        $this->assertEquals($warranty->address->zip, data_get($data, 'address.zip'));

        $this->assertNotificationSentTo(
            $warranty->user_info->email,
            WarrantyStatusChangedNotification::class
        );
    }

    /** @test */
    public function update_warranty_some_fields(): void
    {
        Notification::fake();

        $this->loginAsModerator();

        $warranty = $this->makeWarranty(WarrantyStatus::PENDING());

        $unit = $warranty->unitsPivot->first();

        $warranty->system->units()->sync(
            [
                $serialNumbers = [
                    'product_id' => $unit->product_id,
                    'serial_number' => $unit->serial_number
                ]
            ]
        );

        $serialNumbers['product_guid'] = $unit->product->guid;
        unset($serialNumbers['product_id']);

        self::assertTrue($warranty->warranty_status->isPending());
        self::assertTrue($warranty->system->warranty_status->notRegistered());

        $data = [
            'warranty' => $warranty->id,
            'notice' => $warranty->id,
            'warranty_status' => WarrantyStatus::ON_WARRANTY,
            'serial_numbers' => [$serialNumbers],
            'user' => [
                'email' => $this->faker->safeEmail,
            ],
            'product' => [
                'purchase_place' => $this->faker->name,
            ],
        ];

        $this->postJson(route('1c.warranty.process', $data))
            ->assertOk();

        $warranty->refresh();

        self::assertTrue($warranty->warranty_status->onWarranty());
        self::assertTrue($warranty->system->warranty_status->onWarranty());


        $this->assertEquals($warranty->user_info->email, data_get($data, 'user.email'));

        $this->assertEquals($warranty->product_info->purchase_place, data_get($data, 'product.purchase_place'));

        $this->assertNotificationSentTo(
            $warranty->user_info->email,
            WarrantyStatusChangedNotification::class
        );
    }

    /** @test */
    public function nothing_update(): void
    {
        Notification::fake();

        $this->loginAsModerator();

        $warranty = $this->makeWarranty(WarrantyStatus::ON_WARRANTY());

        $unit = $warranty->unitsPivot->first();

        $warranty->system->units()->sync(
            [
                $serialNumbers = [
                    'product_id' => $unit->product_id,
                    'serial_number' => $unit->serial_number
                ]
            ]
        );

        $serialNumbers['product_guid'] = $unit->product->guid;
        unset($serialNumbers['product_id']);

        self::assertTrue($warranty->warranty_status->onWarranty());

        $warranty->setHash(
            SimpleHasher::hash($warranty->getDataForHash([$serialNumbers]))
        );

        $data = [
            'warranty' => $warranty->id,
            'warranty_status' => $warranty->warranty_status->value,
            'serial_numbers' => [$serialNumbers],
        ];

        $this->postJson(route('1c.warranty.process', $data))
            ->assertOk();

        $warranty->refresh();

        self::assertTrue($warranty->warranty_status->onWarranty());

        $this->assertNotificationNotSentTo(
            $warranty->user_info->email,
            WarrantyStatusChangedNotification::class
        );
    }

    /** @test */
    public function fail_update_warranty_wrong_type(): void
    {
        $this->loginAsModerator();

        $warranty = $this->makeWarranty(WarrantyStatus::PENDING());

        $unit = $warranty->unitsPivot->first();

        $warranty->system->units()->sync(
            [
                $serialNumbers = [
                    'product_id' => $unit->product_id,
                    'serial_number' => $unit->serial_number
                ]
            ]
        );

        $serialNumbers['product_guid'] = $unit->product->guid;
        unset($serialNumbers['product_id']);

        $data = [
            'warranty' => $warranty->id,
            'type' => 'wrong',
            'serial_numbers' => [$serialNumbers],
        ];

        $this->postJson(route('1c.warranty.process', $data))
            ->assertJson([
                "errors" => [
                    [
                        "key" => 'type',
                        "messages" => ["The selected type is invalid."],
                    ]
                ]
            ]);
    }
}
