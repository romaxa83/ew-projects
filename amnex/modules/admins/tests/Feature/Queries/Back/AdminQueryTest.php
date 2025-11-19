<?php

declare(strict_types=1);

namespace Wezom\Admins\Tests\Feature\Queries\Back;

use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Str;
use Symfony\Component\Console\Command\Command;
use Wezom\Admins\Commands\MakeSuperAdminCommand;
use Wezom\Admins\Enums\AdminOrderColumnEnum;
use Wezom\Admins\Enums\AdminStatusEnum;
use Wezom\Admins\GraphQL\Queries\Back\BackAdmins;
use Wezom\Admins\Models\Admin;
use Wezom\Admins\Testing\TestCase;
use Wezom\Core\Enums\OrderDirectionEnum;
use Wezom\Core\Enums\RoleEnum;
use Wezom\Core\Models\Permission\Role;

class AdminQueryTest extends TestCase
{
    public const int COUNT = 3;

    public function testCantGetListOfAdminsForSimpleUser(): void
    {
        $this->loginAsAdmin();

        $result = $this->queryRequest();

        $this->assertGraphQlForbidden($result);
    }

    public function testCantGetListOfAdminsForNotPermittedUser(): void
    {
        $result = $this->queryRequest();

        $this->assertGraphQlUnauthorized($result);
    }

    public function testItGetAdminListForPermittedAdmin(): void
    {
        $this->loginAsSuperAdmin();

        Admin::factory()->times(2)->manager()->create();
        Admin::factory()->times(7)->manager()->create();

        $result = $this->queryRequest()
            ->assertNoErrors();

        $admins = $result->json('data.' . BackAdmins::getName() . '.data');

        self::assertCount(10, $admins);
    }

    public function testCanFilterToOneModelListOfAdminsByEmailChunk(): void
    {
        $this->loginAsSuperAdmin();

        $adminsEmail = 'admin.email@example.com';
        Admin::factory()->admin()->create(['email' => $adminsEmail]);
        Admin::factory()->times(self::COUNT)->admin()->create();

        $result = $this->queryRequest(['query' => 'admin.email'])
            ->assertNoErrors();

        $admins = $result->json('data.' . BackAdmins::getName() . '.data');
        self::assertCount(1, $admins);

        $admin = array_shift($admins);
        self::assertEquals($adminsEmail, $admin['email']);
    }

    public function testItShowListOfUserForCreatedSuperAdminByCommand(): void
    {
        Role::factory()->admin()->create(['system_type' => RoleEnum::SUPER_ADMIN]);

        $this->artisan(MakeSuperAdminCommand::class, [
            '--name' => 'Super Admin',
            '--email' => 'admin@gmail.com',
            '--password' => '12345678',
        ])->assertExitCode(Command::SUCCESS);

        $admin = Admin::query()->first();

        $this->loginAsAdminWithPermissions([], $admin);

        Admin::factory()->times(self::COUNT)->admin()->create();

        $result = $this->queryRequest()
            ->assertNoErrors();

        $admins = $result->json('data.' . BackAdmins::getName() . '.data');

        self::assertCount(self::COUNT + 1, $admins);
    }

    public function testPermittedAdminCanGetAdminDataById(): void
    {
        $this->loginAsSuperAdmin();

        /** @var Collection<Admin> $admins */
        $admins = Admin::factory()->times(self::COUNT)->admin()->create();
        $admin = $admins->random();

        $result = $this->queryRequest(['ids' => $admin->id]);
        $adminsData = $result->json('data.' . BackAdmins::getName() . '.data');

        self::assertCount(1, $adminsData);

        $adminData = array_shift($adminsData);
        self::assertEquals($adminData['id'], $admin->id);
    }

