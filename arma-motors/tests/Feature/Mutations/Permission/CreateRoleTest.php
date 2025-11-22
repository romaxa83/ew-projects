<?php

namespace Tests\Feature\Mutations\Permission;

use App\Exceptions\ErrorsCode;
use App\Models\Admin\Admin;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class CreateRoleTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function create_success()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::ROLE_CREATE)
            ->create();
        $this->loginAsAdmin($admin);
        $data = [
            'name' => 'tester',
            'locale_ru' => 'ru',
            'name_ru' => 'tester_ru',
            'locale_uk' => 'uk',
            'name_uk' => 'tester_uk',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.roleCreate');

        $locale = \App::getLocale();

        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('translations', $responseData);
        $this->assertCount(2, $responseData['translations']);


        $this->assertEquals($responseData['name'], $data['name']);
        $this->assertEquals($responseData['current']['lang'], $locale);
        $this->assertEquals($responseData['current']['name'], $data['name_' . $locale]);
    }

    /** @test */
    public function fail_exist_role()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::ROLE_CREATE)
            ->create();
        $this->loginAsAdmin($admin);
        $data = [
            'name' => 'tester',
            'locale_ru' => 'ru',
            'name_ru' => 'tester_ru',
            'locale_uk' => 'uk',
            'name_uk' => 'tester_uk',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])->assertOk();

        $secondResponse = $this->postGraphQL(['query' => $this->getQueryStr($data)])->assertOk();

        $this->assertArrayHasKey('errors', $secondResponse->json());
    }

    /** @test */
    public function create_success_as_super_admin()
    {
        $superAdmin = Admin::superAdmin()->first();

        $this->loginAsAdmin($superAdmin);

        $data = [
            'name' => 'tester',
            'locale_ru' => 'ru',
            'name_ru' => 'tester_ru',
            'locale_uk' => 'uk',
            'name_uk' => 'tester_uk',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.roleCreate');

        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('translations', $responseData);
        $this->assertCount(2, $responseData['translations']);
    }

    /** @test */
    public function create_not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::ROLE_CREATE)
            ->create();

        $data = [
            'name' => 'tester',
            'locale_ru' => 'ru',
            'name_ru' => 'tester_ru',
            'locale_uk' => 'uk',
            'name_uk' => 'tester_uk',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)], ['Content-Language' => 'uk']);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function create_not_perm()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);


        $data = [
            'name' => 'tester',
            'locale_ru' => 'ru',
            'name_ru' => 'tester_ru',
            'locale_uk' => 'uk',
            'name_uk' => 'tester_uk',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function create_wrong_empty_name()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::ROLE_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'name' => '',
            'locale_ru' => 'ru',
            'name_ru' => 'Маркетолог',
            'locale_uk' => 'uk',
            'name_uk' => 'Маркетолог_uk',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function create_wrong_short_name()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::ROLE_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'name' => 'ww',
            'locale_ru' => 'ru',
            'name_ru' => 'Маркетолог',
            'locale_uk' => 'uk',
            'name_uk' => 'Маркетолог_uk',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function create_wrong_locale()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::ROLE_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'name' => 'tester',
            'locale_ru' => 'en',
            'name_ru' => 'Маркетолог',
            'locale_uk' => 'uk',
            'name_uk' => 'Маркетолог_uk',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function create_wrong_empty_translation_name()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::ROLE_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'name' => 'tester',
            'locale_ru' => 'en',
            'name_ru' => '',
            'locale_uk' => 'uk',
            'name_uk' => 'Маркетолог_uk',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function create_wrong_short_translation_name()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::ROLE_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'name' => 'tester',
            'locale_ru' => 'en',
            'name_ru' => 'ww',
            'locale_uk' => 'uk',
            'name_uk' => 'Маркетолог_uk',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                roleCreate(input:{
                    name: "%s",
                    translations: [
                        {lang: "%s", name: "%s"}
                        {lang: "%s", name: "%s"}
                    ]
                }) {
                    name
                    current {
                        lang
                        name
                    }
                    translations {
                        name
                        lang
                    }
                }
            }',
            $data['name'],
            $data['locale_ru'],
            $data['name_ru'],
            $data['locale_uk'],
            $data['name_uk'],
        );
    }
}
