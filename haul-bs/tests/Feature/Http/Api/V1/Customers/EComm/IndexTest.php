<?php

namespace Feature\Http\Api\V1\Customers\EComm;

use App\Enums\Tags\TagType;
use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Tags\TagBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;
    protected TagBuilder $tagBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->customerBuilder = resolve(CustomerBuilder::class);
        $this->tagBuilder = resolve(TagBuilder::class);
    }

    /** @test */
    public function success_pagination()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->customerBuilder->create();
        $m_2 = $this->customerBuilder->create();
        $m_3 = $this->customerBuilder->create();
        $m_4 =  $this->customerBuilder->fromHaulk()->create();

        $this->getJsonEComm(route('api.v1.e_comm.customers.index'))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'phone',
                        'phone_extension',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    ['id' => $m_4->id],
                    ['id' => $m_3->id],
                    ['id' => $m_2->id],
                    ['id' => $m_1->id],
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

        $this->getJsonEComm(route('api.v1.e_comm.customers.index', ['page' => 2]))
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

        $this->getJsonEComm(route('api.v1.e_comm.customers.index', ['per_page' => 2]))
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

        $this->getJsonEComm(route('api.v1.e_comm.customers.index'))
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
    public function success_search_by_email()
    {
        $this->loginUserAsSuperAdmin();

        $tag_1 = $this->tagBuilder->type(TagType::CUSTOMER())->create();
        $tag_2 = $this->tagBuilder->type(TagType::CUSTOMER())->create();

        /** @var $m_1 Customer */
        $m_1 = $this->customerBuilder
            ->first_name('Alen')
            ->last_name('Wall')
            ->email('test@tesst.com')
            ->tags($tag_1, $tag_2)
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

        $this->getJsonEComm(route('api.v1.e_comm.customers.index', [
            'email' => 'test@tesst.com'
        ]))
            ->assertJson([
                'data' => [
                    [
                        'id' => $m_1->id,
                        'email' => $m_1->email,
                        'type' => $m_1->type->value,
                        'tags' => [
                            ['id' => $tag_1->id],
                            ['id' => $tag_2->id],
                        ]
                    ],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
            ->assertJsonCount(2, 'data.0.tags')
        ;
    }

    /** @test */
    public function wrong_token()
    {

        $res = $this->getJsonEComm(route('api.v1.e_comm.customers.index'), [
            'Authorization' => 'wrong'
        ]);

        self::assertErrorMsg($res, "Wrong e-comm auth-token", Response::HTTP_UNAUTHORIZED);
    }
}
