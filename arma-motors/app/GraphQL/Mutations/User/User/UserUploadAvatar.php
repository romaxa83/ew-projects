<?php


namespace App\GraphQL\Mutations\User\User;

use App\DTO\Media\ImageDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Media\Image;
use App\Models\User\User;
use App\Services\Media\UploadService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;
use Illuminate\Database\Eloquent\Collection;

class UserUploadAvatar extends BaseGraphQL
{
    public function __construct(
        private UploadService $uploadService,
    )
    {}

    /**
     * @param null $_
     * @param array<string, mixed> $args
     *
     * @return Image
     * @throws Error
     *
     */
    public function __invoke($_, array $args): Image
    {
        /** @var $user User */
        $user = \Auth::guard(User::GUARD)->user();
        try {
            $args['model'] = Image::MODEL_USER;
            $args['type'] = User::IMAGE_AVATAR_TYPE;
            $args['modelId'] = $user->id;

            $dto = ImageDTO::byArgs($args);

            // если уже есть аватарка, удаляем старую
            if($user->avatar){
                $this->uploadService->removeImage($user->avatar);
            }

            $this->uploadService->uploadImages($dto);

            $user->refresh();

            return $user->avatar;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

