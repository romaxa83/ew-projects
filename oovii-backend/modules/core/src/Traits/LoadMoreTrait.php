<?php

namespace WezomCms\Core\Traits;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

trait LoadMoreTrait
{
    /**
     * @param  Paginator  $paginatedCollection
     * @param  callable  $itemsRender
     * @param  callable  $fullPageRender
     * @return View|Factory|string|mixed
     */
    public function viewLoadMore(Paginator $paginatedCollection, callable $itemsRender, callable $fullPageRender)
    {
        $request = request();

        if (!$request->expectsJson() || !$request->hasHeader('Load-More')) {
            return $fullPageRender();
        }

        $items = $itemsRender();

        if ($items instanceof Renderable) {
            $items = $items->render();
        }

        $data = [
            'items' => $items,
            'nextPageUrl' => $paginatedCollection->nextPageUrl(),
        ];

        if ($request->hasHeader('With-Paginator')) {
            $data['paginator'] = $paginatedCollection->render();
        }

        return response()->json($data);
    }
}
