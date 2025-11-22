<?php

namespace Tests\Feature\Api\BodyShop\Vehicles\Trucks\Comments;

use App\Models\Users\User;
use App\Models\Vehicles\Comments\TruckComment;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use Tests\Feature\Api\Vehicles\Comments\CreateCommentTest;

class CreateTruckCommentTest extends CreateCommentTest
{
    protected string $routeName = 'body-shop.trucks.comments.store';

    protected string $tableName = TruckComment::TABLE_NAME;
    protected string $relatedColumnName = 'truck_id';

    protected function getVehicle(array $attributes = []): Vehicle
    {
        return factory(Truck::class)->create($attributes + ['carrier_id' => null]);
    }

    protected function loginAsPermittedUser(): User
    {
        return $this->loginAsBodyShopAdmin();
    }

    protected function loginAsNotPermittedUser(): User
    {
        return $this->loginAsBodyShopMechanic();
    }
}
