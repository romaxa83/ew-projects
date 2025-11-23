<?php

namespace WezomCms\Core\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use WezomCms\Core\Contracts\AdminPageNameInterface;
use WezomCms\Core\Http\Controllers\SiteController;

class NotFoundController extends SiteController
{
    /**
     * @param  Request  $request
     * @param  AdminPageNameInterface  $pageName
     * @return JsonResponse|Response
     */
    public function __invoke(Request $request, AdminPageNameInterface $pageName)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => __('cms-core::admin.Page not found')], 404);
        } else {
            $pageName->setPageName(__('cms-core::admin.Page not found'));

            return response()->view('cms-core::admin.errors.404', [], 404);
        }
    }
}
