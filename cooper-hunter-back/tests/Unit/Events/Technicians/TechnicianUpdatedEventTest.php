<?php

namespace Tests\Unit\Events\Technicians;

use App\Models\Technicians\Technician;
use App\Notifications\Members\MemberEmailVerification;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TechnicianUpdatedEventTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /**
     * @throws Exception
     */
    public function test_it_send_re_verified_email(): void
    {
        Notification::fake();

        $technician = Technician::factory()
            ->certified()
            ->verified()
            ->create();

        $technician->email = $this->faker->email;
        $technician->save();

        Notification::assertSentTo(new AnonymousNotifiable(), MemberEmailVerification::class);

        $this->assertDatabaseHas(
            Technician::class,
            [
                'id' => $technician->id,
                'email_verified_at' => null
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function test_it_send_re_moderated_certs(): void
    {
        Notification::fake();

        $technician = Technician::factory()
            ->certified()
            ->verified()
            ->create();

        $technician->hvac_license = $this->faker->lexify;
        $technician->epa_license = $this->faker->lexify;

        $technician->save();

        $this->assertDatabaseHas(
            Technician::class,
            [
                'id' => $technician->id,
                'is_verified' => false,
                'is_certified' => false,
            ]
        );
    }
}
