<?php

namespace WezomCms\Core\Commands;

use DirectoryIterator;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\Exception\NotSupportedException;
use Intervention\Image\Exception\NotWritableException;
use WezomCms\Core\Image\ImageService;

class MakeWebPCommand extends Command
{
    /**
     * Allowed for webp converting image extensions.
     */
    protected const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:make-webp
        {--path=storage/app/public : The path to the image directory. Relative to the root of the project.}
        {--quality=100 : WebP image saving quality.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate webp version for images without webp';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = base_path(trim($this->option('path'), '/\\'));
        $iterator = new DirectoryIterator($path);
        try {
            $this->generateWebP($iterator);
        } catch (NotSupportedException $e) {
            $this->error($e->getMessage());
            return;
        }
        $this->info('Done');
    }

    /**
     * @param  DirectoryIterator  $iterator
     */
    protected function generateWebP(DirectoryIterator $iterator)
    {
        foreach ($iterator as $file) {
            if ($file->isDot()) {
                continue;
            } elseif ($file->isDir()) {
                if (Str::endsWith($file->getPathname(), DIRECTORY_SEPARATOR . ImageService::ORIGINAL)) {
                    $this->warn('Skip original dir: ' . $this->removeBasePath($file->getPathname()));
                    continue;
                }

                $this->info('Processing dir: ' . $this->removeBasePath($file->getPathname()));
                $this->generateWebP(new DirectoryIterator($file->getPathname()));
            } elseif ($file->isFile() && $this->extensionIsAllowed($file->getExtension())) {
                $this->addWebPCopy($file->getPathname());
            }
        }
    }

    /**
     * @param  string  $ext
     * @return bool
     */
    protected function extensionIsAllowed(string $ext): bool
    {
        return in_array(strtolower($ext), self::ALLOWED_EXTENSIONS);
    }

    /**
     * @param  string  $filePath
     */
    protected function addWebPCopy(string $filePath)
    {
        $webPFile = $filePath . '.webp';

        if (file_exists($webPFile)) {
            return;
        }

        try {
            $image = \Image::make($filePath);
            $image->save($webPFile, (int)$this->option('quality'), 'webp');
        } catch (NotReadableException $e) {
            $this->error("Error: check that file [{$filePath}] is correct image");
        } catch (NotWritableException $e) {
            $this->error("Error: can not write image to file [{$webPFile}]");
        }
    }

    /**
     * @param  string  $path
     * @return string
     */
    protected function removeBasePath(string $path): string
    {
        return Str::after($path, base_path());
    }
}
