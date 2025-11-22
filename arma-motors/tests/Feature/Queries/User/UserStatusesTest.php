<?php

namespace Tests\Feature\Queries\User;

use App\Models\User\User;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class UserStatusesTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_LIST])->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr())->assertOk();

        $responseData = $response->json('data.userStatuses');

        $this->assertCount(3, $responseData);

        $this->assertEquals(User::DRAFT, $responseData[0]['key']);
        $this->assertEquals(__('translation.user.status.draft'), $responseData[0]['name']);

        $this->assertEquals(User::ACTIVE, $responseData[1]['key']);
        $this->assertEquals(__('translation.user.status.active'), $responseData[1]['name']);

        $this->assertEquals(User::VERIFY, $responseData[2]['key']);
        $this->assertEquals(__('translation.user.status.verify'), $responseData[2]['name']);
    }

    public static function getQueryStr(): string
    {
        return "{userStatuses {key, name}}";
    }
}
