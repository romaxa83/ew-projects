<?php

namespace Tests\Feature\Queries\FrontOffice\Commercial;

use App\Enums\Commercial\CommercialProjectStatusEnum;
use App\Enums\Commercial\CommercialQuoteStatusEnum;
use App\GraphQL\Queries\FrontOffice\Commercial\CommercialProjectsForQuotesQuery;
use App\Models\Technicians\Technician;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Commercial\ProjectBuilder;
use Tests\Builders\Commercial\QuoteBuilder;
use Tests\TestCase;

class CommercialProjectsForQuotesQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = CommercialProjectsForQuotesQuery::NAME;

    protected $quoteBuilder;
    protected $projectBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->quoteBuilder = resolve(QuoteBuilder::class);
        $this->projectBuilder = resolve(ProjectBuilder::class);
    }

    /** @test */
    public function success_list(): void
    {
        $tech = $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();

        // check
        $project_1 = $this->projectBuilder->setTechnician($tech)->setEstimateEndDate($date->addDays(1))
            ->setStatus(CommercialProjectStatusEnum::PENDING)->create();
        $project_2 = $this->projectBuilder->setTechnician($tech)->setEstimateEndDate($date->addDays(3))
            ->setStatus(CommercialProjectStatusEnum::PENDING)->create();
        // not check
        $project_3 = $this->projectBuilder->setTechnician($tech)->setEstimateEndDate($date->subDays(1))
            ->setStatus(CommercialProjectStatusEnum::PENDING)->create();
        $project_4 = $this->projectBuilder->setTechnician($tech)->setEstimateEndDate($date->addDays(1))
            ->setStatus(CommercialProjectStatusEnum::CREATED)->create();


        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            ['id' => $project_1->id],
                            ['id' => $project_2->id],
                        ],
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.'.self::QUERY)
        ;
    }

    /** @test */
    public function success_list_with_quote(): void
    {
        $tech = $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();

        // check
        $project_1 = $this->projectBuilder->setTechnician($tech)->setEstimateEndDate($date->addDays(1))
            ->setStatus(CommercialProjectStatusEnum::PENDING)->create();
        // not check
        $project_2 = $this->projectBuilder->setTechnician($tech)->setEstimateEndDate($date->addDays(3))
            ->setStatus(CommercialProjectStatusEnum::PENDING)->create();
        $project_3 = $this->projectBuilder->setTechnician($tech)->setEstimateEndDate($date->subDays(1))
            ->setStatus(CommercialProjectStatusEnum::PENDING)->create();
        $project_4 = $this->projectBuilder->setTechnician($tech)->setEstimateEndDate($date->addDays(1))
            ->setStatus(CommercialProjectStatusEnum::CREATED)->create();

        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::DONE)->setProject($project_1)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::PENDING)->setProject($project_2)->create();


        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            ['id' => $project_1->id],
                        ],
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.'.self::QUERY)
        ;
    }

    /** @test */
    public function success_list_with_quotes(): void
    {
        $tech = $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();

        // check
        $project_1 = $this->projectBuilder->setTechnician($tech)->setEstimateEndDate($date->addDays(1))
            ->setStatus(CommercialProjectStatusEnum::PENDING)->create();
        // not check
        $project_2 = $this->projectBuilder->setTechnician($tech)->setEstimateEndDate($date->addDays(3))
            ->setStatus(CommercialProjectStatusEnum::PENDING)->create();

        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::FINAL)->setProject($project_1)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::DONE)->setProject($project_2)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::DONE)->setProject($project_2)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::DONE)->setProject($project_2)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::PENDING)->setProject($project_2)->create();


        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            ['id' => $project_1->id],
                        ],
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.'.self::QUERY)
        ;
    }

    protected function getQueryStr(): string
    {
        return sprintf(
            '
            {
                %s {
                       id
                }
            }',
            self::QUERY
        );
    }

    /** @test */
    public function fail_technic_not_have_certificate(): void
    {
        $this->loginAsTechnicianWithRole(
            Technician::factory()->certified()->verified()
                ->create(['is_commercial_certification' => false])
        );

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.commercial.technician does\'n have a commercial certificate")]
                ]
            ])
        ;
    }
}



