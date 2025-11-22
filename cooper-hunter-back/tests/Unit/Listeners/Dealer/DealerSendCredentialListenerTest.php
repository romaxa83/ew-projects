<?php

namespace Tests\Unit\Listeners\Dealer;

use App\Dto\Dealers\DealerDto;
use App\Events\Dealers\CreateOrUpdateDealerEvent;
use App\Listeners\Dealers\DealerSendCredentialsListener;
use App\Models\Dealers\Dealer;
use App\Notifications\Dealers\SendCredentialsNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\TestCase;

class DealerSendCredentialListenerTest extends TestCase
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
        $model = $this->dealerBuilder->create();
        $model->refresh();

        $dto = DealerDto::byArgs([
            'email' => $this->faker->safeEmail,
            'name' => $this->faker->name,
            'company_id' => 1,
        ]);

        $event = new CreateOrUpdateDealerEvent($model, $dto);
        $listener = resolve(DealerSendCredentialsListener::class);
        $listener->handle($event);

        Notification::assertSentTo(new AnonymousNotifiable(), SendCredentialsNotification::class,
            function ($notification, $channels, $notifiable) use ($dto) {
                return $notifiable->routes['mail'] == $dto->email->getValue();
            }
        );
    }

    /** @test */
    public function not_send_if_not_have_dto()
    {
        Notification::fake();
        /** @var $model Dealer */
        $model = $this->dealerBuilder->create();
        $model->refresh();

        $event = new CreateOrUpdateDealerEvent($model);
        $listener = resolve(DealerSendCredentialsListener::class);
        $listener->handle($event);

        Notification::assertNotSentTo(new AnonymousNotifiable(), SendCredentialsNotification::class);
    }
}
