<?php

namespace App\GraphQL\Mutations\Page;

use App\DTO\Media\FileDTO;
use App\Exceptions\ErrorsCode;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Media\File;
use App\Models\Page\Page;
use App\Repositories\Page\PageRepository;
use App\Services\Media\UploadService;
use App\Services\Page\PageService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class PageAttachFile extends BaseGraphQL
{
    public function __construct(
        private UploadService $uploadService,
        protected PageService $service,
        protected PageRepository $repository,
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return File
     */
    public function __invoke($_, array $args): File
    {
        /** @var $user Admin */
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            /** @var $model Page */
            $model = $this->repository->findByID($args['id']);
            if(null !== $model->file){
                throw new \DomainException(__('error.page have file'), ErrorsCode::BAD_REQUEST);
            }

            $args['modelId'] = $args['id'];
            $args['model'] = File::MODEL_PAGE;
            $args['type'] = Page::FILE_PDF_TYPE;

            $dto = FileDTO::byArgs($args);
            $this->uploadService->uploadFile($dto);

            $model->refresh();

            // @todo dev-telegram
            TelegramDev::info("Админ привязал файл к странице ({$model->alias})", $user->name);

            return $model->file;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}


