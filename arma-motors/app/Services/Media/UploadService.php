<?php

namespace App\Services\Media;

use App\DTO\Media\FileDTO;
use App\Repositories\Media\ImageRepository;
use App\Services\Media\File\FileService;
use App\Services\Media\Image\ImageService;
use DB;
use App\DTO\Media\ImageDTO;
use App\Models\Media\Image;
use App\Models\Media\File;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class UploadService
{
    public function __construct(
        protected ImageService $imageService,
        protected ImageRepository $imageRepository,
        protected FileService $fileService
    )
    {}

    public function uploadImages(ImageDTO $dto): void
    {
        DB::beginTransaction();
        try {
            foreach ($dto->getImages() as $key => $file){
                /** @var \Illuminate\Http\UploadedFile $file */
                $model = $this->imageService->createImageRecord($file, $dto);
                $this->imageService->storeImage($model, $file, $dto->getSizes());
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function uploadAndDeleteImage(ImageDTO $dto): Image
    {
        DB::beginTransaction();
        try {
            // если есть картинка, то удаляем ее
            $exist = $this->imageRepository->getByModeAndId($dto->getModel(), $dto->getModelClass(), $dto->getModelId(), $dto->getType());
            if($exist->isNotEmpty()){
                $this->removes($exist);
            }

            $model = $this->imageService->createImageRecord($dto->getImage(), $dto);
            $this->imageService->storeImage($model, $dto->getImage(), $dto->getSizes());

            DB::commit();

            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * удаление всех картинок у модели
     * @param Model $model
     */
    public function removeAllImageAtModel(Model $model): void
    {
        $model->load(['images']);
        foreach($model->images as $img){
            $this->removeImage($img);
        }
    }

    /**
     * удаление всех картинок у модели
     * @param Model $model
     */
    public function removeAllFileAtModel(Model $model): void
    {
        $model->load(['files']);
        foreach($model->files as $file){
            $this->removeFile($file);
        }
    }

    /**
     * удаление переданых картинок
     * @param Collection $collection
     */
    public function removes(Collection $collection)
    {
        foreach($collection as $img){
            $this->removeImage($img);
        }
    }

    /**
     * удаление переданых картинок
     * @param Collection $collection
     */
    public function removesFile(Collection $collection)
    {
        foreach($collection as $file){
            $this->removeFile($file);
        }
    }

    /**
     * @param Image $image
     */
    public function removeImage(Image $image): void
    {
        $this->imageService->deleteImage($image);
    }

    /**
     * @param File $file
     */
    public function removeFile(File $file): void
    {
        $this->fileService->deleteFile($file);
    }

    public function uploadFile(FileDTO $dto): void
    {
        DB::beginTransaction();
        try {
            /** @var \Illuminate\Http\UploadedFile $file */
            $model = $this->fileService->createFileRecord($dto->getFile(), $dto);
            $this->fileService->storeFile($model, $dto->getFile());

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}



