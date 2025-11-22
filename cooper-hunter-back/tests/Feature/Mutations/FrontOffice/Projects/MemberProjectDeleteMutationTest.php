<?php

namespace Tests\Feature\Mutations\FrontOffice\Projects;

use App\GraphQL\Mutations\FrontOffice\Projects\MemberProjectDeleteMutation;
use App\Models\Catalog\Products\Product;
use App\Models\Projects\Pivot\SystemUnitPivot;
use App\Models\Projects\Project;
use App\Models\Projects\System;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Models\ProjectCreateTrait;

class MemberProjectDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use ProjectCreateTrait;

    public const MUTATION = MemberProjectDeleteMutation::NAME;

    public function test_member_can_delete_project(): void
    {
        $project = $this->createProjectForMember(
            $this->loginAsUserWithRole()
        );

        /** @var System $system */
        $system = $project->systems->first();

        /** @var Product $unit */
        $unit = $system->units->first();

        $this->assertDatabaseHas(Project::TABLE, ['id' => $project->id]);
        $this->assertDatabaseHas(System::TABLE, ['id' => $system->id]);
        $this->assertDatabaseHas(SystemUnitPivot::TABLE, [
            'system_id' => $system->id,
            'product_id' => $unit->id,
        ]);

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'id' => $project->id,
            ]
        );

        $this->postGraphQL($query->getMutation())
            ->assertOk();

        $this->assertDatabaseMissing(Project::TABLE, ['id' => $project->id]);
        $this->assertDatabaseMissing(System::TABLE, ['id' => $system->id]);
        $this->assertDatabaseMissing(SystemUnitPivot::TABLE, [
            'system_id' => $system->id,
            'product_id' => $unit->id,
        ]);
    }
}
