<?php

namespace Tests\Feature\Mutations\FrontOffice\Commercial\CommercialProjects;

use App\Events\Commercial\SendCommercialProjectToOnec;
use App\GraphQL\Mutations\FrontOffice\Commercial\CommercialProjects\CommercialProjectUpdateMutation;
use App\Listeners\Commercial\SendCommercialProjectToOnecListener;
use App\Models\Commercial\CommercialProject;
use App\Models\Technicians\Technician;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CommercialProjectUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = CommercialProjectUpdateMutation::NAME;

    public function test_update_name(): void
    {
        Event::fake([SendCommercialProjectToOnec::class]);

        $technician = $this->loginAsTechnicianWithRole();

        $project = CommercialProject::factory()
            ->forMember($technician)
            ->create();

        $query = $this->getQuery($project, $name = 'new project name');

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'id' => $project->id,
                            'name' => $name,
                        ]
                    ]
                ]
            );

        Event::assertDispatched(function (SendCommercialProjectToOnec $event) use ($project) {
            return $event->getCommercialProject()->id === $project->id;
        });
        Event::assertListening(SendCommercialProjectToOnec::class, SendCommercialProjectToOnecListener::class);
    }

    public function getQuery(CommercialProject $project, string $name): array
    {
        return GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'id' => $project->id,
                    'name' => $name,
                ]
            )
            ->select(
                [
                    'id',
                    'name',
                ]
            )
            ->make();
    }

    public function test_cannot_update_foreign_project(): void
    {
        $this->loginAsTechnicianWithRole();

        $project = CommercialProject::factory()
            ->forMember(Technician::factory())
            ->create();

        $originalName = $project->name;

        $query = $this->getQuery($project, $name = 'new project name');

        $this->assertServerError($this->postGraphQL($query), 'validation');

        $this->assertDatabaseMissing(
            CommercialProject::TABLE,
            compact('name')
        );

        $this->assertDatabaseHas(
            CommercialProject::TABLE,
            [
                'name' => $originalName,
            ]
        );

        self::assertEquals($originalName, $project->fresh()->name);
    }

    /** @test */
    public function fail_technic_not_have_certificate(): void
    {
        $technician = $this->loginAsTechnicianWithRole(
            Technician::factory()->certified()->verified()
                ->create(['is_commercial_certification' => false])
        );

        $project = CommercialProject::factory()
            ->forMember($technician)
            ->create();

        $query = $this->getQuery($project, $name = 'new project name');

        $this->postGraphQL($query)
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.commercial.technician does\'n have a commercial certificate")]
                ]
            ])
        ;
    }
}
