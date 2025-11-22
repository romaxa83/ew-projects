<?php

namespace App\GraphQL\Mutations\Catalog\Calc;

use App\DTO\Catalog\Calc\MileageDTO;
use App\DTO\Media\FileDTO;
use App\Exceptions\ErrorsCode;
use App\GraphQL\BaseGraphQL;
use App\Imports\Spares\SparesImportManager;
use App\Jobs\ImportSpares;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Calc\Mileage;
use App\Models\Catalogs\Calc\SparesDownloadFile;
use App\Models\Media\File;
use App\Services\Catalog\Calc\MileageService;
use App\Services\Media\UploadService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;
use PhpOffice\PhpSpreadsheet\Reader\Xls\ErrorCode;

class SparesDownload extends BaseGraphQL
{
    public function __construct(
        private UploadService $uploadService,
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return array
     */
    public function __invoke($_, array $args): array
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {

            if(!$args['file']){
                throw new \InvalidArgumentException(__('error.file empty'), ErrorsCode::BAD_REQUEST);
            }
            if($args['file'][0]->getError() !== 0){
                throw new \InvalidArgumentException(__('error.file upload with error'), ErrorsCode::BAD_REQUEST);
            }

            $record = SparesDownloadFile::createRecord(
                $args['type'],
                $args['file'][0]->getFilename(),
            );

            $args['model'] = File::MODEL_SPARES;
            $args['modelId'] = $record->id;

            $dto = FileDTO::byArgs($args);
            $this->uploadService->uploadFile($dto);

            // @todo dev-telegram
            TelegramDev::info("Создана запись и загружен файл для импорта запчастей", $user->name);

            $record->refresh();
            dispatch(new ImportSpares($record));

            return $this->successResponse(__('message.calc.file with spares download'));
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

