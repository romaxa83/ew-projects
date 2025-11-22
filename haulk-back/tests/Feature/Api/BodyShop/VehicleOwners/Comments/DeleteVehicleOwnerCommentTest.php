<?php

namespace Tests\Feature\Api\BodyShop\VehicleOwners\Comments;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\BodyShop\VehicleOwners\VehicleOwnerComment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Feature\Api\Orders\OrderTestCase;

class DeleteVehicleOwnerCommentTest extends OrderTestCase
{
    use DatabaseTransactions;

    public function test_comment_deleted(): void
    {
        $user = $this->loginAsBodyShopSuperAdmin();

        $vehicleOwner = factory(VehicleOwner::class)->create();
        $comment = factory(VehicleOwnerComment::class)->create([
            'vehicle_owner_id' => $vehicleOwner->id,
            'user_id' => $user->id,
        ]);

        $this->deleteJson(route('body-shop.vehicle-owners.comments.destroy', [$vehicleOwner, $comment]))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(
            VehicleOwnerComment::class,
            [
                'id' => $comment->id,
            ]
        );
    }
}
