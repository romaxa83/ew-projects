<?php

namespace Tests\Feature\Http\Api\V1\Suppliers\SupplierCrud;

use App\Models\Suppliers\Supplier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Suppliers\SupplierBuilder;
use Tests\Builders\Suppliers\SupplierContactBuilder;
use Tests\TestCase;

class ShortListTest extends TestCase
{
    use DatabaseTransactions;

    protected SupplierBuilder $supplierBuilder;
    protected SupplierContactBuilder $supplierContactBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->supplierBuilder = resolve(SupplierBuilder::class);
        $this->supplierContactBuilder = resolve(SupplierContactBuilder::class);
    }

    /** @test */
    public function success_list_by_id()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Supplier */
        $m_1 = $this->supplierBuilder->create();
        $this->supplierContactBuilder->supplier($m_1)->create();

        $m_2 = $this->supplierBuilder->create();
        $m_3 = $this->supplierBuilder->create();

        $this->getJson(route('api.v1.suppliers.shortlist', [
            'id' => $m_1->id
        ]))
            ->assertJson([
                'data' => [
                    [
                        'id' => $m_1->id,
                        'name' => $m_1->name,
                        'url' => $m_1->url,
                        'contact' => [
                            'name' => $m_1->mainContact()->name,
                            'email' => $m_1->mainContact()->email->getValue(),
                            'phone' => $m_1->mainContact()->phone->getValue(),
                            'phone_extension' => $m_1->mainContact()->phone_extension,
                            'position' => $m_1->mainContact()->position,
                        ],
                    ],
                ],
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_list_by_email()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Supplier */
        $m_1 = $this->supplierBuilder->create();
        $this->supplierContactBuilder->email('tttest@gmail.com')->supplier($m_1)->create();

        $m_2 = $this->supplierBuilder->create();
        $this->supplierContactBuilder->email('amma@gmail.com')->supplier($m_2)->create();

        $m_3 = $this->supplierBuilder->create();
        $this->supplierContactBuilder->email('rit@gmail.com')->supplier($m_3)->create();

        $this->getJson(route('api.v1.suppliers.shortlist', [
            'search' => 'tttes',
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                ],
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_list_by_email_and_search_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.suppliers.shortlist', [
            'search' => 'rit',
        ]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.suppliers.shortlist', [
            'search' => 'rit',
        ]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.suppliers.shortlist', [
            'search' => 'rit',
        ]));

        self::assertUnauthenticatedMessage($res);
    }
}
