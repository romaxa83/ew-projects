<?php

namespace Tests\Feature\Http\Api\OneC\Catalog\Features;

use App\Models\Catalog\Features\Feature;
use App\Models\Catalog\Features\FeatureTranslation;
use App\Models\Catalog\Features\Value;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class FeaturesControllerTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_list(): void
    {
        $this->loginAsModerator();

        $this->getFeature();

        $this->getJson(route('1c.features.index'))
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        $this->getFeatureStructure()
                    ]
                ]
            );
    }

    protected function getFeature(): Feature
    {
        return Feature::factory()
            ->has(FeatureTranslation::factory()->allLocales(), 'translations')
            ->has(
                Value::factory()
            )
            ->create();
    }

    protected function getFeatureStructure(): array
    {
        return [
            'id',
            'guid',
            'active',
            'display_in_web',
            'display_in_mobile',
            'translations' => [
                [
                    'id',
                    'title',
                    'description',
                    'language',
                ]
            ],
            'values' => [
                [
                    'id',
                    'active',
                    'title',
                ]
            ]
        ];
    }

    public function test_show(): void
    {
        $this->loginAsModerator();

        $feature = $this->getFeature();

        $this->getJson(route('1c.features.show', $feature->guid))
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => $this->getFeatureStructure()
                ]
            );
    }

    public function test_create(): void
    {
        $this->loginAsModerator();

        $this->postJson(
            route('1c.features.store'),
            array_merge(
                $this->data(),
                ['guid' => $this->faker->uuid]
            )
        )
            ->assertCreated();
    }

    protected function data(): array
    {
        return [
            'active' => true,
            'translations' => [
                [
                    'title' => 'en title',
                    'description' => 'en description',
                    'language' => 'en',
                ],
                [
                    'title' => 'es title',
                    'description' => 'es description',
                    'language' => 'es',
                ]
            ],
        ];
    }

    public function test_update(): void
    {
        $this->loginAsModerator();

        $feature = Feature::factory()->create();

        $this->putJson(route('1c.features.update', $feature->guid), $this->data())
            ->assertOk();
    }

    public function test_delete(): void
    {
        $this->loginAsModerator();

        $feature = Feature::factory()->create();

        $this->deleteJson(route('1c.features.destroy', $feature->guid))
            ->assertOk();

        $this->assertModelMissing($feature);
    }

    public function test_not_found(): void
    {
        $this->loginAsModerator();

        $this->getJson(route('1c.features.show', 0))
            ->assertNotFound();
    }

    public function test_update_guid(): void
    {
        $this->loginAsModerator();

        $feature = Feature::factory()->create(['guid' => null]);
        $guid = Uuid::uuid4();

        $this->assertDatabaseMissing(Feature::TABLE, ['guid' => $guid]);

        $this->postJson(
            route('1c.features.update.guid'),
            [
                'data' => [
                    [
                        'id' => $feature->id,
                        'guid' => $guid,
                    ]
                ]
            ]
        )->assertJsonStructure(
            [
                'data' => [
                    [
                        'id',
                        'guid',
                    ]
                ],
            ]
        );

        $this->assertDatabaseHas(Feature::TABLE, ['guid' => $guid]);
    }
}
