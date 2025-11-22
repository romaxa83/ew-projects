<?php

namespace Tests\Feature\Mutations\BackOffice\Commercial\Commissioning\Protocol;

use App\Enums\Commercial\Commissioning\ProtocolType;
use App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\Protocol\UpdateMutation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Commercial\Commissioning\ProtocolBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = UpdateMutation::NAME;

    protected $protocolBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->protocolBuilder = resolve(ProtocolBuilder::class);
    }

    /** @test */
    public function success_update(): void
    {
        $this->loginAsSuperAdmin();

        $model = $this->protocolBuilder->withTranslation()->create();

        $data = $this->data();
        $data['id'] = $model->id;
        $data['type'] = ProtocolType::PRE_COMMISSIONING;

        $this->assertNotEquals(
            $model->translations()->where('language', 'en')->first()->title,
            data_get($data, 'translations.en.title')
        );
        $this->assertNotEquals(
            $model->translations()->where('language', 'en')->first()->desc,
            data_get($data, 'translations.en.description')
        );
        $this->assertNotEquals(
            $model->translations()->where('language', 'es')->first()->title,
            data_get($data, 'translations.es.title')
        );
        $this->assertNotEquals(
            $model->translations()->where('language', 'es')->first()->desc,
            data_get($data, 'translations.es.description')
        );

        $this->assertNotEquals($model->type, data_get($data, 'type'));

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'id' => $model->id,
                            'type' => data_get($data, 'type'),
                            'translations' => [
                                [
                                    'title' => data_get($data, 'translations.en.title'),
                                    'description' => data_get($data, 'translations.en.description'),
                                ],
                                [
                                    'title' => data_get($data, 'translations.es.title'),
                                    'description' => data_get($data, 'translations.es.description'),
                                ]
                            ]
                        ],
                    ]
                ]
            );
    }

    public function data(): array
    {
        return [
            'type' => ProtocolType::COMMISSIONING,
            'translations' => [
                'en' => [
                    'language' => 'en',
                    'title' => 'some protocol title en',
                    'description' => 'some protocol desc en',
                ],
                'es' => [
                    'language' => 'es',
                    'title' => 'some protocol title es',
                    'description' => 'some protocol desc es',
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
                        type: %s
                        translations: [
                            {
                                language: %s,
                                title: "%s",
                                description: "%s",
                            },
                            {
                                language: %s,
                                title: "%s",
                                description: "%s",
                            },
                        ]
                    },
                ) {
                    id
                    type
                    translations {
                        title
                        description
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'id'),
            data_get($data, 'type'),
            data_get($data, 'translations.en.language'),
            data_get($data, 'translations.en.title'),
            data_get($data, 'translations.en.description'),
            data_get($data, 'translations.es.language'),
            data_get($data, 'translations.es.title'),
            data_get($data, 'translations.es.description'),
        );
    }
}

