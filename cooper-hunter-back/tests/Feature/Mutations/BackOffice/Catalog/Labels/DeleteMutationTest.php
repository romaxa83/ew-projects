<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Labels;

use App\GraphQL\Mutations\BackOffice\Catalog\Labels\DeleteMutation;
use App\Models\Catalog\Labels\Label;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Catalog\LabelBuilder;
use Tests\TestCase;

class DeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = DeleteMutation::NAME;

    protected LabelBuilder $labelBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->labelBuilder = resolve(LabelBuilder::class);
    }

    /** @test */
    public function success_delete(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Label */
        $model = $this->labelBuilder->create();
        $id = $model->id;

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => true,
                ]
            ]);

        $this->assertNull(Label::find($id));
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
            $id,
        );
    }
}
