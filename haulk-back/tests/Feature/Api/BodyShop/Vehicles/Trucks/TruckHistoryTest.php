<?php

namespace Api\BodyShop\Vehicles\Trucks;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Vehicles\Comments\TruckComment;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TruckHistoryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_on_vehicle_create(): void
    {
        $this->loginAsBodyShopAdmin();

        $response = $this->postJson(route('body-shop.trucks.store'), [
            'vin' => 'DFDFDF3234234',
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'type' => Vehicle::VEHICLE_TYPE_COUPE_2,
            'license_plate' => 'SD34343',
            'notes' => 'test notes',
            'owner_id' => (factory(VehicleOwner::class)->create())->id,
            Vehicle::ATTACHMENT_FIELD_NAME => [
                UploadedFile::fake()->image('image1.jpg'),
                UploadedFile::fake()->image('image2.png'),
            ],
        ])
            ->assertCreated();

        $this->getJson(route('body-shop.trucks.history', $response['data']['id']))
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson(route('body-shop.trucks.history-detailed', $response['data']['id']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(11, 'data.0.histories');
    }

    public function test_on_vehicle_update(): void
    {
        $this->loginAsBodyShopAdmin();

        $vehicle = factory(Truck::class)->create(['carrier_id' => null]);

        $this->postJson(route('body-shop.trucks.update', $vehicle), [
            'vin' => 'DFDFDF3234234',
            'unit_number' => 'df763',
            'make' => 'BMW',
            'model' => 'X5',
            'year' => '2022',
            'type' => Vehicle::VEHICLE_TYPE_ATV,
            'license_plate' => 'SD34343',
            'notes' => 'test notes',
            'owner_id' => (factory(VehicleOwner::class)->create())->id,
            Vehicle::ATTACHMENT_FIELD_NAME => [
                UploadedFile::fake()->image('image1.jpg'),
                UploadedFile::fake()->image('image2.png'),
            ],
        ])
            ->assertOk();

        $vehicle->refresh();

        $this->getJson(route('body-shop.trucks.history', $vehicle))
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson(route('body-shop.trucks.history-detailed', $vehicle))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(11, 'data.0.histories');

        $this->postJson(route('body-shop.trucks.update', $vehicle), [
            'vin' => 'DFDFDF3234234',
            'unit_number' => 'df763',
            'make' => 'BMW',
            'model' => 'X5',
            'year' => '2022',
            'type' => Vehicle::VEHICLE_TYPE_ATV,
            'license_plate' => 'SD34343',
            'notes' => 'test notes',
            'owner_id' => $vehicle->customer_id,
            Vehicle::ATTACHMENT_FIELD_NAME => [
                UploadedFile::fake()->image('image45.jpg'),
            ],
        ])
            ->assertOk();

        $this->getJson(route('body-shop.trucks.history-detailed', $vehicle))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonCount(1, 'data.0.histories');
    }

    public function test_on_attachment_delete(): void
    {
        $this->loginAsBodyShopAdmin();

        $vehicle = factory(Truck::class)->create(['carrier_id' => null]);
        $vehicle->addMediaWithRandomName(Vehicle::ATTACHMENT_COLLECTION_NAME, UploadedFile::fake()->image('image123.jpg'));
        $attachments = $vehicle->getAttachments();
        $attachment = array_shift($attachments);

        $this->deleteJson(route('body-shop.trucks.delete-attachment', [$vehicle->id, $attachment->id]))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->getJson(route('body-shop.trucks.history', $vehicle))
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson(route('body-shop.trucks.history-detailed', $vehicle))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.histories');
    }

    public function test_on_comment_add(): void
    {
        $this->loginAsBodyShopAdmin();

        $vehicle = factory(Truck::class)->create(['carrier_id' => null]);

        $this->postJson(route('body-shop.trucks.comments.store', $vehicle), [
            'comment' => 'test text',
        ])
            ->assertCreated();

        $this->getJson(route('body-shop.trucks.history', $vehicle))
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson(route('body-shop.trucks.history-detailed', $vehicle))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.histories');
    }

    public function test_on_comment_delete(): void
    {
        $user = $this->loginAsBodyShopAdmin();

        $vehicle = factory(Truck::class)->create(['carrier_id' => null]);
        $comment = factory(TruckComment::class)->create([
            'truck_id' => $vehicle->id,
            'user_id' => $user->id,
        ]);

        $this->deleteJson(route('body-shop.trucks.comments.destroy', [$vehicle, $comment]))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->getJson(route('body-shop.trucks.history', $vehicle))
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson(route('body-shop.trucks.history-detailed', $vehicle))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.histories');
    }

    public function test_history_users(): void
    {
        $this->loginAsBodyShopAdmin();

        $vehicle = factory(Truck::class)->create(['carrier_id' => null]);

        $this->postJson(route('body-shop.trucks.update', $vehicle), [
            'vin' => 'DFDFDF3234234',
            'unit_number' => 'df763',
            'make' => 'BMW',
            'model' => 'X5',
            'year' => '2022',
            'type' => Vehicle::VEHICLE_TYPE_ATV,
            'license_plate' => 'SD34343',
            'notes' => 'test notes',
            'owner_id' => (factory(VehicleOwner::class)->create())->id,
            Vehicle::ATTACHMENT_FIELD_NAME => [
                UploadedFile::fake()->image('image1.jpg'),
                UploadedFile::fake()->image('image2.png'),
            ],
        ])
            ->assertOk();

        $this->loginAsBodyShopSuperAdmin();
        $this->postJson(route('body-shop.trucks.comments.store', $vehicle), [
            'comment' => 'test text',
        ])
            ->assertCreated();

        $this->loginAsBodyShopAdmin();
        $this->postJson(route('body-shop.trucks.comments.store', $vehicle), [
            'comment' => 'test text',
        ])
            ->assertCreated();

        $this->loginAsCarrierAdmin();
        $this->postJson(route('trucks.comments.store', $vehicle), [
            'comment' => 'test text',
        ])
            ->assertCreated();

        $this->loginAsBodyShopAdmin();
        $response = $this->getJson(route('body-shop.trucks.history-users-list', $vehicle))
            ->assertOk();

        $this->assertCount(3, $response['data']);
    }
}
