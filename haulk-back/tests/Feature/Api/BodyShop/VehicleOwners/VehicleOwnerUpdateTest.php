<?php

namespace Api\BodyShop\VehicleOwners;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Tags\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class VehicleOwnerUpdateTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_not_update_vehicle_owner_for_unauthorized_users()
    {
        $vehicleOwner = factory(VehicleOwner::class)->create();

        $this->postJson(route('body-shop.vehicle-owners.update', $vehicleOwner))->assertUnauthorized();
    }

    public function test_it_update_vehicle_owner_of_body_shop_by_BS_super_admin()
    {
        /** @var VehicleOwner $vehicleOwner */
        $vehicleOwner = factory(VehicleOwner::class)->create();

        $formRequest = [
            'first_name' => 'Some',
            'last_name' => 'Name',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
            'notes' => 'test notes',
        ];

        $this->assertDatabaseMissing(VehicleOwner::TABLE_NAME, $formRequest);

        $this->loginAsBodyShopSuperAdmin();

        $this->postJson(route('body-shop.vehicle-owners.update', $vehicleOwner), $formRequest)
            ->assertOk();

        $this->assertDatabaseHas(VehicleOwner::TABLE_NAME, $formRequest);
    }

    public function test_it_cat_update_vehicle_owner_with_attachments()
    {
        $this->loginAsBodyShopSuperAdmin();

        /** @var VehicleOwner $vehicleOwner */
        $vehicleOwner = factory(VehicleOwner::class)->create();

        $formRequest = [
            'first_name' => 'Some',
            'last_name' => 'Name',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
            'notes' => 'test notes',
            VehicleOwner::ATTACHMENT_FIELD_NAME => [
                UploadedFile::fake()->image('image1.jpg'),
                UploadedFile::fake()->image('image2.jpg'),
                UploadedFile::fake()->createWithContent('info.txt', 'Some text for file'),
            ],
        ];

        $response = $this->postJson(route('body-shop.vehicle-owners.update', $vehicleOwner), $formRequest)
            ->assertOk();

        $vehicleOwner = $response->json('data');

        $this->assertCount(3, $vehicleOwner[VehicleOwner::ATTACHMENT_COLLECTION_NAME]);
    }

    public function test_it_update_vehicle_owner_with_tags(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        /** @var VehicleOwner $vehicleOwner */
        $vehicleOwner = factory(VehicleOwner::class)->create();

        $tag1 = Tag::factory()->create(['type' => Tag::TYPE_VEHICLE_OWNER, 'carrier_id' => null]);
        $tag2 = Tag::factory()->create(['type' => Tag::TYPE_VEHICLE_OWNER, 'carrier_id' => null]);
        $tag3 = Tag::factory()->create(['type' => Tag::TYPE_VEHICLE_OWNER, 'carrier_id' => null]);

        $vehicleOwner->tags()->sync([$tag1->id]);

        $tags = [
            'tags' => [$tag2->id, $tag3->id],
        ];

        $formRequest = [
            'first_name' => 'Some',
            'last_name' => 'Name',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
            'notes' => 'test notes',
        ];

        $this->assertDatabaseMissing(VehicleOwner::TABLE_NAME, $formRequest);

        $this->postJson(route('body-shop.vehicle-owners.update', $vehicleOwner), $formRequest + $tags)
            ->assertOk();

        $this->assertDatabaseHas(VehicleOwner::TABLE_NAME, $formRequest);

        $this->assertDatabaseHas(
            'taggables',
            [
                'tag_id' => $tag2->id,
                'taggable_id' => $vehicleOwner->id,
                'taggable_type' => VehicleOwner::class,
            ]
        );

        $this->assertDatabaseHas(
            'taggables',
            [
                'tag_id' => $tag3->id,
                'taggable_id' => $vehicleOwner->id,
                'taggable_type' => VehicleOwner::class,
            ]
        );

        $this->assertDatabaseMissing(
            'taggables',
            [
                'tag_id' => $tag1->id,
                'taggable_id' => $vehicleOwner->id,
                'taggable_type' => VehicleOwner::class,
            ]
        );
    }

    public function test_it_update_with_not_unique_data()
    {
        $this->loginAsBodyShopSuperAdmin();

        /** @var VehicleOwner $vehicleOwner */
        $email = 'test@test.com';
        $phone = '1-541-754-3010';
        factory(VehicleOwner::class)->create(['email' => $email, 'phone' => $phone]);

        $vehicleOwner = factory(VehicleOwner::class)->create();

        $formRequest = [
            'first_name' => 'Some',
            'last_name' => 'Name',
            'email' => $vehicleOwner->email,
            'phone' => $vehicleOwner->phone,
            'notes' => 'test notes',
        ];

        $this->postJson(route('body-shop.vehicle-owners.update', $vehicleOwner), $formRequest)
            ->assertOk();

        $formRequest['phone'] = $phone;
        $this->postJson(route('body-shop.vehicle-owners.update', $vehicleOwner), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $formRequest['email'] = $email;
        $this->postJson(route('body-shop.vehicle-owners.update', $vehicleOwner), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $formRequest['phone'] = $vehicleOwner->phone;
        $this->postJson(route('body-shop.vehicle-owners.update', $vehicleOwner), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
