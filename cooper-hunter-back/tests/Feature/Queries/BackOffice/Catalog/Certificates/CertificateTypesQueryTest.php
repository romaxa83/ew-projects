<?php

namespace Tests\Feature\Queries\BackOffice\Catalog\Certificates;

use App\GraphQL\Queries\BackOffice\Catalog\Certificates\CertificateTypesQuery;
use App\Models\Admins\Admin;
use App\Models\Catalog\Certificates\CertificateType;
use App\Permissions\Catalog\Certificates\Type\CreatePermission;
use App\Permissions\Catalog\Certificates\Type\ListPermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Tests\Builders\Catalog\Certificates\TypeBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class CertificateTypesQueryTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    protected TypeBuilder $builder;

    public const QUERY = CertificateTypesQuery::NAME;

    protected function loginByAdminManager(array $permissionKey): Admin
    {
        return $this->loginAsAdmin()
            ->assignRole(
                $this->generateRole('Admin manager', $permissionKey, Admin::GUARD)
            );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = app(TypeBuilder::class);
    }

    /** @test */
    public function list_models(): void
    {
        CertificateType::factory()->count(30)->create();
        $this->loginByAdminManager([ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrPerPage(10)]);

        $resData = $res->json(sprintf('data.%s.data', self::QUERY));
        $this->assertCount(10, $resData);
    }

    /** @test */
    public function list_filter_by_id(): void
    {
        $model = $this->builder->create();
        $this->builder->create();
        $this->builder->create();

        $this->loginByAdminManager([ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrId($model->id)]);

        $resData = $res->json(sprintf('data.%s.data', self::QUERY));
        $this->assertCount(1, $resData);
        $this->assertEquals($model->id, Arr::get($resData, '0.id'));
    }

    /** @test */
    public function list_filter_by_type(): void
    {
        $type = 'AX444';
        $model = $this->builder->setType($type)->create();
        $this->builder->create();
        $this->builder->create();
        $this->builder->create();

        $this->loginByAdminManager([ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrType($type)]);

        $resData = $res->json(sprintf('data.%s.data', self::QUERY));
        $this->assertCount(1, $resData);
        $this->assertEquals($model->id, Arr::get($resData, '0.id'));
    }

    /** @test */
    public function not_perm(): void
    {
        $this->builder->create();

        $this->loginByAdminManager([CreatePermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrPerPage(10)]);

        $this->assertArrayHasKey('errors', $res->json());
        $this->assertEquals('No permission', $res->json('errors.0.message'));
    }

    private function getQueryStrPerPage(int $perPage): string
    {
        return sprintf('
            query {
                %s (per_page: %d) {
                    data {
                        id
                    }
                }
            }',
            self::QUERY,
            $perPage
        );
    }

    private function getQueryStrId(int $id): string
    {
        return sprintf('
            query {
                %s (id: %s) {
                    data {
                        id
                    }
                }
            }',
            self::QUERY,
            $id
        );
    }

    private function getQueryStrType(string $link): string
    {
        return sprintf('
            query {
                %s (type: "%s") {
                    data {
                        id
                    }
                }
            }',
            self::QUERY,
            $link
        );
    }
}


