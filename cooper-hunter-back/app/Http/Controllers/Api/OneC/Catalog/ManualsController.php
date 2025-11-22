<?php

namespace App\Http\Controllers\Api\OneC\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OneC\Catalog\Manuals\ManualResource;
use App\Models\Catalog\Manuals\Manual;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Manuals
 */
class ManualsController extends Controller
{
    /**
     * List
     *
     * @bodyParam page int
     * @bodyParam per_page int
     * @responseFile docs/api/manuals/list.json
     */
    public function index(): AnonymousResourceCollection
    {
        return ManualResource::collection(
            Manual::query()
                ->with('group.translation')
                ->paginate()
        );
    }
}
