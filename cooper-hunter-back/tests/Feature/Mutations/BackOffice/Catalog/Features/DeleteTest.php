<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Features;

use App\GraphQL\Mutations\BackOffice\Catalog\Features\Features\FeatureDeleteMutation;
use App\Models\Admins\Admin;
use App\Models\Catalog\Features\Feature;
use App\Permissions\Catalog\Features\Features;
use Core\Enums\Messages\MessageTypeEnum;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Catalog\FeatureBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const MUTATION = FeatureDeleteMutation::NAME;
    protected array $data = [];

    protected FeatureBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = app(FeatureBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        $model = $this->builder->create();
        $this->loginByAdminManager([Features\DeletePermission::KEY]);

        $data['id'] = $model->id;

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);

        $this->assertEquals(MessageTypeEnum::SUCCESS, $res->json(sprintf('data.%s.type', self::MUTATION)));
        $this->assertEquals(
            __('messages.catalog.feature.feature.actions.delete.success.one-entity'),
            $res->json(sprintf('data.%s.message', self::MUTATION))
        );

        $this->assertNull(Feature::find($data['id']));
    }

    /** @test */
    public function not_found(): void
    {
        $this->loginByAdminManager([Features\DeletePermission::KEY]);

        $data['id'] = 9999;

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $res->json());
        $this->assertEquals('validation', $res->json('errors.0.message'));
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginByAdminManager([Features\CreatePermission::KEY]);
        $model = $this->builder->withTranslation()->create();

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
                    type
                    message
                }
            }',
            self::MUTATION,
            $data['id']
        );
    }
}

