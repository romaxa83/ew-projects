<?php

declare(strict_types=1);

namespace Tests\Feature\Mutations\BackOffice\Sliders;

use App\GraphQL\Mutations\BackOffice\Sliders\SliderDeleteMutation;
use App\Models\Sliders\Slider;
use App\Models\Sliders\SliderTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SliderDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = SliderDeleteMutation::NAME;

    public function test_do_success(): void
    {
        $this->loginAsSuperAdmin();

        $slider = Slider::factory()
            ->has(SliderTranslation::factory()->allLocales(), 'translations')
            ->create();

        $this->assertDatabaseCount(SliderTranslation::TABLE, 2);

        $this->mutation(['id' => $slider->id])
            ->assertOk();

        $this->assertModelMissing($slider);
        $this->assertDatabaseCount(SliderTranslation::TABLE, 0);
    }

    protected function mutation(array $args): TestResponse
    {
        $query = GraphQLQuery::mutation(
            self::MUTATION
        )
            ->args($args)
            ->select(
                [
                    'message'
                ]
            );

        return $this->postGraphQLBackOffice($query->make());
    }

    public function test_not_permitted_user_get_no_permission_error(): void
    {
        $this->loginAsAdmin();
        $slider = Slider::factory()->create();

        $this->assertServerError($this->mutation(['id' => $slider->id]), 'No permission');
    }

    public function test_guest_get_unauthorized_error(): void
    {
        $slider = Slider::factory()->create();

        $this->assertServerError($this->mutation(['id' => $slider->id]), 'Unauthorized');
    }
}
