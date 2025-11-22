<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Categories;

use App\GraphQL\Mutations\BackOffice\Catalog\Categories\CategoryDeleteMutation;
use App\Models\Admins\Admin;
use App\Models\Catalog\Categories\Category;
use App\Permissions\Catalog\Categories;
use Core\Enums\Messages\MessageTypeEnum;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Catalog\CategoryBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const MUTATION = CategoryDeleteMutation::NAME;
    protected array $data = [];

    protected CategoryBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = app(CategoryBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        $model = $this->builder->create();
        $this->loginByAdminManager([Categories\DeletePermission::KEY]);

        $data['id'] = $model->id;

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);

        $this->assertEquals(MessageTypeEnum::SUCCESS, $res->json(sprintf('data.%s.type', self::MUTATION)));
        $this->assertEquals(
            __('messages.catalog.category.actions.delete.success.one-entity'),
            $res->json(sprintf('data.%s.message', self::MUTATION))
        );

        $this->assertNull(Category::find($data['id']));
    }

    /** @test */
    public function not_found(): void
    {
        $this->loginByAdminManager([Categories\DeletePermission::KEY]);

        $data['id'] = 9999;

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $res->json());
        $this->assertEquals('validation', $res->json('errors.0.message'));
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginByAdminManager([Categories\CreatePermission::KEY]);
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

