<?php

namespace WezomCms\Cars\Http\Controllers\Api\V1;

use WezomCms\Cars\DTO\EngineTypeListDto;
use WezomCms\Cars\DTO\EngineTypeListFor1CDto;
use WezomCms\Cars\Repositories\EngineTypeRepository;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\TelegramBot\Telegram;

class EngineTypeController extends ApiController
{
    private EngineTypeRepository $engineTypeRepository;
    private EngineTypeListDto $engineTypeListDto;

    public function __construct(EngineTypeRepository $engineTypeRepository)
    {
        parent::__construct();

        $this->engineTypeRepository = $engineTypeRepository;
        $this->engineTypeListDto = resolve(EngineTypeListDto::class);
    }

    public function list()
    {
        try {

            $engineTypes = $this->engineTypeRepository->getAll(['translations'], 'id', [], false, 'desc');

            return $this->successJsonMessage(
                $this->engineTypeListDto->setCollection($engineTypes)->toList()
            );
        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    public function listFor1C()
    {
        try {
            Telegram::event('От 1с пришел запрос на СПИСОК ТИПОВ ДВИГАТЕЛЕЙ');

            $types = $this->engineTypeRepository->getAll(['translations'], 'id', [], false);

            $dto = \App::make(EngineTypeListFor1CDto::class)
                ->setCollection($types);

            return $this->successJsonCustomMessage(['success' => true, 'data' => $dto->toList()], 200);

        } catch(\Exception $exception){
            return $this->successJsonCustomMessage(['success' => false, 'message' => $exception->getMessage()], 500);
        }
    }
}
