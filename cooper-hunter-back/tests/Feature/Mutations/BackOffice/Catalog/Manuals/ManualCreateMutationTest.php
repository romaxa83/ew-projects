<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Manuals;

use App\GraphQL\Mutations\BackOffice\Catalog\Manuals\ManualCreateMutation;
use App\Models\Catalog\Manuals\ManualGroup;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class ManualCreateMutationTest extends BaseManualMutationTest
{
    public const MUTATION = ManualCreateMutation::NAME;

    /**
     * @throws FileNotFoundException
     */
    public function test_create_manual(): void
    {
        $this->fakeMediaStorage();

        $this->loginAsSuperAdmin();

        $manualGroup = ManualGroup::factory()->create();

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($pdf: Upload!) {%s (manuals: [{manual_group_id: %s, pdf: $pdf}]) {id} }"}',
                self::MUTATION,
                $manualGroup->id,
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
                            [
                                'id' => $manualGroup->manuals->first()->id
                            ]
                        ]
                    ]
                ]
            );
        self::assertCount(1, $manualGroup->manuals);
    }
}
