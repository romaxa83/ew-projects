<?php

namespace Tests\Unit\Services\Permissions\Groups;

use App\Services\Permissions\Groups\PermissionGroupUsers;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PermissionGroupUsersTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * @param $expected
     * @param $without
     * @dataProvider mapsWithoutDataProvider
     */
    public function mapsWithout($expected, $without)
    {
        $this->assertEquals($expected, resolve(PermissionGroupUsers::class)->mapsWithout($without));
    }

    public function mapsWithoutDataProvider()
    {
        return [
            [
                [
                    'users' => [
                        'create',
                        'read',
                        'update',
                        'delete',
                    ],
                ],
                ['destroy'],
            ],
            [
                [
                    'users' => [

                    ],
                ],
                [
                    'create',
                    'read',
                    'update',
                    'delete'
                ],
            ],
        ];
    }
}
