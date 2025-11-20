<?php

namespace WezomCms\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use WezomCms\Core\Image\ImageService;

class DeleteLostImagesCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:delete-lost
                              {model : Class name}
                              {--field=image : Model database field and configuration key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all images in the file system that are not in the database';

    /**
     * @var ImageService
     */
    private $imageService;

    /**
     * Create a new command instance.
     *
     * @param  ImageService  $imageService
     */
    public function __construct(ImageService $imageService)
    {
        parent::__construct();

        $this->imageService = $imageService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
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

        if ($this->imageService->deleteLostImages($model, $field)) {
            $this->info('Lost images have been deleted');
        }
    }
}
