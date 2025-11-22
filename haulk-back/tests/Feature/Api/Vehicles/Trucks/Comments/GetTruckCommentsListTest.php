<?php

namespace Tests\Feature\Api\Vehicles\Trucks\Comments;

use App\Models\Users\User;
use App\Models\Vehicles\Comments\Comment;
use App\Models\Vehicles\Comments\TruckComment;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\Vehicles\Comments\GetCommentsListTest;
use Tests\Helpers\Traits\UserFactoryHelper;

class GetTruckCommentsListTest extends GetCommentsListTest
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    protected string $routeName = 'trucks.comments.index';

    protected function getVehicle(array $attributes = []): Vehicle
    {
        return factory(Truck::class)->create($attributes);
    }

    protected function getComment(Vehicle $vehicle, ?User $user = null, array $attributes = []): Comment
    {
        return factory(TruckComment::class)->create([
                'user_id' => $user->id ?? $this->dispatcherFactory()->id,
                'truck_id' => $vehicle->id,
                'comment' => 'test comment',
            ] + $attributes);
    }

    protected function loginAsPermittedUser(): User
    {
        return $this->loginAsCarrierSuperAdmin();
    }

    protected function loginAsNotPermittedUser(): User
    {
        return $this->loginAsBodyShopAdmin();
    }

    public function test_it_has_only_carrier_comments(): void
    {
        $user = $this->loginAsPermittedUser();

        $vehicle = $this->getVehicle();

        $comment1 = $this->getComment($vehicle, $user);

        $comment2 = $this->getComment($vehicle);

        $comment3 = $this->getComment($vehicle, $this->bsAdminFactory());

        $vehicle2 = $this->getVehicle();

        $this->getComment($vehicle2);

        $response = $this->getJson(route($this->routeName, $vehicle))
            ->assertOk();

        $comments = $response['data'];
        $this->assertCount(2, $comments);
        $this->assertEquals($comment1->id, $comments[0]['id']);
        $this->assertEquals($comment2->id, $comments[1]['id']);
    }
}
