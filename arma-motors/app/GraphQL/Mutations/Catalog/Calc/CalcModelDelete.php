<?php

namespace App\GraphQL\Mutations\Catalog\Calc;

use App\DTO\Catalog\Calc\Model\CalcModelDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Calc\CalcModel;
use App\Repositories\Catalog\Calc\CalcModelRepository;
use App\Services\Catalog\Calc\CalcModelService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class CalcModelDelete extends BaseGraphQL
{
    public function __construct(
        protected CalcModelService $service,
        protected CalcModelRepository $repository
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

            $this->service->delete(
                $this->repository->findByID($args['id'])
            );

            // @todo dev-telegram
            TelegramDev::info("модель для калькулятора удалена", $user->name);

            return $this->successResponse(__('message.calc model deleted'));
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

