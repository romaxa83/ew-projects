<?php

namespace Tests\Feature\Http\Api\V1\Tags\TagCrud;

use App\Enums\Tags\TagType;
use App\Models\Tags\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Tags\TagBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected TagBuilder $tagBuilder;
    protected CustomerBuilder $customerBuilder;

    protected $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->tagBuilder = resolve(TagBuilder::class);
        $this->customerBuilder = resolve(CustomerBuilder::class);

        $this->data = [
            'name' => 'empty',
            'color' => '#2F54EB',
            'type' => TagType::TRUCKS_AND_TRAILER,
        ];
    }

    /** @test */
    public function success_update()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Tag */
        $model = $this->tagBuilder->create();

        $this->customerBuilder->tags($model)->create();

        $data = $this->data;

        $this->assertNotEquals($model->name, data_get($data, 'name'));
        $this->assertNotEquals($model->color, data_get($data, 'color'));
        $this->assertNotEquals($model->type->value, data_get($data, 'type'));

        $this->putJson(route('api.v1.tags.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'name' => data_get($data, 'name'),
                    'color' => data_get($data, 'color'),
                    'type' => data_get($data, 'type'),
                    'hasRelatedEntities' => false,
                ],
            ])
        ;
    }

    /** @test */
    public function fail_more_limit()
    {
        $this->loginUserAsSuperAdmin();

        Tag::factory()->count(10)->create(['type' => TagType::CUSTOMER]);

        /** @var $model Tag */
        $model = $this->tagBuilder->type(TagType::TRUCKS_AND_TRAILER())->create();

        $data = $this->data;
        $data['type'] = TagType::CUSTOMER;

        $res = $this->putJson(route('api.v1.tags.update', ['id' => $model->id]), $data);

        self::assertErrorMsg($res, __('exceptions.tag.more_limit'), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Tag */
        $model = $this->tagBuilder->create();

        $data['name'] = null;

        $res = $this->putJson(route('api.v1.tags.update', ['id' => $model->id]), $data, [
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

        /** @var $model Tag */
        $model = $this->tagBuilder->create();

        $data = $this->data;

        $this->putJson(route('api.v1.users.update', ['id' => $model->id]), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Tag */
        $model = $this->tagBuilder->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->putJson(route('api.v1.tags.update', ['id' => $model->id]), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, $field, $attributes);
    }

    public static function validate(): array
    {
        return [
            ['name', null, 'validation.required', ['attribute' => 'validation.attributes.name']],
            ['color', null, 'validation.required', ['attribute' => 'validation.attributes.color']],
            ['type', null, 'validation.required', ['attribute' => 'validation.attributes.type']],
            ['type', 'wrong', 'validation.enum', ['attribute' => 'validation.attributes.type']],
        ];
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        /** @var $model Tag */
        $model = $this->tagBuilder->create();

        $res = $this->putJson(route('api.v1.tags.update', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        /** @var $model Tag */
        $model = $this->tagBuilder->create();

        $res = $this->putJson(route('api.v1.tags.update', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
