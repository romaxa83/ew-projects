<?php

namespace Tests\Feature\Mutations\Admin\Admin;

use App\Exceptions\ErrorsCode;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class EditProfileTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerm(Permissions::ADMIN_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'name' => 'new_name',
            'phone' => '38444444444444',
            'lang' => 'ru',
        ];

        $this->assertNotEquals($admin->name, $data['name']);
        $this->assertNotEquals($admin->phone, $data['phone']);
        $this->assertNotEquals($admin->lang, $data['lang']);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.adminEditProfile');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('phone', $responseData);
        $this->assertArrayHasKey('lang', $responseData);
        $this->assertArrayHasKey('locale', $responseData);

        $this->assertEquals($admin->id, $responseData['id']);
        $this->assertEquals($responseData['name'], $data['name']);
        $this->assertEquals($responseData['phone'], $data['phone']);

        $admin->refresh();

        $this->assertEquals($admin->name, $data['name']);
        $this->assertEquals($admin->phone, $data['phone']);
    }

    /** @test */
    public function edit_only_name()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerm(Permissions::ADMIN_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'name' => 'new_name',
        ];

        $this->assertNotEquals($admin->name, $data['name']);

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyName($data)]);

        $responseData = $response->json('data.adminEditProfile');

        $this->assertEquals($admin->id, $responseData['id']);
        $this->assertEquals($responseData['name'], $data['name']);
        $this->assertEquals($responseData['lang'], $admin->lang);

        $admin->refresh();

        $this->assertEquals($admin->name, $responseData['name']);
        $this->assertEquals($admin->phone, $responseData['phone']);
        $this->assertEquals($admin->lang, $responseData['lang']);
    }

    /** @test */
    public function edit_wrong_lang()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerm(Permissions::ADMIN_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'name' => 'new_name',
            'phone' => '38444444444444',
            'lang' => 'en',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function not_auth()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerm(Permissions::ADMIN_EDIT)
            ->create();

        $data = [
            'name' => 'new_name',
            'phone' => '38444444444444',
            'lang' => 'ru',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                adminEditProfile(input:{
                    name: "%s",
                    phone: "%s",
                    lang: %s,
                }) {
                    id
                    name
                    phone
                    lang
                    locale {
                        name
                        locale
                    }
                }
            }',
            $data['name'],
            $data['phone'],
            $data['lang'],
        );
    }

    private function getQueryStrOnlyName(array $data): string
    {
        return sprintf('
            mutation {
                adminEditProfile(input:{
                    name: "%s",
                }) {
                    id
                    name
                    phone
                    lang
                    locale {
                        name
                        locale
                    }
                }
            }',
            $data['name'],
        );
    }
}


