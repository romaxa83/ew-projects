<?php

namespace Tests\Feature\Queries\BackOffice\Catalog\Videos\Links;

use App\GraphQL\Queries\BackOffice\Catalog\Videos\Links;
use App\Models\Admins\Admin;
use App\Models\Catalog\Videos\VideoLink;
use App\Permissions\Catalog\Videos\Link as LinkPerm;
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

    protected GroupBuilder $builderGroup;
    protected LinkBuilder $builder;

    public const QUERY = Links\VideoLinksQuery::NAME;

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

        $this->builderGroup = app(GroupBuilder::class);
        $this->builder = app(LinkBuilder::class);
    }

    /** @test */
    public function list_models(): void
    {
        $group = $this->builderGroup->create();
        VideoLink::factory()->times(30)->create(['group_id' => $group->id]);

        $this->loginByAdminManager([LinkPerm\ListPermission::KEY]);

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

        $this->loginByAdminManager([LinkPerm\ListPermission::KEY]);

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
        $model = $this->builder->create();
        $this->builder->create();
        $this->builder->create();

        $this->loginByAdminManager([LinkPerm\ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrId($model->id)]);

        $resData = $res->json(sprintf('data.%s.data', self::QUERY));
        $this->assertCount(1, $resData);
        $this->assertEquals($model->id, Arr::get($resData, '0.id'));
    }

    /** @test */
    public function list_filter_by_link(): void
    {
        $link = 'https://google.com';
        $model = $this->builder->setLink($link)->create();
        $this->builder->create();
        $this->builder->create();
        $this->builder->create();

        $this->loginByAdminManager([LinkPerm\ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrLink($link)]);

        $resData = $res->json(sprintf('data.%s.data', self::QUERY));
        $this->assertCount(1, $resData);
        $this->assertEquals($model->id, Arr::get($resData, '0.id'));
    }

    /** @test */
    public function list_filter_by_group_id(): void
    {
        $group = $this->builderGroup->create();
        $this->builder->setGroupId($group->id)->create();
        $this->builder->setGroupId($group->id)->create();
        $this->builder->create();

        $this->loginByAdminManager([LinkPerm\ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrGroupId($group->id)]);

        $resData = $res->json(sprintf('data.%s.data', self::QUERY));
        $this->assertCount(2, $resData);

        $this->assertArrayHasKey('group', Arr::get($resData, '0'));
        $this->assertArrayHasKey('id', Arr::get($resData, '0.group'));
    }

    /** @test */
    public function list_filter_by_title(): void
    {
        $title = 'Search';
        $model = $this->builder->withTranslation()->setTitle($title)->create();
        $this->builder->withTranslation()->create();
        $this->builder->withTranslation()->create();

        $this->loginByAdminManager([LinkPerm\ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrTitle($title)]);

        $resData = $res->json(sprintf('data.%s.data', self::QUERY));
        $this->assertCount(1, $resData);
        $this->assertEquals($model->id, Arr::get($resData, '0.id'));
    }

    /** @test */
    public function not_perm(): void
    {
        $this->builder->create();

        $this->loginByAdminManager([LinkPerm\CreatePermission::KEY]);

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
                    }
                }
            }',
            self::QUERY,
            $id
        );
    }

    private function getQueryStrGroupId(int $id): string
    {
        return sprintf('
            query {
                %s (group_id: %s) {
                    data {
                        id
                        group {
                            id
                        }
                    }
                }
            }',
            self::QUERY,
            $id
        );
    }

    private function getQueryStrLink(string $link): string
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
            $link
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


