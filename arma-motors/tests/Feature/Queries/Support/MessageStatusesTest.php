<?php

namespace Tests\Feature\Queries\Support;

use App\Models\Support\Message;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class MessageStatusesTest extends TestCase
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

        $responseData = $response->json('data.messageStatuses');

        $this->assertCount(3, $responseData);

        $this->assertEquals(Message::STATUS_DRAFT, $responseData[0]['key']);
        $this->assertEquals(__('translation.support.message.status.draft'), $responseData[0]['name']);

        $this->assertEquals(Message::STATUS_READ, $responseData[1]['key']);
        $this->assertEquals(__('translation.support.message.status.read'), $responseData[1]['name']);

        $this->assertEquals(Message::STATUS_DONE, $responseData[2]['key']);
        $this->assertEquals(__('translation.support.message.status.done'), $responseData[2]['name']);
    }

    public static function getQueryStr(): string
    {
        return "{messageStatuses {key, name}}";
    }
}
