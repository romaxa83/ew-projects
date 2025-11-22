<?php

namespace Tests\Feature\Http\Api\V1\Tags\TagCrud;

use App\Models\Tags\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Tags\TagBuilder;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use DatabaseTransactions;

    protected TagBuilder $tagBuilder;
    protected CustomerBuilder $customerBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->tagBuilder = resolve(TagBuilder::class);
        $this->customerBuilder = resolve(CustomerBuilder::class);
    }

    /** @test */
    public function success_show()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Tag */
        $model = $this->tagBuilder->create();

        $this->customerBuilder->tags($model)->create();

        $this->getJson(route('api.v1.tags.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'name' => $model->name,
                    'color' => $model->color,
                    'type' => $model->type->value,
                    'hasRelatedEntities' => true,
                ],
            ])
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $this->tagBuilder->create();

        $res = $this->getJson(route('api.v1.tags.show', ['id' => 0]))
        ;

        self::assertErrorMsg($res, __("exceptions.tag.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Tag */
        $model = $this->tagBuilder->create();

        $res = $this->getJson(route('api.v1.tags.show', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Tag */
        $model = $this->tagBuilder->create();

        $res = $this->getJson(route('api.v1.tags.show', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
