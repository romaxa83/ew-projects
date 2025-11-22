<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Features;

use App\GraphQL\Mutations\BackOffice\Catalog\Features\Features\FeatureUpdateMutation;
use App\Models\Admins\Admin;
use App\Permissions\Catalog\Features\Features;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Tests\Builders\Catalog\FeatureBuilder;
use Tests\Builders\Catalog\ValueBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;
use Tests\Unit\Dto\Catalog\FeatureDtoTest;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const MUTATION = FeatureUpdateMutation::NAME;
    protected array $data = [];

    protected FeatureBuilder $builder;
    protected ValueBuilder $builderValue;

    /** @test */
    public function success(): void
    {
        $model = $this->builder->withTranslation()->create();
        $this->loginByAdminManager([Features\UpdatePermission::KEY]);

        $data = FeatureDtoTest::data();
        $data['id'] = $model->id;
        $data['active'] = 'false';

        $this->assertTrue($model->active);
        $this->assertEmpty($model->values);

        foreach ($model->translations as $item) {
            $this->assertNotEquals($item->title, Arr::get($data, 'translations.'.$item->language.'.title'));
            $this->assertNotEquals($item->description, Arr::get($data, 'translations.'.$item->language.'description'));
        }

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);
        $res->json(sprintf('data.%s', self::MUTATION));

        $model->refresh();

        $this->assertFalse($model->active);

        foreach ($model->translations as $item) {
            $this->assertEquals($item->title, Arr::get($data, 'translations.'.$item->language.'.title'));
            $this->assertEquals($item->description, Arr::get($data, 'translations.'.$item->language.'.description'));
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
                    translations: [
                        {language: "%s", title: "%s", description: "%s"}
                        {language: "%s", title: "%s", description: "%s"}
                    ]
                ) {
                    id
                    sort
                    active
                    values {
                        id
                    }
                    translation {
                        id
                        title
                        slug
                        language
                        description
                    }
                    translations {
                        id
                        title
                        slug
                        language
                        description
                    }
                }
            }',
            self::MUTATION,

            $data['id'],
            $data['active'],
            $data['translations']['es']['language'],
            $data['translations']['es']['title'],
            $data['translations']['es']['description'],
            $data['translations']['en']['language'],
            $data['translations']['en']['title'],
            $data['translations']['en']['description'],
        );
    }

    /** @test */
    public function not_found(): void
    {
        $this->builder->withTranslation()->create();
        $this->loginByAdminManager([Features\UpdatePermission::KEY]);

        $data = FeatureDtoTest::data();
        $data['id'] = 9999;
        $data['active'] = 'false';

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $res->json());
        $this->assertEquals('validation', $res->json('errors.0.message'));
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginByAdminManager([Features\CreatePermission::KEY]);
        $model = $this->builder->withTranslation()->create();

        $data = FeatureDtoTest::data();
        $data['id'] = $model->id;
        $data['active'] = 'false';

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $res->json());
        $this->assertEquals('No permission', $res->json('errors.0.message'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = app(FeatureBuilder::class);
        $this->builderValue = app(ValueBuilder::class);
    }
}


