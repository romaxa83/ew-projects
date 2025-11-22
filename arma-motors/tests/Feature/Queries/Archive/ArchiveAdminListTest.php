<?php

namespace Tests\Feature\Queries\Archive;

use App\Exceptions\ErrorsCode;
use App\Models\Admin\Admin;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Mutations\Admin\Admin\DeleteTest;
use Tests\Feature\Mutations\Admin\Admin\RestoreTest;
use Tests\Feature\Mutations\Admin\Car\CarRestoreTest;
use Tests\Feature\Mutations\User\User\DeleteCarTest;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\UserBuilder;

class ArchiveAdminListTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function get_success()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerms([
            Permissions::ARCHIVE_ADMIN_LIST, Permissions::ADMIN_RESTORE, Permissions::ADMIN_DELETE
        ])->create();
        $this->loginAsAdmin($admin);

        $someAdmin = $builder->setEmail('some@admin.com')->setStatus(Admin::STATUS_INACTIVE)->create();

        $response = $this->graphQL($this->getQueryStr())->assertOk();

        $responseData = $response->json('data.adminsArchive');
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEmpty($responseData['data']);

        // запрос на удаление админа
        $responseForDelete = $this->graphQL(DeleteTest::getQueryStr($someAdmin->id));

        // делаем повторный запрос в архив
        $secondResponse = $this->graphQL($this->getQueryStr());

        $secondResponseData = $secondResponse->json('data.adminsArchive');

        $this->assertNotEmpty($secondResponseData['data']);
        $this->assertEquals(1, $secondResponseData['paginatorInfo']['count']);
        $this->assertEquals($someAdmin->id, $secondResponseData['data'][0]['id']);

        // делаем запрос на восстановление
        $responseRestore = $this->graphQL(RestoreTest::getQueryStr($someAdmin->id));

        // делаем еще раз запрос в архив (ничего не должно быть)
        $thirdResponse = $this->graphQL($this->getQueryStr());
        $thirdResponseData = $thirdResponse->json('data.adminsArchive');

        $this->assertEmpty($thirdResponseData['data']);
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ARCHIVE_ADMIN_LIST])->create();

        $response = $this->graphQL($this->getQueryStr());

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ADMIN_EDIT])->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr());

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            adminsArchive {
                data{
                    id
                    email
                }
                paginatorInfo {
                    count
                    currentPage
                    hasMorePages
                    lastPage
                }
               }
            }',
        );
    }
}



