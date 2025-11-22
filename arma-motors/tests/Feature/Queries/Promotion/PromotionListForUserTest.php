<?php

namespace Tests\Feature\Queries\Promotion;

use App\Models\Promotion\Promotion;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Builders\PromotionBuilder;
use Tests\Traits\UserBuilder;

class PromotionListForUserTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use UserBuilder;
    use PromotionBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $builder = $this->promotionBuilder();
        $builder->setType(Promotion::TYPE_COMMON)->create();
        $builder->setType(Promotion::TYPE_COMMON)->create();
        $builder->setType(Promotion::TYPE_INDIVIDUAL)->setUsersId([$user->id])->create();
        $builder->setType(Promotion::TYPE_INDIVIDUAL)->create();
        $builder->setType(Promotion::TYPE_INDIVIDUAL)->create();

        $user->refresh();

        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $this->assertEquals(3, count($response->json('data.promotionsForUser')));
    }

    /** @test */
    public function not_auth()
    {
        $user = $this->userBuilder()->create();

        $builder = $this->promotionBuilder();
        $builder->setType(Promotion::TYPE_COMMON)->create();
        $builder->setType(Promotion::TYPE_COMMON)->create();
        $builder->setType(Promotion::TYPE_INDIVIDUAL)->setUsersId([$user->id])->create();
        $builder->setType(Promotion::TYPE_INDIVIDUAL)->create();
        $builder->setType(Promotion::TYPE_INDIVIDUAL)->create();

        $user->refresh();

        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $this->assertEquals(2, count($response->json('data.promotionsForUser')));
    }

    /** @test */
    public function not_another_user()
    {
        $builder = $this->userBuilder();
        $user = $builder->setPhone('38099999888871')->setEmail('test1@ukr.net')->create();
        $user2 = $builder->setPhone('38099999888872')->setEmail('test2@ukr.net')->create();
        $this->loginAsUser($user2);


        $builder = $this->promotionBuilder();
        $builder->setType(Promotion::TYPE_COMMON)->create();
        $builder->setType(Promotion::TYPE_COMMON)->create();
        $builder->setType(Promotion::TYPE_INDIVIDUAL)->setUsersId([$user->id])->create();
        $builder->setType(Promotion::TYPE_INDIVIDUAL)->setUsersId([$user2->id])->create();
        $builder->setType(Promotion::TYPE_INDIVIDUAL)->setUsersId([$user2->id])->create();

        $user->refresh();

        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $this->assertEquals(4, count($response->json('data.promotionsForUser')));
    }

    public function getQueryStr(): string
    {
        return  sprintf('{
            promotionsForUser {
                id
                type
                link
                current {
                    name
                }
                department {
                    id
                }
               }
            }'
        );
    }
}
