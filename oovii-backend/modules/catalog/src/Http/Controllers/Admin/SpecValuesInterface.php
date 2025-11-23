<?php

namespace WezomCms\Catalog\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface SpecValuesInterface
{
    /**
     * Get all elements list and locales.
     *
     * @param $specificationId
     * @param  Request  $request
     * @return JsonResponse
     */
    public function list($specificationId, Request $request): JsonResponse;

    /**
     * Create new specification value.
     *
     * @param $specificationId
     * @param  Request  $request
     * @return JsonResponse
     */
    public function create($specificationId, Request $request): JsonResponse;

    /**
     * Save all specification.
     *
     * @param $specificationId
     * @param  Request  $request
     * @return JsonResponse
     */
    public function save($specificationId, Request $request): JsonResponse;
}
