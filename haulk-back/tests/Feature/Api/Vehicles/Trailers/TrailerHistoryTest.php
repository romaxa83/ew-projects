<?php

namespace Api\Vehicles\Trailers;

use App\Models\Vehicles\Comments\TrailerComment;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class TrailerHistoryTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_on_vehicle_create(): void
    {
        $this->loginAsCarrierAdmin();

        $response = $this->postJson(route('trailers.store'), [
            'vin' => 'DFDFDF3234234',
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'license_plate' => 'SD34343',
            'temporary_plate' => 'SD343431',
            'notes' => 'test notes',
            'owner_id' => $this->ownerFactory()->id,
            Vehicle::ATTACHMENT_FIELD_NAME => [
                UploadedFile::fake()->image('image1.jpg'),
                UploadedFile::fake()->image('image2.png'),
            ],
        ])
            ->assertCreated();

        $this->getJson(route('trailers.history', $response['data']['id']))
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson(route('trailers.history-detailed', $response['data']['id']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(11, 'data.0.histories');
    }

    public function test_on_vehicle_update(): void
    {
        $this->loginAsCarrierAdmin();

        $vehicle = factory(Trailer::class)->create(['carrier_id' => null]);

        $this->postJson(route('trailers.update', $vehicle), [
            'vin' => 'DFDFDF3234234',
            'unit_number' => 'df763',
            'make' => 'BMW',
            'model' => 'X5',
            'year' => '2022',
            'license_plate' => 'SD34343',
            'temporary_plate' => 'SD343431',
            'notes' => 'test notes',
            'owner_id' => $this->ownerFactory()->id,
            Vehicle::ATTACHMENT_FIELD_NAME => [
                UploadedFile::fake()->image('image1.jpg'),
                UploadedFile::fake()->image('image2.png'),
            ],
        ])
            ->assertOk();

        $vehicle->refresh();

        $this->getJson(route('trailers.history', $vehicle))
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson(route('trailers.history-detailed', $vehicle))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(11, 'data.0.histories');

        $this->postJson(route('trailers.update', $vehicle), [
            'vin' => 'DFDFDF3234234',
            'unit_number' => 'df763',
            'make' => 'BMW',
            'model' => 'X5',
            'year' => '2022',
            'license_plate' => 'SD34343',
            'temporary_plate' => 'SD343431',
            'notes' => 'test notes',
            'owner_id' => $vehicle->owner_id,
            Vehicle::ATTACHMENT_FIELD_NAME => [
                UploadedFile::fake()->image('image45.jpg'),
            ],
        ])
            ->assertOk();

        $this->getJson(route('trailers.history-detailed', $vehicle))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonCount(1, 'data.0.histories');
    }

    public function test_on_attachment_delete(): void
    {
        $this->loginAsCarrierAdmin();

        $vehicle = factory(Trailer::class)->create();
        $vehicle->addMediaWithRandomName(Vehicle::ATTACHMENT_COLLECTION_NAME, UploadedFile::fake()->image('image123.jpg'));
        $attachments = $vehicle->getAttachments();
        $attachment = array_shift($attachments);

        $this->deleteJson(route('trailers.delete-attachment', [$vehicle->id, $attachment->id]))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->getJson(route('trailers.history', $vehicle))
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson(route('trailers.history-detailed', $vehicle))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.histories');
    }

    public function test_on_comment_add(): void
    {
        $this->loginAsCarrierAdmin();

        $vehicle = factory(Trailer::class)->create();

        $this->postJson(route('trailers.comments.store', $vehicle), [
            'comment' => 'test text',
        ])
            ->assertCreated();

        $this->getJson(route('trailers.history', $vehicle))
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson(route('trailers.history-detailed', $vehicle))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.histories');
    }

    public function test_on_comment_delete(): void
    {
        $user = $this->loginAsCarrierAdmin();

        $vehicle = factory(Trailer::class)->create();
        $comment = factory(TrailerComment::class)->create([
            'trailer_id' => $vehicle->id,
            'user_id' => $user->id,
        ]);

        $this->deleteJson(route('trailers.comments.destroy', [$vehicle, $comment]))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->getJson(route('trailers.history', $vehicle))
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson(route('trailers.history-detailed', $vehicle))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.histories');
    }

    public function test_history_users(): void
    {
        $this->loginAsCarrierAdmin();

        $vehicle = factory(Trailer::class)->create(['carrier_id' => null]);

        $this->postJson(route('trailers.update', $vehicle), [
            'vin' => 'DFDFDF3234234',
            'unit_number' => 'df763',
            'make' => 'BMW',
            'model' => 'X5',
            'year' => '2022',
            'license_plate' => 'SD34343',
            'temporary_plate' => 'SD343431',
            'notes' => 'test notes',
            'owner_id' => $this->ownerFactory()->id,
            Vehicle::ATTACHMENT_FIELD_NAME => [
                UploadedFile::fake()->image('image1.jpg'),
                UploadedFile::fake()->image('image2.png'),
            ],
        ])
            ->assertOk();

        $this->loginAsBodyShopSuperAdmin();
        $this->postJson(route('body-shop.trailers.comments.store', $vehicle), [
            'comment' => 'test text',
        ])
            ->assertCreated();

        $this->loginAsCarrierAdmin();
        $this->postJson(route('trailers.comments.store', $vehicle), [
            'comment' => 'test text',
        ])
            ->assertCreated();

        $this->loginAsCarrierAdmin();
        $this->postJson(route('trailers.comments.store', $vehicle), [
            'comment' => 'test text',
        ])
            ->assertCreated();

        $this->loginAsCarrierAdmin();
        $response = $this->getJson(route('trailers.history-users-list', $vehicle))
            ->assertOk();

        $this->assertCount(3, $response['data']);
    }
}
