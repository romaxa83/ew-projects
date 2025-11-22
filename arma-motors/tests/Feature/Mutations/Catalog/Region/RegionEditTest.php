<?php

namespace Tests\Feature\Mutations\Catalog\Region;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Region\Region;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class RegionEditTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function edit_success()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_REGION_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = Region::orderBy(\DB::raw('RAND()'))->first();

        $data = [
            'id' => $model->id,
            'sort' => $model->sort + 1,
            'locale_ru' => 'ru',
            'name_ru' => 'new_region_ru',
            'locale_uk' => 'uk',
            'name_uk' => 'new_region_uk',
        ];

        $this->assertTrue($model->active);
        $this->assertNotEquals($data['sort'], $model->sort);
        $this->assertEquals($data['locale_ru'], $model->current->lang);
        $this->assertNotEquals($data['name_ru'], $model->current->name);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.regionEdit');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('name', $responseData['current']);

        $model->refresh();

        $this->assertEquals($data['sort'], $model->sort);
        $this->assertEquals($data['locale_uk'], $model->current->lang);
        $this->assertEquals($data['name_uk'], $model->current->name);
        $this->assertFalse($model->active);
    }

    /** @test */
    public function edit_success_only_translation()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_REGION_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = Region::orderBy(\DB::raw('RAND()'))->first();

        $data = [
            'id' => $model->id,
            'ru' => 'ru',
            'name_ru' => 'new_region_ru',
            'uk' => 'uk',
            'name_uk' => 'new_region_uk',
        ];

        $this->assertTrue($model->active);
        foreach($model->translations as $translation){
            $this->assertNotEquals($translation->name, $data['name_'.$translation->lang]);
        }

        $this->postGraphQL(['query' => $this->getQueryStrOnlyTranslation($data)]);

        $model->refresh();
        $this->assertTrue($model->active);

        foreach($model->translations as $translation){
            $this->assertEquals($translation->name, $data['name_'.$translation->lang]);
        }
    }

    /** @test */
    public function fail_without_translation()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_REGION_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = Region::orderBy(\DB::raw('RAND()'))->first();

        $data = [
            'id' => $model->id,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithoutTranslation($data)]);

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::ROLE_EDIT)
            ->create();

        $data = [
            'id' => 1,
            'region_id' => 1,
            'sort' => 1,
            'locale_ru' => 'ru',
            'name_ru' => 'new_region_ru',
            'locale_uk' => 'uk',
            'name_uk' => 'new_region_uk',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'id' => 1,
            'region_id' => 1,
            'sort' => 1,
            'locale_ru' => 'ru',
            'name_ru' => 'new_region_ru',
            'locale_uk' => 'uk',
            'name_uk' => 'new_region_uk',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                regionEdit(input:{
                    id: "%s",
                    sort: %d,
                    active: false,
                    translations: [
                        {lang: "%s", name: "%s"}
                        {lang: "%s", name: "%s"}
                    ]
                }) {
                    active
                    sort
                    current {
                        lang
                        name
                    }
                }
            }',
            $data['id'],
            $data['sort'],
            $data['locale_ru'],
            $data['name_ru'],
            $data['locale_uk'],
            $data['name_uk'],
        );
    }

    private function getQueryStrOnlyTranslation(array $data): string
    {
        return sprintf('
            mutation {
                regionEdit(input:{
                    id: "%s",
                    translations: [
                        {lang: "%s", name: "%s"}
                        {lang: "%s", name: "%s"}
                    ]
                }) {
                    active
                    sort
                    current {
                        lang
                        name
                    }
                }
            }',
            $data['id'],
            $data['ru'],
            $data['name_ru'],
            $data['uk'],
            $data['name_uk'],
        );
    }

    private function getQueryStrWithoutTranslation(array $data): string
    {
        return sprintf('
            mutation {
                regionEdit(input:{
                    id: "%s",
                }) {
                    active
                }
            }',
            $data['id']
        );
    }

}

