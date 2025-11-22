<?php

namespace Tests\Feature\Api\Vehicles\Trailers\Comments;

use App\Models\Users\User;
use App\Models\Vehicles\Comments\Comment;
use App\Models\Vehicles\Comments\TrailerComment;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Feature\Api\Vehicles\Comments\DeleteCommentTest;
use Tests\Helpers\Traits\UserFactoryHelper;

class DeleteTrailerCommentTest extends DeleteCommentTest
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    protected string $routeName = 'trailers.comments.destroy';

    protected string $tableName = TrailerComment::TABLE_NAME;

    protected function getVehicle(array $attributes = []): Vehicle
    {
        return factory(Trailer::class)->create($attributes);
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
