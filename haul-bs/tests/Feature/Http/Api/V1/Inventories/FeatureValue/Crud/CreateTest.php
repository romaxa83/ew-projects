<?php

namespace Feature\Http\Api\V1\Inventories\FeatureValue\Crud;

use App\Events\Events\Inventories\FeatureValues\CreateFeatureValueEvent;
use App\Events\Listeners\Inventories\FeatureValues\SyncEComCreateFeatureValueListener;
use App\Models\Inventories\Features\Value;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Inventories\FeatureBuilder;
use Tests\Builders\Inventories\FeatureValueBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    protected FeatureBuilder $featureBuilder;
    protected FeatureValueBuilder $featureValueBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        $this->featureBuilder = resolve(FeatureBuilder::class);
        $this->featureValueBuilder = resolve(FeatureValueBuilder::class);

        parent::setUp();

        $feature = $this->featureBuilder->create();

        $this->data = [
            'name' => 'Brand',
            'slug' => 'brand',
            'feature_id' => $feature->id,
            'position' => 10,
        ];
    }

    /** @test */
    public function success_create()
    {
        Event::fake([CreateFeatureValueEvent::class]);

        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $id = $this->postJson(route('api.v1.inventories.feature.value.store'), $data)
            ->assertJson([
                'data' => [
                    'name' => data_get($data, 'name'),
                    'slug' => data_get($data, 'slug'),
                    'feature_id' => data_get($data, 'feature_id'),
                    'position' => data_get($data, 'position'),
                ],
            ])
            ->json('data.id')
        ;

        Event::assertDispatched(fn (CreateFeatureValueEvent $event) =>
            $event->getModel()->id === (int)$id
        );
        Event::assertListening(
            CreateFeatureValueEvent::class,
            SyncEComCreateFeatureValueListener::class
        );
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        $data['name'] = null;

        $res = $this->postJson(route('api.v1.inventories.feature.value.store'), $data, [
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

        $this->postJson(route('api.v1.inventories.feature.value.store'), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;

        $this->assertFalse(Value::query()->where('slug', $data['slug'])->exists());
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        $this->featureValueBuilder->slug('slug')->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.inventories.feature.value.store'), $data)
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
            ['feature_id', null, 'validation.required', ['attribute' => 'validation.attributes.feature_id']],
            ['feature_id', '0', 'validation.exists', ['attribute' => 'validation.attributes.feature_id']],
        ];
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.inventories.feature.value.store'), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        $res = $this->postJson(route('api.v1.inventories.feature.value.store'), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
