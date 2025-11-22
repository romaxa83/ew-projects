<?php

namespace Tests\Feature\Http\Api\V1\Customers\CustomerCrud;

use App\Http\Requests\Customers\CustomerShortListRequest;
use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class ShortListTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;
    protected UserBuilder $userBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->customerBuilder = resolve(CustomerBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success_list_limit()
    {
        $this->loginUserAsSuperAdmin();

        Customer::factory()->count(50)->create(['first_name' => 'Alex']);

        $this->getJson(route('api.v1.customers.shortlist', [
            'search' => 'alex',
        ]))
            ->assertJsonCount(CustomerShortListRequest::DEFAULT_LIMIT, 'data')
        ;
    }

    /** @test */
    public function success_list_by_limit()
    {
        $this->loginUserAsSuperAdmin();

        Customer::factory()->count(50)->create(['first_name' => 'Alex']);

        $this->getJson(route('api.v1.customers.shortlist', [
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

        /** @var $m_1 Customer */
        $m_1 = $this->customerBuilder->create();

        $this->customerBuilder->create();
        $this->customerBuilder->create();

        $this->getJson(route('api.v1.customers.shortlist', [
            'id' => $m_1->id
        ]))
            ->assertJson([
                'data' => [
                    [
                        'id' => $m_1->id,
                        'first_name' => $m_1->first_name,
                        'last_name' => $m_1->last_name,
                        'email' => $m_1->email->getValue(),
                        'phone' => $m_1->phone->getValue(),
                    ],
                ],
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_list_by_name()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Customer */
        $m_1 = $this->customerBuilder
            ->first_name('Alen')
            ->last_name('Wall')
            ->email('test1@test.com')
            ->create();
        $m_2 = $this->customerBuilder
            ->first_name('Mark')
            ->last_name('Aleftin')
            ->email('test2@test.com')
            ->create();
        $this->customerBuilder
            ->first_name('Tommy')
            ->last_name('Wall')
            ->email('test3@test.com')
            ->create();
        $m_4 = $this->customerBuilder->fromHaulk()
            ->first_name('Alen')->create();

        $this->getJson(route('api.v1.customers.shortlist', [
            'search' => 'ale',
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_4->id],
                    ['id' => $m_2->id],
                    ['id' => $m_1->id],
                ],
            ])
            ->assertJsonCount(3, 'data')
        ;
    }

    /** @test */
    public function success_list_by_name_as_sales_manager()
    {
        $sales = $this->loginUserAsSalesManager();

        $sales_1 = $this->userBuilder->asSalesManager()->create();

        /** @var $m_1 Customer */
        $m_1 = $this->customerBuilder
            ->first_name('Alen')
            ->last_name('Wall')
            ->create();
        $m_2 = $this->customerBuilder
            ->first_name('Mark')
            ->salesManager($sales_1)
            ->last_name('Aleftin')
            ->create();
        $m_3 = $this->customerBuilder
            ->first_name('Mark')
            ->last_name('Aleftin')
            ->salesManager($sales)
            ->create();

        $this->customerBuilder->first_name('Tommy')->last_name('Wall')->email('test@t.com')->create();

        $this->getJson(route('api.v1.customers.shortlist', [
            'search' => 'ale',
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id],
                    ['id' => $m_1->id],
                ],
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_list_by_email()
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
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_list_by_phone()
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
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_list_by_phone_and_search_empty()
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
            'search' => '555'
        ]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.customers.shortlist', [
            'search' => 'rit',
        ]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.customers.shortlist', [
            'search' => 'rit',
        ]));

        self::assertUnauthenticatedMessage($res);
    }
}
