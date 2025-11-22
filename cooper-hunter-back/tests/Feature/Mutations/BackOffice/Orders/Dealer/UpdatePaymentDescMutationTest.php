<?php

namespace Tests\Feature\Mutations\BackOffice\Orders\Dealer;

use App\Enums\Orders\Dealer\PaymentType;
use App\GraphQL\Mutations\BackOffice\Orders\Dealer\UpdatePaymentDescMutation;
use App\Models\About\Page;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdatePaymentDescMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = UpdatePaymentDescMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function update(): void
    {
        $this->loginAsSuperAdmin();

        $model = Page::query()->where('slug', PaymentType::BANK)->first();

        $data = $this->data();
        $data['type'] = PaymentType::BANK;

        $this->assertNotNull(
            $model->translations->where('language','en')->first()->description,
            data_get($data, 'translations.en.description')
        );
        $this->assertNotNull(
            $model->translations->where('language','es')->first()->description,
            data_get($data, 'translations.es.description')
        );

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'type' => PaymentType::BANK,
                        'translations' => [
                            [
                                'language' => 'en',
                                'description' => data_get($data, 'translations.en.description')
                            ],
                            [
                                'language' => 'es',
                                'description' => data_get($data, 'translations.es.description')
                            ]
                        ]
                    ],
                ]
            ])
        ;
    }

    /** @test */
    public function fail_type_none(): void
    {
        $this->loginAsSuperAdmin();

        $data = $this->data();
        $data['type'] = PaymentType::NONE;

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                   [
                       'message' => "validation"
                   ]
                ]
            ])
        ;
    }

    public function data(): array
    {
        return [
            'translations' => [
                'en' => [
                    'language' => 'en',
                    'description' => $this->faker->sentence,
                ],
                'es' => [
                    'language' => 'es',
                    'description' => $this->faker->sentence,
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
                        type: %s
                        translations: [
                            {
                                language: %s,
                                description: "%s"
                            },
                            {
                                language: %s,
                                description: "%s",
                            },
                        ]
                    },
                ) {
                    type
                    translations {
                        language
                        description
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'type'),
            data_get($data, 'translations.en.language'),
            data_get($data, 'translations.en.description'),
            data_get($data, 'translations.es.language'),
            data_get($data, 'translations.es.description'),
        );
    }
}

