<?php

namespace Api\BodyShop\TypesOfWork;

use App\Models\BodyShop\TypesOfWork\TypeOfWork;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class TypeOfWorkShortListTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_search()
    {
        $this->loginAsBodyShopSuperAdmin();

        $type1 = factory(TypeOfWork::class)->create([
            'name' => 'Name1',
        ]);

        $type2 = factory(TypeOfWork::class)->create([
            'name' => 'Name2',
        ]);

        $type3 = factory(TypeOfWork::class)->create([
            'name' => 'Name3',
        ]);


        $filter = ['q' => 'Name3'];
        $response = $this->getJson(route('body-shop.types-of-work.shortlist', $filter))
            ->assertOk();

        $types = $response->json('data');
        $this->assertCount(1, $types);
        $this->assertEquals($type3->id, $types[0]['id']);

        $filter = ['searchid' => $type2->id];
        $response = $this->getJson(route('body-shop.types-of-work.shortlist', $filter))
            ->assertOk();

        $types = $response->json('data');
        $this->assertCount(1, $types);
        $this->assertEquals($type2->id, $types[0]['id']);
    }
}
