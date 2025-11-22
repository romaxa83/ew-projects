<?php

namespace Tests\Feature\Mutations\BackOffice\Commercial\Commissioning\Question;

use App\Enums\Commercial\Commissioning\AnswerPhotoType;
use App\Enums\Commercial\Commissioning\AnswerType;
use App\Enums\Commercial\Commissioning\QuestionStatus;
use App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\Question\CreateMutation;
use App\Models\Commercial\Commissioning\Question;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Commercial\Commissioning\ProtocolBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = CreateMutation::NAME;

    protected $protocolBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->protocolBuilder = resolve(ProtocolBuilder::class);
    }

    /** @test */
    public function success_create(): void
    {
        $this->loginAsSuperAdmin();

        $protocol = $this->protocolBuilder->create();

        $data = $this->data();
        $data['protocol_id'] = $protocol->id;

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'answer_type' => data_get($data, 'answer_type'),
                            'photo_type' => data_get($data, 'photo_type'),
                            'status' => data_get($data, 'status'),
                            'translations' => [
                                [
                                    'text' => data_get($data, 'translations.en.text'),
                                ],
                                [
                                    'text' => data_get($data, 'translations.es.text'),
                                ]
                            ]
                        ],
                    ]
                ]
            );

        $id = $res->json('data.'.self::MUTATION.'.id');

        $model = Question::query()->where('id', $id)->first();

        $this->assertTrue($model->isAnswerText());
        $this->assertTrue($model->status->isDraft());
        $this->assertEquals($model->protocol_id, $protocol->id);

    }

    /** @test */
    public function success_create_not_draft_status(): void
    {
        $this->loginAsSuperAdmin();

        $protocol = $this->protocolBuilder->create();

        $data = $this->data();
        $data['protocol_id'] = $protocol->id;
        $data['status'] = QuestionStatus::ACTIVE();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'status' => QuestionStatus::DRAFT(),
                        ],
                    ]
                ]
            );

        $id = $res->json('data.'.self::MUTATION.'.id');

        $model = Question::query()->where('id', $id)->first();

        $this->assertTrue($model->status->isDraft());

    }

    public function data(): array
    {
        return [
            'answer_type' => AnswerType::TEXT,
            'photo_type' => AnswerPhotoType::REQUIRED,
            'status' => QuestionStatus::DRAFT,
            'translations' => [
                'en' => [
                    'language' => 'en',
                    'text' => 'some question title en',
                ],
                'es' => [
                    'language' => 'es',
                    'text' => 'some question title es',
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
                        protocol_id: %s
                        answer_type: %s
                        photo_type: %s
                        status: %s
                        translations: [
                            {
                                language: %s,
                                text: "%s"
                            },
                            {
                                language: %s,
                                text: "%s",
                            },
                        ]
                    },
                ) {
                    id
                    answer_type
                    photo_type
                    status
                    translations {
                        text
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'protocol_id'),
            data_get($data, 'answer_type'),
            data_get($data, 'photo_type'),
            data_get($data, 'status'),
            data_get($data, 'translations.en.language'),
            data_get($data, 'translations.en.text'),
            data_get($data, 'translations.es.language'),
            data_get($data, 'translations.es.text'),
        );
    }
}

