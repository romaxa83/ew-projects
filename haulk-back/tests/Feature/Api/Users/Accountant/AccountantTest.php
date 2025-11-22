<?php

namespace Tests\Feature\Api\Users\Accountant;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class AccountantTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_fot_not_auth_user()
    {
        $this->getJson(route('users.index'))
            ->assertStatus(401);
    }

    public function test_it_show_it_for_super_admin()
    {
        $this->loginAsCarrierSuperAdmin();

        $this->getJson(route('users.index'))
            ->assertOk();
    }

    public function test_it_see_validation_errors_with_empty_request()
    {
        $this->loginAsCarrierSuperAdmin();

        $this->postJson(route('users.store'), [])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure(
                [
                    'errors' => [
                        [
                            'source' => [],
                            'title',
                            'status',
                        ],
                    ]
                ]
            );
    }

    public function test_it_create_account()
    {
        $this->markTestSkipped();
    }
}
