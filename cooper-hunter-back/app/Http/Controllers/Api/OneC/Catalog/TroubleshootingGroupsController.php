<?php

namespace App\Http\Controllers\Api\OneC\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OneC\Catalog\TroubleshootingGroups\TroubleshootingGroupResource;
use App\Models\Catalog\Troubleshoots\Group;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Troubleshooting Groups
 */
class TroubleshootingGroupsController extends Controller
{
    /**
     * List
     *
     * @bodyParam page int
     * @bodyParam per_page int
     *
     * @responseFile docs/api/troubleshooting_groups/list.json
     */
    public function index(): AnonymousResourceCollection
    {
        return TroubleshootingGroupResource::collection(
            Group::query()
                ->with('translation')
                ->paginate()
        );
    }
}
