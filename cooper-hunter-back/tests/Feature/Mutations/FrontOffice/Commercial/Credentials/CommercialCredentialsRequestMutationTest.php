<?php

namespace Tests\Feature\Mutations\FrontOffice\Commercial\Credentials;

use App\GraphQL\Mutations\FrontOffice\Commercial\Credentials\CommercialCredentialsRequestMutation;
use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\CredentialsRequest;
use App\Models\Commercial\RDPAccount;
use Core\Enums\Messages\MessageTypeEnum;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CommercialCredentialsRequestMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = CommercialCredentialsRequestMutation::NAME;

    public function test_request_credentials(): void
    {
        $technician = $this->loginAsTechnicianWithRole();

        $project = CommercialProject::factory()
            ->forTechnician($technician)
            ->withCode()
            ->create();

        $query = $this->getQuery($project);

        $this->postGraphQL($query)
            ->assertJsonPath(
                'data.' . self::MUTATION . '.type',
                MessageTypeEnum::SUCCESS
            );
    }

    public function getQuery(CommercialProject $project): array
    {
        return GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'input' => [
                        'company_name' => $this->faker->company,
                        'company_phone' => $this->faker->e164PhoneNumber,
                        'company_email' => $this->faker->companyEmail,
                        'project_id' => $project->id,
                        'comment' => $this->faker->text,
                    ],
                ]
            )
            ->select(
                [
                    'type',
                    'message',
                ]
            )
            ->make();
    }

    public function test_credentials_already_exists(): void
    {
        $technician = $this->loginAsTechnicianWithRole();

        RDPAccount::factory()
            ->forTechnician($technician)
            ->create();

        $project = CommercialProject::factory()
            ->withCode()
            ->forTechnician($technician)
            ->create();

        $query = $this->getQuery($project);

        $this->postGraphQL($query)
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'type' => MessageTypeEnum::WARNING,
                            'message' => __('Credentials already exists'),
                        ],
                    ],
                ]
            );
    }

    public function test_pending_request_exists(): void
    {
        $technician = $this->loginAsTechnicianWithRole();

        $project = CommercialProject::factory()
            ->forTechnician($technician)
            ->withCode()
            ->create();

        CredentialsRequest::factory()
            ->forTechnician($technician)
            ->for($project, 'commercialProject')
            ->create();

        $query = $this->getQuery($project);

        $this->postGraphQL($query)
            ->assertJsonPath(
                'data.' . self::MUTATION . '.type',
                MessageTypeEnum::WARNING
            );
    }
}