<?php

namespace App\GraphQL\Mutations\Page;

use App\DTO\Page\PageEditDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Page\Page;
use App\Repositories\Page\PageRepository;
use App\Services\Page\PageService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class PageEdit extends BaseGraphQL
{
    public function __construct(
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
     * @return Page
     */
    public function __invoke($_, array $args): Page
    {
        /** @var $user Admin */
        $user = \Auth::guard(Admin::GUARD)->user();
        try {

            $model = $this->service->edit(
                PageEditDTO::byArgs($args),
                $this->repository->findByID($args['id'])
            );
            // @todo dev-telegram
            TelegramDev::info("Админ отредактировал инф. страницу ({$model->alias})", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}


