<?php

namespace Tests\Unit\Events\Listeners\Settings;

use App\Events\Events\Settings\RequestToEcom;
use App\Events\Listeners\Settings\RequestToEcomListener;
use App\Repositories\Settings\SettingRepository;
use App\Services\Requests\ECom\Commands\Settings\SettingsUpdateCommand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;
use Tests\Traits\SettingsData;

class RequestToEcomListenerTest extends TestCase
{
    use DatabaseTransactions;
    use SettingsData;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_send_as_change_status()
    {
        $settingData = $this->setSettings();

        // Create a mock
        $mockUpdate = Mockery::mock(SettingsUpdateCommand::class);

        // Define expectation
        $mockUpdate->shouldReceive('exec')->once();

        $repo = resolve(SettingRepository::class);

        $event = new RequestToEcom($repo->getInfoForEcomm());
        $listener = new RequestToEcomListener($mockUpdate);

        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }
}
