<?php

namespace Tests\Feature\Mutations\BackOffice\Technicians;

use App\GraphQL\Mutations\BackOffice\Technicians\TechnicianToggleCertifiedMutation;
use App\Models\Technicians\Technician;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TechnicianToggleCertifiedMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = TechnicianToggleCertifiedMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function test_it_toggle_certification_status(): void
    {
        $this->loginAsSuperAdmin();

        $technician = Technician::factory()
            ->create();

        $query = sprintf(
            'mutation { %s (
                    id: %d
                )
            }',
            self::MUTATION,
            $technician->id
        );

        self::assertFalse($technician->is_certified);

        $this->postGraphQLBackOffice(compact('query'))
            ->assertOk()
            ->assertJsonPath('data.'.self::MUTATION, true);

        $technician->refresh();

        self::assertTrue($technician->is_certified);
    }
}
