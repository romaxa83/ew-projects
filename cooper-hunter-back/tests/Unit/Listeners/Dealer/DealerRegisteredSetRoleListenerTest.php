<?php

namespace Tests\Unit\Listeners\Dealer;

use App\Events\Dealers\DealerRegisteredEvent;
use App\Listeners\Dealers\DealerRegisteredSetRoleListener;
use App\Models\Dealers\Dealer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\TestCase;

class DealerRegisteredSetRoleListenerTest extends TestCase
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
    public function success_attach_role()
    {
        /** @var $model Dealer */
        $model = $this->dealerBuilder->create();
        $model->refresh();

        $this->assertNull($model->role);

        $event = new DealerRegisteredEvent($model);
        $listener = resolve(DealerRegisteredSetRoleListener::class);
        $listener->handle($event);

        $model->refresh();

        $this->assertEquals($model->role->guard_name, Dealer::GUARD);
        $this->assertEquals($model->role->name, config('permission.roles.dealer'));
    }
}
