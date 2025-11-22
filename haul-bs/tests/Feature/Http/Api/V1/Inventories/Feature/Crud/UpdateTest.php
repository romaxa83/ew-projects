<?php

namespace Feature\Http\Api\V1\Inventories\Feature\Crud;

use App\Events\Events\Inventories\Features\UpdateFeatureEvent;
use App\Events\Listeners\Inventories\Features\SyncEComUpdateFeatureListener;
use App\Models\Inventories\Features\Feature;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Inventories\FeatureBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected FeatureBuilder $featureBuilder;

    protected $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->featureBuilder = resolve(FeatureBuilder::class);

        $this->data = [
            'name' => 'Feature',
            'slug' => 'feature',
            'multiple' => false,
            'active' => false,
            'position' => 100,
        ];
    }

    /** @test */
    public function success_update()
    {
        Event::fake([UpdateFeatureEvent::class]);

        $this->loginUserAsSuperAdmin();

        /** @var $model Feature */
        $model = $this->featureBuilder->create();

        $data = $this->data;

        $this->assertNotEquals($model->name, data_get($data, 'name'));
        $this->assertNotEquals($model->slug, data_get($data, 'slug'));
        $this->assertNotEquals($model->position, data_get($data, 'position'));
        $this->assertNotEquals($model->multiple, data_get($data, 'multiple'));
        $this->assertNotEquals($model->active, data_get($data, 'active'));

        $this->postJson(route('api.v1.inventories.feature.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'name' => data_get($data, 'name'),
                    'slug' => data_get($data, 'slug'),
                    'position' => data_get($data, 'position'),
                    'multiple' => data_get($data, 'multiple'),
                    'active' => data_get($data, 'active'),
                ],
            ])
        ;

        Event::assertDispatched(fn (UpdateFeatureEvent $event) =>
            $event->getModel()->id === $model->id
        );
        Event::assertListening(
            UpdateFeatureEvent::class,
            SyncEComUpdateFeatureListener::class
        );
    }

    /** @test */
    public function success_update_nothing_change()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Feature */
        $model = $this->featureBuilder->create();

        $data = $this->data;
        $data['name'] = $model->name;
        $data['slug'] = $model->slug;
        $data['position'] = $model->position;
        $data['multiple'] = $model->multiple;
        $data['active'] = $model->active;

        $this->postJson(route('api.v1.inventories.feature.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'name' => data_get($data, 'name'),
                    'slug' => data_get($data, 'slug'),
                    'position' => data_get($data, 'position'),
                    'multiple' => data_get($data, 'multiple'),
                ],
            ])
        ;
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Feature */
        $model = $this->featureBuilder->create();

        $data['name'] = null;

        $res = $this->postJson(route('api.v1.inventories.feature.update', ['id' => $model->id]), $data, [
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

        /** @var $model Feature */
        $model = $this->featureBuilder->create();

        $data = $this->data;

        $this->postJson(route('api.v1.inventories.feature.update', ['id' => $model->id]), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;

        $model->refresh();

        $this->assertNotEquals($model->name, data_get($data, 'name'));
        $this->assertNotEquals($model->slug, data_get($data, 'slug'));
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Feature */
        $model = $this->featureBuilder->create();

        $this->featureBuilder->slug('slug')->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.inventories.feature.update', ['id' => $model->id]), $data)
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

        $res = $this->postJson(route('api.v1.inventories.feature.update', ['id' => 0]), $data);

        self::assertErrorMsg($res, __("exceptions.inventories.features.feature.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        /** @var $model Feature */
        $model = $this->featureBuilder->create();

        $res = $this->postJson(route('api.v1.inventories.feature.update', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        /** @var $model Feature */
        $model = $this->featureBuilder->create();

        $res = $this->postJson(route('api.v1.inventories.feature.update', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
