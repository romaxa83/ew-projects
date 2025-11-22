<?php

namespace Tests\Feature\Queries\BackOffice\Catalog\Features\Values;

use App\GraphQL\Queries\BackOffice\Catalog\Features\Values;
use App\Models\Admins\Admin;
use App\Models\Catalog\Features\Feature;
use App\Models\Catalog\Features\Value;
use App\Permissions\Catalog\Features\Values as ValuePerm;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Tests\Builders\Catalog\ValueBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class ForAdminPanelTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const QUERY = Values\ValuesQuery::NAME;
    protected ValueBuilder $builder;

    /** @test */
    public function list_models(): void
    {
        $values = Value::factory()
            ->times(10)
            ->for(Feature::factory())
            ->create();

        $this->loginByAdminManager([ValuePerm\ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($values->first()->feature_id)]);

        $resData = $res->json(sprintf('data.%s', self::QUERY));
        $this->assertCount(10, $resData);
    }

    protected function loginByAdminManager(array $permissionKey): Admin
    {
        return $this->loginAsAdmin()
            ->assignRole(
                $this->generateRole('Admin manager', $permissionKey, Admin::GUARD)
            );
    }

    private function getQueryStr(int $featureId, ?int $valueId = null): string
    {
        return sprintf(
            '
            query {
                %s (feature_id: %d %s) {
                    id
                }
            }',
            self::QUERY,
            $featureId,
            $valueId ? 'value_id: ' . $valueId : '',
        );
    }

    /** @test */
    public function list_filter_by_active(): void
    {
        $feature = Feature::factory()->create();

        Value::factory()
            ->times(5)
            ->for($feature)
            ->create(
                [
                    'active' => false,
                ]
            );

        Value::factory()
            ->times(5)
            ->for($feature)
            ->create(
                [
                    'active' => true,
                ]
            );

        $this->loginByAdminManager([ValuePerm\ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrActive($feature->id, 'false')]);

        $resData = $res->json(sprintf('data.%s', self::QUERY));
        $this->assertCount(5, $resData);

        $this->postGraphQLBackOffice(['query' => $this->getQueryStrActive($feature->id, 'true')])
            ->assertJsonCount(5, 'data.' . self::QUERY)
            ->assertJsonStructure([
                'data' => [
                    self::QUERY => [
                        ['id']
                    ]
                ]
            ]);
    }

    private function getQueryStrActive(int $featureId, string $active): string
    {
        return sprintf(
            '
            query {
                %s (feature_id: %d, active: %s) {
                    id
                }
            }',
            self::QUERY,
            $featureId,
            $active,
        );
    }

    /** @test */
    public function list_filter_by_id(): void
    {
        $feature = Feature::factory()->create();

        $values = Value::factory()
            ->times(5)
            ->for($feature)
            ->create();

        $this->loginByAdminManager([ValuePerm\ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(
            ['query' => $this->getQueryStrId($feature->id, $valueId = $values->first()->id)]
        );

        $resData = $res->json(sprintf('data.%s', self::QUERY));
        $this->assertCount(1, $resData);
        $this->assertEquals($valueId, Arr::get($resData, '0.id'));
    }

    private function getQueryStrId(int $featureId, int $valueId): string
    {
        return sprintf(
            '
            query {
                %s (feature_id: %s, value_id: %s) {
                    id
                }
            }',
            self::QUERY,
            $featureId,
            $valueId,
        );
    }

    /** @test */
    public function not_perm(): void
    {
        $value = Value::factory()->create();

        $this->loginByAdminManager([ValuePerm\CreatePermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($value->feature_id, $value->id)]);

        $this->assertArrayHasKey('errors', $res->json());
        $this->assertEquals('No permission', $res->json('errors.0.message'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = app(ValueBuilder::class);
    }
}

