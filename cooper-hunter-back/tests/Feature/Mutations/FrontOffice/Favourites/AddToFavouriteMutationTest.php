<?php

namespace Tests\Feature\Mutations\FrontOffice\Favourites;

use App\GraphQL\Mutations\FrontOffice\Favourites\AddToFavouriteMutation;
use App\Models\Catalog\Favourites\Favourite;
use App\Models\Catalog\Products\Product;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AddToFavouriteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = AddToFavouriteMutation::NAME;

    public function test_add_to_favourites(): void
    {
        $user = $this->loginAsUser();

        $product = Product::factory()->create();

        $type = new EnumValue(
            mb_strtoupper($product->getFavorableType()),
        );

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'favourite' => [
                    'id' => $product->getId(),
                    'type' => $type,
                ],
            ],
        );

        $this->assertDatabaseCount(Favourite::TABLE, 0);

        $this->postGraphQL($query->getMutation())
            ->assertJsonPath('data.' . self::MUTATION, true);

        $this->assertDatabaseCount(Favourite::TABLE, 1);

        self::assertInstanceOf(Favourite::class, $user->favourites()->first());
    }
}
