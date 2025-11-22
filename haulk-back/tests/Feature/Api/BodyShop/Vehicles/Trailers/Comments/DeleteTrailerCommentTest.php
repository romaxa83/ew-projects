<?php

namespace Tests\Feature\Api\BodyShop\Vehicles\Trailers\Comments;

use App\Models\Users\User;
use App\Models\Vehicles\Comments\Comment;
use App\Models\Vehicles\Comments\TrailerComment;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\Vehicles\Comments\DeleteCommentTest;

class DeleteTrailerCommentTest extends DeleteCommentTest
{
    use DatabaseTransactions;

    protected string $routeName = 'body-shop.trailers.comments.destroy';

    protected string $tableName = TrailerComment::TABLE_NAME;

    protected function getVehicle(array $attributes = []): Vehicle
    {
        return factory(Trailer::class)->create($attributes + ['carrier_id' => null]);
    }

    protected function getComment(Vehicle $vehicle, User $user, array $attributes = []): Comment
    {
        return factory(TrailerComment::class)->create([
            'user_id' => $user->id,
            'trailer_id' => $vehicle->id,
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
