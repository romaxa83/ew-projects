<?php

namespace Tests\Feature\Queries\Admin;

use App\Events\Admin\GeneratePassword;
use App\Exceptions\ErrorsCode;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class GenerateNewPasswordTest extends TestCase
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
        \Event::fake([GeneratePassword::class]);

        $builder = $this->adminBuilder();
        $admin = $builder
            ->createRoleWithPerms([Permissions::ADMIN_GENERATE_PASSWORD])
            ->create();
        $this->loginAsAdmin($admin);

        $someAdmin = $builder->setEmail('some@admin.com')->create();

        $response = $this->graphQL($this->getQueryStr($someAdmin->id))
            ->assertOk();

        $responseData = $response->json('data.generateNewPassword');
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals($responseData['message'], __('message.admin.generate new password'));
        $this->assertTrue($responseData['status']);

        \Event::assertDispatched(GeneratePassword::class);
    }

    /** @test */
    public function not_perm()
    {
        $builder = $this->adminBuilder();
        $admin = $builder
            ->createRoleWithPerms([Permissions::ADMIN_CREATE])
            ->create();
        $this->loginAsAdmin($admin);

        $someAdmin = $builder->setEmail('some@admin.com')->create();

        $response = $this->graphQL($this->getQueryStr($someAdmin->id));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_auth()
    {
        $builder = $this->adminBuilder();
        $admin = $builder
            ->createRoleWithPerms([Permissions::ADMIN_GENERATE_PASSWORD])
            ->create();

        $someAdmin = $builder->setEmail('some@admin.com')->create();

        $response = $this->graphQL($this->getQueryStr($someAdmin->id));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    public function getQueryStr($id): string
    {
        return  sprintf('{
            generateNewPassword(id: %d) {
                status
                message
               }
            }',
            $id
        );
    }
}


