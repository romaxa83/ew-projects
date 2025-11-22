<?php

namespace Tests\Feature\Mutations\BackOffice\Commercial\CommercialQuotes;

use App\GraphQL\Mutations\BackOffice\Commercial\CommercialQuotes\CommercialQuoteSendEmailMutation;
use App\Models\Commercial\CommercialSettings;
use App\Notifications\Commercial\CommercialQuoteNotification;
use App\Services\Commercial\CommercialQuoteHistoryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\Builders\Commercial\QuoteBuilder;
use Tests\Builders\Commercial\QuoteHistoryBuilder;
use Tests\TestCase;

class SendEmailTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = CommercialQuoteSendEmailMutation::NAME;

    protected $quoteBuilder;
    protected $quoteHistoryBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->quoteBuilder = resolve(QuoteBuilder::class);
        $this->quoteHistoryBuilder = resolve(QuoteHistoryBuilder::class);
    }

    /** @test */
    public function success_send(): void
    {
        $admin = $this->loginAsSuperAdmin();

        Notification::fake();
        Storage::fake('public');

        CommercialSettings::factory()->create();

        $model = $this->quoteBuilder->create();

        $this->assertEmpty($model->histories);

        $data = [
            'id' => $model->id,
        ];

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'message' => __('messages.commercial.quote.email_send'),
                        'type' => 'success',
                    ],
                ]
            ])
        ;

        $model->refresh();

        Storage::disk('public')
            ->assertExists($model->histories->first()->getPdfPath());

        $this->assertEquals($model->histories->first()->position, 1);
        $this->assertEquals($model->histories->first()->admin_id, $admin->id);
        $this->assertEquals(
            $model->histories->first()->estimate,
            $model->estimate.'-'.$model->histories->first()->position
        );
        $this->assertNotNull($model->histories->first()->data);

        Notification::assertSentTo(new AnonymousNotifiable(), CommercialQuoteNotification::class,
            function ($notification, $channels, $notifiable) use ($model) {
                return $notifiable->routes['mail'] == $model->email;
            }
        );
    }

    /** @test */
    public function success_few_history(): void
    {
        $admin = $this->loginAsSuperAdmin();

        Notification::fake();
        Storage::fake('public');

        CommercialSettings::factory()->create();

        $model = $this->quoteBuilder->create();
        $history = $this->quoteHistoryBuilder
            ->setAdminId($admin->id)
            ->setQuoteId($model->id)
            ->setPosition(2)
            ->create();

        $this->assertCount(1, $model->histories);

        $data = [
            'id' => $model->id,
        ];

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'message' => __('messages.commercial.quote.email_send'),
                            'type' => 'success',
                        ],
                    ]
                ]
            )
        ;

        $model->refresh();

        $this->assertCount(2, $model->histories);
        $this->assertEquals($model->histories->first()->position, $history->position + 1);
    }

    /** @test */
    public function exception_create_history(): void
    {
        $this->loginAsSuperAdmin();

        Notification::fake();
        Storage::fake('public');

        CommercialSettings::factory()->create();

        $model = $this->quoteBuilder->create();
        $data = [
            'id' => $model->id,
        ];

        $this->assertEmpty($model->histories);

        $this->mock(CommercialQuoteHistoryService::class, function(MockInterface $mock){
            $mock->shouldReceive("create")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'message' => "some exception message",
                            'type' => 'warning',
                        ],
                    ]
                ]
            )
        ;

        $model->refresh();

        $this->assertEmpty($model->histories);
    }

    protected function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s,
                ) {
                    message
                    type
                }
            }',
            self::MUTATION,
            $data['id']
        );
    }
}

