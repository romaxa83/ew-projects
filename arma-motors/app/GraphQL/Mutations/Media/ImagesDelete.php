<?php

namespace App\GraphQL\Mutations\Media;

use App\GraphQL\BaseGraphQL;
use App\Repositories\Media\ImageRepository;
use App\Services\Media\UploadService;
use App\Services\Telegram\TelegramDev;
use App\Traits\HashData;

class ImagesDelete extends BaseGraphQL
{
    use HashData;

    public function __construct(
        private UploadService $uploadService,
        private ImageRepository $imageRepository,
    )
    {}

    /**
     * Upload user's image and save to storage
     *
     * @param null                 $_
     * @param array<string, mixed> $args
     *
     */
    public function __invoke($_, array $args)
    {
        try {
            $images = $this->imageRepository->getRowsByIds($args['ids']);

            $modelName = $images[0]->model ?? null;

            $this->uploadService->removes($images);

            if($modelName){
                // кидаем event, для перезаписи хеша данных, если нужная модель
                $this->throwEventForImage($modelName);
            }

            return $this->successResponse(__('message.images remove success'));
        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
