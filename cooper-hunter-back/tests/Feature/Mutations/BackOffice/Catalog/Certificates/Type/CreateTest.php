<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Certificates\Type;

use Tests\TestCase;
use App\Models\Admins\Admin;
use Tests\Traits\Permissions\RoleHelperTrait;
use App\Permissions\Catalog\Certificates\Type;
use Tests\Unit\Dto\Catalog\Certificate\TypeDtoTest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\GraphQL\Mutations\BackOffice\Catalog\Certificates\Type\CertificateTypeCreateMutation;

class CreateTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const MUTATION = CertificateTypeCreateMutation::NAME;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success(): void
    {
        $this->loginByAdminManager([Type\CreatePermission::KEY]);

        $data = TypeDtoTest::data();

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);
        $resData = $res->json(sprintf('data.%s', self::MUTATION));

        $this->assertArrayHasKey('id', $resData);
        $this->assertArrayHasKey('type', $resData);
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginByAdminManager([Type\UpdatePermission::KEY]);

        $data = TypeDtoTest::data();

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $res->json());
        $this->assertEquals('No permission', $res->json('errors.0.message'));
    }

    protected function loginByAdminManager(array $permissionKey): Admin
    {
        return $this->loginAsAdmin()
            ->assignRole(
                $this->generateRole('Admin manager', $permissionKey, Admin::GUARD)
            );
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                %s (
                    type: "%s"
                ) {
                    id
                    type
                }
            }',
            self::MUTATION,
            $data['type'],
        );
    }
}
