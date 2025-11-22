<?php

namespace Tests\Feature\Mutations\FrontOffice\Commercial\CommercialProjects;

use App\GraphQL\Mutations\FrontOffice\Commercial\CommercialProjects\CommercialProjectAdditionDeleteMutation;
use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\CommercialProjectAddition;
use App\Models\Warranty\WarrantyRegistration;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Commercial\ProjectAdditionBuilder;
use Tests\Builders\Commercial\ProjectBuilder;
use Tests\TestCase;

class CommercialProjectAdditionDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = CommercialProjectAdditionDeleteMutation::NAME;

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
        /** @var $addition CommercialProjectAddition */
        $addition = $this->projectAdditionBuilder->setProject($project)->create();

        $project->refresh();

        $this->assertNotNull($project->additions);

        $this->postGraphQL([
            'query' => $this->getQueryStr($addition->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => true
                ]
            ])
        ;

        $project->refresh();

        $this->assertNull($project->additions);
    }

    /** @test */
    public function fail_not_can_update(): void
    {
        $this->loginAsTechnicianWithRole();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->create();
        WarrantyRegistration::factory()->create(['commercial_project_id' => $project->id]);
        /** @var $addition CommercialProjectAddition */
        $addition = $this->projectAdditionBuilder->setProject($project)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($addition->id)
        ])
            ->assertJson([
                'errors' => [
                    ["message" => __('exceptions.commercial.addition.can\'t delete')]
                ]
            ])
        ;
    }


    /** @test */
    public function fail_wrong_commercial_project_addition(): void
    {
        $this->loginAsTechnicianWithRole();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr(1)
        ])
            ->assertJson([
                'errors' => [
                    ["message" => "validation"]
                ]
            ])
        ;
    }

    protected function getQueryStr($id): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                )
            }',
            self::MUTATION,
            $id
        );
    }
}


