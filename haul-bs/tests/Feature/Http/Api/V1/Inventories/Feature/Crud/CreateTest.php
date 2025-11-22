<?php

namespace Feature\Http\Api\V1\Inventories\Feature\Crud;

use App\Events\Events\Inventories\Features\CreateFeatureEvent;
use App\Events\Listeners\Inventories\Features\SyncEComCreateFeatureListener;
use App\Models\Inventories\Features\Feature;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Inventories\FeatureBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    protected FeatureBuilder $featureBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        $this->featureBuilder = resolve(FeatureBuilder::class);

        parent::setUp();

        $this->data = [
            'name' => 'Brand',
            'slug' => 'brand',
            'multiple' => true,
            'active' => true,
            'position' => 10,
        ];
    }

    /** @test */
    public function success_create()
    {
        Event::fake([CreateFeatureEvent::class]);

        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $id = $this->postJson(route('api.v1.inventories.feature.store'), $data)
            ->assertJson([
                'data' => [
                    'name' => data_get($data, 'name'),
                    'slug' => data_get($data, 'slug'),
                    'multiple' => data_get($data, 'multiple'),
                    'active' => data_get($data, 'active'),
                    'position' => data_get($data, 'position'),
                    'values' => [],
                ],
            ])
            ->assertJsonCount(0, 'data.values')
            ->json('data.id')
        ;

        Event::assertDispatched(fn (CreateFeatureEvent $event) =>
            $event->getModel()->id === (int)$id
        );
        Event::assertListening(
            CreateFeatureEvent::class,
            SyncEComCreateFeatureListener::class
        );
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        $data['name'] = null;

        $res = $this->postJson(route('api.v1.inventories.feature.store'), $data, [
            'Validate-Only' => true
        ])
        ;

        $this->assertValidationMsgWithValidateOnly(
            $res,
            __('validation.required', ['attribute' => __('validation.attributes.name')]),
            'name'
        );
    }

    /** @test */
    public function field_success_role_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $this->postJson(route('api.v1.inventories.feature.store'), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;

        $this->assertFalse(Feature::query()->where('slug', $data['slug'])->exists());
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        $this->featureBuilder->slug('slug')->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.inventories.feature.store'), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, $field, $attributes);
    }

    public static function validate(): array
    {
        return [
            ['name', null, 'validation.required', ['attribute' => 'validation.attributes.name']],
            ['slug', null, 'validation.required', ['attribute' => 'validation.attributes.slug']],
            ['slug', 1111, 'validation.string', ['attribute' => 'validation.attributes.slug']],
            ['slug', 'slug', 'validation.unique', ['attribute' => 'validation.attributes.slug']],
        ];
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.inventories.feature.store'), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        $res = $this->postJson(route('api.v1.inventories.feature.store'), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
