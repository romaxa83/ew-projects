<?php

namespace WezomCms\Translates\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Core\Models\Translation;
use WezomCms\Translates\DTO\TranslatesDto;
use WezomCms\Translates\Repositories\TranslateRepository;
use WezomCms\Translates\Services\TranslatesService;
use WezomCms\Translates\UseCase\TranslateHash;

class TranslatesController extends ApiController
{
    private TranslatesService $translatesService;
    private TranslateRepository $translateRepository;

    public function __construct(
        TranslatesService $translatesService,
        TranslateRepository $translateRepository
    )
    {
        parent::__construct();

        $this->translatesService = $translatesService;
        $this->translateRepository = $translateRepository;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTranslates()
    {
        try {
            return $this->successJsonMessage(
                (new TranslatesDto($this->translateRepository->getTranslates()))->toArray()
            );

        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setTranslates(Request $request)
    {
        try {
            $this->translatesService->fillOrUpdate($request->all());

            return $this->successJsonMessage(
                TranslateHash::hash($this->translateRepository->getTranslates())
            );

        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHash()
    {
        try {
            return $this->successJsonMessage(
                TranslateHash::hash($this->translateRepository->getTranslates())
            );

        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    public function removeTranslate($key)
    {
        try {

            $translates = $this->translateRepository->getTranslateByKey($key);
            $this->translatesService->remove($translates);

            return $this->successJsonMessage(
                TranslateHash::hash($this->translateRepository->getTranslates())
            );

        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    public function removeAllTranslate()
    {
        try {

            $translates = $this->translateRepository->getTranslatesByNamespace(Translation::API_NAMESPACE);
            $this->translatesService->remove($translates);

            return $this->successJsonMessage(
                TranslateHash::hash($this->translateRepository->getTranslates())
            );

        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }
}
