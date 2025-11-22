<?php

namespace Tests\Unit\Listeners\Company;

use App\Events\Companies\UpdateCompanyByOnecEvent;
use App\Listeners\Companies\SendCodeForDealerListener;
use App\Models\Companies\Company;
use App\Models\Request\Request;
use App\Notifications\Companies\SendCodeForDealerNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\Builders\Company\CompanyBuilder;
use Tests\TestCase;

class SendCodeForDealerListenerTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected CompanyBuilder $companyBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    /** @test */
    public function success_send()
    {
        Notification::fake();
        /** @var $model Company */
        $model = $this->companyBuilder->create();

        $this->assertNull(Request::first());

        $event = new UpdateCompanyByOnecEvent($model);
        $listener = resolve(SendCodeForDealerListener::class);
        $listener->handle($event);

        Notification::assertSentTo(new AnonymousNotifiable(), SendCodeForDealerNotification::class,
            function ($notification, $channels, $notifiable) use ($model) {
                return $notifiable->routes['mail'] == $model->email->getValue();
            }
        );
    }
}
