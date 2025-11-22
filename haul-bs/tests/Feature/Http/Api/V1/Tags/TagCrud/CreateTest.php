<?php

namespace Tests\Feature\Http\Api\V1\Tags\TagCrud;

use App\Enums\Tags\TagType;
use App\Models\Tags\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Tags\TagBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    protected TagBuilder $tagBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->tagBuilder = resolve(TagBuilder::class);

        $this->data = [
            'name' => 'empty',
            'color' => '#2F54EB',
            'type' => TagType::CUSTOMER,
        ];
    }

    /** @test */
    public function success_create()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $this->postJson(route('api.v1.tags.store'), $data)
            ->assertJson([
                'data' => [
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

        Tag::factory()->count(10)->create();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.tags.store'), $data);

        self::assertErrorMsg($res, __('exceptions.tag.more_limit'), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        $data['name'] = null;

        $res = $this->postJson(route('api.v1.tags.store'), $data, [
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

        $this->postJson(route('api.v1.tags.store'), $data, [
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

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.tags.store'), $data)
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

        $res = $this->postJson(route('api.v1.tags.store'), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        $res = $this->postJson(route('api.v1.tags.store'), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
