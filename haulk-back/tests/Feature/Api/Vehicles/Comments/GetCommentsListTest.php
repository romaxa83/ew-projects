<?php

namespace Tests\Feature\Api\Vehicles\Comments;

use App\Models\Users\User;
use App\Models\Vehicles\Comments\Comment;
use App\Models\Vehicles\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

abstract class GetCommentsListTest extends TestCase
{
    use DatabaseTransactions;

    protected string $routeName = '';

    abstract protected function getVehicle(array $attributes = []): Vehicle;

    abstract protected function getComment(Vehicle $vehicle, ?User $user = null, array $attributes = []): Comment;

    abstract protected function loginAsPermittedUser(): User;

    abstract protected function loginAsNotPermittedUser(): User;

    public function test_it_for_not_authorized(): void
    {
        $this->getJson(route($this->routeName, $this->getVehicle()))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_it_authorized(): void
    {
        $user = $this->loginAsPermittedUser();

        $vehicle = $this->getVehicle();

        $comment1 = $this->getComment($vehicle, $user);

        $comment2 = $this->getComment($vehicle);

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
