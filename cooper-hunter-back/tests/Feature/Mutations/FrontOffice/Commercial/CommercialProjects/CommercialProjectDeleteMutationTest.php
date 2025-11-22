<?php

namespace Tests\Feature\Mutations\FrontOffice\Commercial\CommercialProjects;

use App\Events\Commercial\DeleteCommercialProjectToOnec;
use App\GraphQL\Mutations\FrontOffice\Commercial\CommercialProjects\CommercialProjectDeleteMutation;
use App\Listeners\Commercial\DeleteCommercialProjectToOnecListener;
use App\Models\Commercial\CommercialProject;
use App\Models\Technicians\Technician;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CommercialProjectDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = CommercialProjectDeleteMutation::NAME;

    public function test_delete(): void
    {
        Event::fake([DeleteCommercialProjectToOnec::class]);

        $technician = $this->loginAsTechnicianWithRole();

        $project = CommercialProject::factory()
            ->forMember($technician)
            ->create();

        $query = $this->getQuery($project);

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonPath('data.' . self::MUTATION, true);

        $this->assertModelMissing($project);

        Event::assertDispatched(function (DeleteCommercialProjectToOnec $event) use ($project) {
            return $event->getCommercialProject()->id === $project->id;
        });
        Event::assertListening(DeleteCommercialProjectToOnec::class,  DeleteCommercialProjectToOnecListener::class);
    }

    public function getQuery(CommercialProject $project): array
    {
        return GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'id' => $project->id,
                ]
            )
            ->make();
    }

    public function test_cannot_delete_foreign_project(): void
    {
        $this->loginAsTechnicianWithRole();

        $project = CommercialProject::factory()
            ->forMember(Technician::factory())
            ->create();

        $query = $this->getQuery($project);

        $this->assertServerError($this->postGraphQL($query), 'validation');

        $this->assertModelExists($project);
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

        $query = $this->getQuery($project);

        $this->postGraphQL($query)
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.commercial.technician does\'n have a commercial certificate")]
                ]
            ])
        ;
    }
}
