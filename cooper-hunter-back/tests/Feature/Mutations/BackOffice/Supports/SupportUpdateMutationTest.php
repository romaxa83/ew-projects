<?php

declare(strict_types=1);

namespace Tests\Feature\Mutations\BackOffice\Supports;

use App\GraphQL\Mutations\BackOffice\Supports\SupportUpdateMutation;
use App\Models\Support\Supports\Support;
use App\Models\Support\Supports\SupportTranslation;
use App\ValueObjects\Phone;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SupportUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = SupportUpdateMutation::NAME;

    public function test_create(): void
    {
        $this->loginAsSuperAdmin();

        $data = $this->getData();

        $this->mutation(
            [
                'input' => $data,
            ],
            $this->getSelect()
        )
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => $this->getSelect(),
                    ],
                ]
            );
    }

    protected function getData(): array
    {
        return [
            'phone' => new Phone($this->faker->e164PhoneNumber),
            'translations' => [
                [
                    'language' => 'en',
                    'description' => $this->faker->sentence,
                    'short_description' => $this->faker->sentence,
                    'working_time' => $this->faker->sentence,
                    'video_link' => $this->faker->imageUrl,
                ],
                [
                    'language' => 'es',
                    'description' => $this->faker->sentence,
                    'short_description' => $this->faker->sentence,
                    'working_time' => $this->faker->sentence,
                    'video_link' => $this->faker->imageUrl,
                ]
            ],
        ];
    }

    protected function mutation(array $args, array $select): TestResponse
    {
        $query = GraphQLQuery::mutation(self::MUTATION)
            ->args($args)
            ->select($select);

        return $this->postGraphQLBackOffice($query->make());
    }

    protected function getSelect(): array
    {
        return [
            'phone',
            'translation' => [
                'language',
                'description',
                'short_description',
                'working_time',
                'video_link',
            ],
        ];
    }

    public function test_update(): void
    {
        $this->loginAsSuperAdmin();

        Support::factory()
            ->has(SupportTranslation::factory()->allLocales(), 'translations')
            ->create();

        $data = $this->getData();

        $this->mutation(
            [
                'input' => $data,
            ],
            $this->getSelect()
        )
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => $this->getSelect(),
                    ],
                ]
            );
    }
}
