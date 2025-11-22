<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Labels;

use App\Enums\Catalog\Labels\ColorType;
use App\GraphQL\Mutations\BackOffice\Catalog\Labels\UpdateMutation;
use App\Models\Catalog\Labels\Label;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Catalog\LabelBuilder;
use Tests\TestCase;

class UpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = UpdateMutation::NAME;

    protected LabelBuilder $labelBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->labelBuilder = resolve(LabelBuilder::class);
    }

    /** @test */
    public function success_update(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Label */
        $model = $this->labelBuilder->withTranslation()->create();

        $data = $this->data();
        $data['color_type'] = ColorType::RED();
        $data['id'] = $model->id;

        $this->assertNotEquals($model->color_type, data_get($data, 'color_type'));
        $this->assertNotEquals(
            $model->translations->where('language', 'en')->first()->title,
            data_get($data, 'translations.en.title')
        );
        $this->assertNotEquals(
            $model->translations->where('language', 'es')->first()->title,
            data_get($data, 'translations.es.title')
        );

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
                    id: %s
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
            data_get($data, 'id'),
            data_get($data, 'color_type'),
            data_get($data, 'translations.en.language'),
            data_get($data, 'translations.en.title'),
            data_get($data, 'translations.es.language'),
            data_get($data, 'translations.es.title'),
        );
    }
}
