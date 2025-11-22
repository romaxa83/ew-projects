<?php

namespace Tests\Feature\Http\Api\V1\TypeOfWorks\Crud;

use App\Http\Requests\TypeOfWorks\TypeOfWorkShortListRequest;
use App\Models\Customers\Customer;
use App\Models\TypeOfWorks\TypeOfWork;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\TypeOfWorks\TypeOfWorkBuilder;
use Tests\TestCase;

class ShortListTest extends TestCase
{
    use DatabaseTransactions;

    protected TypeOfWorkBuilder $typeOfWorkBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->typeOfWorkBuilder = resolve(TypeOfWorkBuilder::class);
    }

    /** @test */
    public function success_list_limit()
    {
        $this->loginUserAsSuperAdmin();

        TypeOfWork::factory()->count(50)->create(['name' => 'Alex']);

        $this->getJson(route('api.v1.type-of-works.shortlist', [
            'search' => 'alex',
        ]))
            ->assertJsonCount(TypeOfWorkShortListRequest::DEFAULT_LIMIT, 'data')
        ;
    }

    /** @test */
    public function success_list_by_limit()
    {
        $this->loginUserAsSuperAdmin();

        TypeOfWork::factory()->count(50)->create(['name' => 'Alex']);

        $this->getJson(route('api.v1.type-of-works.shortlist', [
            'search' => 'alex',
            'limit' => 10,
        ]))
            ->assertJsonCount(10, 'data')
        ;
    }

    /** @test */
    public function success_list_by_id()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 TypeOfWork */
        $m_1 = $this->typeOfWorkBuilder->create();

        $this->typeOfWorkBuilder->create();
        $this->typeOfWorkBuilder->create();

        $this->getJson(route('api.v1.type-of-works.shortlist', [
            'id' => $m_1->id
        ]))
            ->assertJson([
                'data' => [
                    [
                        'id' => $m_1->id,
                        'name' => $m_1->name,
                    ],
                ],
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_list_search()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Customer */
        $m_1 = $this->typeOfWorkBuilder->name('Alen')->create();
        $this->typeOfWorkBuilder->name('Mark')->create();
        $this->typeOfWorkBuilder->name('Tommy')->create();

        $this->getJson(route('api.v1.type-of-works.shortlist', [
            'search' => 'ale',
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
    public function success_list_search_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.type-of-works', [
            'search' => '555'
        ]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.type-of-works.shortlist', [
            'search' => 'rit',
        ]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.type-of-works.shortlist', [
            'search' => 'rit',
        ]));

        self::assertUnauthenticatedMessage($res);
    }
}
