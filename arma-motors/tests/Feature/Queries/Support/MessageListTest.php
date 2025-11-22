<?php

namespace Tests\Feature\Queries\Support;

use App\Exceptions\ErrorsCode;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Builders\SupportBuilder;
use Tests\Traits\UserBuilder;

class MessageListTest extends TestCase
{
    use DatabaseTransactions;
    use SupportBuilder;
    use AdminBuilder;
    use UserBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::SUPPORT_MESSAGE_LIST])
            ->create();
        $this->loginAsAdmin($admin);

        $builderSupport = $this->supportBuilder();
        $builderSupport->setCount(10)->create();

        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $responseData = $response->json('data.messages');

        $this->assertNotEmpty($responseData['data']);

        $this->assertArrayHasKey('id', $responseData['data'][0]);
        $this->assertArrayHasKey('status', $responseData['data'][0]);
        $this->assertArrayHasKey('email', $responseData['data'][0]);
        $this->assertArrayHasKey('text', $responseData['data'][0]);

        $this->assertArrayHasKey('paginatorInfo', $responseData);
        $this->assertArrayHasKey('count', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('total', $responseData['paginatorInfo']);

        $this->assertEquals(10, $responseData['paginatorInfo']['total']);
    }

    /** @test */
    public function list_empty()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::SUPPORT_MESSAGE_LIST])
            ->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr())->assertOk();

        $responseData = $response->json('data.messages');

        $this->assertEmpty($responseData['data']);
    }

    /** @test */
    public function list_by_category()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::SUPPORT_MESSAGE_LIST])
            ->create();
        $this->loginAsAdmin($admin);

        $builderSupport = $this->supportBuilder();
        $category1 = $builderSupport->onlyCategory()->create();
        $category2 = $builderSupport->onlyCategory()->create();
        $builderSupport->setCount(10)->setCategoryId($category1->id)->create();
        $builderSupport->setCount(2)->setCategoryId($category2->id)->create();

        $response = $this->graphQL($this->getQueryStrByCategory($category2->id))->assertOk();
        $this->assertEquals(2,  $response->json('data.messages.paginatorInfo.total'));

        $response = $this->graphQL($this->getQueryStrByCategory($category1->id))->assertOk();
        $this->assertEquals(10,  $response->json('data.messages.paginatorInfo.total'));
    }

    /** @test */
    public function list_by_username()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::SUPPORT_MESSAGE_LIST])
            ->create();
        $this->loginAsAdmin($admin);

        $userBuilder = $this->userBuilder();
        $user = $userBuilder->setName('cubic rubic')->create();

        $builderSupport = $this->supportBuilder();
        $builderSupport->setCount(7)->setUserId($user->id)->create();
        $builderSupport->setCount(5)->create();

        $response = $this->graphQL($this->getQueryStrByUsername('cubic'))->assertOk();
        $this->assertEquals(7,  $response->json('data.messages.paginatorInfo.total'));

        // запрос на общее кол-во
        $response = $this->graphQL($this->getQueryStr())->assertOk();
        $this->assertEquals(12,  $response->json('data.messages.paginatorInfo.total'));
    }

    /** @test */
    public function sort_by_id()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::SUPPORT_MESSAGE_LIST])
            ->create();
        $this->loginAsAdmin($admin);

        $builderSupport = $this->supportBuilder();
        $builderSupport->setCount(5)->create();

        $responseAsc = $this->graphQL($this->getQueryStrSortByID('ASC'))->assertOk();

        $ascFirstId = $responseAsc->json('data.messages.data.0.id');
        $ascLastId = $responseAsc->json('data.messages.data.4.id');

        $responseDesc = $this->graphQL($this->getQueryStrSortByID('DESC'))->assertOk();

        $descFirstId = $responseDesc->json('data.messages.data.0.id');

        $this->assertEquals($ascLastId,  $descFirstId);
        $this->assertNotEquals($ascFirstId,  $descFirstId);
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::SUPPORT_MESSAGE_LIST])
            ->create();

        $response = $this->graphQL($this->getQueryStr());

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::ADMIN_EDIT])
            ->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr());

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    public function getQueryStr(): string
    {
        return  sprintf('{
            messages {
                data{
                    id
                    status
                    email
                    text
                }
                paginatorInfo {
                    count
                    total
                }
               }
            }'
        );
    }

    public function getQueryStrByCategory($categoryId): string
    {
        return  sprintf('{
            messages (categoryId: %s) {
                data{
                    id
                    status
                    email
                    text
                }
                paginatorInfo {
                    count
                    total
                }
               }
            }',
        $categoryId
        );
    }

    public function getQueryStrByUsername(string $username): string
    {
        return  sprintf('{
            messages (userName: "%s") {
                data{
                    id
                    status
                    email
                    text
                }
                paginatorInfo {
                    count
                    total
                }
               }
            }',
            $username
        );
    }

    public function getQueryStrSortByID(string $type): string
    {
        return  sprintf('{
            messages (orderBy: [{field: ID, order: %s}]) {
                data{
                    id
                }
                paginatorInfo {
                    count
                    total
                }
               }
            }',
            $type
        );
    }
}
