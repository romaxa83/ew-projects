<?php

namespace Tests\Feature\Http\Api\V1\Users\UserProfile;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->passportInit();
    }

    /** @test */
    public function success_profile()
    {
        $model = $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.users.profile'))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'full_name' => $model->full_name,
                    'first_name' => $model->first_name,
                    'last_name' => $model->last_name,
                    'email' => $model->email,
                    'phone' => $model->phone,
                    'phone_extension' => $model->phone_extension,
                    'phones' => $model->phones,
                    'language' => $model->lang,
                    'photo' => null,
                    'role' => [
                        'id' => $model->role->id,
                        'name' => __('permissions.roles.' . $model->role->name),
                    ],
                    'permissions' => $model->getPermissions()
                ]
            ])
        ;
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.users.profile'));

        self::assertUnauthenticatedMessage($res);
    }
}

