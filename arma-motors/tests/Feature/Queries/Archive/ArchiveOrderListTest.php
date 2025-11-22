<?php

namespace Tests\Feature\Queries\Archive;

use App\Exceptions\ErrorsCode;
use App\Types\Order\Status;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Mutations\Order\DeleteTest;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\OrderBuilder;

class ArchiveOrderListTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use OrderBuilder;

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
            Permissions::ARCHIVE_ORDER_LIST, Permissions::ORDER_DELETE
        ])->create();
        $this->loginAsAdmin($admin);

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setStatus(Status::CLOSE)->asOne()->create();

        $response = $this->graphQL($this->getQueryStr())->assertOk();
        $this->assertEquals(0, $response->json('data.ordersArchive.paginatorInfo.total'));

        // запрос на удаление заявки
        $this->graphQL(DeleteTest::getQueryStr($order->id));

        // делаем повторный запрос в архив
        $secondResponse = $this->graphQL($this->getQueryStr());
        $this->assertEquals(1, $secondResponse->json('data.ordersArchive.paginatorInfo.total'));
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ARCHIVE_ORDER_LIST])->create();

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
            ordersArchive {
                data{
                    id
                }
                paginatorInfo {
                    total
                }
               }
            }',
        );
    }
}




