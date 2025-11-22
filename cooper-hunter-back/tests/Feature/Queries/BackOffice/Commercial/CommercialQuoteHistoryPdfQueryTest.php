<?php

namespace Tests\Feature\Queries\BackOffice\Commercial;

use App\GraphQL\Queries\BackOffice\Commercial\CommercialQuoteHistoryPdfQuery;
use App\Models\Commercial\CommercialSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Commercial\QuoteBuilder;
use Tests\Builders\Commercial\QuoteHistoryBuilder;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class CommercialQuoteHistoryPdfQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = CommercialQuoteHistoryPdfQuery::NAME;

    protected $quoteBuilder;
    protected $quoteHistoryBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->quoteBuilder = resolve(QuoteBuilder::class);
        $this->quoteHistoryBuilder = resolve(QuoteHistoryBuilder::class);
    }

    /** @test */
    public function success_generate(): void
    {
        $admin = $this->loginAsSuperAdmin();

        Storage::fake('public');

        CommercialSettings::factory()->create();

        $model = $this->quoteBuilder->create();

        $history_1 = $this->quoteHistoryBuilder->setAdminId($admin->id)
            ->setQuoteId($model->id)->setPosition(1)->create();

        $this->assertEmpty($history_1->media);

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($history_1->id)
        ]);

        $history_1->refresh();

        $this->assertEquals(
            $history_1->media->first()->getUrl(),
            $res->json("data.".self::MUTATION.".message")
        );
        $this->assertNotEmpty($history_1->media);
    }

    protected function getQueryStr($id): string
    {
        return sprintf(
            '
            {
                %s (id: %s) {
                    message
                    type
                }
            }',
            self::MUTATION,
            $id
        );
    }
}
