<?php

namespace Tests\Feature\Http\Api\V1\Common\FileBrowser;

use App\Console\Commands\Storage\CreateAppRoot;
use App\Console\Commands\Storage\CreateFileBrowserRoot;
use App\Models\Users\User;
use App\Services\FileBrowser\FileBrowserStorage;
use Artisan;
use Cache;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Storage;
use Tests\TestCase;

class AbstractFileBrowserTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var FileBrowserStorage
     */
    protected $fileBrowser;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::clear();

        if (is_testing()) {
            $paths = config('filebrowser.root');
            Storage::deleteDirectory($paths);
        }

        Artisan::call(CreateAppRoot::class);
        Artisan::call(CreateFileBrowserRoot::class);

        $this->fileBrowser = resolve(FileBrowserStorage::class);
    }

    protected function setFileBrowserPrefixForUser(User $user): void
    {
        $prefix = $user->getFileBrowserPrefix();
        $this->fileBrowser->setFileBrowserPrefix($prefix);
    }

    public function test_it_true(): void
    {
        $this->assertTrue(true);
    }
}
