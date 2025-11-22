<?php

namespace Tests\Feature\Api\BodyShop\VehicleOwners\Comments;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\BodyShop\VehicleOwners\VehicleOwnerComment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\Orders\OrderTestCase;
use Tests\Helpers\Traits\UserFactoryHelper;

class GetVehicleOwnerCommentsListTest extends OrderTestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_list(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $vehicleOwner1 = factory(VehicleOwner::class)->create();
        $user1 = $this->bsAdminFactory();
        $comment1 = factory(VehicleOwnerComment::class)->create([
            'vehicle_owner_id' => $vehicleOwner1->id,
            'user_id' => $user1->id,
        ]);
        $user2 = $this->bsAdminFactory();
        $comment2 = factory(VehicleOwnerComment::class)->create([
            'vehicle_owner_id' => $vehicleOwner1->id,
            'user_id' => $user2->id,
        ]);

        $vehicleOwner2 = factory(VehicleOwner::class)->create();
        factory(VehicleOwnerComment::class)->create([
            'vehicle_owner_id' => $vehicleOwner2->id,
            'user_id' => $user1->id,
        ]);

        $response = $this->getJson(route('body-shop.vehicle-owners.comments.index', $vehicleOwner1))
            ->assertOk();

        $comments = $response['data'];
        $this->assertCount(2, $comments);
        $this->assertEquals($comment1->id, $comments[0]['id']);
        $this->assertEquals($comment2->id, $comments[1]['id']);
    }
}
