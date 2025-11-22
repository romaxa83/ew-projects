<?php

namespace Tests\Feature\Http\Api\V1\Suppliers\SupplierCrud;

use App\Models\Suppliers\Supplier;
use App\Models\Suppliers\SupplierContact;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Suppliers\SupplierBuilder;
use Tests\Builders\Suppliers\SupplierContactBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected SupplierBuilder $supplierBuilder;
    protected SupplierContactBuilder $supplierContactBuilder;

    protected $data = [];

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
    public function success_update()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m Supplier */
        $m = $this->supplierBuilder->create();

        /** @var $c SupplierContact */
        $c = $this->supplierContactBuilder->supplier($m)->create();

        $data = $this->data;
        $data['contacts'][0]['id'] = $c->id;

        $m->refresh();

        $this->assertCount(1, $m->contacts);

        $this->assertNotEquals($m->name, data_get($data, 'name'));
        $this->assertNotEquals($m->url, data_get($data, 'url'));
        $this->assertNotEquals($c->name, data_get($data, 'contacts.0.name'));
        $this->assertNotEquals($c->position, data_get($data, 'contacts.0.position'));
        $this->assertNotEquals($c->phone, data_get($data, 'contacts.0.phone'));
        $this->assertNotEquals($c->phone_extension, data_get($data, 'contacts.0.phone_extension'));
        $this->assertNotEquals($c->phones, data_get($data, 'contacts.0.phones'));
        $this->assertNotEquals($c->email, data_get($data, 'contacts.0.email'));
        $this->assertNotEquals($c->emails, data_get($data, 'contacts.0.emails'));

        $this->postJson(route('api.v1.suppliers.update', ['id' => $m->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $m->id,
                    'name' => data_get($data, 'name'),
                    'url' => data_get($data, 'url'),
                    'contacts' => [
                        [
                            'id' => $c->id,
                            'name' => data_get($data, 'contacts.0.name'),
                            'position' => data_get($data, 'contacts.0.position'),
                            'phone' => data_get($data, 'contacts.0.phone'),
                            'phones' => data_get($data, 'contacts.0.phones'),
                            'phone_extension' => data_get($data, 'contacts.0.phone_extension'),
                            'email' => data_get($data, 'contacts.0.email'),
                            'emails' => data_get($data, 'contacts.0.emails'),
                        ],
                        [
                            'name' => data_get($data, 'contacts.1.name'),
                            'position' => data_get($data, 'contacts.1.position'),
                            'phone' => data_get($data, 'contacts.1.phone'),
                            'phones' => data_get($data, 'contacts.1.phones'),
                            'phone_extension' => data_get($data, 'contacts.1.phone_extension'),
                            'email' => data_get($data, 'contacts.1.email'),
                            'emails' => data_get($data, 'contacts.1.emails'),
                        ]
                    ],
                ],
            ])
        ;
    }

    /** @test */
    public function success_update_and_delete_company()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m Supplier */
        $m = $this->supplierBuilder->create();

        /** @var $c SupplierContact */
        $c = $this->supplierContactBuilder->supplier($m)->create();
        $c_2 = $this->supplierContactBuilder->supplier($m)->create();

        $data = $this->data;
        unset($data['contacts'][0]);

        $m->refresh();

        $this->assertCount(2, $m->contacts);

        $this->postJson(route('api.v1.suppliers.update', ['id' => $m->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $m->id,
                    'contacts' => [
                        [
                            'name' => data_get($data, 'contacts.1.name'),
                            'position' => data_get($data, 'contacts.1.position'),
                            'phone' => data_get($data, 'contacts.1.phone'),
                            'phones' => data_get($data, 'contacts.1.phones'),
                            'phone_extension' => data_get($data, 'contacts.1.phone_extension'),
                            'email' => data_get($data, 'contacts.1.email'),
                            'emails' => data_get($data, 'contacts.1.emails'),
                        ]
                    ],
                ],
            ])
            ->assertJsonCount(1, 'data.contacts')
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $m Supplier */
        $m = $this->supplierBuilder->create();
        /** @var $c SupplierContact */
        $c = $this->supplierContactBuilder->supplier($m)->create();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.suppliers.update', ['id' => $m->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $m Supplier */
        $m = $this->supplierBuilder->create();
        /** @var $c SupplierContact */
        $c = $this->supplierContactBuilder->supplier($m)->create();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.suppliers.update', ['id' => $m->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
