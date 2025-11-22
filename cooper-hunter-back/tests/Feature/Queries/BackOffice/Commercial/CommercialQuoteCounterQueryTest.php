<?php

namespace Tests\Feature\Queries\BackOffice\Commercial;

use App\Enums\Commercial\CommercialQuoteStatusEnum;
use App\GraphQL\Queries\BackOffice\Commercial\CommercialQuoteCounterQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Commercial\QuoteBuilder;
use Tests\TestCase;

class CommercialQuoteCounterQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = CommercialQuoteCounterQuery::NAME;

    protected $quoteBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->quoteBuilder = resolve(QuoteBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        $this->loginAsSuperAdmin();

        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::PENDING)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::PENDING)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::PENDING)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::DONE)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::DONE)->create();
        $this->quoteBuilder->setStatus(CommercialQuoteStatusEnum::FINAL)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'pending' => 3,
                            'done' => 2,
                            'final' => 1,
                            'total' => 6,
                        ],
                    ]
                ]
            )
        ;
    }

    /** @test */
    public function success_not_any_records(): void
    {
        $this->loginAsSuperAdmin();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'pending' => 0,
                            'done' => 0,
                            'final' => 0,
                            'total' => 0,
                        ],
                    ]
                ]
            )
        ;
    }

    protected function getQueryStr(): string
    {
        return sprintf(
            '
            {
                %s {
                        pending
                        done
                        final
                        total
                }
            }',
            self::MUTATION
        );
    }
}

