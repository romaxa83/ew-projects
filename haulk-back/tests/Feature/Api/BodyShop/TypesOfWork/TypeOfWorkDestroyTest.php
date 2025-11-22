<?php

namespace Api\BodyShop\TypesOfWork;

use App\Models\BodyShop\TypesOfWork\TypeOfWork;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class TypeOfWorkDestroyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_delete_for_unauthorized_users()
    {
        $typeOfWork = factory(TypeOfWork::class)->create();

        $this->deleteJson(route('body-shop.types-of-work.destroy', $typeOfWork))
            ->assertUnauthorized();
    }

    public function test_it_not_delete_for_not_permitted_users()
    {
        $typeOfWork = factory(TypeOfWork::class)->create();

        $this->loginAsCarrierDispatcher();

        $this->deleteJson(route('body-shop.types-of-work.destroy', $typeOfWork))
            ->assertForbidden();
    }

    public function test_it_delete_by_bs_super_admin()
    {
        $typeOfWork = factory(TypeOfWork::class)->create();

        $this->assertDatabaseHas(TypeOfWork::TABLE_NAME, $typeOfWork->getAttributes());

        $this->loginAsBodyShopSuperAdmin();
        $this->deleteJson(route('body-shop.types-of-work.destroy', $typeOfWork))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(TypeOfWork::TABLE_NAME, $typeOfWork->getAttributes());
    }

    public function test_it_delete_by_bs_admin()
    {
        $typeOfWork = factory(TypeOfWork::class)->create();

        $this->assertDatabaseHas(TypeOfWork::TABLE_NAME, $typeOfWork->getAttributes());

        $this->loginAsBodyShopAdmin();
        $this->deleteJson(route('body-shop.types-of-work.destroy', $typeOfWork))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(TypeOfWork::TABLE_NAME, $typeOfWork->getAttributes());
    }
}
