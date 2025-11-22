<?php

namespace App\GraphQL\Mutations\Media;

use App\GraphQL\BaseGraphQL;
use App\Repositories\Media\FileRepository;
use App\Services\Media\UploadService;
use App\Services\Telegram\TelegramDev;
use App\Traits\HashData;

class FilesDelete extends BaseGraphQL
{
    use HashData;

    public function __construct(
        private UploadService $uploadService,
        private FileRepository $repository,
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
            $files = $this->repository->getRowsByIds($args['ids']);

            $this->uploadService->removesFile($files);

            return $this->successResponse(__('message.files remove success'));
        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
