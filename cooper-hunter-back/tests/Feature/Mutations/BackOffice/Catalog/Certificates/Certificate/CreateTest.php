<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Certificates\Certificate;

use Tests\Builders\Catalog\Certificates\TypeBuilder;
use Tests\TestCase;
use App\Models\Admins\Admin;
use Tests\Traits\Permissions\RoleHelperTrait;
use App\Permissions\Catalog\Certificates\Certificate;
use Tests\Unit\Dto\Catalog\Certificate\CertificateDtoTest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\GraphQL\Mutations\BackOffice\Catalog\Certificates\Certificate\CertificateCreateMutation;

class CreateTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    protected TypeBuilder $builderType;

    public const MUTATION = CertificateCreateMutation::NAME;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builderType = app(TypeBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        $type = $this->builderType->create();
        $this->loginByAdminManager([Certificate\CreatePermission::KEY]);

        $data = CertificateDtoTest::data();
        $data['type_id'] = $type->id;

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);
        $resData = $res->json(sprintf('data.%s', self::MUTATION));

        $this->assertArrayHasKey('id', $resData);
        $this->assertArrayHasKey('link', $resData);
        $this->assertArrayHasKey('number', $resData);
        $this->assertArrayHasKey('type_name', $resData);
    }

    /** @test */
    public function not_perm(): void
    {
        $type = $this->builderType->create();
        $this->loginByAdminManager([Certificate\UpdatePermission::KEY]);

        $data = CertificateDtoTest::data();
        $data['type_id'] = $type->id;

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
                    number: "%s"
                    link: "%s"
                    type_id: %d
                ) {
                    id
                    number
                    link
                    type_name
                }
            }',
            self::MUTATION,
            $data['number'],
            $data['link'],
            $data['type_id'],
        );
    }
}

