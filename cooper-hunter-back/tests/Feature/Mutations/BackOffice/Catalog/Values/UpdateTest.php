<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Values;

use App\GraphQL\Mutations\BackOffice\Catalog\Features\Values\FeatureValueUpdateMutation;
use App\Models\Admins\Admin;
use App\Models\Catalog\Features\Feature;
use App\Permissions\Catalog\Features\Values;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Catalog\ValueBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;
use Tests\Unit\Dto\Catalog\ValueDtoTest;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const MUTATION = FeatureValueUpdateMutation::NAME;
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

        $feature = Feature::factory()->create();

        $data = ValueDtoTest::data();
        $data['feature_id'] = $feature->id;
        $data['id'] = $model->id;
        $data['active'] = 'false';

        $this->assertTrue($model->active);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);
        $res->json(sprintf('data.%s', self::MUTATION));

        $model->refresh();

        $this->assertFalse($model->active);
    }

    /** @test */
    public function not_found(): void
    {
        $this->loginByAdminManager([Values\UpdatePermission::KEY]);

        $feature = Feature::factory()->create();

        $data = ValueDtoTest::data();
        $data['feature_id'] = $feature->id;
        $data['active'] = 'true';
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

        $feature = Feature::factory()->create();

        $data = ValueDtoTest::data();
        $data['feature_id'] = $feature->id;
        $data['active'] = 'true';
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
                    feature_id: "%s",
                    id: %d,
                    active: %s,
                    title: "%s",
                ) {
                    id
                    sort
                    active
                    title
                }
            }',
            self::MUTATION,
            $data['feature_id'],
            $data['id'],
            $data['active'],
            $data['title'],
        );
    }
}

