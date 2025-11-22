<?php

namespace Tests\Feature\Mutations\FrontOffice\Commercial\CommercialProjects;

use App\GraphQL\Mutations\FrontOffice\Commercial\CommercialProjects\CommercialProjectAdditionCreateMutation;
use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\CommercialProjectAddition;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Commercial\ProjectAdditionBuilder;
use Tests\Builders\Commercial\ProjectBuilder;
use Tests\TestCase;

class CommercialProjectAdditionCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = CommercialProjectAdditionCreateMutation::NAME;

    protected ProjectBuilder $projectBuilder;
    protected ProjectAdditionBuilder $projectAdditionBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->projectBuilder = resolve(ProjectBuilder::class);
        $this->projectAdditionBuilder = resolve(ProjectAdditionBuilder::class);
    }

    /** @test */
    public function success_create(): void
    {
        $this->loginAsTechnicianWithRole();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->create();

        $data = $this->data();
        $data['commercial_project_id'] = $project->id;

        $this->assertNull($project->additions);

        $id = $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'purchase_place' => data_get($data, 'purchase_place'),
                        'installer_license_number' => data_get($data, 'installer_license_number'),
                        'installation_date' => data_get($data, 'installation_date'),
                        'purchase_date' => data_get($data, 'purchase_date'),
                    ],
                ]
            ])
            ->json('data.' . self::MUTATION . '.id')
        ;

        $project->refresh();

        $this->assertEquals($project->additions->id, $id);
    }

    /** @test */
    public function fail_create_if_exist(): void
    {
        $this->loginAsTechnicianWithRole();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->create();
        /** @var $addition CommercialProjectAddition */
        $addition = $this->projectAdditionBuilder->setProject($project)->create();

        $data = $this->data();
        $data['commercial_project_id'] = $project->id;

        $this->assertNotNull($project->additions);

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    ["message" => __('exceptions.commercial.addition.exist')]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_wrong_commercial_project(): void
    {
        $this->loginAsTechnicianWithRole();

        $data = $this->data();
        $data['commercial_project_id'] = 1;

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    ["message" => "validation"]
                ]
            ])
        ;
    }

    public function data(): array
    {
        return [
            'purchase_place' => 'some place',
            'installer_license_number' => '0001',
            'purchase_date' => now()->format('Y-m-d'),
            'installation_date' => now()->format('Y-m-d'),
        ];
    }

    protected function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    input: {
                        commercial_project_id: %s
                        purchase_place: "%s"
                        installer_license_number: "%s"
                        installation_date: "%s"
                        purchase_date: "%s"
                    },
                ) {
                    id
                    purchase_place
                    installer_license_number
                    installation_date
                    purchase_date
                }
            }',
            self::MUTATION,
            data_get($data, 'commercial_project_id'),
            data_get($data, 'purchase_place'),
            data_get($data, 'installer_license_number'),
            data_get($data, 'installation_date'),
            data_get($data, 'purchase_date'),
        );
    }
}
