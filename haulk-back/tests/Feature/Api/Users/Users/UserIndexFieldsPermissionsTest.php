<?php

namespace Tests\Feature\Api\Users\Users;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserIndexFieldsPermissionsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_show_role_field_for_not_permitted_user()
    {
        self::markTestSkipped();
        $this->loginAsCarrierDispatcher();

        $this->getJson(route('users.index'))
            ->assertForbidden();
//
//        $content = json_to_array($response->getContent());
//        foreach ($content['data'] as $user) {
//            $this->assertFalse(isset($user['security_level']));
//        }
    }
}
