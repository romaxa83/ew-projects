<?php

namespace WezomCms\Cars\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use WezomCms\Cars\DTO\ModelBrandListDto;
use WezomCms\Cars\DTO\ModelListDto;
use WezomCms\Cars\Http\Requests\Api\ModelSyncRequest;
use WezomCms\Cars\Models\Model;
use WezomCms\Cars\Repositories\CarBrandRepository;
use WezomCms\Cars\Repositories\ModelRepository;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\TelegramBot\Telegram;

class ModelController extends ApiController
{
    private ModelListDto $modelListDto;
    private ModelRepository $modelRepository;
    private CarBrandRepository $carBrandRepository;

    public function __construct(
        ModelRepository $modelRepository,
        CarBrandRepository $carBrandRepository
    )
    {
        parent::__construct();

        $this->modelListDto = resolve(ModelListDto::class);
        $this->modelRepository = $modelRepository;
        $this->carBrandRepository = $carBrandRepository;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forBrand(Request $request, $brandId)
    {
        try {
            $brand = $this->carBrandRepository->byId($brandId,[],'id',false);
            $models = $this->modelRepository->getAllByBrand($brand->niko_id,[], 'id', $request->all());

            return $this->successJsonMessage(
                $this->modelListDto->setCollection($models)->toList()
            );
        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    public function sync(ModelSyncRequest $request)
    {
        try {
            Telegram::event('От 1с пришел запрос на СИСНХРОНИЗАЦИЮ МОДЕЛЕЙ');
            //Telegram::event(serialize($request['Data']));

                $data = [];
                foreach($request['Data'] as $key => $item){
                    $data[$key]['niko_id'] = $item['ModelID'];
                    $data[$key]['name'] = $item['ModelName'];
                    $data[$key]['car_brand_id'] = $item['BrandID'];
                }

                array_values($data);

                $columns = [
                    'niko_id', 'name', 'car_brand_id'
                ];
                $result = Model::insertOnDuplicateKey($data, $columns);

            return $this->successJsonCustomMessage(['success' => true, 'data' => 'brand sync'], 200);

        } catch(\Exception $exception){
            return $this->successJsonCustomMessage(['success' => false, 'message' => $exception->getMessage()], 500);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function actualModel()
    {
        try {
            $models = $this->modelRepository->getAllForTrade([
                'brand'
            ], 'id');

            Telegram::event("От 1с пришел запрос на АКТУАЛЬНЫЕ МОДЕЛИ - ({$models->count()})");

            return $this->successJsonMessage(
                resolve(ModelBrandListDto::class)->setCollection($models)->toList()
            );
        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }
}
