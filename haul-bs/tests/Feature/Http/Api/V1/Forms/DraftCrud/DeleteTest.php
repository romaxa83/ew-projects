<?php

namespace Feature\Http\Api\V1\Forms\DraftCrud;

use App\Models\Forms\Draft;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Forms\DraftBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected DraftBuilder $draftBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->draftBuilder = resolve(DraftBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $m Draft */
        $m = $this->draftBuilder->user($user)->create();
        $id = $m->id;

        $this->deleteJson(route('api.v1.forms.drafts.delete', ['path' => $m->path]))
            ->assertNoContent()
        ;

        $this->assertFalse(Draft::query()->where('id', $id)->exists());
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->deleteJson(route('api.v1.forms.drafts.delete', ['path' => 'tttt']));

        self::assertErrorMsg($res, "Model not found", Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $m Draft */
        $m = $this->draftBuilder->create();

        $res = $this->deleteJson(route('api.v1.forms.drafts.delete', ['path' => $m->path]));

        self::assertUnauthenticatedMessage($res);
    }
}
