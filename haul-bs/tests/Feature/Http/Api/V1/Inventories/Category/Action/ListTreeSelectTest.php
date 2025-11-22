<?php

namespace Feature\Http\Api\V1\Inventories\Category\Action;

use App\Models\Inventories\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\CategoryBuilder;
use Tests\TestCase;

class ListTreeSelectTest extends TestCase
{
    use DatabaseTransactions;

    protected CategoryBuilder $categoryBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->categoryBuilder = resolve(CategoryBuilder::class);
    }

    /** @test */
    public function success_list()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Category */
        $root_1 = $this->categoryBuilder->name('root_1')->position(1)->create();
        $root_2 = $this->categoryBuilder->name('root_2')->position(2)->create();

        $cat_1_1 = $this->categoryBuilder->name('cat_1_1')->parent($root_1)->position(1)->create();
        $cat_1_2 = $this->categoryBuilder->name('cat_1_2')->parent($root_1)->position(3)->create();
        $cat_1_3 = $this->categoryBuilder->name('cat_1_3')->parent($root_1)->position(2)->create();

        $cat_1_1_1 = $this->categoryBuilder->name('cat_1_1_1')->parent($cat_1_1)->position(1)->create();
        $cat_1_1_2 = $this->categoryBuilder->name('cat_1_1_2')->parent($cat_1_1)->position(2)->create();

        $cat_1_2_1 = $this->categoryBuilder->name('cat_1_2_1')->parent($cat_1_2)->position(3)->create();
        $cat_1_2_2 = $this->categoryBuilder->name('cat_1_2_2')->parent($cat_1_2)->position(2)->create();
        $cat_1_2_3 = $this->categoryBuilder->name('cat_1_2_3')->parent($cat_1_2)->position(1)->create();

        $cat_2_1 = $this->categoryBuilder->name('cat_2_1')->parent($root_2)->position(1)->create();
        $cat_2_2 = $this->categoryBuilder->name('cat_2_2')->parent($root_2)->position(2)->create();

        $sym = chr(0xC2).chr(0xA0).chr(0xC2).chr(0xA0);

        $this->getJson(route('api.v1.inventories.category.list-tree-select'))
            ->assertJson([
                'data' => [
                    $root_1->id => $root_1->name,
                    $cat_1_1->id => $sym . $cat_1_1->name,
                    $cat_1_1_1->id => $sym . $sym .  $cat_1_1_1->name,
                    $cat_1_1_2->id => $sym . $sym . $cat_1_1_2->name,
                    $cat_1_3->id => $sym . $cat_1_3->name,
                    $cat_1_2->id => $sym . $cat_1_2->name,
                    $cat_1_2_3->id => $sym . $sym . $cat_1_2_3->name,
                    $cat_1_2_2->id => $sym . $sym . $cat_1_2_2->name,
                    $cat_1_2_1->id => $sym . $sym . $cat_1_2_1->name,
                    $root_2->id => $root_2->name,
                    $cat_2_1->id => $sym . $cat_2_1->name,
                    $cat_2_2->id => $sym . $cat_2_2->name,
                ]
            ])
        ;
    }

    /** @test */
    public function success_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.inventories.category.list-tree-select'))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.inventories.category.list-tree-select'));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.inventories.category.list-tree-select'));

        self::assertUnauthenticatedMessage($res);
    }
}
