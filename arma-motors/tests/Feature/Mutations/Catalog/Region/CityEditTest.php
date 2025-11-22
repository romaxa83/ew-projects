<?php

namespace Tests\Feature\Mutations\Catalog\Region;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Region\City;
use App\Models\Catalogs\Region\Region;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class CityEditTest extends TestCase
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
            ->createRoleWithPerm(Permissions::CATALOG_CITY_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $region = Region::where('id', 10)->first();
        $city = City::where('id', 1)->first();

        $data = [
            'id' => $city->id,
            'region_id' => $region->id,
            'sort' => $city->sort + 1,
            'locale_ru' => 'ru',
            'name_ru' => 'new_city_ru',
            'locale_uk' => 'uk',
            'name_uk' => 'new_city_uk',
        ];

        $this->assertNotEquals($data['region_id'], $city->region_id);
        $this->assertTrue($city->active);
        $this->assertNotEquals($data['sort'], $city->sort);
        $this->assertEquals($data['locale_ru'], $city->current->lang);
        $this->assertNotEquals($data['name_ru'], $city->current->name);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.cityEdit');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('name', $responseData['current']);
        $this->assertArrayHasKey('region', $responseData);
        $this->assertArrayHasKey('id', $responseData['region']);

        $city->refresh();

        $this->assertEquals($data['region_id'], $city->region_id);
        $this->assertEquals($data['sort'], $city->sort);
        $this->assertEquals($data['locale_uk'], $city->current->lang);
        $this->assertEquals($data['name_uk'], $city->current->name);
        $this->assertFalse($city->active);
    }

    /** @test */
    public function edit_success_only_translation()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_CITY_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $city = City::orderBy(\DB::raw('RAND()'))->first();

        $data = [
            'id' => $city->id,
            'ru' => 'ru',
            'name_ru' => 'new_city_ru',
            'uk' => 'uk',
            'name_uk' => 'new_city_uk',
        ];

        $this->assertTrue($city->active);
        foreach($city->translations as $translation){
            $this->assertNotEquals($translation->name, $data['name_'.$translation->lang]);
        }

        $this->postGraphQL(['query' => $this->getQueryStrOnlyTranslation($data)]);

        $city->refresh();
        $this->assertTrue($city->active);

        foreach($city->translations as $translation){
            $this->assertEquals($translation->name, $data['name_'.$translation->lang]);
        }
    }

    /** @test */
    public function fail_without_translation()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_CITY_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $city = City::orderBy(\DB::raw('RAND()'))->first();

        $data = [
            'id' => $city->id,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithoutTranslation($data)]);

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function edit_not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::ROLE_EDIT)
            ->create();

        $data = [
            'id' => 1,
            'region_id' => 1,
            'sort' => 1,
            'locale_ru' => 'ru',
            'name_ru' => 'new_city_ru',
            'locale_uk' => 'uk',
            'name_uk' => 'new_city_uk',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function edit_not_perm()
    {
        $admin = $this->adminBuilder()
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'id' => 1,
            'region_id' => 1,
            'sort' => 1,
            'locale_ru' => 'ru',
            'name_ru' => 'new_city_ru',
            'locale_uk' => 'uk',
            'name_uk' => 'new_city_uk',
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
                cityEdit(input:{
                    id: "%s",
                    regionId: "%s",
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
                    region {
                        id
                    }
                }
            }',
            $data['id'],
            $data['region_id'],
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
                cityEdit(input:{
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
                    region {
                        id
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
                cityEdit(input:{
                    id: "%s",
                }) {
                    active
                }
            }',
            $data['id']
        );
    }

}

