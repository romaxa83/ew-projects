<?php

namespace Tests\Feature\Api\Users\Owner;

use App\Models\Tags\Tag;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OwnerCreateTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_it_create_new_owner_success()
    {
        $this->withoutExceptionHandling();
        $this->loginAsCarrierSuperAdmin();

        $email = $this->faker->email;
        $attributes = [
            'full_name' => 'Full Name',
            'phone' => '1-541-754-3010',
            'email' => $email,
        ];

        $dbAttributes = [
            'first_name' => 'Full',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => $email,
        ];

        $driversRole = $this->getRoleRepository()->findByName(User::OWNER_ROLE);

        $roles = [
            'role_id' => $driversRole->id,
        ];


        $this->assertDatabaseMissing(User::TABLE_NAME, $dbAttributes);

        $this->postJson(route('users.store'), $attributes + $roles)
            ->assertCreated();

        $this->assertDatabaseHas(User::TABLE_NAME, $dbAttributes);
    }

    public function test_it_create_new_owner_with_tags_success()
    {
        $this->withoutExceptionHandling();
        $this->loginAsCarrierSuperAdmin();

        $email = $this->faker->email;
        $attributes = [
            'full_name' => 'Full Name',
            'phone' => '1-541-754-3010',
            'email' => $email,
        ];

        $dbAttributes = [
            'first_name' => 'Full',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => $email,
        ];

        $tag = Tag::factory()->create(['type' => Tag::TYPE_VEHICLE_OWNER]);

        $driversRole = $this->getRoleRepository()->findByName(User::OWNER_ROLE);

        $roles = [
            'role_id' => $driversRole->id,
        ];


        $this->assertDatabaseMissing(User::TABLE_NAME, $dbAttributes);

        $response = $this->postJson(route('users.store'), $attributes + $roles + ['tags' => [$tag->id]])
            ->assertCreated();

        $userId = $response['data']['id'] ?? null;

        $this->assertDatabaseHas(User::TABLE_NAME, $dbAttributes);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag->id,
            'taggable_id' => $userId,
            'taggable_type' => User::class,
        ]);
    }
}
