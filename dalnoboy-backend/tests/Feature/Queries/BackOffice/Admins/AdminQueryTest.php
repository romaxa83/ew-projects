<?php

namespace Tests\Feature\Queries\BackOffice\Admins;

use App\GraphQL\Queries\BackOffice\Admins\AdminQuery;
use App\Models\Phones\Phone;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminQueryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_query(): void
    {
        $admin = $this->loginAsAdminWithRole();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(AdminQuery::NAME)
                ->select(
                    [
                        'id',
                        'first_name',
                        'last_name',
                        'second_name',
                        'phone',
                        'phones' => [
                            'phone',
                            'is_default',
                        ],
                        'email',
                        'language' => [
                            'name',
                            'slug',
                        ],
                        'role' => [
                            'id',
                            'name',
                            'permissions'
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        AdminQuery::NAME => [
                            'id' => $admin->id,
                            'first_name' => $admin->first_name,
                            'last_name' => $admin->last_name,
                            'second_name' => $admin->second_name,
                            'phone' => $admin->phone->phone,
                            'phones' => $admin->phones->map(
                                fn(Phone $phone) => [
                                    'phone' => $phone->phone,
                                    'is_default' => $phone->is_default
                                ]
                            )
                                ->toArray(),
                            'email' => $admin->email,
                            'language' => [
                                'name' => $admin->language->name,
                                'slug' => $admin->language->slug,
                            ],
                            'role' => [
                                'id' => $admin->role->id,
                                'name' => $admin->role->name,
                                'permissions' => $admin->role->permissions->pluck('name')
                                    ->toArray()
                            ]
                        ]
                    ]
                ]
            );
    }
}
