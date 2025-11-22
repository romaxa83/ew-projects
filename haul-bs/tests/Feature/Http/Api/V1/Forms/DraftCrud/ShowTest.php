<?php

namespace Tests\Feature\Http\Api\V1\Forms\DraftCrud;

use App\Models\Forms\Draft;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Forms\DraftBuilder;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use DatabaseTransactions;

    protected DraftBuilder $draftBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->draftBuilder = resolve(DraftBuilder::class);
    }

    /** @test */
    public function success_show()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $m Draft */
        $m = $this->draftBuilder->user($user)->create();
        $this->draftBuilder->create();
        $this->draftBuilder->create();

        $this->getJson(route('api.v1.forms.drafts.show', ['path' => $m->path]))
            ->assertJson([
                'data' => $m->body,
            ])
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->getJson(route('api.v1.forms.drafts.show', ['path' => 'tttt']));

        self::assertErrorMsg($res, "Model not found", Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $m Draft */
        $m = $this->draftBuilder->create();

        $res = $this->getJson(route('api.v1.forms.drafts.show', ['path' => $m->path]));

        self::assertUnauthenticatedMessage($res);
    }
}
