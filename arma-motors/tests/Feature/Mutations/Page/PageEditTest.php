<?php

namespace Tests\Feature\Mutations\Page;

use App\Exceptions\ErrorsCode;
use App\Models\Page\Page;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class PageEditTest extends TestCase
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
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::PAGE_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $page = Page::where('id', 1)->first();

        $data = $this->data($page->id);

        $this->assertNotEquals($page->current->name, $data[$page->current->lang]['name']);
        $this->assertNotEquals($page->current->text, $data[$page->current->lang]['text']);
        $this->assertNull($page->current->sub_text);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $page->refresh();
        $this->assertEquals($page->current->name, $data[$page->current->lang]['name']);
        $this->assertEquals($page->current->text, $data[$page->current->lang]['text']);
        $this->assertEquals($page->current->text, $data[$page->current->lang]['text']);
        $this->assertNull($page->current->sub_text);

        $responseData = $response->json('data.pageEdit');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('alias', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('name', $responseData['current']);
        $this->assertArrayHasKey('text', $responseData['current']);
        $this->assertArrayHasKey('subText', $responseData['current']);
    }

    /** @test */
    public function with_sub_text()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::PAGE_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $page = Page::where('id', 1)->first();

        $data = $this->data($page->id);
        $data['ru']['subText'] = 'sub text ru';
        $data['uk']['subText'] = 'sub text uk';

        $this->assertNotEquals($page->current->name, $data[$page->current->lang]['name']);
        $this->assertNotEquals($page->current->text, $data[$page->current->lang]['text']);
        $this->assertNull($page->current->sub_text);

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithSubText($data)])
            ->assertOk();

        $page->refresh();
        $this->assertEquals($page->current->name, $data[$page->current->lang]['name']);
        $this->assertEquals($page->current->text, $data[$page->current->lang]['text']);
        $this->assertEquals($page->current->sub_text, $data[$page->current->lang]['subText']);

        $responseData = $response->json('data.pageEdit');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('alias', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('name', $responseData['current']);
        $this->assertArrayHasKey('text', $responseData['current']);
        $this->assertArrayHasKey('subText', $responseData['current']);
    }

    /** @test */
    public function not_found_model()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::PAGE_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $data = $this->data(9999);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.not found model'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_FOUND, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::PAGE_EDIT)
            ->create();

        $data = $this->data(1);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::USER_CAR_LIST)
            ->create();
        $this->loginAsAdmin($admin);

        $data = $this->data(1);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function data(
        $id
    ): array
    {
        return [
            'id' => $id,
            'ru' => [
                'lang' => 'ru',
                'name' => 'name update page ru',
                'text' => 'name update page text ru',
            ],
            'uk' => [
                'lang' => 'uk',
                'name' => 'name update page uk',
                'text' => 'name update page text uk',
            ],
        ];
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                pageEdit(input:{
                    id: "%s",
                    translations: [
                        {lang: "%s", name: "%s", text: "%s"}
                        {lang: "%s", name: "%s", text: "%s"}
                    ]
                }) {
                    id
                    alias
                    current {
                        lang
                        name
                        text
                        subText
                    }
                }
            }',
            $data['id'],
            $data['ru']['lang'],
            $data['ru']['name'],
            $data['ru']['text'],
            $data['uk']['lang'],
            $data['uk']['name'],
            $data['uk']['text'],
        );
    }

    private function getQueryStrWithSubText(array $data): string
    {
        return sprintf('
            mutation {
                pageEdit(input:{
                    id: "%s",
                    translations: [
                        {lang: "%s", name: "%s", text: "%s", subText: "%s"}
                        {lang: "%s", name: "%s", text: "%s", subText: "%s"}
                    ]
                }) {
                    id
                    alias
                    current {
                        lang
                        name
                        text
                        subText
                    }
                }
            }',
            $data['id'],
            $data['ru']['lang'],
            $data['ru']['name'],
            $data['ru']['text'],
            $data['ru']['subText'],
            $data['uk']['lang'],
            $data['uk']['name'],
            $data['uk']['text'],
            $data['uk']['subText'],
        );
    }
}

