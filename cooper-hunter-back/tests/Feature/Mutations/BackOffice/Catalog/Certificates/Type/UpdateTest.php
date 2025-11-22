<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Certificates\Type;

use App\GraphQL\Mutations\BackOffice\Catalog\Certificates\Type\CertificateTypeUpdateMutation;
use App\Models\Admins\Admin;
use App\Permissions\Catalog\Certificates\Type;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Tests\Builders\Catalog\Certificates\TypeBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;
use Tests\Unit\Dto\Catalog\Certificate\TypeDtoTest;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const MUTATION = CertificateTypeUpdateMutation::NAME;

    protected TypeBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = app(TypeBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        $model = $this->builder->create();
        $this->loginByAdminManager([Type\UpdatePermission::KEY]);

        $data = TypeDtoTest::data();
        $data["id"] = $model->id;

        $this->assertNotEquals($model->type, Arr::get($data, 'type'));

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);
        $resData = $res->json(sprintf('data.%s', self::MUTATION));

        $model->refresh();

        $this->assertEquals($model->type, Arr::get($data, 'type'));
        $this->assertEquals(Arr::get($resData, 'type'), Arr::get($data, 'type'));
    }

    /** @test */
    public function not_found(): void
    {
        $this->loginByAdminManager([Type\UpdatePermission::KEY]);

        $data = TypeDtoTest::data();
        $data['id'] = 9999;

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $res->json());
        $this->assertEquals('validation', $res->json('errors.0.message'));
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginByAdminManager([Type\CreatePermission::KEY]);
        $model = $this->builder->create();

        $data = TypeDtoTest::data();
        $data['id'] = $model->id;

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
                    id: %d,
                    type: "%s",) {
                    id
                    type
                }
            }',
            self::MUTATION,

            $data['id'],
            $data['type'],
        );
    }
}

