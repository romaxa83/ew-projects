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

class DeleteMessageStatus extends TestCase
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
            ->createRoleWithPerms([Permissions::SUPPORT_MESSAGE_DELETE])
            ->create();
        $this->loginAsAdmin($admin);

        $builder = $this->supportBuilder();
        $message1 = $builder->asOne()->setStatus(Message::STATUS_DONE)->create();
        $message2 = $builder->asOne()->setStatus(Message::STATUS_DONE)->create();

        $total = Message::where('status', Message::STATUS_DONE)->count();
        $this->assertEquals(2, $total);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($message1->id)])
            ->assertOk();

        $responseData = $response->json('data.supportMessageDelete');

        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);

        $this->assertEquals($responseData['message'], __('message.support message deleted'));
        $this->assertTrue($responseData['status']);

        $total = Message::where('status', Message::STATUS_DONE)->count();
        $this->assertEquals(1, $total);
    }

    /** @test */
    public function wrong_status()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::SUPPORT_MESSAGE_DELETE])
            ->create();
        $this->loginAsAdmin($admin);

        $builder = $this->supportBuilder();
        $message1 = $builder->asOne()->setStatus(Message::STATUS_READ)->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStr($message1->id)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.can\'t delete support message'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_found()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::SUPPORT_MESSAGE_DELETE])
            ->create();
        $this->loginAsAdmin($admin);

        $response = $this->postGraphQL(['query' => $this->getQueryStr(11)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.not found model'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_FOUND, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::SUPPORT_MESSAGE_DELETE])
            ->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStr(11)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::SUPPORT_MESSAGE_LIST])
            ->create();
        $this->loginAsAdmin($admin);

        $response = $this->postGraphQL(['query' => $this->getQueryStr(11)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }


    private function getQueryStr($id): string
    {
        return sprintf('
            mutation {
                supportMessageDelete(id: %d) {
                    message
                    status
                }
            }',
            $id,
        );
    }
}
