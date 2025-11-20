<?php

namespace WezomCms\Cars\Http\Controllers\Api\V1;

use WezomCms\Cars\DTO\TransmissionListDto;
use WezomCms\Cars\DTO\TransmissionListFor1CDto;
use WezomCms\Cars\Repositories\TransmissionRepository;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\TelegramBot\Events\TelegramDev;
use WezomCms\TelegramBot\Telegram;

class TransmissionController extends ApiController
{
    private TransmissionRepository $transmissionRepository;
    private TransmissionListDto $transmissionListDto;

    public function __construct(TransmissionRepository $transmissionRepository)
    {
        parent::__construct();

        $this->transmissionRepository = $transmissionRepository;
        $this->transmissionListDto = resolve(TransmissionListDto::class);
    }

    public function list()
    {
        try {
            $transmissions = $this->transmissionRepository->getAll(['translations'], 'id', [], false, 'desc');

            return $this->successJsonMessage(
                $this->transmissionListDto->setCollection($transmissions)->toList()
            );
        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    public function listFor1C()
    {
        try {
            Telegram::event('От 1с пришел запрос на СПИСОК ТРАНСМИССИЙ');

            $transmissions = $this->transmissionRepository->getAll(['translations'], 'id', [], false);

            $dto = \App::make(TransmissionListFor1CDto::class)
                ->setCollection($transmissions);

            return $this->successJsonCustomMessage(['success' => true, 'data' => $dto->toList()], 200);

        } catch(\Exception $exception){
            return $this->successJsonCustomMessage(['success' => false, 'message' => $exception->getMessage()], 500);
        }
    }
}
