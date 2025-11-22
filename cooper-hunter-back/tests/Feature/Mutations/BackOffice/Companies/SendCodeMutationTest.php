<?php

namespace Tests\Feature\Mutations\BackOffice\Companies;

use App\GraphQL\Mutations\BackOffice\Companies;
use App\Models\Companies\Company;
use App\Notifications\Companies\SendCodeForDealerNotification;
use App\Services\Companies\CompanyService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Mockery\MockInterface;
use Tests\Builders\Company\CompanyBuilder;
use Tests\TestCase;

class SendCodeMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = Companies\SendCodeMutation::NAME;

    protected CompanyBuilder $companyBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    /** @test */
    public function success_send(): void
    {
        Notification::fake();
        $this->loginAsSuperAdmin();

        /** @var $company Company */
        $company = $this->companyBuilder->setData([
            'code' => '909090'
        ])->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($company->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'message' => __('messages.company.send_code.message'),
                        'type' => 'success',
                    ],
                ]
            ])
        ;

        Notification::assertSentTo(new AnonymousNotifiable(), SendCodeForDealerNotification::class,
            function ($notification, $channels, $notifiable) use ($company) {
                return $notifiable->routes['mail'] == $company->email;
            }
        );
    }

    /** @test */
    public function fail_not_code(): void
    {
        Notification::fake();
        $this->loginAsSuperAdmin();

        /** @var $company Company */
        $company = $this->companyBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($company->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'message' => __('messages.company.send_code.has no code'),
                        'type' => 'warning',
                    ],
                ]
            ])
        ;

        Notification::assertNotSentTo(new AnonymousNotifiable(), SendCodeForDealerNotification::class);
    }

    /** @test */
    public function something_wrong_into_service(): void
    {
        Notification::fake();
        $this->loginAsSuperAdmin();

        /** @var $company Company */
        $company = $this->companyBuilder->setData([
            'code' => '909090'
        ])->create();

        $this->mock(CompanyService::class, function(MockInterface $mock){
            $mock->shouldReceive("sendCode")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($company->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'message' => "some exception message",
                        'type' => 'warning',
                    ],
                ]
            ])
        ;

        Notification::assertNotSentTo(new AnonymousNotifiable(), SendCodeForDealerNotification::class);
    }

    protected function getQueryStr($id): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                ) {
                    type
                    message
                }
            }',
            self::MUTATION,
            $id
        );
    }
}
