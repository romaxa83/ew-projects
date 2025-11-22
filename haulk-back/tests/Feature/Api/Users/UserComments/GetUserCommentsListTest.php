<?php

namespace Tests\Feature\Api\Users\UserComments;

use App\Models\Users\UserComment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\Orders\OrderTestCase;
use Tests\Helpers\Traits\UserFactoryHelper;

class GetUserCommentsListTest extends OrderTestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_authorized(): void
    {
        $user = $this->loginAsCarrierAdmin();

        $driver = $this->driverFactory();

        $comment1 = factory(UserComment::class)->create([
            'user_id' => $driver->id,
            'author_id' => $user->id,
        ]);

        $comment2 = factory(UserComment::class)->create([
            'user_id' => $driver->id,
            'author_id' => $this->dispatcherFactory()->id,
        ]);

        $owner = $this->ownerFactory();

        $comment3 = factory(UserComment::class)->create([
            'user_id' => $owner->id,
            'author_id' => $this->dispatcherFactory()->id,
        ]);

        $response = $this->getJson(route('users.comments.index', $driver))
            ->assertOk();

        $comments = $response['data'];
        $this->assertCount(2, $comments);
        $this->assertEquals($comment1->id, $comments[0]['id']);
        $this->assertEquals($comment2->id, $comments[1]['id']);
    }
}