    public function testPermittedAdminCanGetAdminDataByStatus(): void
    {
        $this->loginAsSuperAdmin();

        Admin::factory()->times(3)->admin()->create(['status' => AdminStatusEnum::INACTIVE]);
        Admin::factory()->times(1)->admin()->create(['status' => AdminStatusEnum::ACTIVE]);
        Admin::factory()->times(5)->admin()->create(['status' => AdminStatusEnum::PENDING]);

        $result = $this->queryRequest(['status' => AdminStatusEnum::INACTIVE]);
        $adminsData = $result->json('data.' . BackAdmins::getName() . '.data');

        self::assertCount(3, $adminsData);

        $result = $this->queryRequest(['status' => AdminStatusEnum::ACTIVE]);
        $adminsData = $result->json('data.' . BackAdmins::getName() . '.data');

        self::assertCount(2, $adminsData);

        $result = $this->queryRequest(['status' => AdminStatusEnum::PENDING]);
        $adminsData = $result->json('data.' . BackAdmins::getName() . '.data');

        self::assertCount(5, $adminsData);
    }

    public function testSearchAdminRegardlessOfCase(): void
    {
        $this->loginAsSuperAdmin();

        /** @var \Illuminate\Database\Eloquent\Collection<int, Admin> $admins */
        $admins = Admin::factory()->times(3)->create();

        $first = $admins->first();

        self::assertSame(Str::lower($first->email), $first->email);

        $result = $this->queryRequest(['query' => Str::upper($first->email)]);
        $adminsData = $result->json('data.' . BackAdmins::getName() . '.data');

        self::assertCount(1, $adminsData);
        self::assertEquals($first->id, $adminsData[0]['id']);
    }

    public function testSearchAdminByFullName(): void
    {
        $this->loginAsSuperAdmin();

        Admin::factory()->times(3)->create();
        $admin = Admin::factory()->create(['first_name' => 'Foo', 'last_name' => 'Bar']);

        $result = $this->queryRequest(['query' => 'Foo Bar']);
        $adminsData = $result->json('data.' . BackAdmins::getName() . '.data');

        self::assertCount(1, $adminsData);
        self::assertEquals($admin->id, $adminsData[0]['id']);

        $result = $this->queryRequest(['query' => 'Bar Foo']);
        $adminsData = $result->json('data.' . BackAdmins::getName() . '.data');

        self::assertCount(1, $adminsData);
        self::assertEquals($admin->id, $adminsData[0]['id']);
    }

    public function testSortByEmail(): void
    {
        $this->loginAsSuperAdmin();

        Admin::factory()->times(self::COUNT)->admin()->create();

        $data = $this->queryPaginate(BackAdmins::getName())
            ->select('firstName', 'email')
            ->args(['first' => self::COUNT])
            ->ordering(AdminOrderColumnEnum::EMAIL, OrderDirectionEnum::DESC)
            ->execute();

        $this->assertEquals(
            $data->sortByDesc('email')->pluck('email'),
            $data->pluck('email')
        );
    }

    public function testSortByDefault(): void
    {
        $this->loginAsSuperAdmin();

        Admin::factory()->admin()->create(['first_name' => 'testing 1']);
        Admin::factory()->admin()->create(['first_name' => 'testing 2']);
        Admin::factory()->admin()->create(['first_name' => 'testing 3']);
        Admin::factory()->admin()->create(['first_name' => 'testing 4']);

        $names = $this->queryPaginate(BackAdmins::getName())
            ->select(['firstName'])
            ->execute()
            ->pluck('firstName')
            ->filter(fn ($v) => str_starts_with($v, 'testing'))
            ->values();

        $this->assertCount(4, $names);

        $this->assertTrue($names->get(0) == 'testing 4');
        $this->assertTrue($names->get(1) == 'testing 3');
        $this->assertTrue($names->get(2) == 'testing 2');
        $this->assertTrue($names->get(3) == 'testing 1');
    }

    public function testSortByFirstName(): void
    {
        $this->loginAsSuperAdmin();

        Admin::factory()->times(self::COUNT)->admin()->create();

        $data = $this->queryPaginate(BackAdmins::getName())
            ->select('firstName')
            ->args(['first' => self::COUNT])
            ->ordering(AdminOrderColumnEnum::FIRST_NAME, OrderDirectionEnum::DESC)
            ->execute();

        $this->assertEquals(
            $data->sortByDesc('firstName')->pluck('firstName'),
            $data->pluck('firstName')
        );
    }

