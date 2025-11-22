<?php

namespace Tests\Feature\Queries\FrontOffice\Commercial;

use App\GraphQL\Queries\FrontOffice\Commercial\CheckRDPCredentialsQuery;
use App\Models\Commercial\RDPAccount;
use App\Models\Technicians\Technician;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Database\Factories\Commercial\RDPAccountFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CheckRDPCredentialsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = CheckRDPCredentialsQuery::NAME;

    public function test_check_valid_credentials(): void
    {
        $technician = $this->loginAsTechnicianWithRole();

        $this->createRDPCredentials($technician);

        $this->checkResult(true);
    }

    public function createRDPCredentials(Technician $technician, bool $expired = false): void
    {
        RDPAccount::factory()
            ->forTechnician($technician)
            ->when($expired, static fn(RDPAccountFactory $factory) => $factory->expired())
            ->create();
    }

    public function checkResult(bool $expect): void
    {
        $this->postGraphQL(
            GraphQLQuery::query(self::QUERY)->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => $expect
                    ],
                ],
            );
    }

    public function test_check_invalid_credentials(): void
    {
        $technician = $this->loginAsTechnicianWithRole();

        $this->createRDPCredentials($technician, true);

        $this->checkResult(false);
    }
}