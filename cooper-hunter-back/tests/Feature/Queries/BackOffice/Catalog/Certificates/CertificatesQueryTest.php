<?php

namespace Tests\Feature\Queries\BackOffice\Catalog\Certificates;

use App\GraphQL\Queries\BackOffice\Catalog\Certificates\CertificatesQuery;
use App\Models\Admins\Admin;
use App\Models\Catalog\Certificates\Certificate;
use App\Permissions\Catalog\Certificates\Certificate\CreatePermission;
use App\Permissions\Catalog\Certificates\Certificate\ListPermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Tests\Builders\Catalog\Certificates\CertificateBuilder;
use Tests\Builders\Catalog\Certificates\TypeBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class CertificatesQueryTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    protected TypeBuilder $builderType;
    protected CertificateBuilder $builder;

    public const QUERY = CertificatesQuery::NAME;

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

        $this->builderType = app(TypeBuilder::class);
        $this->builder = app(CertificateBuilder::class);
    }

    /** @test */
    public function list_models(): void
    {
        Certificate::factory()->count(30)->create();
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
    public function list_filter_by_type_id(): void
    {
        $type = $this->builderType->create();
        $model = $this->builder->setTypeId($type->id)->create();
        $this->builder->create();
        $this->builder->create();
        $this->builder->create();

        $this->loginByAdminManager([ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrType($type->id)]);

        $resData = $res->json(sprintf('data.%s.data', self::QUERY));
        $this->assertCount(1, $resData);
        $this->assertEquals($model->id, Arr::get($resData, '0.id'));
    }

    /** @test */
    public function list_filter_by_link(): void
    {
        $value = 'some_link';
        $model = $this->builder->setLink($value)->create();
        $this->builder->create();
        $this->builder->create();
        $this->builder->create();

        $this->loginByAdminManager([ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrLink($value)]);

        $resData = $res->json(sprintf('data.%s.data', self::QUERY));
        $this->assertCount(1, $resData);
        $this->assertEquals($model->id, Arr::get($resData, '0.id'));
    }

    /** @test */
    public function list_filter_by_number(): void
    {
        $value = 'some_link';
        $model = $this->builder->setNumber($value)->create();
        $this->builder->create();
        $this->builder->create();
        $this->builder->create();

        $this->loginByAdminManager([ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrNumber($value)]);

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

    private function getQueryStrLink(string $value): string
    {
        return sprintf('
            query {
                %s (link: "%s") {
                    data {
                        id
                    }
                }
            }',
            self::QUERY,
            $value
        );
    }

    private function getQueryStrNumber(string $value): string
    {
        return sprintf('
            query {
                %s (number: "%s") {
                    data {
                        id
                    }
                }
            }',
            self::QUERY,
            $value
        );
    }
}


