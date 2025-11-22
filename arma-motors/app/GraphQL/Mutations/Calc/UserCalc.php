<?php

namespace App\GraphQL\Mutations\Calc;

use App\DTO\Catalog\Calc\Model\CalcModelDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\User\User;
use App\Repositories\Catalog\Calc\CalcModelRepository;
use App\Repositories\Catalog\Car\BrandRepository;
use App\Services\Telegram\TelegramDev;
use App\Traits\Validations\CalcValidate;
use GraphQL\Error\Error;

class UserCalc extends BaseGraphQL
{
    use CalcValidate;

    public function __construct(
        protected CalcModelRepository $repository,
        protected BrandRepository $brandRepository
    ){}

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
        $user = \Auth::guard(User::GUARD)->user();
        try {
            $result = [];

            $brand = $this->brandRepository->findByID($args['brandId']);
            $this->validate($args, $brand);

            $dto = CalcModelDTO::byArgs($args, $brand);

            if($model = $this->repository->getModelByDTO($dto)){
                $result = $model->runCalc();
            }

            // @todo dev-telegram
            TelegramDev::info("📝💵 Запрос на расчет стоимости ТО", $user->name ?? null, TelegramDev::LEVEL_IMPORTANT);
            TelegramDev::info(serialize($result), $user->name ?? null);

            return $result;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name ?? null, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}


