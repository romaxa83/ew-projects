<?php

namespace Feature\Http\Api\OneC\Users;

use App\Models\OneC\Moderator;
use App\Models\Users\User;
use App\Permissions\Catalog\Categories\DeletePermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class UsersControllerTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public function test_unauthorized(): void
    {
        $this->getJson(route('1c.categories.index'))
            ->assertUnauthorized();
    }

    public function test_no_permission(): void
    {
        $role = $this->generateRole(
            'Wrong permission role',
            [DeletePermission::KEY],
            Moderator::GUARD
        );

        $this->loginAsModerator(role: $role);

        $this->getJson(route('1c.users.index'))
            ->assertForbidden();
    }

    public function test_index(): void
    {
        $this->loginAsModerator();

        $countOfRecords = 3;

        User::factory()
            ->count($countOfRecords)
            ->create();

        $response = $this->getJson(route('1c.users.index'))
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        [
                            'id',
                            'email',
                            'phone',
                            'guid',
                        ],
                    ],
                ],
            );

        $data = $response->json('data');
        $this->assertCount($countOfRecords, $data);
    }

    public function test_new(): void
    {
        $this->loginAsModerator();

        $countOfRecords = 3;

        User::factory()
            ->count($countOfRecords)
            ->create(['guid' => null]);

        $response = $this->getJson(route('1c.users.new'))
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        [
                            'id',
                            'email',
                            'phone',
                            'guid',
                        ],
                    ],
                ],
            );

        $data = $response->json('data');
        $this->assertCount($countOfRecords, $data);
    }

    public function test_show(): void
    {
        $this->loginAsModerator();

        $user = User::factory()
            ->create();

        $this->getJson(route('1c.users.show', $user->guid))
            ->assertOk()
            ->assertJsonStructure(
                $this->getJsonStructure()
            );
    }

    protected function getJsonStructure(): array
    {
        return [
            'data' => [
                'id',
                'email',
                'phone',
                'guid',
            ],
        ];
    }

    public function test_show_not_found(): void
    {
        $this->loginAsModerator();

        $this->getJson(route('1c.users.show', 0))
            ->assertNotFound();
    }

//    public function test_store(): void
//    {
//        $this->loginAsModerator();
//
//        $this->postJson(
//            route('1c.users.store'),
//            $this->getParams()
//        )
//            ->assertCreated()
//            ->assertJsonStructure(
//                $this->getJsonStructure()
//            );
//    }

    protected function getParams(): array
    {
        return [
            'first_name' => 'First',
            'last_name' => 'Last',
            'password' => 'password1',
            'password_confirmation' => 'password1',
            'email' => 'test@gmail.com',
            'phone' => null,
        ];
    }

//    public function test_update(): void
//    {
//        $this->loginAsModerator();
//
//        $user = User::factory()->create();
//
//        $this->putJson(
//            route('1c.users.update', $user->guid),
//            [
//                'first_name' => 'First',
//                'last_name' => 'Last',
//                'email' => (string)$user->email,
//            ]
//        )->assertJsonStructure(
//            $this->getJsonStructure()
//        );
//    }

    public function test_destroy(): void
    {
        $this->loginAsModerator();

        $user = User::factory()->create();

        $this->deleteJson(route('1c.users.destroy', $user->guid))
            ->assertOk();
    }

    public function test_import(): void
    {
        $this->loginAsModerator();

        $this->postJson(
            route('1c.users.import'),
            [
                'users' => [
                    array_merge($this->getParams(), ['phone' => '+380970000000']),
                ]
            ]
        )->assertJsonStructure(
            [
                'data' => [
                    [
                        'id',
                        'email',
                        'phone',
                        'guid',
                    ]
                ],
            ]
        );
    }

    public function test_update_guid(): void
    {
        $this->loginAsModerator();

        $user = User::factory()->create(['guid' => null]);
        $guid = Uuid::uuid4();

        $this->assertDatabaseMissing(User::TABLE, ['guid' => $guid]);

        $this->postJson(
            route('1c.users.update.guid'),
            [
                'data' => [
                    [
                        'id' => $user->id,
                        'guid' => $guid,
                    ]
                ]
            ]
        )->assertJsonStructure(
            [
                'data' => [
                    [
                        'id',
                        'email',
                        'phone',
                        'guid',
                    ]
                ],
            ]
        );

        $this->assertDatabaseHas(User::TABLE, ['guid' => $guid]);
    }
}