    public function testSortByFullName(): void
    {
        $this->loginAsSuperAdmin();

        Admin::factory()->times(self::COUNT)->admin()->create();

        $data = $this->queryPaginate(BackAdmins::getName())
            ->select('firstName', 'lastName')
            ->args(['first' => self::COUNT])
            ->ordering(AdminOrderColumnEnum::FULL_NAME, OrderDirectionEnum::DESC)
            ->execute();

        $mapper = function ($a) {
            return $a['firstName'] . ' ' . $a['lastName'];
        };

        $this->assertEquals(
            collect($data)->map($mapper),
            collect($data)->map($mapper)->sortDesc()->values()
        );
    }

    public function testSortByCreatedAt(): void
    {
        $this->loginAsSuperAdmin();

        Admin::factory()->times(self::COUNT)->admin()->create();

        $data = $this->queryPaginate(BackAdmins::getName())
            ->select('createdAt')
            ->args(['first' => self::COUNT])
            ->ordering(AdminOrderColumnEnum::CREATED_AT, OrderDirectionEnum::DESC)
            ->execute();

        $this->assertEquals(
            $data->sortByDesc('createdAt')->pluck('createdAt'),
            $data->pluck('createdAt')
        );
    }

    public function testFilterByRole(): void
    {
        $this->loginAsSuperAdmin();
        $role = Role::factory()->create();
        $admin = Admin::factory()->create();
        $admin->roles()->sync($role);
        Admin::factory()->manager()->create();
        Admin::factory()->manager()->create();

        /** @var Role $firstRole */
        $firstRole = $admin->roles()->first();
        $result = $this->queryRequest(
            [
                'roleIds' => $firstRole->id,
                'first' => 20,
            ],
        )
            ->assertNoErrors();

        $data = collect($result->json('data.' . BackAdmins::getName() . '.data'));
        $this->assertCount(1, $data);
    }

    public function testCanFilterByPhone(): void
    {
        $this->loginAsSuperAdmin();

        $phone = '+19231234567';
        Admin::factory()->admin()->create(['phone' => $phone]);
        Admin::factory()->times(self::COUNT)->admin()->create();

        $result = $this->queryRequest(['query' => $phone])
            ->assertOk();

        $admins = $result->json('data.' . BackAdmins::getName() . '.data');
        self::assertCount(1, $admins);

        $admin = array_shift($admins);
        self::assertEquals($phone, $admin['phone']);
    }

    public function testItGetAdminListForPermittedManager(): void
    {
        $this->loginAsAdminWithPermissions(['admins.view']);

        Admin::factory()->times(self::COUNT)->admin()->create();
        Admin::factory()->times(2)->manager()->create();

        $result = $this->queryRequest()->assertOk();

        $admins = $result->json('data.' . BackAdmins::getName() . '.data');

        self::assertCount(self::COUNT + 1 + 2, $admins);
    }

    public function testAdminCanAccessPermissionFields(): void
    {
        $this->loginAsAdminWithPermissions(['admins.view', 'admins.update', 'admins.delete']);

        Admin::factory()->times(self::COUNT)->admin()->create();

        $result = $this->queryRequest(select: ['abilities' => ['update', 'delete']])->assertOk();

        $admins = $result->json('data.' . BackAdmins::getName() . '.data');

        self::assertCount(self::COUNT + 1, $admins);
    }

    protected function queryRequest(array $args = [], array $select = []): TestResponse
    {
        return $this->query(BackAdmins::getName())
            ->args($args)
            ->select(
                [
                    'data' => array_merge([
                        'id',
                        'firstName',
                        'lastName',
                        'email',
                        'phone',
                        'roles' => [
                            'id',
                            'name',
                        ],
                        'active',
                        'inviteAccepted',
                        'newEmailForVerification',
                        'status',
                    ], $select),
                ]
            )
            ->executeAndReturnResponse();
    }
}
