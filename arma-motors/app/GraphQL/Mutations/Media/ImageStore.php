<?php

namespace App\GraphQL\Mutations\Media;

use App\DTO\Media\ImageDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Media\Image;
use App\Repositories\Media\ImageRepository;
use App\Services\Media\UploadService;
use App\Services\Telegram\TelegramDev;
use App\Traits\HashData;

class ImageStore extends BaseGraphQL
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
    public function __invoke($_, array $args): Image
    {
        try {
            $dto = ImageDTO::byArgs($args);

            $image = $this->uploadService->uploadAndDeleteImage($dto);

            // кидаем event, для перезаписи хеша данных, если нужная модель
            $this->throwEventForImage($dto->getModel());

            return $image;

        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
