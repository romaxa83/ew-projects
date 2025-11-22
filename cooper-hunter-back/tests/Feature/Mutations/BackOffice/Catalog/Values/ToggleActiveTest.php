<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Values;

use App\GraphQL\Mutations\BackOffice\Catalog\Features\Values\FeatureValueToggleActiveMutation;
use App\Models\Admins\Admin;
use App\Permissions\Catalog\Features\Values;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Catalog\ValueBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class ToggleActiveTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const MUTATION = FeatureValueToggleActiveMutation::NAME;
    protected array $data = [];

    protected ValueBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = app(ValueBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        $model = $this->builder->create();
        $this->loginByAdminManager([Values\UpdatePermission::KEY]);

        $data['id'] = $model->id;

        $this->assertTrue($model->active);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);
        $this->assertFalse($res->json(sprintf('data.%s.active', self::MUTATION)));

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);
        $this->assertTrue( $res->json(sprintf('data.%s.active', self::MUTATION)));
    }

    /** @test */
    public function not_found(): void
    {
        $this->loginByAdminManager([Values\UpdatePermission::KEY]);

        $data['id'] = 9999;

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $res->json());
        $this->assertEquals('validation', $res->json('errors.0.message'));
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginByAdminManager([Values\CreatePermission::KEY]);
        $model = $this->builder->create();

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
                ) {
                    id
                    sort
                    active
                }
            }',
            self::MUTATION,
            $data['id']
        );
    }
}

