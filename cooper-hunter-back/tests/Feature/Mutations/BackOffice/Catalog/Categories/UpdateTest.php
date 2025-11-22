<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Categories;

use App\GraphQL\Mutations\BackOffice\Catalog\Categories\CategoryUpdateMutation;
use App\Models\Admins\Admin;
use App\Permissions\Catalog\Categories;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Tests\Builders\Catalog\CategoryBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;
use Tests\Unit\Dto\Catalog\CategoryDtoTest;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const MUTATION = CategoryUpdateMutation::NAME;
    protected array $data = [];

    protected CategoryBuilder $builder;

    /** @test */
    public function success(): void
    {
        $parent = $this->builder->create();
        $model = $this->builder->withTranslation()->create();
        $this->loginByAdminManager([Categories\UpdatePermission::KEY]);

        $data = CategoryDtoTest::data();
        $data['id'] = $model->id;
        $data['active'] = 'false';
        $data['parent_id'] = $parent->id;

        $this->assertTrue($model->active);
        $this->assertNotEquals($model->parent_id, Arr::get($data, 'parent_id'));

        foreach ($model->translations as $item) {
            $this->assertNotEquals($item->title, Arr::get($data, 'translations.' . $item->language . '.title'));
            $this->assertNotEquals(
                $item->description,
                Arr::get($data, 'translations.' . $item->language . 'description')
            );
        }

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);
        $resData = $res->json(sprintf('data.%s', self::MUTATION));

        $this->assertEquals($data['translations']['en']['seo_title'], $resData['translation']['seo_title']);
        $this->assertEquals($data['translations']['en']['seo_description'], $resData['translation']['seo_description']);
        $this->assertEquals($data['translations']['en']['seo_h1'], $resData['translation']['seo_h1']);

        $model->refresh();

        $this->assertFalse($model->active);
        $this->assertEquals($model->parent_id, Arr::get($data, 'parent_id'));

        foreach ($model->translations as $item) {
            $this->assertEquals($item->title, Arr::get($data, 'translations.' . $item->language . '.title'));
            $this->assertEquals(
                $item->description,
                Arr::get($data, 'translations.' . $item->language . '.description')
            );
        }
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
        return sprintf(
            '
            mutation {
                %s (
                    id: %d,
                    active: %s,
                    parent_id: %s,
                    slug: "%s",
                    translations: [
                        {language: "%s", title: "%s", description: "%s", seo_title: "%s", seo_description: "%s", seo_h1: "%s"}
                        {language: "%s", title: "%s", description: "%s", seo_title: "%s", seo_description: "%s", seo_h1: "%s"}
                    ]
                ) {
                    id
                    sort
                    active
                    slug
                    parent {
                        id
                    }
                    translation {
                        id
                        title
                        language
                        description
                        seo_title
                        seo_description
                        seo_h1
                    }
                    translations {
                        id
                        title
                        language
                        description
                        seo_title
                        seo_description
                        seo_h1
                    }
                }
            }',
            self::MUTATION,

            $data['id'],
            $data['active'],
            $data['parent_id'],
            $data['slug'],
            $data['translations']['es']['language'],
            $data['translations']['es']['title'],
            $data['translations']['es']['description'],
            $data['translations']['es']['seo_title'],
            $data['translations']['es']['seo_description'],
            $data['translations']['es']['seo_h1'],
            $data['translations']['en']['language'],
            $data['translations']['en']['title'],
            $data['translations']['en']['description'],
            $data['translations']['en']['seo_title'],
            $data['translations']['en']['seo_description'],
            $data['translations']['en']['seo_h1'],

        );
    }

    /** @test */
    public function not_found(): void
    {
        $this->loginByAdminManager([Categories\UpdatePermission::KEY]);

        $data = CategoryDtoTest::data();
        $data['active'] = 'true';
        $data['parent_id'] = 'null';
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

        $data = CategoryDtoTest::data();
        $data['active'] = 'true';
        $data['parent_id'] = 'null';
        $data['id'] = $model->id;

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $res->json());
        $this->assertEquals('No permission', $res->json('errors.0.message'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = app(CategoryBuilder::class);
    }
}

