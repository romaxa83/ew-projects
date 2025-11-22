<?php

namespace Tests\Feature\Api\Users\UserComments;

use App\Models\Users\UserComment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class DeleteUserCommentTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_deleted(): void
    {
        $user = $this->loginAsCarrierSuperAdmin();

        $driver = $this->driverFactory();
        $comment = factory(UserComment::class)->create([
            'user_id' => $driver->id,
            'author_id' => $user->id,
        ]);

        $this->deleteJson(route('users.comments.destroy', [$driver, $comment]))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(
            UserComment::TABLE_NAME,
            [
                'id' => $comment->id,
            ]
        );
    }
    public function test_it_deleted_own_comment(): void
    {
        $user = $this->loginAsCarrierDispatcher();

        $driver = $this->driverFactory();
        $comment = factory(UserComment::class)->create([
            'user_id' => $driver->id,
            'author_id' => $user->id,
        ]);

        $this->deleteJson(route('users.comments.destroy', [$driver, $comment]))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(
            UserComment::TABLE_NAME,
            [
                'id' => $comment->id,
            ]
        );
    }

    public function test_it_deleted_other_user_comment_by_dispatcher(): void
    {
        $user = $this->loginAsCarrierDispatcher();

        $driver = $this->driverFactory();
        $otherUser = $this->dispatcherFactory();
        $comment = factory(UserComment::class)->create([
            'user_id' => $driver->id,
            'author_id' => $otherUser->id,
        ]);

        $this->deleteJson(route('users.comments.destroy', [$driver, $comment]))
            ->assertForbidden();
    }
}
