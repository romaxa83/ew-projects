<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Manuals;

use App\GraphQL\Mutations\BackOffice\Catalog\Manuals\ManualUpdateMutation;
use App\Models\Catalog\Manuals\Manual;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class ManualUpdateMutationTest extends BaseManualMutationTest
{
    public const MUTATION = ManualUpdateMutation::NAME;

    /**
     * @throws FileNotFoundException
     */
    public function test_update_manual_pdf(): void
    {
        $this->fakeMediaStorage();

        $this->loginAsSuperAdmin();

        $manual = Manual::factory()->create();
        $manualGroup = $manual->group;

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($pdf: Upload!) {%s (manual: {manual_group_id: %s, manual_id: %s, pdf: $pdf}) {id} }"}',
                self::MUTATION,
                $manualGroup->id,
                $manual->id,
            ),
            'map' => '{ "pdf": ["variables.pdf"] }',
            'pdf' => $this->getSamplePdf(),
        ];

        $this->postGraphQLBackOfficeUpload($attributes)
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'id' => $manual->id
                        ]
                    ]
                ]
            );

        self::assertEquals(1, $manualGroup->manuals()->count());
    }
}
