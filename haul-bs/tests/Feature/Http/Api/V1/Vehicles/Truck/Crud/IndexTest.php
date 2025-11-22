<?php

namespace Tests\Feature\Http\Api\V1\Vehicles\Truck\Crud;

use App\Enums\Tags\TagType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Comments\CommentBuilder;
use Tests\Builders\Companies\CompanyBuilder;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Tags\TagBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;

    protected TruckBuilder $truckBuilder;
    protected CustomerBuilder $customerBuilder;
    protected TagBuilder $tagBuilder;
    protected CommentBuilder $commentBuilder;
    protected CompanyBuilder $companyBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->customerBuilder = resolve(CustomerBuilder::class);
        $this->tagBuilder = resolve(TagBuilder::class);
        $this->commentBuilder = resolve(CommentBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    /** @test */
    public function success_pagination()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->truckBuilder->create();
        $m_2 = $this->truckBuilder->create();
        $m_3 = $this->truckBuilder->create();
        $this->truckBuilder->delete()->create();

        $this->getJson(route('api.v1.vehicles.trucks'))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'vin',
                        'unit_number',
                        'license_plate',
                        'temporary_plate',
                        'make',
                        'model',
                        'year',
                        'type',
                        'customer_id',
                        'color',
                        'gvwr',
                        'owner_name',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    [
                        'id' => $m_3->id,
                        'owner_name' => $m_3->customer->full_name,
                    ],
                    ['id' => $m_2->id],
                    ['id' => $m_1->id],
                ],
                'meta' => [
                    'total' => 3
                ]
            ])
        ;
    }

    /** @test */
    public function success_by_page()
    {
        $this->loginUserAsSuperAdmin();

        $this->truckBuilder->create();
        $this->truckBuilder->create();
        $this->truckBuilder->create();

        $this->getJson(route('api.v1.vehicles.trucks', ['page' => 2]))
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

        $this->truckBuilder->create();
        $this->truckBuilder->create();
        $this->truckBuilder->create();

        $this->getJson(route('api.v1.vehicles.trucks', ['per_page' => 2]))
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

        $this->getJson(route('api.v1.vehicles.trucks'))
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
    public function success_order_by_company_name_asc()
    {
        $this->loginUserAsSuperAdmin();

        $company_1 = $this->companyBuilder->name('Allen')->create();
        $company_2 = $this->companyBuilder->name('Zona')->create();

        $m_1 = $this->truckBuilder->company($company_1)->create();
        $m_2 = $this->truckBuilder->create();
        $m_3 = $this->truckBuilder->company($company_2)->create();


        $this->getJson(route('api.v1.vehicles.trucks', [
            'order_by' => 'company_name',
            'order_type' => 'asc',
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id,],
                    ['id' => $m_3->id,],
                    ['id' => $m_2->id,],
                ],
                'meta' => [
                    'total' => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function success_order_by_company_name_desc()
    {
        $this->loginUserAsSuperAdmin();

        $company_1 = $this->companyBuilder->name('Allen')->create();
        $company_2 = $this->companyBuilder->name('Zona')->create();

        $m_1 = $this->truckBuilder->company($company_1)->create();
        $m_2 = $this->truckBuilder->create();
        $m_3 = $this->truckBuilder->company($company_2)->create();


        $this->getJson(route('api.v1.vehicles.trucks', [
            'order_by' => 'company_name',
            'order_type' => 'desc',
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id,],
                    ['id' => $m_1->id,],
                    ['id' => $m_2->id,],
                ],
                'meta' => [
                    'total' => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function success_search_by_vin()
    {
        $this->loginUserAsSuperAdmin();

        $company = $this->companyBuilder->create();

        $model = $this->truckBuilder->vin('11111')->company($company)->create();
        $this->truckBuilder->vin('22222')->create();
        $this->truckBuilder->vin('33333')->create();

        $this->commentBuilder
            ->model($model)
            ->create();
        $this->commentBuilder
            ->model($model)
            ->create();

        $this->getJson(route('api.v1.vehicles.trucks', [
            'search' => '11111'
        ]))
            ->assertJson([
                'data' => [
                    [
                        'id' => $model->id,
                        'comments_count' => 2,
                        'company_name' => $company->name
                    ]
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_search_by_unit_number()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->truckBuilder->unit_number('11111')->create();
        $this->truckBuilder->unit_number('22222')->create();
        $this->truckBuilder->unit_number('33333')->create();

        $this->getJson(route('api.v1.vehicles.trucks', [
            'search' => '11111'
        ]))
            ->assertJson([
                'data' => [
                    [
                        'id' => $m_1->id,
                        'comments_count' => 0,
                        'company_name' => null
                    ]
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_search_by_license_plate()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->truckBuilder->license_plate('11111')->create();
        $this->truckBuilder->license_plate('22222')->create();
        $this->truckBuilder->license_plate('33333')->create();

        $this->getJson(route('api.v1.vehicles.trucks', [
            'search' => '11111'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id]
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_search_by_temporary_plate()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->truckBuilder->temporary_plate('11111')->create();
        $this->truckBuilder->temporary_plate('22222')->create();
        $this->truckBuilder->temporary_plate('33333')->create();

        $this->getJson(route('api.v1.vehicles.trucks', [
            'search' => '11111'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id]
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_search_by_customer_name()
    {
        $this->loginUserAsSuperAdmin();

        $c_1 = $this->customerBuilder
            ->first_name('Ben')->last_name('Alex')->create();
        $m_1 = $this->truckBuilder->customer($c_1)->create();

        $c_2 = $this->customerBuilder
            ->first_name('Ben')->last_name('Allen')->create();
        $m_2 = $this->truckBuilder->customer($c_2)->create();

        $c_3 = $this->customerBuilder
            ->first_name('Walls')->last_name('Carl')->create();
        $m_3 = $this->truckBuilder->customer($c_3)->create();

        $this->getJson(route('api.v1.vehicles.trucks', [
            'search' => 'Ben al'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id],
                    ['id' => $m_1->id]
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_customer_id()
    {
        $this->loginUserAsSuperAdmin();

        $c_1 = $this->customerBuilder->create();
        $c_2 = $this->customerBuilder->create();

        $m_1 = $this->truckBuilder->customer($c_1)->create();
        $m_2 = $this->truckBuilder->customer($c_1)->create();
        $m_3 = $this->truckBuilder->customer($c_2)->create();

        $this->getJson(route('api.v1.vehicles.trucks', [
            'customer_id' => $c_1->id
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id],
                    ['id' => $m_1->id]
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_tag_id()
    {
        $this->loginUserAsSuperAdmin();

        $t_1 = $this->tagBuilder->type(TagType::TRUCKS_AND_TRAILER())->create();
        $t_2 = $this->tagBuilder->type(TagType::TRUCKS_AND_TRAILER())->create();
        $t_3 = $this->tagBuilder->type(TagType::TRUCKS_AND_TRAILER())->create();

        $m_1 = $this->truckBuilder->tags($t_1)->create();
        $m_2 = $this->truckBuilder->tags($t_2)->create();
        $m_3 = $this->truckBuilder->tags($t_3, $t_1)->create();

        $this->getJson(route('api.v1.vehicles.trucks', [
            'tag_id' => $t_1->id
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id],
                    ['id' => $m_1->id]
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.vehicles.trucks'));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.vehicles.trucks'));

        self::assertUnauthenticatedMessage($res);
    }
}
