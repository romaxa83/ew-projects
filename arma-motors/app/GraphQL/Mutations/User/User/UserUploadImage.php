<?php


namespace App\GraphQL\Mutations\User\User;

use App\DTO\Media\ImageDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\User\User;
use App\Services\Media\UploadService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;
use Illuminate\Database\Eloquent\Collection;

class UserUploadImage extends BaseGraphQL
{
    public function __construct(
        private UploadService $uploadService,
    )
    {}

    /**
     * @param null $_
     * @param array<string, mixed> $args
     *
     * @return Collection
     * @throws Error
     *
     */
    public function __invoke($_, array $args): Collection
    {
        $guard = \Auth::guard(User::GUARD);
        try {
            $args['model'] = 'user';
            $args['modelId'] = $guard->user()->id;

            $dto = ImageDTO::byArgs($args);

            $this->uploadService->uploadImages($dto);

            return $guard->user()->imagesByType($dto->getType())->get();
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

