<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Labels;

use App\Enums\Catalog\Labels\ColorType;
use App\GraphQL\Mutations\BackOffice\Catalog\Labels\CreateMutation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = CreateMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_create(): void
    {
        $this->loginAsSuperAdmin();

        $data = $this->data();
        $data['color_type'] = ColorType::BLUE;

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'color_type' => data_get($data, 'color_type'),
                        'translations' => [
                            [
                                'title' => data_get($data, 'translations.en.title'),
                            ],
                            [
                                'title' => data_get($data, 'translations.es.title'),
                            ]
                        ]
                    ],
                ]
            ]);
    }

    public function data(): array
    {
        return [
            'translations' => [
                'en' => [
                    'language' => 'en',
                    'title' => $this->faker->sentence,
                ],
                'es' => [
                    'language' => 'es',
                    'title' => $this->faker->sentence,
                ],
            ]
        ];
    }

    protected function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    input: {
                        color_type: %s
                        translations: [
                            {
                                language: %s,
                                title: "%s"
                            },
                            {
                                language: %s,
                                title: "%s",
                            },
                        ]
                    },
                ) {
                    id
                    color_type
                    translations {
                        title
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'color_type'),
            data_get($data, 'translations.en.language'),
            data_get($data, 'translations.en.title'),
            data_get($data, 'translations.es.language'),
            data_get($data, 'translations.es.title'),
        );
    }
}
