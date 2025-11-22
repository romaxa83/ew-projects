<?php

namespace Tests\Feature\Mutations\BackOffice\Commercial\Commissioning\Protocol;

use App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\Protocol\DeleteMutation;
use App\Models\Commercial\Commissioning\Protocol;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Commercial\Commissioning\ProtocolBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = DeleteMutation::NAME;

    protected $protocolBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->protocolBuilder = resolve(ProtocolBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        $this->loginAsSuperAdmin();

        $model = $this->protocolBuilder->create();

        $id = $model->id;

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($id)
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => true
                    ]
                ]
            );

        $this->assertNull(Protocol::find($id));
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


