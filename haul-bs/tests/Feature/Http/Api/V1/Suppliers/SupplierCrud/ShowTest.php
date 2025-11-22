<?php

namespace Tests\Feature\Http\Api\V1\Suppliers\SupplierCrud;

use App\Models\Suppliers\Supplier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Suppliers\SupplierBuilder;
use Tests\Builders\Suppliers\SupplierContactBuilder;
use Tests\TestCase;

class ShowTest extends TestCase
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
    public function success_show()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m Supplier */
        $m = $this->supplierBuilder->create();

        $this->supplierContactBuilder->supplier($m)->create();

        $this->getJson(route('api.v1.suppliers.show', ['id' => $m->id]))
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'url',
                    'contacts' => [
                        [
                            'id',
                            'name',
                            'email',
                            'emails',
                            'phone',
                            'phones',
                            'phone_extension',
                            'position',
                            'is_main',
                        ]
                    ],
                    'hasRelatedEntities'
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $m->id,
                    'hasRelatedEntities' => false
                ],
            ])
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->getJson(route('api.v1.suppliers.show', ['id' => 0]));

        self::assertErrorMsg($res, __("exceptions.supplier.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $m Supplier */
        $m = $this->supplierBuilder->create();

        $res = $this->getJson(route('api.v1.suppliers.show', ['id' => $m->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $m Supplier */
        $m = $this->supplierBuilder->create();

        $res = $this->getJson(route('api.v1.suppliers.show', ['id' => $m->id]));

        self::assertUnauthenticatedMessage($res);
    }
}

