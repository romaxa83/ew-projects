<?php

namespace Tests\Unit\Models\Users;

use App\Models\Users\User;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserTest extends TestCase
{

    use DatabaseTransactions;

    /** @test */
    public function it_create_user()
    {
        $attributes = ['first_name' => 'TestUserName'];

        $this->assertDatabaseMissing('users', $attributes);

        User::factory()->create($attributes);

        $this->assertDatabaseHas('users', $attributes);
    }

    /**
     * @test
     *
     * @throws Exception
     */
    public function it_delete_user()
    {
        $attributes = ['first_name' => 'TestUserName'];

        $user = User::factory()->create($attributes);

        $this->assertDatabaseHas('users', $attributes);

        $user->forceDelete();

        $this->assertDatabaseMissing('users', $attributes);
    }
}
