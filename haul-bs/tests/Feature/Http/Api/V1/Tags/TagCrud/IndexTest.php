<?php

namespace Tests\Feature\Http\Api\V1\Tags\TagCrud;

use App\Enums\Tags\TagType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Tags\TagBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;

    protected TagBuilder $tagBuilder;
    protected CustomerBuilder $customerBuilder;
    protected TruckBuilder $truckBuilder;
    protected TrailerBuilder $trailerBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->tagBuilder = resolve(TagBuilder::class);
        $this->customerBuilder = resolve(CustomerBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->trailerBuilder = resolve(TrailerBuilder::class);
    }

    /** @test */
    public function success_list()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->tagBuilder->name('aaaaa')->type(TagType::CUSTOMER())->create();
        $m_2 = $this->tagBuilder->name('zzzzz')->type(TagType::CUSTOMER())->create();
        $m_3 = $this->tagBuilder->name('zzzzz')->type(TagType::TRUCKS_AND_TRAILER())->create();

        $this->customerBuilder->tags($m_1)->create();

        $this->getJson(route('api.v1.tags'))
            ->assertJson([
                'data' => [
                    TagType::TRUCKS_AND_TRAILER => [
                        [
                            'id' => $m_3->id,
                            'name' => $m_3->name,
                            'color' => $m_3->color,
                            'type' => $m_3->type->value,
                            'hasRelatedEntities' => false,
                            'hasRelatedTrucks' => false,
                            'hasRelatedTrailers' => false,
                        ]
                    ],
                    TagType::CUSTOMER => [
                        [
                            'id' => $m_2->id,
                            'name' => $m_2->name,
                            'color' => $m_2->color,
                            'type' => $m_2->type->value,
                            'hasRelatedEntities' => false,
                        ],
                        [
                            'id' => $m_1->id,
                            'name' => $m_1->name,
                            'color' => $m_1->color,
                            'type' => $m_1->type->value,
                            'hasRelatedEntities' => true,
                        ]
                    ]
                ],
            ])
        ;
    }

    /** @test */
    public function success_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.tags'))
            ->assertJson([
                'data' => [
                    TagType::TRUCKS_AND_TRAILER => [],
                    TagType::CUSTOMER => []
                ]
            ])
            ->assertJsonCount(0, 'data.'.TagType::TRUCKS_AND_TRAILER)
            ->assertJsonCount(0, 'data.'.TagType::CUSTOMER)
        ;
    }

    /** @test */
    public function success_filter_by_type()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->tagBuilder->name('aaaaa')->type(TagType::CUSTOMER())->create();
        $m_2 = $this->tagBuilder->name('zzzzz')->type(TagType::CUSTOMER())->create();
        $m_3 = $this->tagBuilder->name('zzzzz')->type(TagType::TRUCKS_AND_TRAILER())->create();

        $this->truckBuilder->tags($m_3)->create();

        $this->getJson(route('api.v1.tags', [
            'type' => TagType::TRUCKS_AND_TRAILER
        ]))
            ->assertJson([
                'data' => [
                    TagType::TRUCKS_AND_TRAILER => [
                        [
                            'id' => $m_3->id,
                            'hasRelatedEntities' => true,
                            'hasRelatedTrucks' => true,
                            'hasRelatedTrailers' => false,
                        ]
                    ],
                    TagType::CUSTOMER => []
                ],
            ])
            ->assertJsonCount(0, 'data.'.TagType::CUSTOMER)
        ;
    }

    /** @test */
    public function success_search_by_name()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->tagBuilder->name('aaaaa')->type(TagType::CUSTOMER())->create();
        $m_2 = $this->tagBuilder->name('zzzzz')->type(TagType::CUSTOMER())->create();
        $m_3 = $this->tagBuilder->name('zzzzz')->type(TagType::TRUCKS_AND_TRAILER())->create();

        $this->trailerBuilder->tags($m_3)->create();

        $this->getJson(route('api.v1.tags', [
            'search' => 'zzz'
        ]))
            ->assertJson([
                'data' => [
                    TagType::TRUCKS_AND_TRAILER => [
                        [
                            'id' => $m_3->id,
                            'hasRelatedEntities' => true,
                            'hasRelatedTrucks' => false,
                            'hasRelatedTrailers' => true,
                        ]
                    ],
                    TagType::CUSTOMER => [
                        ['id' => $m_2->id,]
                    ]
                ],
            ])
            ->assertJsonCount(1, 'data.'.TagType::TRUCKS_AND_TRAILER)
            ->assertJsonCount(1, 'data.'.TagType::CUSTOMER)
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.tags'));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.tags'));

        self::assertUnauthenticatedMessage($res);
    }
}
