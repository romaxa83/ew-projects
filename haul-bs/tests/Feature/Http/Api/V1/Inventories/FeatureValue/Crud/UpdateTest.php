<?php

namespace Feature\Http\Api\V1\Inventories\FeatureValue\Crud;

use App\Events\Events\Inventories\FeatureValues\UpdateFeatureValueEvent;
use App\Events\Listeners\Inventories\FeatureValues\SyncEComUpdateFeatureValueListener;
use App\Models\Inventories\Features\Value;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Inventories\FeatureBuilder;
use Tests\Builders\Inventories\FeatureValueBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected FeatureBuilder $featureBuilder;
    protected FeatureValueBuilder $featureValueBuilder;

    protected $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->featureBuilder = resolve(FeatureBuilder::class);
        $this->featureValueBuilder = resolve(FeatureValueBuilder::class);

        $this->data = [
            'name' => 'Feature',
            'slug' => 'feature',
            'position' => 100,
        ];
    }

    /** @test */
    public function success_update()
    {
        Event::fake([UpdateFeatureValueEvent::class]);

        $this->loginUserAsSuperAdmin();

        /** @var $model Value */
        $model = $this->featureValueBuilder->create();

        $data = $this->data;

        $this->assertNotEquals($model->name, data_get($data, 'name'));
        $this->assertNotEquals($model->slug, data_get($data, 'slug'));
        $this->assertNotEquals($model->position, data_get($data, 'position'));

        $this->postJson(route('api.v1.inventories.feature.value.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'name' => data_get($data, 'name'),
                    'slug' => data_get($data, 'slug'),
                    'position' => data_get($data, 'position'),
                    'feature_id' => $model->feature_id,
                ],
            ])
        ;

        Event::assertDispatched(fn (UpdateFeatureValueEvent $event) =>
            $event->getModel()->id === $model->id
        );
        Event::assertListening(
            UpdateFeatureValueEvent::class,
            SyncEComUpdateFeatureValueListener::class
        );
    }

    /** @test */
    public function success_update_nothing_change()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Value */
        $model = $this->featureValueBuilder->create();

        $data = $this->data;
        $data['name'] = $model->name;
        $data['slug'] = $model->slug;
        $data['position'] = $model->position;

        $this->postJson(route('api.v1.inventories.feature.value.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'name' => data_get($data, 'name'),
                    'slug' => data_get($data, 'slug'),
                    'position' => data_get($data, 'position'),
                ],
            ])
        ;
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Value */
        $model = $this->featureValueBuilder->create();

        $data['name'] = null;

        $res = $this->postJson(route('api.v1.inventories.feature.value.update', ['id' => $model->id]), $data, [
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
    public function field_success_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Value */
        $model = $this->featureValueBuilder->create();

        $data = $this->data;

        $this->postJson(route('api.v1.inventories.feature.value.update', ['id' => $model->id]), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;

        $model->refresh();

        $this->assertNotEquals($model->name, data_get($data, 'name'));
        $this->assertNotEquals($model->slug, data_get($data, 'name'));
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Value */
        $model = $this->featureValueBuilder->create();

        $this->featureValueBuilder->slug('slug')->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.inventories.feature.value.update', ['id' => $model->id]), $data)
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
    public function not_found()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.inventories.feature.value.update', ['id' => 999999]), $data);

        self::assertErrorMsg($res, __("exceptions.inventories.features.value.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        /** @var $model Value */
        $model = $this->featureValueBuilder->create();

        $res = $this->postJson(route('api.v1.inventories.feature.value.update', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        /** @var $model Value */
        $model = $this->featureValueBuilder->create();

        $res = $this->postJson(route('api.v1.inventories.feature.value.update', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
