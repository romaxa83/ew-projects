<?php

namespace Tests\Feature\Api\BodyShop\Vehicles\Trucks\Comments;

use App\Models\Users\User;
use App\Models\Vehicles\Comments\Comment;
use App\Models\Vehicles\Comments\TruckComment;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\Vehicles\Comments\DeleteCommentTest;

class DeleteTruckCommentTest extends DeleteCommentTest
{
    use DatabaseTransactions;

    protected string $routeName = 'body-shop.trucks.comments.destroy';

    protected string $tableName = TruckComment::TABLE_NAME;

    protected function getVehicle(array $attributes = []): Vehicle
    {
        return factory(Truck::class)->create($attributes + ['carrier_id' => null]);
    }

    protected function getComment(Vehicle $vehicle, User $user, array $attributes = []): Comment
    {
        return factory(TruckComment::class)->create([
            'user_id' => $user->id,
            'truck_id' => $vehicle->id,
            'comment' => 'test comment',
        ] + $attributes);
    }

    protected function loginAsPermittedUser(): User
    {
        return $this->loginAsBodyShopSuperAdmin();
    }

    protected function loginAsNotPermittedUser(): User
    {
        return $this->loginAsBodyShopMechanic();
    }
}
