<?php

namespace Tests\Feature\Mutations\Admin\Admin;

use App\Exceptions\ErrorsCode;
use App\Models\Admin\Admin;
use App\Models\Permission\Role;
use App\Notifications\Mail\CredentialsNotification;
use App\Services\Localizations\LocalizationService;
use App\Types\Permissions;
use App\ValueObjects\Phone;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\AnonymousNotifiable;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Statuses;

class ChangeStatusTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function change_success()
    {
        $builder = $this->adminBuilder();
        $admin = $builder
            ->createRoleWithPerm(Permissions::ADMIN_CHANGE_STATUS)
            ->create();
        $this->loginAsAdmin($admin);

        $someAdmin = $builder->setEmail('some@admin.com')->create();

        $this->assertTrue($someAdmin->isActive());

        $data = [
            'id' => $someAdmin->id,
            'status' => $this->admin_status_inactive,
        ];

        $response = $this->graphQL($this->getQuery($data))
            ->assertOk();

        $responseData = $response->json('data.adminChangeStatus');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('status', $responseData);

        $this->assertEquals($someAdmin->id, $responseData['id']);
        $this->assertEquals($responseData['status'], $data['status']);

        $someAdmin->refresh();

        $this->assertFalse($someAdmin->isActive());
    }

    /** @test */
    public function change_wrong_status()
    {
        $builder = $this->adminBuilder();
        $admin = $builder
            ->createRoleWithPerm(Permissions::ADMIN_CHANGE_STATUS)
            ->create();
        $this->loginAsAdmin($admin);

        $someAdmin = $builder->setEmail('some@admin.com')->create();

        $this->assertTrue($someAdmin->isActive());

        $data = [
            'id' => $someAdmin->id,
            'status' => 'wrong',
        ];

        $response = $this->graphQL($this->getQuery($data))
            ->assertOk();

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function wrong_for_super_admin()
    {
        $builder = $this->adminBuilder();
        $admin = $builder
            ->createRoleWithPerm(Permissions::ADMIN_CHANGE_STATUS)
            ->create();
        $this->loginAsAdmin($admin);

        $someAdmin = Admin::superAdmin()->first();

        $this->assertTrue($someAdmin->isSuperAdmin());

        $data = [
            'id' => $someAdmin->id,
            'status' => $this->admin_status_inactive,
        ];

        $response = $this->graphQL($this->getQuery($data));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.not manipulate by this user'), $response->json('errors.0.message'));
    }

    /** @test */
    public function change_wrong_id()
    {
        $builder = $this->adminBuilder();
        $admin = $builder
            ->createRoleWithPerm(Permissions::ADMIN_CHANGE_STATUS)
            ->create();
        $this->loginAsAdmin($admin);

        $someAdmin = $builder->setEmail('some@admin.com')->create();

        $this->assertTrue($someAdmin->isActive());

        $data = [
            'id' => 'q',
            'status' => $this->admin_status_inactive,
        ];

        $response = $this->graphQL($this->getQuery($data))
            ->assertOk();

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function not_auth()
    {
        $builder = $this->adminBuilder();
        $admin = $builder
            ->createRoleWithPerm(Permissions::ADMIN_CHANGE_STATUS)
            ->create();

        $someAdmin = $builder->setEmail('some@admin.com')->create();

        $data = [
            'id' => $someAdmin->id,
            'status' => $this->admin_status_inactive,
        ];

        $response = $this->graphQL($this->getQuery($data));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()    {
        $builder = $this->adminBuilder();
        $admin = $builder
            ->createRoleWithPerm(Permissions::ADMIN_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $someAdmin = $builder->setEmail('some@admin.com')->create();

        $this->assertTrue($someAdmin->isActive());

        $data = [
            'id' => $someAdmin->id,
            'status' => $this->admin_status_inactive,
        ];

        $response = $this->graphQL($this->getQuery($data));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function getQuery(array $data): string
    {
        return sprintf('
            mutation {
                adminChangeStatus(input:{
                    id: "%s"
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

