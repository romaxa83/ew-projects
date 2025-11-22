<?php

namespace Api\BodyShop\VehicleOwners;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Tags\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class VehicleOwnerCreateTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_forbidden_to_users_create_for_not_authorized_users()
    {
        $this->postJson(route('body-shop.vehicle-owners.store'), [])->assertUnauthorized();
    }

    public function test_it_create_vehicle_owner_by_not_permited_user()
    {
        $this->loginAsCarrierSuperAdmin();

        $this->postJson(route('body-shop.vehicle-owners.store'), [])->assertForbidden();
    }

    public function test_it_create_vehicle_owner_by_bs_super_admin()
    {
        $this->loginAsBodyShopSuperAdmin();

        $formRequest = [
            'first_name' => 'Some',
            'last_name' => 'Name',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
            'notes' => 'test notes',
        ];

        $this->assertDatabaseMissing(VehicleOwner::TABLE_NAME, $formRequest);

        $this->postJson(route('body-shop.vehicle-owners.store'), $formRequest)
            ->assertCreated();

        $this->assertDatabaseHas(VehicleOwner::TABLE_NAME, $formRequest);
    }

    public function test_it_can_create_vehicle_owner_by_body_shop_admin()
    {
        $this->loginAsBodyShopAdmin();

        $formRequest = [
            'first_name' => 'Some',
            'last_name' => 'Name',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
            'notes' => 'test notes',
        ];

        $this->assertDatabaseMissing(VehicleOwner::TABLE_NAME, $formRequest);

        $this->postJson(route('body-shop.vehicle-owners.store'), $formRequest)
            ->assertCreated();

        $this->assertDatabaseHas(VehicleOwner::TABLE_NAME, $formRequest);
    }

    public function test_it_cat_create_new_vehicle_owner_with_attachments()
    {
        $this->loginAsBodyShopAdmin();

        $formRequest = [
            'first_name' => 'Some',
            'last_name' => 'Name',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
            VehicleOwner::ATTACHMENT_FIELD_NAME => [
                UploadedFile::fake()->image('image1.jpg'),
                UploadedFile::fake()->image('image2.jpg'),
                UploadedFile::fake()->createWithContent('info.txt', 'Some text for file'),
            ],
        ];

        $response = $this->postJson(route('body-shop.vehicle-owners.store'), $formRequest)
            ->assertCreated();

        $vehicleOwner = $response->json('data');

        $this->assertCount(3, $vehicleOwner[VehicleOwner::ATTACHMENT_COLLECTION_NAME]);
    }

    /**
     * @param $attributes
     * @param $expectErrors
     * @dataProvider formSubmitDataProvider
     */
    public function test_it_see_validation_messages($attributes, $expectErrors)
    {
        $this->loginAsBodyShopAdmin();

        $this->assertDatabaseMissing(VehicleOwner::TABLE_NAME, $attributes);
        $this->postJson(route('body-shop.vehicle-owners.store'), $attributes)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                [
                    'errors' => $expectErrors,
                ]
            );

        $this->assertDatabaseMissing(VehicleOwner::TABLE_NAME, $attributes);
    }

    public function formSubmitDataProvider(): array
    {
        $firstName = 'Name';
        $lastName = 'Last';
        $phone = '1-541-754-3010';
        $email = 'chernenko.v@wezom.com.ua';

        return [
            [
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => null,
                    'email' => $email,
                ],
                [
                    [
                        'source' => ['parameter' => 'phone'],
                        'title' => 'The Phone field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ]
            ],
            [
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $phone,
                    'email' => null,
                ],
                [
                    [
                        'source' => ['parameter' => 'email'],
                        'title' => 'The Email field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ]
            ],
            [
                [
                    'first_name' => null,
                    'last_name' => null,
                    'email' => $email,
                    'phone' => $phone,
                ],
                [
                    [
                        'source' => ['parameter' => 'first_name'],
                        'title' => 'The First Name field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ]
            ],
        ];
    }

    public function test_it_create_vehicle_owner_with_tags(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $formRequest = [
            'first_name' => 'Some',
            'last_name' => 'Name',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
            'notes' => 'test notes',
        ];

        $tag1 = Tag::factory()->create(['type' => Tag::TYPE_VEHICLE_OWNER, 'carrier_id' => null]);
        $tag2 = Tag::factory()->create(['type' => Tag::TYPE_VEHICLE_OWNER, 'carrier_id' => null]);

        $tags = [
            'tags' => [$tag1->id, $tag2->id],
        ];

        $this->assertDatabaseMissing(VehicleOwner::TABLE_NAME, $formRequest);

        $response = $this->postJson(route('body-shop.vehicle-owners.store'), $formRequest + $tags)
            ->assertCreated();

        $createdId = $response['data']['id'];

        $this->assertDatabaseHas(VehicleOwner::TABLE_NAME, $formRequest);

        $this->assertDatabaseHas(
            'taggables',
            [
                'tag_id' => $tag1->id,
                'taggable_id' => $createdId,
                'taggable_type' => VehicleOwner::class,
            ]
        );

        $this->assertDatabaseHas(
            'taggables',
            [
                'tag_id' => $tag2->id,
                'taggable_id' => $createdId,
                'taggable_type' => VehicleOwner::class,
            ]
        );
    }

    public function test_create_with_not_unique_data(): void
    {
        $this->loginAsBodyShopAdmin();
        $email = 'test@test.com';
        $phone = '1-541-754-3010';
        factory(VehicleOwner::class)->create(['email' => $email, 'phone' => $phone]);

        $formRequest = [
            'first_name' => 'Some',
            'last_name' => 'Name',
            'email' => $email,
            'phone' => '1-541-754-3010',
            'notes' => 'test notes',
        ];

        $this->postJson(route('body-shop.vehicle-owners.store'), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $formRequest['phone'] = $phone;

        $this->postJson(route('body-shop.vehicle-owners.store'), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $formRequest['email'] = 'test123@test.com';

        $this->postJson(route('body-shop.vehicle-owners.store'), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
