<?php

namespace WezomCms\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use WezomCms\Core\Image\ImageService;

class ReCropImagesCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:re-crop
                              {model : Class name}
                              {--field=image : Model database field and configuration key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-crop images from original file for each size';

    /**
     * @var ImageService
     */
    private $imageService;

    /**
     * ReCropImagesCommand constructor.
     * @param  ImageService  $imageService
     */
    public function __construct(ImageService $imageService)
    {
        parent::__construct();

        $this->imageService = $imageService;
    }

    /**
     * Execute the console command.
     * @throws \Throwable
     */
    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $class = $this->argument('model');

        if (!class_exists($class)) {
            $this->error(sprintf('Class [%s] does not exists', $class));
            return;
        }

        $model = new $class();
        if (!method_exists($model, 'imageSettings')) {
            $this->error(sprintf('Model [%s] does not support image attachable trait', $class));
            return;
        }

        $field = (string)$this->option('field');

        if (!array_key_exists($field, $model->imageSettings())) {
            $this->error(sprintf('Model [%s] does not have image field: %s', $class, $field));
            return;
        }

        if ($this->imageService->reCropImages($model, $field, $this->output)) {
            $this->info('Images have been re cropped');
        } else {
            $this->warn('No images for re crop');
        }
    }
}
