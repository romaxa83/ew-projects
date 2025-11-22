<?php

namespace Tests\Traits\Storage;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait TestStorage
{
    protected function fakeMediaStorage(): void
    {
        Storage::fake();
        Storage::fake(config('media-library.disk_name'));
    }

    /**
     * @throws FileNotFoundException
     */
    protected function getSamplePdf(): File
    {
        return UploadedFile::fake()->create('test.pdf', $this->getTestingDisk()->get('c_h_logo.pdf'));
    }

    /**
     * @throws FileNotFoundException
     */
    protected function getSampleImage(string $name = 'test.png'): File
    {
        return UploadedFile::fake()->create($name, $this->getTestingDisk()->get('logo.png'));
    }

    protected function getTestingDisk(): Filesystem
    {
        return Storage::build(
            [
                'driver' => 'local',
                'root' => storage_path('testing')
            ]
        );
    }
}
