<?php

namespace Tests\Feature\Mutations\BackOffice\Dealers;

use App\GraphQL\Mutations\BackOffice\Dealers\ToggleMainMutation;
use App\Models\Dealers\Dealer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\TestCase;

class ToggleMainMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = ToggleMainMutation::NAME;

    protected DealerBuilder $dealerBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->dealerBuilder = resolve(DealerBuilder::class);
    }

    /** @test */
    public function success_toggle(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->create();
        $dealer->refresh();
        $this->assertFalse($dealer->isMain());

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($dealer->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => true,
                ]
            ])
        ;

        $dealer->refresh();
        $this->assertTrue($dealer->isMain());
    }

    protected function getQueryStr($id): string
    {
        return sprintf(
            '
            mutation {
                %s (id: %s)
            }',
            self::MUTATION,
            $id
        );
    }
}
