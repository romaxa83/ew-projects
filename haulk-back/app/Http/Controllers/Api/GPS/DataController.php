<?php

namespace App\Http\Controllers\Api\GPS;

use App\Http\Controllers\Controller;
use App\Http\Requests\GPS\GPSDataRequest;
use App\Services\GPS\GPSDataService;

class DataController extends Controller
{
    protected GPSDataService $service;

    public function __construct(GPSDataService $service)
    {
        $this->service = $service;
    }

    /**
     * @param GPSDataRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function receiveData(GPSDataRequest $request)
    {
        foreach ($request->getDtos() as $dto){
            $this->service->createMessage($dto);
        }

        return response('');
    }
}
