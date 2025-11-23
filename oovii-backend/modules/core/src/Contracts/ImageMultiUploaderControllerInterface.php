<?php

namespace WezomCms\Core\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface ImageMultiUploaderControllerInterface
{
    /**
     * @return Model
     */
    public function getModel(): Model;

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse;

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getUploadedImages(Request $request): JsonResponse;

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse;

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function setAsDefault(Request $request): JsonResponse;

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function sort(Request $request): JsonResponse;

    /**
     * @param $id
     * @param  Request  $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function renameForm($id, Request $request);

    /**
     * @param $id
     * @param  Request  $request
     * @return JsonResponse
     */
    public function rename($id, Request $request): JsonResponse;
}
