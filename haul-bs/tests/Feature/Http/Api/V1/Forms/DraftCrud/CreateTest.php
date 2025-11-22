<?php

namespace Feature\Http\Api\V1\Forms\DraftCrud;

use App\Models\Forms\Draft;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Forms\DraftBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    protected DraftBuilder $draftBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->draftBuilder = resolve(DraftBuilder::class);
    }

    /** @test */
    public function success_create_new()
    {
        $user = $this->loginUserAsSuperAdmin();

        $path = 'new_path_1';

        $data = [
            'field1' => 'text1',
            'field2' => 'text2',
        ];

        $this->assertFalse(Draft::query()->where('path', $path)->where('user_id', $user->id)->exists());

        $this->postJson(route('api.v1.forms.drafts.store', ['path' => $path]), $data)
            ->assertJson([
                'data' => [
                    'message' => "Success",
                ],
            ])
        ;

        $model = Draft::query()->where('path', $path)->where('user_id', $user->id)->first();

        $this->assertEquals($model->body, $data);
    }

    /** @test */
    public function success_update()
    {
        $user = $this->loginUserAsSuperAdmin();

        $path = 'new_path_1';

        $model = $this->draftBuilder->user($user)->path($path)->body([
            'field1' => 'text1',
            'field2' => 'text2',
        ])->create();

        $data = [
            'field1' => 'text1 update',
            'field3' => 'text3',
        ];

        $this->postJson(route('api.v1.forms.drafts.store', ['path' => $path]), $data)
            ->assertJson([
                'data' => [
                    'message' => "Success",
                ],
            ])
        ;

        $model->refresh();

        $this->assertEquals($model->body, $data);
    }

    /** @test */
    public function not_auth()
    {

        $path = 'new_path_1';

        $data = [
            'field1' => 'text1',
            'field2' => 'text2',
        ];

        $res = $this->postJson(route('api.v1.forms.drafts.store', ['path' => $path]), $data)
        ;

        self::assertUnauthenticatedMessage($res);
    }
}
