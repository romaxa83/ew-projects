<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Certificates\Certificate;

use App\GraphQL\Mutations\BackOffice\Catalog\Certificates\Certificate\CertificateUpdateMutation;
use App\Models\Admins\Admin;
use App\Permissions\Catalog\Certificates\Certificate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Tests\Builders\Catalog\Certificates\CertificateBuilder;
use Tests\Builders\Catalog\Certificates\TypeBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;
use Tests\Unit\Dto\Catalog\Certificate\CertificateDtoTest;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const MUTATION = CertificateUpdateMutation::NAME;

    protected TypeBuilder $builderType;
    protected CertificateBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builderType = app(TypeBuilder::class);
        $this->builder = app(CertificateBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        $model = $this->builder->create();
        $type = $this->builderType->create();
        $this->loginByAdminManager([Certificate\UpdatePermission::KEY]);

        $data = CertificateDtoTest::data();
        $data["id"] = $model->id;
        $data["type_id"] = $type->id;

        $this->assertNotEquals($model->number, Arr::get($data, 'number'));
        $this->assertNotEquals($model->link, Arr::get($data, 'link'));
        $this->assertNotEquals($model->certificate_type_id, Arr::get($data, 'type_id'));

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);
        $resData = $res->json(sprintf('data.%s', self::MUTATION));

        $this->assertArrayHasKey('id', $resData);

        $model->refresh();

        $this->assertEquals($model->number, Arr::get($data, 'number'));
        $this->assertEquals($model->link, Arr::get($data, 'link'));
        $this->assertEquals($model->certificate_type_id, Arr::get($data, 'type_id'));
    }

    /** @test */
    public function not_found(): void
    {
        $type = $this->builderType->create();
        $this->loginByAdminManager([Certificate\UpdatePermission::KEY]);

        $data = CertificateDtoTest::data();
        $data["id"] = 999;
        $data["type_id"] = $type->id;

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $res->json());
        $this->assertEquals('validation', $res->json('errors.0.message'));
    }

    /** @test */
    public function not_perm(): void
    {
        $model = $this->builder->create();
        $type = $this->builderType->create();
        $this->loginByAdminManager([Certificate\CreatePermission::KEY]);

        $data = CertificateDtoTest::data();
        $data['id'] = $model->id;
        $data["type_id"] = $type->id;

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
                    type_id: %d,
                    number: "%s",
                    link: "%s",
                ) {
                    id
                }
            }',
            self::MUTATION,

            $data['id'],
            $data['type_id'],
            $data['number'],
            $data['link'],
        );
    }
}

