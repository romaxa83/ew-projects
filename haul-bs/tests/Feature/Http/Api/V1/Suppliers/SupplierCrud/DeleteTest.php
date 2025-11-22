<?php

namespace Tests\Feature\Http\Api\V1\Suppliers\SupplierCrud;

use App\Models\Suppliers\Supplier;
use App\Models\Suppliers\SupplierContact;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Suppliers\SupplierBuilder;
use Tests\Builders\Suppliers\SupplierContactBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected SupplierBuilder $supplierBuilder;
    protected SupplierContactBuilder $supplierContactBuilder;
    protected InventoryBuilder $inventoryBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->supplierBuilder = resolve(SupplierBuilder::class);
        $this->supplierContactBuilder = resolve(SupplierContactBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m Supplier */
        $m = $this->supplierBuilder->create();
        $this->supplierContactBuilder->supplier($m)->create();

        $id = $m->id;

        $this->deleteJson(route('api.v1.suppliers.delete', ['id' => $m->id]))
            ->assertNoContent()
        ;

        $this->assertFalse(Supplier::query()->where('id', $id)->exists());
        $this->assertFalse(SupplierContact::query()->where('supplier_id', $id)->exists());
    }

    /** @test */
    public function fail_delete_has_inventory()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m Supplier */
        $m = $this->supplierBuilder->create();

        $this->inventoryBuilder->supplier($m)->create();

        $id = $m->id;

        $res = $this->deleteJson(route('api.v1.suppliers.delete', ['id' => $m->id]))
        ;

        $link = str_replace('{id}', $m->id, config('routes.front.inventories_with_supplier_filter_url'));

        self::assertErrorMsg(
            $res,
            __('exceptions.supplier.has_inventory', ['link' => $link]),
            Response::HTTP_UNPROCESSABLE_ENTITY
        );

        $this->assertTrue(Supplier::query()->where('id', $id)->exists());

    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->deleteJson(route('api.v1.suppliers.delete', ['id' => 0]));

        self::assertErrorMsg($res, __("exceptions.supplier.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $m Supplier */
        $m = $this->supplierBuilder->create();

        $res = $this->deleteJson(route('api.v1.suppliers.delete', ['id' => $m->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $m Supplier */
        $m = $this->supplierBuilder->create();

        $res = $this->deleteJson(route('api.v1.suppliers.delete', ['id' => $m->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
