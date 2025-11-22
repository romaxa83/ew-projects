<?php

namespace Tests\Feature\Http\Api\V1\Customers\CustomerCrud;

use App\Enums\Customers\CustomerType;
use App\Enums\Tags\TagType;
use App\Models\Customers\Customer;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Comments\CommentBuilder;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Tags\TagBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;
    protected TagBuilder $tagBuilder;
    protected CommentBuilder $commentBuilder;
    protected TruckBuilder $truckBuilder;
    protected UserBuilder $userBuilder;

    protected CarbonImmutable $now;

    public function setUp(): void
    {
        parent::setUp();

        $this->customerBuilder = resolve(CustomerBuilder::class);
        $this->tagBuilder = resolve(TagBuilder::class);
        $this->commentBuilder = resolve(CommentBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);

        $this->now = CarbonImmutable::now()->subDay();
    }

    /** @test */
    public function success_pagination()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->customerBuilder->setDate('created_at', $this->now->addDays(2))->create();
        $m_2 = $this->customerBuilder->setDate('created_at', $this->now->addDays(3))->create();
        $m_3 = $this->customerBuilder->setDate('created_at', $this->now->addDays(4))->create();
        $m_4 = $this->customerBuilder->fromHaulk()
            ->setDate('created_at', $this->now->addDays(1))->create();

        $this->getJson(route('api.v1.customers'))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'type',
                        'first_name',
                        'last_name',
                        'email',
                        'phone',
                        'phone_extension',
                        'tags' => [],
                        'comments_count',
                        'hasRelatedTrucks',
                        'hasRelatedTrailers',
                        'sales_manager',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id],
                    ['id' => $m_2->id],
                    ['id' => $m_1->id],
                    ['id' => $m_4->id],
                ],
                'meta' => [
                    'current_page' => 1,
                    'total' => 4,
                    'to' => 4,
                ]
            ])
        ;
    }

    /** @test */
    public function success_by_page()
    {
        $this->loginUserAsSuperAdmin();

        $this->customerBuilder->create();
        $this->customerBuilder->create();
        $this->customerBuilder->create();

        $this->getJson(route('api.v1.customers', ['page' => 2]))
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

        $this->customerBuilder->create();
        $this->customerBuilder->create();
        $this->customerBuilder->create();

        $this->getJson(route('api.v1.customers', ['per_page' => 2]))
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

        $this->getJson(route('api.v1.customers'))
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
    public function success_for_sales_manager()
    {
        $now = CarbonImmutable::now()->subDay();

        $sales = $this->loginUserAsSalesManager();

        $sales_1 = $this->userBuilder->asSalesManager()->create();

        /** @var $customer_1 Customer */
        $customer_1 = $this->customerBuilder->setDate('created_at', $now->addHours(2))
            ->salesManager($sales)->create();
        $customer_2 = $this->customerBuilder->setDate('created_at', $now->addHours(3))
            ->salesManager($sales_1)->create();
        $customer_3 = $this->customerBuilder->setDate('created_at', $now->addHours(4))
            ->create();
        $customer_4 = $this->customerBuilder->setDate('created_at', $now->addHours(5))
            ->create();

        $this->assertNull($customer_3->salesManager);
        $this->assertNull($customer_4->salesManager);

        $this->getJson(route('api.v1.customers'))
            ->assertJson([
                'data' => [
                    ['id' => $customer_4->id],
                    ['id' => $customer_3->id],
                    ['id' => $customer_1->id],
                ],
                'meta' => [
                    'total' => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function success_for_sales_manager_only_free()
    {
        $sales = $this->loginUserAsSalesManager();

        $sales_1 = $this->userBuilder->asSalesManager()->create();

        /** @var $customer_1 Customer */
        $customer_2 = $this->customerBuilder->setDate('created_at', $this->now->addHours(2))
            ->salesManager($sales_1)->create();
        $customer_3 = $this->customerBuilder->setDate('created_at', $this->now->addHours(3))
            ->create();
        $customer_4 = $this->customerBuilder->setDate('created_at', $this->now->addHours(4))->create();

        $this->assertNull($customer_3->salesManager);
        $this->assertNull($customer_4->salesManager);

        $this->getJson(route('api.v1.customers'))
            ->assertJson([
                'data' => [
                    ['id' => $customer_4->id],
                    ['id' => $customer_3->id],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_id()
    {
        $this->loginUserAsSuperAdmin();

        $t_1 = $this->tagBuilder->create();
        $t_2 = $this->tagBuilder->create();

        /** @var $m Customer */
        $m = $this->customerBuilder->tags($t_1, $t_2)->create();

        $this->truckBuilder->customer($m)->create();

        $this->commentBuilder->model($m)->create();
        $this->commentBuilder->model($m)->create();

        $this->customerBuilder->create();

        $this->getJson(route('api.v1.customers', [
            'id' => $m->id
        ]))
            ->assertJson([
                'data' => [
                    [
                        'id' => $m->id,
                        'first_name' => $m->first_name,
                        'last_name' => $m->last_name,
                        'email' => $m->email->getValue(),
                        'phone' => $m->phone->getValue(),
                        'phone_extension' => $m->phone_extension,
                        'comments_count' => 2,
                        'tags' => [
                            [
                                'id' => $t_1->id,
                                'name' => $t_1->name,
                                'color' => $t_1->color
                            ],
                            ['id' => $t_2->id]
                        ],
                        'hasRelatedTrucks' => true,
                        'hasRelatedTrailers' => false,
                    ]
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
            ->assertJsonCount(2, 'data.0.tags')
        ;
    }

    /** @test */
    public function success_filter_by_tag()
    {
        $this->loginUserAsSuperAdmin();

        $t_1 = $this->tagBuilder->type(TagType::CUSTOMER())->create();
        $t_2 = $this->tagBuilder->type(TagType::CUSTOMER())->create();

        /** @var $m Customer */
        $m_1 = $this->customerBuilder->tags($t_1)
            ->setDate('created_at', $this->now->addHours(2))->create();
        $m_2 = $this->customerBuilder->tags($t_2)
            ->setDate('created_at', $this->now->addHours(3))->create();
        $m_3 = $this->customerBuilder->tags($t_2)
            ->setDate('created_at', $this->now->addHours(4))->create();

        $this->customerBuilder->create();

        $this->getJson(route('api.v1.customers', [
            'tag_id' => $t_2->id
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id,],
                    ['id' => $m_2->id,],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function fail_filter_by_tag_wrong_tag()
    {
        $this->loginUserAsSuperAdmin();

        $t_1 = $this->tagBuilder->type(TagType::TRUCKS_AND_TRAILER())->create();
        $t_2 = $this->tagBuilder->type(TagType::CUSTOMER())->create();

        /** @var $m Customer */
        $m_1 = $this->customerBuilder->tags($t_1)->create();
        $m_2 = $this->customerBuilder->tags($t_2)->create();
        $m_3 = $this->customerBuilder->tags($t_2)->create();

        $this->customerBuilder->create();

        $res = $this->getJson(route('api.v1.customers', [
            'tag_id' => $t_1->id
        ]))
        ;

        self::assertValidationMsg($res,
            __('validation.exists', ['attribute' => 'tag id']),'tag_id');
    }

    /** @test */
    public function success_filter_by_types()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m Customer */
        $m_1 = $this->customerBuilder->type(CustomerType::Haulk)->create();
        $m_2 = $this->customerBuilder->type(CustomerType::BS)->create();
        $m_3 = $this->customerBuilder->type(CustomerType::BS)->create();
        $m_4 = $this->customerBuilder->type(CustomerType::EComm)->create();

        $this->customerBuilder->create();

        $this->getJson(route('api.v1.customers', [
            'types' => [
                CustomerType::EComm(),
            ]
        ]))
            ->assertJson([
                'data' => [
                    [
                        'id' => $m_4->id,
                        'type' => $m_4->type->value,
                    ],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function fail_filter_by_wrong_types()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m Customer */
        $this->customerBuilder->type(CustomerType::Haulk)->create();
        $this->customerBuilder->type(CustomerType::BS)->create();
        $this->customerBuilder->type(CustomerType::BS)->create();
        $this->customerBuilder->type(CustomerType::EComm)->create();

        $this->customerBuilder->create();

        $res = $this->getJson(route('api.v1.customers', [
            'types' => [
                'wrong',
            ]
        ]))
        ;

        self::assertValidationMsg($res,
            __('validation.in', ['attribute' => 'types.0']),'types.0');
    }

    /** @test */
    public function success_search_by_name()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now()->subDay();

        /** @var $m_1 Customer */
        $m_1 = $this->customerBuilder
            ->first_name('Alen')
            ->last_name('Wall')
            ->setDate('created_at', $now->addDays(2))
            ->create();
        $m_2 = $this->customerBuilder
            ->first_name('Mark')
            ->last_name('Aleftin')
            ->setDate('created_at', $now->addDays(3))
            ->create();
        $this->customerBuilder
            ->first_name('Tommy')
            ->last_name('Wall')
            ->email('tets@gmail.com')
            ->create();

        $this->getJson(route('api.v1.customers', [
            'search' => 'ale'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id],
                    ['id' => $m_1->id],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_search_by_email()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Customer */
        $m_1 = $this->customerBuilder
            ->first_name('Alen')
            ->last_name('Wall')
            ->email('test@tesst.com')
            ->create();
        $this->customerBuilder
            ->first_name('Mark')
            ->last_name('Aleftin')
            ->email('aaaaa@gmail.com')
            ->create();
        $this->customerBuilder
            ->first_name('Tommy')
            ->last_name('Wall')
            ->email('rrrrr@gmail.com')
            ->create();

        $this->getJson(route('api.v1.customers', [
            'search' => 'tes'
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
    public function success_search_by_phone()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Customer */
        $m_1 = $this->customerBuilder
            ->first_name('Alen')
            ->last_name('Wall')
            ->email('test@tesst.com')
            ->phone('1888888888')
            ->create();
        $m_2 = $this->customerBuilder
            ->first_name('Mark')
            ->last_name('Aleftin')
            ->email('aaaaa@gmail.com')->phone('1222222222')
            ->create();
        $this->customerBuilder
            ->first_name('Tommy')
            ->last_name('Wall')
            ->email('rrrrr@gmail.com')
            ->phone('18888888884')
            ->create();

        $this->getJson(route('api.v1.customers', [
            'search' => '122'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id],
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

        $res = $this->getJson(route('api.v1.customers'));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.customers'));

        self::assertUnauthenticatedMessage($res);
    }
}
