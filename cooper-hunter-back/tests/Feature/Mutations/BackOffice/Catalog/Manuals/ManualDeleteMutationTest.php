<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Manuals;

use App\GraphQL\Mutations\BackOffice\Catalog\Manuals\ManualDeleteMutation;
use App\Models\Catalog\Manuals\Manual;
use App\Models\Media\Media;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ManualDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = ManualDeleteMutation::NAME;

    public function test_delete_manual(): void
    {
        $this->loginAsSuperAdmin();

        $manual = Manual::factory()->create();

        $pdf = $manual->getFirstMedia(Manual::MEDIA_COLLECTION_NAME);

        $this->assertDatabaseHas(Manual::TABLE, [
            'id' => $manual->id
        ]);

        $this->assertDatabaseHas(Media::TABLE, [
            'id' => $pdf->id
        ]);

        $query = new GraphQLQuery(self::MUTATION, ['manual_id' => $manual->id]);

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertJsonPath('data.' . self::MUTATION, true);

        $this->assertDatabaseMissing(Manual::TABLE, [
            'id' => $manual->id
        ]);

        $this->assertDatabaseMissing(Media::TABLE, [
            'id' => $pdf->id
        ]);
    }
}
