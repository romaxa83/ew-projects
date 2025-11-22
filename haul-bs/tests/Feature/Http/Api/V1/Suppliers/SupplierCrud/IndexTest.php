<?php

namespace Tests\Feature\Http\Api\V1\Suppliers\SupplierCrud;

use App\Models\Suppliers\Supplier;
use App\Models\Suppliers\SupplierContact;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Suppliers\SupplierBuilder;
use Tests\Builders\Suppliers\SupplierContactBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
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
    public function success_pagination()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->supplierBuilder->create();
        $m_2 = $this->supplierBuilder->create();
        $m_3 = $this->supplierBuilder->create();

        $this->getJson(route('api.v1.suppliers'))
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id],
                    ['id' => $m_2->id],
                    ['id' => $m_1->id],
                ],
                'meta' => [
                    'current_page' => 1,
                    'total' => 3,
                    'to' => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function success_by_page()
    {
        $this->loginUserAsSuperAdmin();

        $this->supplierBuilder->create();
        $this->supplierBuilder->create();
        $this->supplierBuilder->create();

        $this->getJson(route('api.v1.suppliers', ['page' => 2]))
            ->assertJson([
                'meta' => [
                    'current_page' => 2,
                    'total' => 3,
                    'to' => null,
                ]
            ])
        ;
    }

    /** @test */
    public function success_by_per_page()
    {
        $this->loginUserAsSuperAdmin();

        $this->supplierBuilder->create();
        $this->supplierBuilder->create();
        $this->supplierBuilder->create();

        $this->getJson(route('api.v1.suppliers', ['per_page' => 2]))
            ->assertJson([
                'meta' => [
                    'current_page' => 1,
                    'total' => 3,
                    'per_page' => 2,
                    'to' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.suppliers'))
            ->assertJson([
                'meta' => [
                    'current_page' => 1,
                    'total' => 0,
                    'to' => 0,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_id()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Supplier */
        $m_1 = $this->supplierBuilder->create();
        $this->supplierBuilder->create();

        $this->inventoryBuilder->supplier($m_1)->create();

        /** @var $m_c_2 SupplierContact */
        $this->supplierContactBuilder->supplier($m_1)->main(false)->create();
        $m_c_2 = $this->supplierContactBuilder->supplier($m_1)->main(true)->create();
        $this->supplierContactBuilder->supplier($m_1)->main(false)->create();

        $this->getJson(route('api.v1.suppliers', [
            'id' => $m_1->id
        ]))
            ->assertJson([
                'data' => [
                    [
                        'id' => $m_1->id,
                        'name' => $m_1->name,
                        'url' => $m_1->url,
                        'contact' => [
                            'name' => $m_c_2->name,
                            'email' => $m_c_2->email,
                            'emails' => $m_c_2->emails,
                            'phone' => $m_c_2->phone,
                            'phones' => $m_c_2->phones,
                            'phone_extension' => $m_c_2->phone_extension,
                            'position' => $m_c_2->position,
                        ],
                        'hasRelatedEntities' => true
                    ]
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_search_by_name()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Supplier */
        $m_1 = $this->supplierBuilder->name('test')->create();
        $this->supplierBuilder->name('alen')->create();
        $this->supplierBuilder->name('well')->create();

        $this->getJson(route('api.v1.suppliers', [
            'search' => 'test'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.suppliers'));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.suppliers'));

        self::assertUnauthenticatedMessage($res);
    }
}

