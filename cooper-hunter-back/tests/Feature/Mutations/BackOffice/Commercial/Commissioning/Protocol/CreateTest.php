<?php

namespace Tests\Feature\Mutations\BackOffice\Commercial\Commissioning\Protocol;

use App\Enums\Commercial\Commissioning\ProtocolStatus;
use App\Enums\Commercial\Commissioning\ProtocolType;
use App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\Protocol\CreateMutation;
use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\Commissioning\Protocol;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Commercial\Commissioning\ProjectProtocolBuilder;
use Tests\Builders\Commercial\Commissioning\ProtocolBuilder;
use Tests\Builders\Commercial\ProjectBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = CreateMutation::NAME;

    protected ProjectBuilder $projectBuilder;
    protected ProtocolBuilder $protocolBuilder;
    protected ProjectProtocolBuilder $protocolProjectBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->projectBuilder = resolve(ProjectBuilder::class);
        $this->protocolBuilder = resolve(ProtocolBuilder::class);
        $this->protocolProjectBuilder = resolve(ProjectProtocolBuilder::class);
    }

    /** @test */
    public function success_create_commissioning(): void
    {
        $this->loginAsSuperAdmin();

        $data = $this->data();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
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

        $id = $res->json('data.'.self::MUTATION.'.id');

        $model = Protocol::query()->where('id', $id)->first();

        $this->assertTrue($model->isCommissioning());

    }

    /** @test */
    public function create_pre_commissioning_protocol_and_add_to_pre_commissioning(): void
    {
        $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();
        /** @var $project_1 CommercialProject */
        $project_1 = $this->projectBuilder->setStartPreCommissioningDate($date)->create();
        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();
        $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project_1)->create();

        $project_2 = $this->projectBuilder->setStartCommissioningDate($date)->create();
        $protocol_2 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();
        $this->protocolProjectBuilder->setProtocol($protocol_2)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project_2)->create();

        $this->assertTrue($project_1->isStartPreCommissioning());
        $this->assertCount(1, $project_1->projectProtocols);

        $this->assertTrue($project_2->isStartCommissioning());
        $this->assertCount(1, $project_2->projectProtocols);

        $data = $this->data();
        $data['type'] = ProtocolType::PRE_COMMISSIONING;

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertOk()
            ->json('data.'.self::MUTATION.'.id')
        ;

        $project_1->refresh();
        $project_2->refresh();

        $this->assertCount(2, $project_1->projectProtocols);
        $this->assertCount(1, $project_2->projectProtocols);
    }

    /** @test */
    public function create_commissioning_protocol_and_add_to_commissioning(): void
    {
        $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();
        /** @var $project_1 CommercialProject */
        $project_1 = $this->projectBuilder->setStartCommissioningDate($date)->create();
        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::COMMISSIONING)->create();
        $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project_1)->create();

        $project_2 = $this->projectBuilder->setEndCommissioningDate($date)->create();
        $protocol_2 = $this->protocolBuilder->setType(ProtocolType::COMMISSIONING)->create();
        $this->protocolProjectBuilder->setProtocol($protocol_2)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project_2)->create();

        $this->assertTrue($project_1->isStartCommissioning());
        $this->assertCount(1, $project_1->projectProtocols);

        $this->assertTrue($project_2->isEndCommissioning());
        $this->assertCount(1, $project_2->projectProtocols);

        $data = $this->data();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertOk()
            ->json('data.'.self::MUTATION.'.id')
        ;

        $project_1->refresh();
        $project_2->refresh();

        $this->assertCount(2, $project_1->projectProtocols);
        $this->assertCount(1, $project_2->projectProtocols);
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
