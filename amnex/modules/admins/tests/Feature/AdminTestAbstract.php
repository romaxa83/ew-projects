<?php

declare(strict_types=1);

namespace Wezom\Admins\Tests\Feature;

use Illuminate\Testing\TestResponse;
use JsonException;
use Wezom\Admins\GraphQL\Queries\Back\BackAdmins;
use Wezom\Admins\Testing\TestCase;
use Wezom\Core\Models\Permission\Role;
use Wezom\Core\Testing\QueryBuilder\GraphQLQuery;

abstract class AdminTestAbstract extends TestCase
{
    protected function attrs(): array
    {
        $role = Role::factory()->admin()->create(['name' => 'Manager']);

        return [
            'firstName' => 'New Admin',
            'lastName' => 'Name',
            'email' => 'new.admin.email@example.com',
            'phone' => '43434234234',
            'role' => $role,
        ];
    }

    /**
     * @throws JsonException
     */
    protected function queryRequest(array $args = []): TestResponse
    {
        return $this->postGraphQL(GraphQLQuery::query(BackAdmins::getName())
            ->args($args)
            ->select([
                'data' => [
                    'id',
                    'firstName',
                    'lastName',
                    'email',
                    'phone',
                    'roles' => [
                        'id',
                        'name',
                    ],
                    'permission' => [
                        'canBeUpdated',
                        'canBeDeleted',
                    ],
                    'active',
                    'inviteAccepted',
                    'newEmailForVerification',
                    'status',
                ],
            ])
            ->make());
    }
}
