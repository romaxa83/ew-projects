<?php

namespace Tests\Feature\Queries\BackOffice\Catalog\Videos\Groups;

use App\GraphQL\Queries\BackOffice\Catalog\Videos\Groups;
use App\Models\Admins\Admin;
use App\Models\Catalog\Videos\Group;
use App\Permissions\Catalog\Videos\Group as GroupPerm;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Tests\Builders\Catalog\Video\GroupBuilder;
use Tests\Builders\Catalog\Video\LinkBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class ForAdminPanelTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    protected GroupBuilder $builder;
    protected LinkBuilder $builderLink;

    public const QUERY = Groups\VideoGroupsQuery::NAME;

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

        $this->builder = app(GroupBuilder::class);
        $this->builderLink = app(LinkBuilder::class);
    }

    /** @test */
    public function list_models(): void
    {
        Group::factory()->times(30)->create();

        $this->loginByAdminManager([GroupPerm\ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrPerPage(10)]);

        $resData = $res->json(sprintf('data.%s.data', self::QUERY));
        $this->assertCount(10, $resData);
    }

    /** @test */
    public function list_filter_by_active(): void
    {
        $model = $this->builder->setActive(false)->create();
        $this->builder->create();
        $this->builder->create();

        $this->loginByAdminManager([GroupPerm\ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrActive('false')]);

        $resData = $res->json(sprintf('data.%s.data', self::QUERY));
        $this->assertCount(1, $resData);
        $this->assertEquals($model->id, Arr::get($resData, '0.id'));

        $this->postGraphQLBackOffice(['query' => $this->getQueryStrActive('true')])
            ->assertJsonStructure([
                'data' => [
                    self::QUERY => [
                        'data' => [['id']]
                    ]
                ]
            ]);
    }

    /** @test */
    public function list_filter_by_id(): void
    {
        $model = $this->builder->withLinks()->create();
        $this->builder->create();
        $this->builder->create();

        $this->loginByAdminManager([GroupPerm\ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrId($model->id)]);

        $resData = $res->json(sprintf('data.%s.data', self::QUERY));
        $this->assertCount(1, $resData);
        $this->assertEquals($model->id, Arr::get($resData, '0.id'));

        $this->assertArrayHasKey('links', Arr::get($resData, '0'));
        $this->assertArrayHasKey('id', Arr::get($resData, '0.links.0'));
        $this->assertCount(2, Arr::get($resData, '0.links'));
    }

    /** @test */
    public function list_filter_by_title(): void
    {
        $title = 'Search';
        $model = $this->builder->withTranslation()->setTitle($title)->create();
        $this->builder->withTranslation()->create();
        $this->builder->withTranslation()->create();

        $this->loginByAdminManager([GroupPerm\ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrTitle($title)]);

        $resData = $res->json(sprintf('data.%s.data', self::QUERY));
        $this->assertCount(1, $resData);
        $this->assertEquals($model->id, Arr::get($resData, '0.id'));
    }

    /** @test */
    public function not_perm(): void
    {
        $this->builder->create();

        $this->loginByAdminManager([GroupPerm\CreatePermission::KEY]);

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

    private function getQueryStrActive($active): string
    {
        return sprintf('
            query {
                %s (active: %s) {
                    data {
                        id
                    }
                }
            }',
            self::QUERY,
            $active
        );
    }

    private function getQueryStrId(int $id): string
    {
        return sprintf('
            query {
                %s (id: %s) {
                    data {
                        id
                        links {
                            id
                        }
                    }
                }
            }',
            self::QUERY,
            $id
        );
    }

    private function getQueryStrTitle(string $title): string
    {
        return sprintf('
            query {
                %s (title: "%s") {
                    data {
                        id
                    }
                }
            }',
            self::QUERY,
            $title
        );
    }
}

