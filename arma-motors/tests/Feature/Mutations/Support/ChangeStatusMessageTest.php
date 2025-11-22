<?php

namespace Tests\Feature\Mutations\Support;

use App\Exceptions\ErrorsCode;
use App\Models\Support\Message;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Builders\SupportBuilder;
use Tests\Traits\Statuses;

class ChangeStatusMessageTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use SupportBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::SUPPORT_MESSAGE_EDIT])
            ->create();
        $this->loginAsAdmin($admin);

        $message = $this->supportBuilder()->asOne()->setStatus(Message::STATUS_DRAFT)->create();

        $this->assertEquals($message->status, Message::STATUS_DRAFT);

        $data = [
            'id' => $message->id,
            'status' => $this->support_message_status_read,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.supportMessageChangeStatus');

        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('id', $responseData);

        $this->assertEquals($responseData['id'], $data['id']);
        $this->assertEquals($responseData['status'], $data['status']);

        $message->refresh();
        $this->assertEquals($message->status, Message::STATUS_READ);
    }

    /** @test */
    public function not_found()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::SUPPORT_MESSAGE_EDIT])
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'id' => 999,
            'status' => $this->support_message_status_read,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.not found model'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_FOUND, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::SUPPORT_MESSAGE_GET])
            ->create();

        $data = [
            'id' => 1,
            'status' => $this->support_message_status_read,
        ];

        $response = $this->graphQL($this->getQueryStr($data));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::SUPPORT_MESSAGE_GET])
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'id' => 1,
            'status' => $this->support_message_status_read,
        ];

        $response = $this->graphQL($this->getQueryStr($data));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }


    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                supportMessageChangeStatus(input:{
                    id: %d,
                    status: %s
                }) {
                    id
                    status
                }
            }',
            $data['id'],
            $data['status'],
        );
    }
}


