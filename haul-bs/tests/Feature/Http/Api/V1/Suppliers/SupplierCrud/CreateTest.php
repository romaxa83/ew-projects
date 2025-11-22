<?php

namespace Tests\Feature\Http\Api\V1\Suppliers\SupplierCrud;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Suppliers\SupplierBuilder;
use Tests\Builders\Suppliers\SupplierContactBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    protected SupplierBuilder $supplierBuilder;
    protected SupplierContactBuilder $supplierContactBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->supplierBuilder = resolve(SupplierBuilder::class);
        $this->supplierContactBuilder = resolve(SupplierContactBuilder::class);

        $this->data = [
            'name' => 'test',
            'url' => 'http://google.com',
            'contacts' => [
                [
                    'is_main' => true,
                    'name' => 'contact_1',
                    'position' => 'manager',
                    'phone' => '18888888888',
                    'phone_extension' => '888',
                    'phones' => [
                        [
                            'number' => '18888888881',
                            'extension' => '881'
                        ],
                        [
                            'number' => '18888888882',
                            'extension' => '882'
                        ]
                    ],
                    'email' => 'contact_1@gmail.com',
                    'emails' => [
                        [
                            'value' => 'contact_1_1@gmail.com'
                        ],
                        [
                            'value' => 'contact_1_2@gmail.com'
                        ]
                    ]
                ],
                [
                    'is_main' => false,
                    'name' => 'contact_2',
                    'phone' => '28888888888',
                    'phone_extension' => '288',
                    'email' => 'contact_2@gmail.com',
                ]
            ]
        ];
    }

    /** @test */
    public function success_create()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $this->postJson(route('api.v1.suppliers.store'), $data)
            ->assertJson([
                'data' => [
                    'name' => data_get($data, 'name'),
                    'url' => data_get($data, 'url'),
                    'contacts' => [
                        [
                            'name' => data_get($data, 'contacts.0.name'),
                            'email' => data_get($data, 'contacts.0.email'),
                            'emails' => data_get($data, 'contacts.0.emails'),
                            'phone' => data_get($data, 'contacts.0.phone'),
                            'phone_extension' => data_get($data, 'contacts.0.phone_extension'),
                            'phones' => data_get($data, 'contacts.0.phones'),
                            'position' => data_get($data, 'contacts.0.position'),
                            'is_main' => data_get($data, 'contacts.0.is_main'),
                        ],
                        [
                            'name' => data_get($data, 'contacts.1.name'),
                            'email' => data_get($data, 'contacts.1.email'),
                            'emails' => data_get($data, 'contacts.1.emails'),
                            'phone' => data_get($data, 'contacts.1.phone'),
                            'phone_extension' => data_get($data, 'contacts.1.phone_extension'),
                            'phones' => data_get($data, 'contacts.1.phones'),
                            'position' => null,
                            'is_main' => data_get($data, 'contacts.1.is_main'),
                        ]
                    ],
                ],
            ])
        ;
    }


    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.suppliers.store'), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        $res = $this->postJson(route('api.v1.suppliers.store'), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
