<?php

namespace Tests\Feature\Http\Api\OneC\Permissions;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PermissionsControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_get_roles(): void
    {
        $this->loginAsModerator();

        $this->getJson(route('1c.permissions.roles'))
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        [
                            'id',
                            'name',
                            'translation' => [
                                'id',
                                'title',
                                'language',
                            ],
                            'permissions' => [
                                [
                                    'id',
                                    'name',
                                ]
                            ],
                        ]
                    ]
                ]
            );
    }
}
