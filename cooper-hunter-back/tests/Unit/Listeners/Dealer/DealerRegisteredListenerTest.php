<?php

namespace Tests\Unit\Listeners\Dealer;

use App\Events\Dealers\DealerRegisteredEvent;
use App\Listeners\Dealers\DealerRegisteredListener;
use App\Models\Dealers\Dealer;
use App\Notifications\Members\MemberEmailVerification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\TestCase;

class DealerRegisteredListenerTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected DealerBuilder $dealerBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->dealerBuilder = resolve(DealerBuilder::class);
    }

    /** @test */
    public function success_send()
    {
        Notification::fake();
        /** @var $model Dealer */
        $model = $this->dealerBuilder->setData([
            'email_verified_at' => null
        ])->create();
        $model->refresh();

        $this->assertFalse($model->isEmailVerified());
        $this->assertNull($model->email_verification_code);

        $event = new DealerRegisteredEvent($model);
        $listener = resolve(DealerRegisteredListener::class);
        $listener->handle($event);

        Notification::assertSentTo(new AnonymousNotifiable(), MemberEmailVerification::class,
            function ($notification, $channels, $notifiable) use ($model) {
                return $notifiable->routes['mail'] == $model->email->getValue()
                    && $notification->member->id === $model->id
                    ;
            }
        );

        $model->refresh();

        $this->assertNotNull($model->email_verification_code);
    }
}
