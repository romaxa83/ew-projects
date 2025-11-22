<?php

namespace Tests\Feature\Api\BodyShop\VehicleOwners\Comments;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\BodyShop\VehicleOwners\VehicleOwnerComment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\Orders\OrderTestCase;

class CreateVehicleOwnerCommentTest extends OrderTestCase
{
    use DatabaseTransactions;

    public function test_it_comment_created(): void
    {
        $user = $this->loginAsBodyShopAdmin();

        $vehicleOwner = factory(VehicleOwner::class)->create();

        $this->postJson(
            route('body-shop.vehicle-owners.comments.store', $vehicleOwner->id),
            [
                'comment' => 'comment text',
            ]
        )->assertCreated();

        $this->assertDatabaseHas(
            VehicleOwnerComment::TABLE_NAME,
            [
                'vehicle_owner_id' => $vehicleOwner->id,
                'comment' => 'comment text',
                'user_id' => $user->id,
            ]
        );
    }
}
