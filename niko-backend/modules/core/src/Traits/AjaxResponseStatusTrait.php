<?php

namespace WezomCms\Core\Traits;

use Illuminate\Http\JsonResponse;

trait AjaxResponseStatusTrait
{
    /**
     * @param  string|array|null  $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error($data = null)
    {
        if (false === is_array($data)) {
            if ($data) {
                $data = ['message' => $data];
            } else {
                $data = [];
            }
        }

        $data['success'] = false;

        return JsonResponse::create($data);
    }

    /**
     * @param  string|array|null  $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success($data = null)
    {
        if (false === is_array($data)) {
            if ($data) {
                $data = ['message' => $data];
            } else {
                $data = [];
            }
        }

        $data['success'] = true;

        return JsonResponse::create($data);
    }
}
