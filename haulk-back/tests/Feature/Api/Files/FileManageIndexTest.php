<?php

namespace Tests\Feature\Api\Files;

use App\Models\Files\File;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FileManageIndexTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var string
     */
    protected $route;

    public function setUp(): void
    {
        parent::setUp();

        $this->route = route('files.list');
    }

    public function test_it_show_only_authenticated_users()
    {
        $this->getJson($this->route)
            ->assertStatus(401);
    }

    public function test_it_show_only_permitted_users()
    {
        self::markTestSkipped();
        factory(File::class, 10)->create();

        $this->loginAsCarrierDispatcher();
        $this->getJson($this->route)
            ->assertStatus(403);
    }

    public function test_it_show_media_list()
    {
        File::factory()->count(10)->create();

        $this->loginAsCarrierSuperAdmin();
        $response = $this->getJson($this->route)
            ->assertOk()
            ->assertJsonStructure(['data' => []]);

        $content = json_to_array($response->content());
        $this->assertCount(10, $content['data']);
    }
}
