<?php

namespace WezomCms\Cars\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use WezomCms\Cars\DTO\BrandListDto;
use WezomCms\Cars\Http\Requests\Api\BrandSyncRequest;
use WezomCms\Cars\Models\Brand;
use WezomCms\Cars\Repositories\CarBrandRepository;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\TelegramBot\Telegram;

class BrandController extends ApiController
{
    private CarBrandRepository $brandRepository;

    public function __construct(CarBrandRepository $brandRepository)
    {
        parent::__construct();

        $this->brandRepository = $brandRepository;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        try {
            $brands = $this->brandRepository->getAll(['dealership'], 'sort', $request->all());
            $dto = resolve(BrandListDto::class)->setCollection($brands);

            return $this->successJsonMessage($dto->toList());
        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    public function sync(BrandSyncRequest $request)
    {
        try {
            Telegram::event('От 1с пришел запрос на СИСНХРОНИЗАЦИЮ БРЕНДОВ');
            //Telegram::event(serialize($request['Data']));

            $data = [];
            foreach($request['Data'] as $key => $item){
                $data[$key]['niko_id'] = $item['BrandID'];
                $data[$key]['name'] = $item['BrandName'];
            }
            array_values($data);


            $columns = [
                'niko_id', 'name'
            ];
            $result = Brand::insertOnDuplicateKey($data, $columns);

            return $this->successJsonCustomMessage(['success' => true, 'data' => 'brand sync'], 200);

        } catch(\Exception $exception){
            return $this->successJsonCustomMessage(['success' => false, 'message' => $exception->getMessage()], 500);
        }
    }
}
