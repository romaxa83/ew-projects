<?php

namespace Tests\Feature\Api\Vehicles\Trucks\Comments;

use App\Models\Users\User;
use App\Models\Vehicles\Comments\Comment;
use App\Models\Vehicles\Comments\TruckComment;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Feature\Api\Vehicles\Comments\DeleteCommentTest;
use Tests\Helpers\Traits\UserFactoryHelper;

class DeleteTruckCommentTest extends DeleteCommentTest
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    protected string $routeName = 'trucks.comments.destroy';

    protected string $tableName = TruckComment::TABLE_NAME;

    protected function getVehicle(array $attributes = []): Vehicle
    {
        return factory(Truck::class)->create($attributes);
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
        return $this->loginAsCarrierSuperAdmin();
    }

    protected function loginAsNotPermittedUser(): User
    {
        return $this->loginAsCarrierDispatcher();
    }

    public function test_dispatcher_cant_delete_other_user_comment(): void
    {
        $this->loginAsCarrierDispatcher();

        $otherUser = $this->userFactory(User::ADMIN_ROLE);

        $vehicle = $this->getVehicle();
        $comment = $this->getComment($vehicle, $otherUser);

        $this->deleteJson(route($this->routeName, [$vehicle, $comment]))
            ->assertForbidden();
    }

    public function test_dispatcher_can_delete_own_comment(): void
    {
        $user = $this->loginAsCarrierDispatcher();

        $vehicle = $this->getVehicle();
        $comment = $this->getComment($vehicle, $user);

        $this->deleteJson(route($this->routeName, [$vehicle, $comment]))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(
            $this->tableName,
            [
                'id' => $comment->id,
            ]
        );
    }
}
