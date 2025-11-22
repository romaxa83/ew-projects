<?php

namespace Tests\Feature\Api\Vehicles\Comments;

use App\Models\Users\User;
use App\Models\Vehicles\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

abstract class CreateCommentTest extends TestCase
{
    use DatabaseTransactions;

    protected string $routeName = '';

    protected string $tableName = '';
    protected string $relatedColumnName = '';

    abstract protected function getVehicle(array $attributes = []): Vehicle;

    abstract protected function loginAsPermittedUser(): User;

    abstract protected function loginAsNotPermittedUser(): User;

    public function test_it_created(): void
    {
        $user = $this->loginAsPermittedUser();

        $vehicle = $this->getVehicle();

        $this->postJson(
            route($this->routeName, $vehicle->id),
            [
                'comment' => 'comment text',
            ]
        )->assertCreated();

        $this->assertDatabaseHas(
            $this->tableName,
            [
                $this->relatedColumnName => $vehicle->id,
                'comment' => 'comment text',
                'user_id' => $user->id,
            ]
        );
    }
}
