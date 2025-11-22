<?php

namespace Tests\Feature\Queries\User;

use App\Types\Permissions;
use App\Types\UserType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class UserTypeTest extends TestCase
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

        $responseData = $response->json('data.userType');

        $this->assertCount(2, $responseData);

        $this->assertEquals(UserType::TYPE_PERSONAL, $responseData[0]['key']);
        $this->assertEquals(__('translation.user.type.personal'), $responseData[0]['name']);

        $this->assertEquals(UserType::TYPE_LEGAL, $responseData[1]['key']);
        $this->assertEquals(__('translation.user.type.legal'), $responseData[1]['name']);
    }

    public static function getQueryStr(): string
    {
        return "{userType {key, name}}";
    }
}
