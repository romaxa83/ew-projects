<?php

namespace App\Services\Media\Image;

use App\DTO\Media\ImageDTO;
use App\Models\Media\Image as ImageModel;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;

use Intervention\Image\ImageManager;

class ImageService
{
    public const ORIGINAL = 'original';

    private string $defaultDisk;
    private string $defaultOriginalDisk;
    private FilesystemAdapter $storage;

    public function __construct()
    {
        $this->defaultDisk = config('image.storage');
        $this->defaultOriginalDisk = config('image.original_storage');

        $this->storage = \Storage::disk($this->defaultDisk);
    }

    public function getStorage(): FilesystemAdapter
    {
        return $this->storage;
    }

    public function createImageRecord(UploadedFile $file, ImageDTO $dto): ImageModel
    {
        $image = new ImageModel();

        $image->entity_type = $dto->getModelClass();
        $image->entity_id = $dto->getModelId();
        $image->model = $dto->getModel();
        $image->type = $dto->getType();
        $image->basename = $file->getFilename();
        $image->hash = $file->hashName();
        $image->mime = $file->getClientMimeType();
        $image->ext = $file->getClientOriginalExtension();
        $image->size = $file->getSize();

        $image->save();

        return $image;
    }

    public function storeImage(ImageModel $model, UploadedFile $file, array $settings = []): void
    {
        $manager = new ImageManager(array('driver' => 'imagick'));
//        $manager = new ImageManager(array('driver' => 'gd'));

        $dir = "{$model->model}/{$model->entity_id}";

        foreach ($settings as $size => $item){
            $sizeDir = "{$dir}/{$size}";

            $this->getStorage()->makeDirectory($sizeDir);

            ImageHandler::make($manager->make($file), $this->getStorage())
                ->orientate()
                ->modify($item)
                ->save("{$sizeDir}/{$file->hashName()}");
        }

        $sizeDir = "{$dir}/" . self::ORIGINAL;

        ImageHandler::make($manager->make($file), $this->getStorage())
            ->forcedOriental()
            ->save("{$sizeDir}/{$file->hashName()}");
    }

    public function deleteImage(ImageModel $model): void
    {
        // удаляем файлы
        foreach ($model->sizes as $item){
            if(file_exists($model->pathToFileStorage($item['size']))){
                unlink($model->pathToFileStorage($item['size']));
                // если папка пуста , удаляем ее
                if(!glob("{$model->pathToFolderStorageSize($item['size'])}*")){
                    rmdir($model->pathToFolderStorageSize($item['size']));
                }
                if(!glob("{$model->pathToFolderStorage()}*")){
                    rmdir($model->pathToFolderStorage());
                }
            }
        }

        // удаляем модель
        $model->forceDelete();
    }
}

