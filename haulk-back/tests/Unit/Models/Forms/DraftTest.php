<?php

namespace Tests\Unit\Models\Forms;

use App\Models\Forms\Draft;
use App\Models\Users\User;
use App\Services\Forms\DraftService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DraftTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    private User $user;

    private DraftService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = resolve(DraftService::class);
    }

    /**
     * @throws Exception
     */
    public function test_it_create_new_draft_end_rewrite_exists()
    {
        $this->test_it_create_draft_success();

        $path = 'someKey';
        $this->assertEquals(
            1,
            $this->user->drafts()->where('path', $path)->count()
        );

        $this->service->createOrUpdate(
            $this->user,
            'contact',
            [
                'field1' => 'text',
            ]
        );

        $this->assertEquals(
            1,
            $this->user->drafts()->where('path', $path)->count()
        );
    }

    /**
     * @throws Exception
     */
    public function test_it_create_draft_success()
    {
        $body = [
            'filed1' => $this->faker->sentence,
            'filed2' => $this->faker->sentence,
            'text' => $this->faker->text(1000),
        ];

        $path = 'someKey';

        $attributes = [
            'path' => $path,
        ];

        $this->user = User::factory()->create();

        $this->assertDatabaseMissing(
            Draft::TABLE_NAME,
            $attributes + ['user_id' => $this->user->id]
        );

        $this->service->createOrUpdate($this->user, $path, $body);

        $this->assertDatabaseHas(
            Draft::TABLE_NAME,
            $attributes + ['user_id' => $this->user->id]
        );
    }
}
