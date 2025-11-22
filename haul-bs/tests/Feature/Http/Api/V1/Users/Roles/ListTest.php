<?php

namespace Tests\Feature\Http\Api\V1\Users\Roles;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ListTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_list()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.users.roles'))
            ->assertJson([
                'data' => [
                    ['name' => __('permissions.roles.admin')],
                    ['name' => __('permissions.roles.mechanic')],
                    ['name' => __('permissions.roles.sales_manager')],
                    ['name' => __('permissions.roles.super_admin')],
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name'
                    ]
                ]
            ])
            ->assertJsonCount(4, 'data')
        ;
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.users.roles'));

        self::assertUnauthenticatedMessage($res);
    }
}
