<?php

namespace Tests\Feature\Http\Api\OneC\Warranty;

use App\Enums\Projects\Systems\WarrantyStatus;
use App\Enums\Warranties\WarrantyType;
use App\Events\Warranty\WarrantyRegistrationProcessedEvent;
use App\Listeners\Warranty\WarrantyRegistrationProcessedListener;
use App\Models\Commercial\CommercialProject;
use App\Models\Projects\System;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use App\Models\Warranty\WarrantyRegistration;
use App\Notifications\Warranty\WarrantyStatusChangedNotification;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\TestCase;
use Tests\Traits\Notifications\FakeNotifications;

class CreateTest extends TestCase
{
    use DatabaseTransactions;
    use FakeNotifications;
    use WithFaker;

    protected ProductBuilder $productBuilder;

    protected array $data;

    public function setUp(): void
    {
        parent::setUp();
        $this->productBuilder = resolve(ProductBuilder::class);

        $this->data = [
            'notice' => $this->faker->sentence,
            'warranty_status' => WarrantyStatus::ON_WARRANTY,
            'type' => WarrantyType::COMMERCIAL,
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
    }

    /** @test */
    public function success_create(): void
    {
        Notification::fake();

        $this->loginAsModerator();

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();

        $purchaseDate = CarbonImmutable::now()->addDays(5);
        $installationDate = CarbonImmutable::now()->addDays(5);

        $system = System::factory()->create();
        $commercialProject = CommercialProject::factory()->create(['guid' => $this->faker->uuid]);
        $user = User::factory()->create();

        $data = $this->data;
        $data['product']['purchase_date'] = $purchaseDate->format('Y-m-d');
        $data['product']['installation_date'] = $installationDate->format('Y-m-d');
        $data['serial_numbers'] = [
            [
                'product_guid' => $product_1->guid,
                'serial_number' => $this->faker->postcode,
            ],
            [
                'product_guid' => $product_2->guid,
                'serial_number' => $this->faker->postcode,
            ]
        ];
        $data['system_id'] = $system->id;
        $data['commercial_project_guid'] = $commercialProject->guid;
        $data['user_type'] = User::MORPH_NAME;
        $data['user_guid'] = $user->guid;

        $this->postJson(route('1c.warranty.create', $data))
            ->assertCreated()
            ->assertJson([
                'data' => [
                    'technician_verified' => false,
                    'notice' => data_get($this->data, 'notice'),
                    'warranty_status' => data_get($this->data, 'warranty_status'),
                    'type' => data_get($this->data, 'type'),
                    'user_info' => [
                        'is_user' => true,
                        'first_name' => data_get($this->data, 'user.first_name'),
                        'last_name' => data_get($this->data, 'user.last_name'),
                        'email' => data_get($this->data, 'user.email'),
                        'company_name' => data_get($this->data, 'user.company_name'),
                        'company_address' => data_get($this->data, 'user.company_address'),
                    ],
                    'product_info' => [
//                        'purchase_date' => (string)$purchaseDate->getTimestamp(),
//                        'installation_date' => (string)$installationDate->getTimestamp(),
                        'installer_license_number' => data_get($this->data, 'product.installation_license_number'),
                        'purchase_place' => data_get($this->data, 'product.purchase_place'),
                    ],
                    'address_info' => [
                        'country' => 'US',
                        'state' => 'MI',
                        'city' => data_get($this->data, 'address.city'),
                        'street' => data_get($this->data, 'address.street'),
                        'zip' => data_get($this->data, 'address.zip'),
                    ],
                    'technician' => [
                        'id' => $user->id
                    ],
                    'system' => [
                        'id' => $system->id
                    ],
                ]
            ])
            ->assertJsonCount(2, 'data.units')
        ;

        $this->assertNotificationSentTo(
            data_get($this->data, 'user.email'),
            WarrantyStatusChangedNotification::class
        );
    }

    /** @test */
    public function success_create_with_tech(): void
    {
        $this->loginAsModerator();

        $product_1 = $this->productBuilder->create();

        $purchaseDate = CarbonImmutable::now()->addDays(5);
        $installationDate = CarbonImmutable::now()->addDays(5);

        $system = System::factory()->create();
        $commercialProject = CommercialProject::factory()->create(['guid' => $this->faker->uuid]);
        $user = Technician::factory()->create();

        $data = $this->data;
        $data['product']['purchase_date'] = $purchaseDate->format('Y-m-d');
        $data['product']['installation_date'] = $installationDate->format('Y-m-d');
        $data['serial_numbers'] = [
            [
                'product_guid' => $product_1->guid,
                'serial_number' => $this->faker->postcode,
            ]
        ];
        $data['system_id'] = $system->id;
        $data['commercial_project_guid'] = $commercialProject->guid;
        $data['user_type'] = Technician::MORPH_NAME;
        $data['user_guid'] = $user->guid;
        $data['address']['state'] = 'CA';

        $this->postJson(route('1c.warranty.create', $data))
            ->assertCreated()
            ->assertJson([
                'data' => [
                    'technician_verified' => false,
                    'notice' => data_get($this->data, 'notice'),
                    'warranty_status' => data_get($this->data, 'warranty_status'),
                    'type' => data_get($this->data, 'type'),
                    'user_info' => [
                        'is_user' => true,
                        'first_name' => data_get($this->data, 'user.first_name'),
                        'last_name' => data_get($this->data, 'user.last_name'),
                        'email' => data_get($this->data, 'user.email'),
                        'company_name' => data_get($this->data, 'user.company_name'),
                        'company_address' => data_get($this->data, 'user.company_address'),
                    ],
                    'product_info' => [
                        'purchase_date' => (string)$purchaseDate->getTimestamp(),
                        'installation_date' => (string)$installationDate->getTimestamp(),
                        'installer_license_number' => data_get($this->data, 'product.installation_license_number'),
                        'purchase_place' => data_get($this->data, 'product.purchase_place'),
                    ],
                    'address_info' => [
                        'country' => 'US',
                        'state' => 'CA',
                        'city' => data_get($this->data, 'address.city'),
                        'street' => data_get($this->data, 'address.street'),
                        'zip' => data_get($this->data, 'address.zip'),
                    ],
                    'units' => [
                        [
                            'product_id' => $product_1->id,
                            'product_guid' => $product_1->guid,
                            'product_title' => $product_1->title,
                            'serial_number' => data_get($data, 'serial_numbers.0.serial_number'),
                        ],
                    ],
                    'technician' => [
                        'id' => $user->id
                    ],
                ]
            ])
            ->assertJsonCount(1, 'data.units')
        ;
    }

    /** @test */
    public function success_create_only_required_fields(): void
    {
        Event::fake([WarrantyRegistrationProcessedEvent::class]);

        $this->loginAsModerator();

        $product_1 = $this->productBuilder->create();

        $purchaseDate = CarbonImmutable::now()->addDays(5);
        $installationDate = CarbonImmutable::now()->addDays(5);

        $data = $this->data;
        unset(
            $data['user']['company_name'],
            $data['user']['company_address'],
            $data['notice']
        );
        $data['product']['purchase_date'] = $purchaseDate->format('Y-m-d');
        $data['product']['installation_date'] = $installationDate->format('Y-m-d');
        $data['serial_numbers'] = [
            [
                'product_guid' => $product_1->guid,
                'serial_number' => $this->faker->postcode,
            ]
        ];

        $id = $this->postJson(route('1c.warranty.create', $data))
            ->assertCreated()
            ->assertJson([
                'data' => [
                    'technician_verified' => false,
                    'notice' => null,
                    'warranty_status' => data_get($this->data, 'warranty_status'),
                    'type' => data_get($this->data, 'type'),
                    'user_info' => [
                        'is_user' => true,
                        'first_name' => data_get($this->data, 'user.first_name'),
                        'last_name' => data_get($this->data, 'user.last_name'),
                        'email' => data_get($this->data, 'user.email'),
                        'company_name' => null,
                        'company_address' => null,
                    ],
                    'product_info' => [
                        'purchase_date' => (string)$purchaseDate->getTimestamp(),
                        'installation_date' => (string)$installationDate->getTimestamp(),
                        'installer_license_number' => data_get($this->data, 'product.installation_license_number'),
                        'purchase_place' => data_get($this->data, 'product.purchase_place'),
                    ],
                    'address_info' => [
                        'country' => 'US',
                        'state' => 'MI',
                        'city' => data_get($this->data, 'address.city'),
                        'street' => data_get($this->data, 'address.street'),
                        'zip' => data_get($this->data, 'address.zip'),
                    ],
                    'technician' => null,
                    'system' => null,
                ]
            ])
            ->json('data.id')
        ;

        Event::assertDispatched(function (WarrantyRegistrationProcessedEvent $event) use ($data, $id) {
            return $event->getSerialNumbers() === $data['serial_numbers'] && $event->getWarrantyRegistration()->id === $id;
        });

        Event::assertListening(
            WarrantyRegistrationProcessedEvent::class,
            WarrantyRegistrationProcessedListener::class
        );
    }

    /** @test */
    public function fail_create_exist_sn_another_warranty(): void
    {
        $this->loginAsModerator();

        $sn_1 = $this->faker->postcode;
        $sn_2 = $this->faker->postcode;
        $sn_3 = $this->faker->postcode;
        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();
        $product_3 = $this->productBuilder->create();

        $warranty = WarrantyRegistration::factory()->create();
        $warranty->unitsBySerial()->sync([
            $sn_1 => ['product_id' => $product_1->id],
            $sn_2 => ['product_id' => $product_2->id],
        ]);

        $date = CarbonImmutable::now()->addDays(5);
        $data = $this->data;
        $data['product']['purchase_date'] = $date->format('Y-m-d');
        $data['product']['installation_date'] = $date->format('Y-m-d');
        $data['serial_numbers'] = [
            [
                'product_guid' => $product_1->guid,
                'serial_number' => $sn_1,
            ],
            [
                'product_guid' => $product_2->guid,
                'serial_number' => $sn_2,
            ],
            [
                'product_guid' => $product_3->guid,
                'serial_number' => $sn_3,
            ]
        ];

        $res = $this->postJson(route('1c.warranty.create', $data))
            ->json('data')
        ;

        $this->assertTrue(str_contains($res, $sn_1));
        $this->assertTrue(str_contains($res, $sn_2));
    }
}

