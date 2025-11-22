<?php

namespace Tests\Feature\Mutations\Permission;

use App\Exceptions\ErrorsCode;
use App\Models\Admin\Admin;
use App\Models\Permission\Role;
use App\Types\Permissions;
use Database\Factories\Permission\RoleTranslationFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class EditRoleTest extends TestCase
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
            ->createRoleWithPerm(Permissions::ROLE_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $role = $this->getRoleWithTranslations();

        $data = [
            'id' => $role->id,
            'locale_ru' => 'ru',
            'name_ru' => 'tester_ru',
            'locale_uk' => 'uk',
            'name_uk' => 'tester_uk',
        ];

        $this->assertEquals($data['locale_ru'], $role->current->lang);
        $this->assertNotEquals($data['name_ru'], $role->current->name);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.roleEdit');

        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('translations', $responseData);
        $this->assertCount(2, $responseData['translations']);

        $role->refresh();

        $this->assertEquals($data['locale_uk'], $role->current->lang);
        $this->assertEquals($data['name_uk'], $role->current->name);
    }

    /** @test */
    public function edit_not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::ROLE_EDIT)
            ->create();

        $role = $this->getRoleWithTranslations();

        $data = [
            'id' => $role->id,
            'locale_ru' => 'ru',
            'name_ru' => 'tester_ru',
            'locale_uk' => 'uk',
            'name_uk' => 'tester_uk',
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

        $role = $this->getRoleWithTranslations();

        $data = [
            'id' => $role->id,
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
    public function edit_wrong_locale()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::ROLE_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $role = $this->getRoleWithTranslations();

        $data = [
            'id' => $role->id,
            'locale_ru' => 'en',
            'name_ru' => 'Маркетолог',
            'locale_uk' => 'uk',
            'name_uk' => 'Маркетолог_uk',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function edit_wrong_empty_translation_name()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::ROLE_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $role = $this->getRoleWithTranslations();

        $data = [
            'id' => $role->id,
            'locale_ru' => 'en',
            'name_ru' => '',
            'locale_uk' => 'uk',
            'name_uk' => 'Маркетолог_uk',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function edit_wrong_short_translation_name()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::ROLE_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $role = $this->getRoleWithTranslations();

        $data = [
            'id' => $role->id,
            'locale_ru' => 'en',
            'name_ru' => 'ww',
            'locale_uk' => 'uk',
            'name_uk' => 'Маркетолог_uk',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
    }

    private function getRoleWithTranslations()
    {
        $role = Role::factory()->new(['guard_name' => Admin::GUARD])->create();
        RoleTranslationFactory::new(['role_id' => $role->id])->create(['lang' => 'ru']);
        RoleTranslationFactory::new(['role_id' => $role->id])->create(['lang' => 'uk']);

        return $role;
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                roleEdit(input:{
                    id: "%s",
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
            $data['id'],
            $data['locale_ru'],
            $data['name_ru'],
            $data['locale_uk'],
            $data['name_uk'],
        );
    }
}

