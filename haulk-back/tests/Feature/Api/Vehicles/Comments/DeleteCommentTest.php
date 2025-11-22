<?php

namespace Tests\Feature\Api\Vehicles\Comments;

use App\Models\Users\User;
use App\Models\Vehicles\Comments\Comment;
use App\Models\Vehicles\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

abstract class DeleteCommentTest extends TestCase
{
    use DatabaseTransactions;

    protected string $routeName = '';

    protected string $tableName = '';

    abstract protected function getVehicle(array $attributes = []): Vehicle;

    abstract protected function getComment(Vehicle $vehicle, User $user, array $attributes = []): Comment;

    abstract protected function loginAsPermittedUser(): User;

    abstract protected function loginAsNotPermittedUser(): User;

    public function test_it_deleted(): void
    {
        $user = $this->loginAsPermittedUser();

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
