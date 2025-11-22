<?php

namespace Tests\Feature\Api\Users\UserComments;

use App\Models\Users\UserComment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class CreateUserCommentTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_comment_created(): void
    {
        $user = $this->loginAsCarrierDispatcher();

        $driver = $this->driverFactory();

        $this->postJson(
            route('users.comments.store', $driver),
            [
                'comment' => 'comment text',
            ]
        )->assertCreated();

        $this->assertDatabaseHas(
            UserComment::TABLE_NAME,
            [
                'comment' => 'comment text',
                'user_id' => $driver->id,
                'author_id' => $user->id,
            ]
        );
    }
}
