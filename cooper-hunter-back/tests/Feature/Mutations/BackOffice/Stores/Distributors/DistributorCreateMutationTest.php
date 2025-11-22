<?php

declare(strict_types=1);

namespace Tests\Feature\Mutations\BackOffice\Stores\Distributors;

use App\GraphQL\Mutations\BackOffice\Stores\Distributors\DistributorCreateMutation;
use App\Models\Locations\State;
use App\ValueObjects\Phone;
use App\ValueObjects\Point;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class DistributorCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = DistributorCreateMutation::NAME;

    public function test_do_success(): void
    {
        $this->loginAsSuperAdmin();

        $this->getMutation()
            ->assertJsonStructure(
                [
                    'data' => [
                        static::MUTATION => $this->getSelect(),
                    ],
                ]
            );
    }

    protected function getMutation(array $args = []): TestResponse
    {
        return $this->mutation(
            array_merge($this->getArgs(), $args),
            $this->getSelect()
        );
    }

    protected function mutation(array $args, array $select): TestResponse
    {
        $query = GraphQLQuery::mutation(static::MUTATION)
            ->args($args)
            ->select($select);

        return $this->postGraphQLBackOffice($query->make());
    }

    protected function getArgs(): array
    {
        return [
            'input' => [
                'state_id' => State::factory()->create()->id,
                'active' => true,
                'coordinates' => (new Point($this->faker->longitude, $this->faker->latitude))->asCoordinates(),
                'address' => $this->faker->streetAddress,
                'link' => $this->faker->imageUrl,
                'phone' => new Phone($this->faker->e164PhoneNumber),
                'translations' => [
                    [
                        'language' => 'en',
                        'title' => 'en title',
                    ],
                    [
                        'language' => 'es',
                        'title' => 'es title',
                    ],
                ],
            ],
        ];
    }

    protected function getSelect(): array
    {
        return [
            'id',
            'active',
            'coordinates' => [
                'longitude',
                'latitude',
            ],
            'address',
            'link',
            'phone',
            'translation' => [
                'language',
                'title',
            ]
        ];
    }

    public function test_not_permitted_user_get_no_permission_error(): void
    {
        $this->loginAsAdmin();

        $this->assertServerError($this->getMutation(), 'No permission');
    }

    public function test_guest_get_unauthorized_error(): void
    {
        $this->assertServerError($this->getMutation(), 'Unauthorized');
    }
}
